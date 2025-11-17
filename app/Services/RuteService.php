<?php

namespace App\Services;

use App\Models\Rute;
use App\Models\RutePrediksiCuaca;
use App\Models\Point;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RuteService
{
    private $endpoint = 'https://api.open-meteo.com/v1/forecast';
    private $params = 'hourly=temperature_2m,weather_code,relative_humidity_2m,wind_speed_10m,wind_direction_10m&timezone=auto';

    /**
     * CUACA
     */
    public function prediksiCuacaFetch(Point $point)
    {
        $prediksiCuaca = RutePrediksiCuaca::where(['rute_id' => $point->rute_id])->first();
        $needUpdate = !$prediksiCuaca || $prediksiCuaca->result === null || Carbon::parse($prediksiCuaca->updated_at)->diffInHours(Carbon::now(), true) >= 4;

        $url = "{$this->endpoint}?latitude={$point->lat}&longitude={$point->long}&{$this->params}";

        try {
            $res = Http::get($url);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                dd($e);
            }
            abort(500);
        }

        $result = $res->failed() ? null : $res->json();

        if ($needUpdate) {
            RutePrediksiCuaca::where(['rute_id' => $point->rute_id])->delete();
            $prediksiCuaca = RutePrediksiCuaca::create([
                'rute_id' => $point->rute_id,
                'result' => $result,
            ]);
        }

        return $prediksiCuaca;
    }

    public function formatHourlyData($result)
    {
        if (!$result || !isset($result['hourly'])) {
            return collect([]);
        }

        $hourly = $result['hourly'];
        $formattedData = collect();

        $timeCount = count($hourly['time']);

        for ($i = 0; $i < $timeCount; $i++) {
            $datetime = Carbon::parse($hourly['time'][$i]);

            $weatherObject = (object) [
                'datetime'            => $hourly['time'][$i],
                'date'                => $datetime->translatedFormat('j F'),
                'time'                => $datetime->translatedFormat('H:i'),
                'day'                 => $datetime->translatedFormat('l'),
                'weather'             => $hourly['weather_code'][$i],
                'weather_description' => $this->kondisiCuacaTerdekat($hourly['weather_code'][$i], $datetime, 'message'),
                'temperature'         => $hourly['temperature_2m'][$i],
                'wind_speed'          => $hourly['wind_speed_10m'][$i],
                'wind_direction'      => $hourly['wind_direction_10m'][$i],
                'humidity'            => $hourly['relative_humidity_2m'][$i],
                'image'               => asset('img/cuaca/' . $this->kondisiCuacaTerdekat($hourly['weather_code'][$i], $datetime, 'value') . '.svg')
            ];

            $formattedData->push($weatherObject);
        }

        return $formattedData;
    }

    public function kondisiCuacaTerdekat($code, $datetime, $key = null)
    {
        $weather_conditions = [
            [
                'code' => 0,
                'value' => 'clear',
                'message' => 'Cerah',
            ],
            [
                'code' => 1,
                'value' => 'clear',
                'message' => 'Cerah',
            ],
            [
                'code' => 2,
                'value' => 'partly-cloudy',
                'message' => 'Cerah Berawan',
            ],
            [
                'code' => 3,
                'value' => 'cloudy',
                'message' => 'Berawan',
            ],
            [
                'code' => 4,
                'value' => 'overcast',
                'message' => 'Berawan Tebal',
            ],
            [
                'code' => 10,
                'value' => 'haze',
                'message' => 'Udara Kabur',
            ],
            [
                'code' => 45,
                'value' => 'fog',
                'message' => 'Kabut',
            ],
            [
                'code' => 60,
                'value' => 'drizzle',
                'message' => 'Hujan Ringan',
            ],
            [
                'code' => 61,
                'value' => 'rain',
                'message' => 'Hujan Sedang',
            ],
            [
                'code' => 63,
                'value' => 'extreme-rain',
                'message' => 'Hujan Lebat',
            ],
            [
                'code' => 80,
                'value' => 'local-rain',
                'message' => 'Hujan Lokal',
            ],
            [
                'code' => 95,
                'value' => 'thunderstorms-rain',
                'message' => 'Hujan Petir',
            ],
            [
                'code' => 97,
                'value' => 'thunderstorms-rain',
                'message' => 'Hujan Petir',
            ],
        ];

        $roundedCode = round($code);
        $condition = collect($weather_conditions)
            ->sortBy(function ($condition) use ($roundedCode) {
                return abs($condition['code'] - $roundedCode);
            })
            ->first();

        if (!$condition) {
            return null;
        }

        $hour = Carbon::parse($datetime)->hour;
        $timeOfDaySuffix = ($hour >= 6 && $hour < 18) ? '-day' : '-night';

        if ($key === null) {
            return $condition;
        } elseif ($key === 'value') {
            return $condition['value'] . $timeOfDaySuffix;
        } elseif (array_key_exists($key, $condition)) {
            return $condition[$key];
        }

        return null;
    }

    /**
     * FITTING KALORI
     */
    public function fittingKalori(
        $rute,
        $points,
        $beratOrang = 75,
        $beratBebanNaik = 15,
        $skalaWaktu = 1,
    ) {
        $isEmpty           = $points[count($points) - 1]->energi_kumulatif === null;
        $isEmptyAndDefault = $isEmpty && $beratOrang == 75 && $beratBebanNaik == 15 && $skalaWaktu == 1;
        $isRuteUpdated     = $rute->updated_at > $points[count($points) - 1]->updated_at;

        if ($isEmptyAndDefault || $isRuteUpdated) {
            $points = $this->storeFittingKalori($points, $rute);
        }

        $isEmpty = $points[count(value: $points) - 1]->energi_kumulatif === null;
        $isNotDefault = $beratOrang != 75 || $beratBebanNaik != 15 || $skalaWaktu != 1;

        if ($isEmpty || $isNotDefault) {
            foreach ($points as $i => &$item) {
                if ($i == 0) {
                    $item['penambahan_elevasi'] = 0;
                    $item['jarak_per_dua_titik'] = 0;
                    $item['jarak_kumulatif'] = 0;
                    $item['jarak_total'] = 0;
                    $item['kemiringan_per_dua_titik'] = 0;
                    $item['kecepatan_per_dua_titik'] = 0;
                    $item['waktu_tempuh_per_dua_titik'] = 0;
                    $item['waktu_tempuh_kumulatif'] = 0;
                    $item['waktu_tempuh_per_dua_titik_s'] = 0;
                    $item['waktu_tempuh_kumulatif_s'] = 0;
                    $item['ee_per_dua_titik_w_per_kg'] = 0;
                    $item['ee_per_dua_titik_kkal_per_kg_per_s'] = 0;
                    $item['energi_per_dua_titik'] = 0;
                    $item['energi_kumulatif'] = 0;

                    continue;
                };

                $item['penambahan_elevasi'] = $this->penambahanElevasi(
                    $item['elev'],
                    $points[$i - 1]['elev'],
                    $points[$i - 1]['penambahan_elevasi']
                );

                $item['jarak_per_dua_titik'] = $this->jarakPerDuaTitik(
                    $item['lat'],
                    $item['long'],
                    $item['elev'],
                    $points[$i - 1]['lat'],
                    $points[$i - 1]['long'],
                    $points[$i - 1]['elev'],
                );

                $item['jarak_kumulatif'] = $this->jarakKumulatif(
                    $item['lat'],
                    $item['long'],
                    $points[$i - 1]['lat'],
                    $points[$i - 1]['long'],
                    $points[$i - 1]['jarak_per_dua_titik'],
                );

                $item['jarak_total'] = $this->jarakTotal(
                    $item['jarak_per_dua_titik'],
                    $points[$i - 1]['jarak_total'],
                );

                $item['kemiringan_per_dua_titik'] = $this->kemiringanPerDuaTitik(
                    $item['lat'],
                    $item['long'],
                    $item['elev'],
                    $points[$i - 1]['lat'],
                    $points[$i - 1]['long'],
                    $points[$i - 1]['elev'],
                );

                $item['kecepatan_per_dua_titik'] = $this->kecepatanPerDuaTitik(
                    $item['kemiringan_per_dua_titik'],
                    $rute->a_wt,
                    $rute->b_wt,
                    $rute->c_wt,
                    $rute->d_wt,
                    $rute->e_wt,
                    $beratOrang,
                    $beratBebanNaik,
                    $skalaWaktu,
                );

                $item['waktu_tempuh_per_dua_titik'] = $this->waktuTempuhPerDuaTitik(
                    $item['jarak_per_dua_titik'],
                    $item['kecepatan_per_dua_titik'],
                );

                $item['waktu_tempuh_kumulatif'] = $this->waktuTempuhKumulatif(
                    $item['waktu_tempuh_per_dua_titik'],
                    $points[$i - 1]['waktu_tempuh_kumulatif'],
                );

                $item['waktu_tempuh_per_dua_titik_s'] = $this->waktuTempuhPerDuaTitikS(
                    $item['waktu_tempuh_per_dua_titik'],
                );

                $item['waktu_tempuh_kumulatif_s'] = $this->waktuTempuhKumulatifS(
                    $item['waktu_tempuh_kumulatif'],
                );

                $item['ee_per_dua_titik_w_per_kg'] = $this->eePerDuaTitikWPerKg(
                    $item['kecepatan_per_dua_titik'],
                    $item['kemiringan_per_dua_titik'],
                    $rute->a_k,
                    $rute->b_k,
                    $rute->c_k,
                    $rute->d_k,
                );

                $item['ee_per_dua_titik_kkal_per_kg_per_s'] = $this->eePerDuaTitikKkalPerKgPerS(
                    $item['ee_per_dua_titik_w_per_kg']
                );

                $item['energi_per_dua_titik'] = $this->energiPerDuaTitik(
                    $item['ee_per_dua_titik_kkal_per_kg_per_s'],
                    $item['waktu_tempuh_per_dua_titik_s'],
                    $beratOrang,
                    $beratBebanNaik,
                );

                $item['energi_kumulatif'] = $this->energiKumulatif(
                    $item['energi_per_dua_titik'],
                    $points[$i - 1]['energi_kumulatif']
                );
            }
        }

        return collect($points);
    }

    private function storeFittingKalori($points, $rute)
    {
        foreach ($points as $i => &$item) {
            if ($i == 0) {
                $item['penambahan_elevasi'] = 0;
                $item['jarak_per_dua_titik'] = 0;
                $item['jarak_kumulatif'] = 0;
                $item['jarak_total'] = 0;
                $item['kemiringan_per_dua_titik'] = 0;
                $item['kecepatan_per_dua_titik'] = 0;
                $item['waktu_tempuh_per_dua_titik'] = 0;
                $item['waktu_tempuh_kumulatif'] = 0;
                $item['waktu_tempuh_per_dua_titik_s'] = 0;
                $item['waktu_tempuh_kumulatif_s'] = 0;
                $item['ee_per_dua_titik_w_per_kg'] = 0;
                $item['ee_per_dua_titik_kkal_per_kg_per_s'] = 0;
                $item['energi_per_dua_titik'] = 0;
                $item['energi_kumulatif'] = 0;

                continue;
            };

            $item['penambahan_elevasi'] = $this->penambahanElevasi(
                $item['elev'],
                $points[$i - 1]['elev'],
                $points[$i - 1]['penambahan_elevasi']
            );

            $item['jarak_per_dua_titik'] = $this->jarakPerDuaTitik(
                $item['lat'],
                $item['long'],
                $item['elev'],
                $points[$i - 1]['lat'],
                $points[$i - 1]['long'],
                $points[$i - 1]['elev'],
            );

            $item['jarak_kumulatif'] = $this->jarakKumulatif(
                $item['lat'],
                $item['long'],
                $points[$i - 1]['lat'],
                $points[$i - 1]['long'],
                $points[$i - 1]['jarak_per_dua_titik'],
            );

            $item['jarak_total'] = $this->jarakTotal(
                $item['jarak_per_dua_titik'],
                $points[$i - 1]['jarak_total'],
            );

            $item['kemiringan_per_dua_titik'] = $this->kemiringanPerDuaTitik(
                $item['lat'],
                $item['long'],
                $item['elev'],
                $points[$i - 1]['lat'],
                $points[$i - 1]['long'],
                $points[$i - 1]['elev'],
            );

            $item['kecepatan_per_dua_titik'] = $this->kecepatanPerDuaTitik(
                $item['kemiringan_per_dua_titik'],
                $rute->a_wt,
                $rute->b_wt,
                $rute->c_wt,
                $rute->d_wt,
                $rute->e_wt,
                75,
                15,
                1,
            );

            $item['waktu_tempuh_per_dua_titik'] = $this->waktuTempuhPerDuaTitik(
                $item['jarak_per_dua_titik'],
                $item['kecepatan_per_dua_titik'],
            );

            $item['waktu_tempuh_kumulatif'] = $this->waktuTempuhKumulatif(
                $item['waktu_tempuh_per_dua_titik'],
                $points[$i - 1]['waktu_tempuh_kumulatif'],
            );

            $item['waktu_tempuh_per_dua_titik_s'] = $this->waktuTempuhPerDuaTitikS(
                $item['waktu_tempuh_per_dua_titik'],
            );

            $item['waktu_tempuh_kumulatif_s'] = $this->waktuTempuhKumulatifS(
                $item['waktu_tempuh_kumulatif'],
            );

            $item['ee_per_dua_titik_w_per_kg'] = $this->eePerDuaTitikWPerKg(
                $item['kecepatan_per_dua_titik'],
                $item['kemiringan_per_dua_titik'],
                $rute->a_k,
                $rute->b_k,
                $rute->c_k,
                $rute->d_k,
            );

            $item['ee_per_dua_titik_kkal_per_kg_per_s'] = $this->eePerDuaTitikKkalPerKgPerS(
                $item['ee_per_dua_titik_w_per_kg']
            );

            $item['energi_per_dua_titik'] = $this->energiPerDuaTitik(
                $item['ee_per_dua_titik_kkal_per_kg_per_s'],
                $item['waktu_tempuh_per_dua_titik_s'],
                75,
                15,
            );

            $item['energi_kumulatif'] = $this->energiKumulatif(
                $item['energi_per_dua_titik'],
                $points[$i - 1]['energi_kumulatif']
            );
        }

        try {
            DB::beginTransaction();

            foreach ($points as $item) {
                $item->update([
                    'penambahan_elevasi'                 => $item->penambahan_elevasi,
                    'jarak_per_dua_titik'                => $item->jarak_per_dua_titik,
                    'jarak_kumulatif'                    => $item->jarak_kumulatif,
                    'jarak_total'                        => $item->jarak_total,
                    'kemiringan_per_dua_titik'           => $item->kemiringan_per_dua_titik,
                    'kecepatan_per_dua_titik'            => $item->kecepatan_per_dua_titik,
                    'waktu_tempuh_per_dua_titik'         => $item->waktu_tempuh_per_dua_titik,
                    'waktu_tempuh_kumulatif'             => $item->waktu_tempuh_kumulatif,
                    'waktu_tempuh_per_dua_titik_s'       => $item->waktu_tempuh_per_dua_titik_s,
                    'waktu_tempuh_kumulatif_s'           => $item->waktu_tempuh_kumulatif_s,
                    'ee_per_dua_titik_w_per_kg'          => $item->ee_per_dua_titik_w_per_kg,
                    'ee_per_dua_titik_kkal_per_kg_per_s' => $item->ee_per_dua_titik_kkal_per_kg_per_s,
                    'energi_per_dua_titik'               => $item->energi_per_dua_titik,
                    'energi_kumulatif'                   => $item->energi_kumulatif,
                    'updated_at'                         => now(),
                ]);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
        }

        return $points;
    }

    private function penambahanElevasi($currElev, $prevElev, $prevPenambahanElevasi)
    {
        return ($currElev - $prevElev) + $prevPenambahanElevasi;
    }

    private function jarakPerDuaTitik($currLat, $currLong, $currElev, $prevLat, $prevLong, $prevElev)
    {
        $latDiffM = ($currLat - $prevLat) * 113319;
        $longDiffM = ($currLong - $prevLong) * 113319;

        $elevDiff = $currElev - $prevElev;

        $latDiffSquared = $latDiffM ** 2;
        $longDiffSquared = $longDiffM ** 2;
        $elevDiffSquared = $elevDiff ** 2;

        $sumOfSquaredDiff = $latDiffSquared + $longDiffSquared + $elevDiffSquared;

        $distancePerSegment = sqrt($sumOfSquaredDiff);

        return $distancePerSegment;
    }

    private function jarakKumulatif($currLat, $currLong, $prevLat, $prevLong, $prevJarakPerDuaTitik)
    {
        $latDiffM = ($currLat - $prevLat) * 113319;
        $longDiffM = ($currLong - $prevLong) * 113319;

        $latDiffSquared = $latDiffM ** 2;
        $longDiffSquared = $longDiffM ** 2;

        $sumOfSquaredDiff = $latDiffSquared + $longDiffSquared;

        $cumulativeDistance = sqrt($sumOfSquaredDiff) + $prevJarakPerDuaTitik;

        return $cumulativeDistance;
    }

    private function jarakTotal($currJarakPerDuaTitik, $prevJarakTotal)
    {
        return $currJarakPerDuaTitik + $prevJarakTotal;
    }

    private function kemiringanPerDuaTitik($currLat, $currLong, $currElev, $prevLat, $prevLong, $prevElev)
    {
        $elevDiff = $currElev - $prevElev;

        $latDiffM = ($currLat - $prevLat) * 113319;
        $longDiffM = ($currLong - $prevLong) * 113319;

        $latDiffSquared = $latDiffM ** 2;
        $longDiffSquared = $longDiffM ** 2;

        $sumOfSquaredDiff = $latDiffSquared + $longDiffSquared;

        $denominator = sqrt($sumOfSquaredDiff);

        if ($denominator != 0) {
            $inclinationPerSegment = ($elevDiff * 100) / $denominator;
        } else {
            $inclinationPerSegment = 0;
        }

        return $inclinationPerSegment;
    }

    private function kecepatanPerDuaTitik(
        $currKemiringanPerDuaTitik,
        $aWt,
        $bWt,
        $cWt,
        $dWt,
        $eWt,
        $userBeratOrang,
        $userBeratBebanNaik,
        $userSkalaWaktu
    ) {
        $slopeComponent = abs($currKemiringanPerDuaTitik / 100 + $cWt);
        $weightComponent = ($userBeratBebanNaik + $userBeratOrang) / $dWt;
        $expComponent = exp(-$bWt * $slopeComponent - $weightComponent);

        $kecepatan = $userSkalaWaktu * $aWt * $expComponent * 1000 / 3600 + $eWt;

        return $kecepatan;
    }

    private function waktuTempuhPerDuaTitik($currJarakPerDuaTitik, $currKecepatanPerDuaTitik)
    {
        return $currJarakPerDuaTitik / $currKecepatanPerDuaTitik / 60;
    }

    private function waktuTempuhKumulatif($currWaktuTempuhPerDuaTitik, $prevWaktuTempuhKumulatif)
    {
        return $currWaktuTempuhPerDuaTitik + $prevWaktuTempuhKumulatif;
    }

    private function waktuTempuhPerDuaTitikS($currWaktuTempuhPerDuaTitik)
    {
        return $currWaktuTempuhPerDuaTitik * 60;
    }

    private function waktuTempuhKumulatifS($currWaktuTempuhKumulatif)
    {
        return $currWaktuTempuhKumulatif * 60;
    }

    private function eePerDuaTitikWPerKg(
        $currKecepatanPerDuaTitik,
        $currKemiringanPerDuaTitik,
        $aK,
        $bK,
        $cK,
        $dK
    ) {
        $term1 = 1.44;
        $term2 = 1.94 * pow($currKecepatanPerDuaTitik, 0.43);
        $term3 = 0.24 * pow($currKecepatanPerDuaTitik, 4);
        $term4 = $aK * $currKecepatanPerDuaTitik * $currKemiringanPerDuaTitik * (1 - pow($bK, 1 - pow($cK, $currKemiringanPerDuaTitik + $dK)));

        $eePerDuaTitikWPerKg = $term1 + $term2 + $term3 + $term4;

        return $eePerDuaTitikWPerKg;
    }

    private function eePerDuaTitikKkalPerKgPerS($currEePerDuaTitikWPerKg)
    {
        $conversionFactor = 0.8598;

        $timeConversionFactor = 1 / 3600;

        $eePerDuaTitikKkalPerKgPerS = $currEePerDuaTitikWPerKg * $conversionFactor * $timeConversionFactor;

        return $eePerDuaTitikKkalPerKgPerS;
    }

    private function energiPerDuaTitik(
        $currEePerDuaTitikKkalPerKgPerS,
        $currWaktuTempuhPerDuaTitikS,
        $userBeratOrang,
        $userBeratBebanNaik
    ) {
        $totalBerat = $userBeratBebanNaik + $userBeratOrang;

        $energiPerSegmen = $currEePerDuaTitikKkalPerKgPerS * $totalBerat * $currWaktuTempuhPerDuaTitikS;

        return $energiPerSegmen;
    }

    private function energiKumulatif($currEnergiPerDuaTitik, $prevEnergiKumulatif)
    {
        return $currEnergiPerDuaTitik + $prevEnergiKumulatif;
    }

    /**
     * SEGMENTASI
     */
    public function segmentasi(
        $rute,
        $points,
        $cuaca,
        $beratOrang = 75,
        $beratBebanNaik = 15,
        $skalaWaktu = 1,
    ) {
        $segmentasi = $this->segmentasiSegmentasi(
            $rute,
            $points,
            $beratOrang,
            $beratBebanNaik,
            $skalaWaktu
        );

        $cuaca = $this->segmentasiCuacaPerSegmen(
            $segmentasi,
            $rute,
            $cuaca
        );

        $waktuKalori = $this->segmentasiWaktuKaloriPerSegmen(
            $cuaca
        );

        $airMinum = $this->segmentasiAirMinumPerSegmen(
            $waktuKalori,
            $rute
        );

        $kriteriaJalur = $this->segmentasiKriteriaJalurPerSegmen(
            $airMinum,
            $rute
        );

        return $kriteriaJalur;
    }

    private function segmentasiSegmentasi(
        $rute,
        $points,
        $beratOrang,
        $beratBebanNaik,
        $skalaWaktu
    ) {
        $points = $this->fittingKalori(
            $rute,
            $points,
            $beratOrang,
            $beratBebanNaik,
            $skalaWaktu
        );

        $segmentasi = collect($points)->skip(1)->chunk($rute->segmentasi);

        $segmentasi = $segmentasi->map(
            function ($item, $index) use ($segmentasi) {
                $avgPenambahanElevasi  = $item->avg('penambahan_elevasi');
                $waktuTempuh           = $item->sum('waktu_tempuh_per_dua_titik_s');
                $gradientPerSegmen     = $item->max('kemiringan_per_dua_titik');
                $avgKecepatanPerSegmen = $item->sum('kecepatan_per_dua_titik') / count($item);
                $line                  = $item->map(fn($point) => [(float) $point->long, (float) $point->lat]);
                $grafik                = $item->map(fn($point) => (float) $point->elev);
                $jarakTotal            = $item->sum('jarak_per_dua_titik');

                if ($index > 0) {
                    $line->prepend([
                        (float) $segmentasi[$index - 1]->last()->long,
                        (float) $segmentasi[$index - 1]->last()->lat
                    ]);
                    $grafik->prepend((float) $segmentasi[$index - 1]->last()->elev);
                }

                return [
                    'no'                       => $index + 1,
                    'points'                   => $item->values(),
                    'avg_penambahan_elevasi'   => $avgPenambahanElevasi,
                    'waktu_tempuh_s'           => $waktuTempuh,
                    'waktu_tempuh_m'           => $waktuTempuh / 60,
                    'gradient_per_segmen'      => $gradientPerSegmen,
                    'avg_kecepatan_per_segmen' => $avgKecepatanPerSegmen,
                    'line'                     => $line->values(),
                    'grafik'                   => $grafik->values(),
                    'jarak_total'              => $jarakTotal,
                ];
            }
        )->values();

        return $segmentasi;
    }

    /**
     * Segmentasi: Cuaca Per Segmen
     */
    private function segmentasiCuacaPerSegmen(
        Collection $segmentasi,
        Rute $rute,
        Collection $cuaca,
    ) {
        $filteredCuaca = $cuaca
            ->map(function ($item) use ($rute) {
                $datetime = Carbon::parse($item->datetime);

                $koreksiCuaca = $this->cuacaPerSegmenKoreksiCuaca(
                    $item->weather,
                    $rute->f_wt
                );

                return [
                    'datetime'                   => $datetime,
                    'date'                       => $datetime->translatedFormat('j F'),
                    'time'                       => $datetime->translatedFormat('H:i'),
                    'day'                        => $datetime->translatedFormat('l'),
                    't'                          => $item->temperature,
                    'ws'                         => $item->wind_speed,
                    'weather'                    => $item->weather,
                    'weather_desc'               => $item->weather_description,
                    'koreksi_cuaca_waktu_kalori' => $koreksiCuaca,
                    'image'                      => $item->image,
                ];
            })
            ->all();

        $segmentasiArray = $segmentasi->all();

        $segmentasiArray[0]['prediksi_cuaca'] = $filteredCuaca;

        foreach ($segmentasiArray as $i => &$item) {
            if ($i === 0) continue;

            $prediksiCuaca = [];

            foreach ($filteredCuaca as $j => $cuaca) {
                $cuaca = (object) $cuaca;
                $datetime = Carbon::parse($cuaca->datetime);

                $weather = $this->cuacaPerSegmenCuaca(
                    $cuaca->weather,
                    $item['avg_penambahan_elevasi'],
                    $rute->a_cps
                );

                $ws = $this->cuacaPerSegmenAngin(
                    $cuaca->ws,
                    $item['avg_penambahan_elevasi'],
                    $rute->b_cps
                );

                $t = $this->cuacaPerSegmenSuhu(
                    $cuaca->t,
                    $item['avg_penambahan_elevasi']
                );

                $koreksiCuaca = $this->cuacaPerSegmenKoreksiCuaca(
                    $weather,
                    $rute->f_wt
                );

                $prediksiCuaca[$j] = [
                    'datetime'                   => $datetime,
                    'local_datetime'             => $datetime,
                    'date'                       => $datetime->translatedFormat('j F'),
                    'time'                       => $datetime->translatedFormat('H:i'),
                    'day'                        => $datetime->translatedFormat('l'),
                    't'                          => $t,
                    'ws'                         => $ws,
                    'weather'                    => $weather,
                    'weather_desc'               => $this->kondisiCuacaTerdekat($weather, $datetime, 'message'),
                    'koreksi_cuaca_waktu_kalori' => $koreksiCuaca,
                    'image'                      => asset('img/cuaca/' . $this->kondisiCuacaTerdekat($weather, $datetime, 'value') . '.svg'),
                ];
            }

            $item['prediksi_cuaca'] = $prediksiCuaca;
        }

        unset($item);

        return collect($segmentasiArray);
    }

    private function cuacaPerSegmenCuaca($firstCuaca, $currPenambahanElevasi, $aCps)
    {
        $ratio = $currPenambahanElevasi / $aCps;

        if ($ratio == 0.0) {
            $c = 1e-6;
            $ratio = $c;
        }

        $cothValue = 1 / sinh($ratio) * cosh($ratio);
        $result = $firstCuaca * (1 - $cothValue + 1 / $ratio);

        return $result;
    }

    private function cuacaPerSegmenAngin($firstAngin, $currPenambahanElevasi, $bCps)
    {
        if ($bCps == 0) {
            throw new \Exception("Division by zero error in bCps.");
        }
        $result = $firstAngin * log(2.72 + $currPenambahanElevasi / $bCps);

        return $result;
    }

    private function cuacaPerSegmenSuhu($firstSuhu, $currPenambahanElevasi)
    {
        $adjustment = -6.5 * ($currPenambahanElevasi / 1000);
        $newSuhu = $firstSuhu + $adjustment;

        return (float)$newSuhu;
    }

    private function cuacaPerSegmenKoreksiCuaca($currCuaca, $fWt)
    {
        $result = exp(-$currCuaca / $fWt);

        return $result;
    }

    /**
     * Segmentasi: Waktu Kalori Per Segmen
     */
    private function segmentasiWaktuKaloriPerSegmen(Collection $segmentasi)
    {
        $segmentasiArray = $segmentasi->toArray();

        foreach ($segmentasiArray as &$item) {
            foreach ($item['prediksi_cuaca'] as &$cuaca) {
                $cuaca['waktu_tempuh_m'] = $this->waktuKaloriPerSegmenWaktu(
                    $item['waktu_tempuh_m'],
                    $cuaca['koreksi_cuaca_waktu_kalori']
                );

                $cuaca['energi'] = $this->waktuKaloriPerSegmenKalori(
                    collect($item['points'])->sum('energi_per_dua_titik'),
                    $cuaca['koreksi_cuaca_waktu_kalori']
                );
            }
        }

        unset($item, $cuaca);

        return collect($segmentasiArray);
    }

    private function waktuKaloriPerSegmenWaktu($waktuTempuhM, $koreksiCuaca)
    {
        $result = $waktuTempuhM / $koreksiCuaca;

        return $result;
    }

    private function waktuKaloriPerSegmenKalori($sumKalori, $koreksiCuaca)
    {
        $result = $sumKalori / $koreksiCuaca;

        return $result;
    }

    /**
     * Segmentasi: Air Minum Per Segmen
     */
    private $eta = 0.8;
    private $s0 = 0.5;
    private $rh0 = 0.7;
    private $t0 = 25;
    private $r0 = 0.25;

    private function segmentasiAirMinumPerSegmen(
        Collection $segmentasi,
        Rute $rute,
    ) {
        $segmentasiArray = $segmentasi->toArray();

        foreach ($segmentasiArray as &$item) {
            foreach ($item['prediksi_cuaca'] as &$cuaca) {
                $cuaca['air_minum_keringat'] = $this->airMinumPerSegmenKehilanganKeringat(
                    $cuaca['t'],
                    $cuaca['waktu_tempuh_m'],
                    $rute->h_wt,
                    $rute->i_wt,
                    $rute->j_wt
                );

                $cuaca['air_minum_pernafasan'] = $this->airMinumPerSegmenKehilanganPernafasan(
                    $cuaca['t'],
                    $cuaca['waktu_tempuh_m'],
                    $rute->k_wt
                );

                $cuaca['air_minum_kalori'] = $this->airMinumPerSegmenKehilanganKalori(
                    $cuaca['waktu_tempuh_m'],
                    $cuaca['energi'],
                    $rute->g_wt
                );

                $cuaca['air_minum_prediksi'] = $this->airMinumPerSegmenPrediksi(
                    $cuaca['air_minum_keringat'],
                    $cuaca['air_minum_pernafasan'],
                    $cuaca['air_minum_kalori']
                );
            }
        }

        unset($item, $cuaca);

        return collect($segmentasiArray);
    }

    private function airMinumPerSegmenKehilanganKeringat(
        $currSuhu,
        $currWaktuTempuhM,
        $hWt,
        $iWt,
        $jWt,
    ) {
        $result = (
            $this->s0 +
            $hWt * ($currSuhu - 30) +
            $iWt * ($this->rh0 * exp(-$jWt * ($currSuhu - $this->t0)))
        ) * ($currWaktuTempuhM / (24 * 60));

        return $result;
    }

    private function airMinumPerSegmenKehilanganPernafasan(
        $currSuhu,
        $currWaktuTempuhM,
        $kWt,
    ) {
        $result = $this->r0 * exp(-$kWt * ($currSuhu - $this->t0)) * ($currWaktuTempuhM / (24 * 60));

        return $result;
    }

    private function airMinumPerSegmenKehilanganKalori(
        $currWaktuTempuhM,
        $currEnergi,
        $gWt,
    ) {
        $result = ($currEnergi / $this->eta) * $gWt * $currWaktuTempuhM / (24 * 60);

        return $result;
    }

    private function airMinumPerSegmenPrediksi(
        $currAirMinumKeringat,
        $currAirMinumPernafasan,
        $currAirMinumKalori,
    ) {
        $result = $currAirMinumKeringat + $currAirMinumPernafasan + $currAirMinumKalori;

        return $result;
    }

    /**
     * Segmentasi: Kriteria Jalur Per Segmen
     */
    private function segmentasiKriteriaJalurPerSegmen(
        Collection $segmentasi,
        Rute $rute,
        $beratOrang = 75,
        $beratBebanNaik = 15,
    ) {
        $segmentasiArray = $segmentasi->toArray();
        $gayaNaik        = 3 * ($rute->g_kr * $beratBebanNaik + $beratOrang) * 10;
        $gayaTurun       = 0.55 * $gayaNaik;

        foreach ($segmentasiArray as $i => &$item) {
            $gradientPerSegmen     = $item['gradient_per_segmen'];
            $avgKecepatanPerSegmen = $item['avg_kecepatan_per_segmen'];

            foreach ($item['prediksi_cuaca'] as $j => &$record) {
                $prevBebanOtot = $i > 0 ? $segmentasiArray[$i - 1]['prediksi_cuaca'][$j]['beban_otot_naik'] : 0;

                $koreksiCuacaKriteriaJalur = $this->kriteriaJalurPerSegmenKoreksiCuaca(
                    $record['weather'],
                    $rute->c_kr
                );

                $bebanOtotNaik = $this->kriteriaJalurPerSegmenBebanOtotNaik(
                    $prevBebanOtot,
                    $gradientPerSegmen,
                    $avgKecepatanPerSegmen,
                    $koreksiCuacaKriteriaJalur,
                    $rute->d_kr,
                    $rute->f_kr,
                    $rute->h_kr,
                    $gayaNaik
                );

                $kriteriaJalurNaik = $bebanOtotNaik / $gayaNaik;

                $keteranganNaik = $this->keteranganKriteriaJalurPerSegmen($kriteriaJalurNaik);

                $record['koreksi_cuaca_kriteria_jalur'] = $koreksiCuacaKriteriaJalur;
                $record['beban_otot_naik']              = $bebanOtotNaik;
                $record['kriteria_jalur_naik']          = $kriteriaJalurNaik;
                $record['keterangan_naik']              = $keteranganNaik;
            }
        }

        unset($item, $record);

        $segmentasiReversed = array_reverse($segmentasiArray);

        foreach ($segmentasiReversed as $i => &$item) {
            $gradientPerSegmen     = $item['gradient_per_segmen'];
            $avgKecepatanPerSegmen = $item['avg_kecepatan_per_segmen'];

            foreach ($item['prediksi_cuaca'] as $j => &$record) {
                $prevBebanLutut = $i > 0 ? $segmentasiReversed[$i - 1]['prediksi_cuaca'][$j]['beban_lutut_turun'] : 0;

                $koreksiCuacaKriteriaJalur = $record['koreksi_cuaca_kriteria_jalur'];

                $bebanLututTurun = $this->kriteriaJalurPerSegmenBebanLututTurun(
                    $prevBebanLutut,
                    $gradientPerSegmen,
                    $avgKecepatanPerSegmen,
                    $koreksiCuacaKriteriaJalur,
                    $rute->e_kr,
                    $rute->f_kr,
                    $rute->h_kr,
                    $gayaTurun
                );

                $kriteriaJalurTurun = $bebanLututTurun / $gayaTurun;

                $keteranganTurun = $this->keteranganKriteriaJalurPerSegmen($kriteriaJalurTurun);

                $record['beban_lutut_turun']    = $bebanLututTurun;
                $record['kriteria_jalur_turun'] = $kriteriaJalurTurun;
                $record['keterangan_turun']     = $keteranganTurun;
            }
        }

        unset($item, $record);

        return collect(array_reverse($segmentasiReversed));
    }

    private function kriteriaJalurPerSegmenKoreksiCuaca($cuaca, $cKr)
    {
        $result = exp(-$cuaca / $cKr);

        return $result;
    }

    private function kriteriaJalurPerSegmenBebanOtotNaik(
        $prevBebanOtot = 0,
        $gradientPerSegmen,
        $avgKecepatanPerSegmen,
        $koreksiCuacaKriteriaJalur,
        $dKr,
        $fKr,
        $hKr,
        $gayaNaik
    ) {
        if ($prevBebanOtot == 0) {
            $result = $gayaNaik * exp(-$gradientPerSegmen / $dKr) * $koreksiCuacaKriteriaJalur * exp(-$avgKecepatanPerSegmen / $fKr);
        } else {
            $result = $prevBebanOtot - $gayaNaik * $hKr * exp(-$gradientPerSegmen / $dKr) * $koreksiCuacaKriteriaJalur * exp(-$avgKecepatanPerSegmen / $fKr);
        }

        return $result;
    }

    private function kriteriaJalurPerSegmenBebanLututTurun(
        $prevBebanLutut = 0,
        $gradientPerSegmen,
        $avgKecepatanPerSegmen,
        $koreksiCuacaKriteriaJalur,
        $eKr,
        $fKr,
        $hKr,
        $gayaTurun
    ) {
        if ($prevBebanLutut == 0) {
            $result = $gayaTurun * exp(-$gradientPerSegmen / $eKr) * $koreksiCuacaKriteriaJalur * exp(-$avgKecepatanPerSegmen / $fKr);
        } else {
            $result = $prevBebanLutut - $gayaTurun * $hKr * exp(-$gradientPerSegmen / $eKr) * $koreksiCuacaKriteriaJalur * exp(-$avgKecepatanPerSegmen / $fKr);
        }

        return $result;
    }

    private function keteranganKriteriaJalurPerSegmen($value)
    {
        if ($value > 0.7 && $value <= 1) {
            return [
                'level' => 1,
                'keterangan' => 'Normal',
            ];
        } elseif ($value > 0.25 && $value <= 7) {
            return [
                'level' => 2,
                'keterangan' => 'Lelah',
            ];
        } elseif ($value <= 0.25) {
            return [
                'level' => 3,
                'keterangan' => 'Sakit',
            ];
        }
        return [
            'level' => 0,
            'keterangan' => 'Tidak Diketahui',
        ];
    }
}
