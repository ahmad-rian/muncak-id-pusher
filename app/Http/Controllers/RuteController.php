<?php

namespace App\Http\Controllers;

use App\Services\RuteService;
use App\Models\Point;
use App\Models\Rute;
use App\Models\RutePrediksiCuaca;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\SchemaOrg\Schema;

class RuteController extends Controller
{
    public $ruteService;

    public function __construct(RuteService $ruteService)
    {
        $this->ruteService = $ruteService;
    }

    public function slug($slug)
    {
        $rute = Rute::select(['id', 'gunung_id', 'kode_desa', 'negara_id', 'lokasi', 'nama', 'slug', 'deskripsi', 'informasi', 'aturan_dan_larangan', 'rute_tingkat_kesulitan_id', 'is_cuaca_siap', 'is_kalori_siap', 'is_kriteria_jalur_siap', 'is_verified', 'comment_count', 'comment_rating', 'updated_at', 'a_k', 'b_k', 'c_k', 'd_k', 'a_wt', 'b_wt', 'c_wt', 'd_wt', 'e_wt', 'f_wt', 'a_cps', 'b_cps', 'c_kr', 'd_kr', 'e_kr', 'f_kr', 'g_kr', 'h_kr'])
            ->where('slug', $slug)
            ->with([
                'negara:id,nama,nama_lain',
                'desa:kode,kode_kecamatan,nama,nama_lain',
                'desa.kecamatan:kode,kode_kabupaten_kota,nama,nama_lain',
                'desa.kecamatan.kabupatenKota:kode,kode_provinsi,nama,nama_lain',
                'desa.kecamatan.kabupatenKota.provinsi:kode,nama,nama_lain',
                'gunung:id,nama,slug,elev,lat,long',
                'ruteTingkatKesulitan:id,nama,deskripsi'
            ])
            ->firstOrFail();

        $points = Point::where(['rute_id' => $rute->id])
            ->with(['media' => fn($query) => $query->where('collection_name', 'point-gallery')])
            ->orderBy('nomor', 'asc')
            ->get();

        $points = $this->ruteService->fittingKalori($rute, $points)
            ->whenEmpty(fn() => []);

        if (count($points) == 0) abort(404);

        $lastPoint = $points->last();

        $lokasiPrediksiCuaca = $points->first(function (Point $point) {
            return $point->is_lokasi_prediksi_cuaca;
        });

        $targetHour = Carbon::createFromTime(13, 0, 0);

        $prediksiCuaca = $this->ruteService->prediksiCuacaFetch($lokasiPrediksiCuaca);
        $prediksiCuaca = $this->ruteService->formatHourlyData($prediksiCuaca->result);
        $prediksiCuaca = $prediksiCuaca->groupBy(function ($item) {
            return Carbon::parse($item->datetime)->toDateString();
        })->map(function ($items) use ($targetHour) {
            return $items->sortBy(function ($item) use ($targetHour) {
                $itemTime = Carbon::parse($item->datetime);
                return abs($itemTime->diffInSeconds($targetHour->copy()->setDateFrom($itemTime)));
            })->first();
        })
            ->values()
            ->take(3)
            ->map(function ($item) {
                $datetime          = Carbon::parse($item->datetime)->setTimezone('Asia/Jakarta');
                $item->temperature = number_format($item->temperature, 0) . " °C";
                $item->wind_speed  = number_format($item->wind_speed, 1) . " km/j";
                return $item;
            });

        $waypoints = $points->filter(fn(Point $point) => $point->is_waypoint)
            ->map(function ($item) {
                $item->gallery = $item->getGalleryUrls();
                return $item;
            });

        $commentGallery = $rute
            ->comment()
            ->whereHas('media', fn($query) => $query->where('collection_name', 'comment-gallery'))
            ->with('media')
            ->limit(8)
            ->latest()
            ->get()
            ->map(fn($item) => $item->getGalleryUrls())
            ->flatten()
            ->whenEmpty(fn() => collect([]))
            ->take(8);

        $webPage = Schema::webPage()
            ->url(url("/jalur-pendakian/{$slug}"))
            ->name("Gunung {$rute->gunung->nama} via {$rute->nama}")
            ->description($rute->deskripsi)
            ->mainEntityOfPage(Schema::webPage()->url(url("/jalur-pendakian/{$slug}")))
            ->addSubPage(
                [
                    Schema::webPage()
                        ->url(url("/jalur-pendakian/{$slug}/prediksi-cuaca"))
                        ->name("Prediksi Cuaca Gunung {$rute->gunung->nama} via {$rute->nama}")
                        ->description($rute->deskripsi),
                    Schema::webPage()
                        ->url(url("/jalur-pendakian/{$slug}/segmentasi"))
                        ->name("Segmentasi Jalur Gunung {$rute->gunung->nama} via {$rute->nama}")
                        ->description($rute->deskripsi)
                ]
            );

        $schemaOrg = $webPage->toScript();

        return view('rute.slug', [
            'rute'                 => $rute,
            'penambahanElevasi'    => $lastPoint->penambahan_elevasi,
            'waktuTempuhKumulatif' => number_format($lastPoint->waktu_tempuh_kumulatif / 60, 1),
            'jarakTotal'           => number_format($lastPoint->jarak_total / 1000, 2),
            'kalori'               => number_format($lastPoint->energi_kumulatif),
            'prediksiCuaca'        => $prediksiCuaca,
            'waypoints'            => $waypoints,
            'lokasiPrediksiCuaca'  => $lokasiPrediksiCuaca,
            'commentGallery'       => $commentGallery,
            'schemaOrg'            => $schemaOrg,
        ]);
    }

    public function prediksiCuaca($slug)
    {
        $rute = Rute::select([
            'id',
            'gunung_id',
            'kode_desa',
            'negara_id',
            'lokasi',
            'nama',
            'slug',
            'deskripsi',
            'is_verified',
        ])
            ->where('slug', $slug)
            ->with([
                'negara:id,nama,nama_lain',
                'desa:kode,kode_kecamatan,nama,nama_lain',
                'desa.kecamatan:kode,kode_kabupaten_kota,nama,nama_lain',
                'desa.kecamatan.kabupatenKota:kode,kode_provinsi,nama,nama_lain',
                'desa.kecamatan.kabupatenKota.provinsi:kode,nama,nama_lain',
                'gunung:id,nama,slug'
            ])
            ->firstOrFail();

        $lokasiPrediksiCuaca = Point::select(['id', 'rute_id', 'nama', 'nomor', 'elev', 'is_waypoint', 'is_lokasi_prediksi_cuaca', 'lat', 'long'])
            ->with('rute:id')
            ->where(['rute_id' => $rute->id, 'is_lokasi_prediksi_cuaca' => true])
            ->orderBy('nomor', 'asc')
            ->first();

        if (!$lokasiPrediksiCuaca) abort(404);

        $prediksiCuaca = $this->ruteService->prediksiCuacaFetch($lokasiPrediksiCuaca);
        $result        = $this->ruteService->formatHourlyData($prediksiCuaca->result);

        $groupedResult = $result->groupBy(function ($item) {
            return Carbon::parse($item->datetime)->toDateString();
        });

        $webPage = Schema::webPage()
            ->url(url("/jalur-pendakian/{$slug}/prediksi-cuaca"))
            ->name("Prediksi Cuaca Gunung {$rute->gunung->nama} via {$rute->nama}")
            ->description($rute->deskripsi)
            ->mainEntityOfPage(Schema::webPage()->url(url("/jalur-pendakian/{$slug}/prediksi-cuaca")));

        $schemaOrg = $webPage->toScript();

        return view('rute.prediksi-cuaca', [
            'rute'                => $rute,
            'lokasiPrediksiCuaca' => $lokasiPrediksiCuaca,
            'prediksiCuaca'       => $groupedResult,
            'schemaOrg'           => $schemaOrg,
        ]);
    }

    public function segmentasi($slug)
    {
        $rute = Rute::select(['id', 'gunung_id', 'kode_desa', 'negara_id', 'lokasi', 'nama', 'slug', 'deskripsi',  'is_verified', 'is_cuaca_siap', 'is_kalori_siap', 'is_kriteria_jalur_siap', 'segmentasi', 'a_cps', 'b_cps', 'a_wt', 'b_wt', 'c_wt', 'd_wt', 'e_wt', 'f_wt', 'a_k', 'b_k', 'c_k', 'd_k', 'c_kr', 'd_kr', 'e_kr', 'f_kr', 'g_kr'])
            ->where('slug', $slug)
            ->with([
                'negara:id,nama,nama_lain',
                'desa:kode,kode_kecamatan,nama,nama_lain',
                'desa.kecamatan:kode,kode_kabupaten_kota,nama,nama_lain',
                'desa.kecamatan.kabupatenKota:kode,kode_provinsi,nama,nama_lain',
                'desa.kecamatan.kabupatenKota.provinsi:kode,nama,nama_lain',
                'gunung:id,nama,slug,lat,long'
            ])
            ->firstOrFail();

        if (!$rute->is_cuaca_siap || !$rute->is_kalori_siap || !$rute->is_kriteria_jalur_siap) abort(404);

        if (
            !$rute->a_cps || !$rute->b_cps || !$rute->a_wt || !$rute->b_wt || !$rute->c_wt || !$rute->d_wt || !$rute->e_wt || !$rute->f_wt || !$rute->a_k || !$rute->b_k || !$rute->c_k || !$rute->d_k || !$rute->c_kr || !$rute->d_kr || !$rute->e_kr || !$rute->f_kr || !$rute->g_kr
        ) abort(404);

        $webPage = Schema::webPage()
            ->url(url("/jalur-pendakian/{$slug}/segmentasi"))
            ->name("Segmentasi Jalur Gunung {$rute->gunung->nama} via {$rute->nama}")
            ->description($rute->deskripsi)
            ->mainEntityOfPage(Schema::webPage()->url(url("/jalur-pendakian/{$slug}/segmentasi")));

        $schemaOrg = $webPage->toScript();

        return view('rute.segmentasi', [
            'rute'      => $rute,
            'schemaOrg' => $schemaOrg,
        ]);
    }

    /**
     * API
     */
    public function apiRute($id)
    {
        $rute = Rute::selectRaw('ST_AsGeoJSON(rute) as rute_geojson')->find($id);
        return response()->json($rute->rute_geojson);
    }

    public function apiPrediksiCuaca($id, Request $req, RuteService $ruteService)
    {
        $rute = Rute::select(['kode_desa', 'negara_id', 'lokasi'])->findOrFail($id);

        $lokasiPrediksiCuaca = Point::select(['id', 'rute_id', 'nama', 'nomor', 'elev', 'is_waypoint', 'is_lokasi_prediksi_cuaca', 'lat', 'long'])
            ->with('rute:id')
            ->where(['rute_id' => $rute->id, 'is_lokasi_prediksi_cuaca' => true])
            ->orderBy('nomor', 'asc')
            ->first();

        if (!$lokasiPrediksiCuaca) abort(404);

        $prediksiCuacaData = $ruteService->prediksiCuacaFetch($lokasiPrediksiCuaca);
        if (!$prediksiCuacaData) abort(500);

        $result = $ruteService->formatHourlyData($prediksiCuacaData->result);

        $filtered = $req->full
            ? $result
            : $result->filter(function ($item) {
                $hour = Carbon::parse($item->datetime)->hour;
                return $hour == 6; // Show only 6 AM data when not full
            });

        $cuaca = $filtered->map(function ($item) {
            $datetime = Carbon::parse($item->datetime)->setTimezone('Asia/Jakarta');

            return [
                'datetime'     => $datetime,
                'date'         => $datetime->translatedFormat('j F'),
                'time'         => $datetime->translatedFormat('H:i'),
                'day'          => $datetime->translatedFormat('l'),
                'weather'      => $item->weather,
                'weather_desc' => $item->weather_description,
                't'            => number_format($item->temperature, 1) . " °C",
                'ws'           => number_format($item->wind_speed, 1) . " km/j",
                'wd'           => $item->wind_direction,
                'image'        => $item->image
            ];
        });

        return response()->json($cuaca);
    }

    public function apiFittingKalori($id, Request $req, RuteService $ruteService)
    {
        $beratBebanNaik = $req->berat_beban_naik ?? 15;
        $beratOrang = $req->berat_orang ?? 75;
        $skalaWaktu = $req->skala_waktu ?? 1;

        $rute = Rute::select(['id', 'a_wt', 'b_wt', 'c_wt', 'd_wt', 'e_wt', 'a_k', 'b_k', 'c_k', 'd_k', 'updated_at'])->findOrFail($id);

        if (!$rute->a_wt || !$rute->b_wt || !$rute->c_wt || !$rute->d_wt || !$rute->e_wt || !$rute->a_k || !$rute->b_k || !$rute->c_k || !$rute->d_k) {
            return response()->json('Not Found', 404);
        }

        $points = Point::where(['rute_id' => $rute->id])
            ->with(['media' => function ($query) {
                $query->where('collection_name', 'point-gallery');
            }])
            ->orderBy('nomor', 'asc')
            ->get();

        $kalori = $ruteService->fittingKalori(
            $rute,
            $points,
            $beratOrang,
            $beratBebanNaik,
            $skalaWaktu
        );

        $waypoints = $kalori->filter(function (Point $point) use ($req) {
            return $req->full ? true : $point->is_waypoint;
        })
            ->map(function (Point $point) {
                return [
                    'id'                     => $point->id,
                    'nama'                   => $point->nama,
                    'elev'                   => (string) $point->elev . ' m',
                    'deskripsi'              => $point->deskripsi,
                    'penambahan_elevasi'     => number_format($point->penambahan_elevasi) . ' m',
                    'jarak_total'            => number_format($point->jarak_total / 1000, 2) . ' km',
                    'waktu_tempuh_kumulatif' => number_format($point->waktu_tempuh_kumulatif / 60, 1) . ' jam',
                    'energi_kumulatif'       => number_format($point->energi_kumulatif) . ' kkal',
                    'gallery'                => $point->getGalleryUrls(),
                ];
            })
            ->values();

        return response()->json($waypoints);
    }

    public function apiSegmentasi($id, Request $req, RuteService $ruteService)
    {
        $beratBebanNaik = $req->berat_beban_naik ?? 15;
        $beratOrang     = $req->berat_orang ?? 75;
        $skalaWaktu     = $req->skala_waktu ?? 1;

        $rute = Rute::select(['id', 'kode_desa', 'segmentasi', 'a_cps', 'b_cps', 'a_wt', 'b_wt', 'c_wt', 'd_wt', 'e_wt', 'f_wt', 'g_wt', 'h_wt', 'i_wt', 'j_wt', 'k_wt', 'a_k', 'b_k', 'c_k', 'd_k', 'c_kr', 'd_kr', 'e_kr', 'f_kr', 'g_kr', 'h_kr', 'updated_at'])
            ->findOrFail($id);

        if (!$rute->a_k || !$rute->b_k || !$rute->c_k || !$rute->d_k || !$rute->a_wt || !$rute->b_wt || !$rute->c_wt || !$rute->d_wt || !$rute->e_wt || !$rute->f_wt || !$rute->g_wt || !$rute->h_wt || !$rute->i_wt || !$rute->j_wt || !$rute->k_wt || !$rute->a_cps || !$rute->b_cps || !$rute->c_kr || !$rute->d_kr || !$rute->e_kr || !$rute->f_kr || !$rute->g_kr || !$rute->h_kr) {
            return response()->json('Not Found', 404);
        }

        $lokasiPrediksiCuaca = Point::where(['rute_id' => $id, 'is_lokasi_prediksi_cuaca' => true])
            ->orderBy('nomor', 'asc')
            ->first();

        if (!$lokasiPrediksiCuaca) abort(404);

        $prediksiCuacaData = $ruteService->prediksiCuacaFetch($lokasiPrediksiCuaca);
        if (!$prediksiCuacaData) abort(500);

        $formattedData = $ruteService->formatHourlyData($prediksiCuacaData->result);

        $targetHour = Carbon::createFromTime(2, 0, 0);

        $filtered = $formattedData
            ->groupBy(function ($item) {
                return Carbon::parse($item->datetime)->toDateString();
            })
            ->map(function ($items) use ($targetHour) {
                return $items->sortBy(function ($item) use ($targetHour) {
                    $itemTime = Carbon::parse($item->datetime);
                    return abs($itemTime->diffInSeconds($targetHour->copy()->setDateFrom($itemTime)));
                })->first();
            })
            ->values();

        $points = Point::where(['rute_id' => $id])
            ->orderBy('nomor', 'asc')
            ->get();

        $segmentasi = $ruteService->segmentasi(
            $rute,
            $points,
            $filtered,
            $beratOrang,
            $beratBebanNaik,
            $skalaWaktu
        );

        return response()->json($segmentasi);
    }
}
