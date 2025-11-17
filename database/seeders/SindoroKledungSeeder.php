<?php

namespace Database\Seeders;

use App\Models\Gunung;
use App\Models\KabupatenKota;
use App\Models\Point;
use App\Models\Rute;
use App\Models\RuteKabupatenKota;
use App\Models\RuteWilayah;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SindoroKledungSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->sindoro_kledung();
    }

    private function sindoro_kledung()
    {
        $gunungLat   = '-7.3020264538039585';
        $gunungLong  = '109.99586908104298';
        $gunungPoint = "POINT({$gunungLong} {$gunungLat})";

        $gunung = Gunung::create([
            'kode_kabupaten_kota' => '33.23',
            'nama'                => 'Gunung Sindoro',
            'slug'                => 'sindoro',
            'deskripsi'           => 'Gunung Sindoro menjulang tinggi di tengah Pulau Jawa, berdiri megah dengan ketinggian sekitar 3.150 meter di atas permukaan laut. Layaknya dua saudara kembar, Gunung Sindoro dan Gunung Sumbing berdampingan, menciptakan lanskap alami yang memukau mata setiap pendaki dengan keindahan alam yang mempesona. Pendakian ke puncaknya adalah perjalanan melintasi keharmonisan antara alam dan manusia. Jalur yang menantang menjadi bagian tersendiri bagi para pendaki yang berjuang melawan lelah demi menyaksikan matahari pagi dari sudut Gunung Merbabu, Merapi, dan Lawu.',
            'lat'                 => $gunungLat,
            'long'                => $gunungLong,
            'point'               => DB::raw("ST_GeomFromText('{$gunungPoint}')"),
            'elev'                => '3146',
        ]);

        $rute = Rute::create([
            'gunung_id'   => $gunung->id,
            'kode_desa'   => '33.23.17.2001',
            'nama'        => 'Kledung',
            'slug'        => 'kledung',
            'deskripsi'   => 'Jalur pendakian melalui Basecamp Kledung adalah salah satu yang paling populer dan sering digunakan untuk mendaki Gunung Sindoro. Basecamp ini terletak di Desa Kledung, yang berada di perbatasan antara Kabupaten Temanggung dan Kabupaten Wonosobo, Jawa Tengah. Jalur ini dikelola oleh GRASINSDO (Gabungan Remaja Anak Sindoro) bekerja sama dengan Perhutani dan Pemerintah Desa Kledung. Jalur ini berada di sepanjang lereng tenggara, menjadikannya lokasi ideal untuk menyaksikan matahari terbit. Pemandangan indah sepanjang perjalanan, yang mencakup lanskap Gunung Ungaran, Merbabu, Merapi, dan Lawu, menjadikannya pilihan favorit bagi banyak pendaki, terutama karena lokasinya yang berada di tepi jalan raya Wonosobo-Temanggung. Pendakian melalui jalur Kledung biasanya memakan waktu sekitar 8-10 jam, tergantung pada kecepatan dan kondisi fisik pendaki.',
            'is_verified' => true,

            'is_cuaca_siap'          => true,
            'is_kalori_siap'         => true,
            'is_kriteria_jalur_siap' => false,
            'segmentasi'             => 3,
            'a_k'                    => 0.79,
            'b_k'                    => 1.03,
            'c_k'                    => 1.07,
            'd_k'                    => 35.2,
            'a_wt'                   => 5.500,
            'b_wt'                   => 4.817,
            'c_wt'                   => 0.0003,
            'd_wt'                   => 2013.122,
            'e_wt'                   => 0.032,
            'f_wt'                   => null,
            'a_cps'                  => 1500,
            'b_cps'                  => 200,
            'a_kr'                   => 2362.5,
            'b_kr'                   => 3150.0,
            'c_kr'                   => 100.0,
            'd_kr'                   => 540.0,
            'e_kr'                   => 80.0,
            'f_kr'                   => 0.75,
            'g_kr'                   => 0.75,
        ]);

        $point = [
            [
                'nama'        => 'Posko Keberangkatan',
                'deskripsi'   => 'Tempat berkumpul para pendaki sebelum memulai perjalanan',
                'is_waypoint' => true,
                'lat'         => '-7.319079',
                'long'        => '110.01553',
                'elev'        => '1791',
            ],
            ['lat' => '-7.317939', 'long' => '110.014058', 'elev' => '1855',],
            ['lat' => '-7.316711', 'long' => '110.014087', 'elev' => '1900',],
            ['lat' => '-7.316201', 'long' => '110.013108', 'elev' => '1937',],
            ['lat' => '-7.314412', 'long' => '110.013558', 'elev' => '1974',],
            ['lat' => '-7.314201', 'long' => '110.013604', 'elev' => '1985',],
            ['lat' => '-7.314169', 'long' => '110.013602', 'elev' => '1980',],
            ['lat' => '-7.313082', 'long' => '110.013151', 'elev' => '2032',],
            ['lat' => '-7.312329', 'long' => '110.011881', 'elev' => '2085',],
            ['lat' => '-7.312064', 'long' => '110.011669', 'elev' => '2101',],
            ['lat' => '-7.312239', 'long' => '110.011006', 'elev' => '2130',],
            ['lat' => '-7.312123', 'long' => '110.01013', 'elev' => '2163',],
            ['lat' => '-7.311932', 'long' => '110.009654', 'elev' => '2184',],
            ['lat' => '-7.311536', 'long' => '110.008919', 'elev' => '2235',],
            ['lat' => '-7.311308', 'long' => '110.008435', 'elev' => '2265',],
            ['lat' => '-7.311102', 'long' => '110.007956', 'elev' => '2300',],
            ['lat' => '-7.310822', 'long' => '110.007182', 'elev' => '2335',],
            ['lat' => '-7.310658', 'long' => '110.006546', 'elev' => '2378',],
            ['lat' => '-7.310548', 'long' => '110.005898', 'elev' => '2411',],
            [
                'nama'                     => 'Sunrise Camp',
                'deskripsi'                => 'Sunrise Camp adalah lokasi berkemah yang terkenal di kalangan pendaki karena pemandangannya yang memukau saat matahari terbit.',
                'is_waypoint'              => true,
                'is_lokasi_prediksi_cuaca' => true,
                'lat'                      => '-7.309817',
                'long'                     => '110.005702',
                'elev'                     => '2427',
            ],
            ['lat' => '-7.309733', 'long' => '110.005624', 'elev' => '2433',],
            ['lat' => '-7.309445', 'long' => '110.005206', 'elev' => '2477',],
            ['lat' => '-7.308777', 'long' => '110.004758', 'elev' => '2520',],
            ['lat' => '-7.308091', 'long' => '110.003833', 'elev' => '2586',],
            ['lat' => '-7.307629', 'long' => '110.003497', 'elev' => '2619',],
            ['lat' => '-7.306496', 'long' => '110.002627', 'elev' => '2737',],
            ['lat' => '-7.306069', 'long' => '110.002367', 'elev' => '2770',],
            ['lat' => '-7.30531', 'long' => '110.001848', 'elev' => '2829',],
            ['lat' => '-7.304244', 'long' => '110.001298', 'elev' => '2898',],
            ['lat' => '-7.303417', 'long' => '110.000349', 'elev' => '2968',],
            ['lat' => '-7.302673', 'long' => '109.999452', 'elev' => '3036',],
            [
                'nama'        => 'Titik Akhir',
                'deskripsi'   => 'Puncak Gunung Sindoro via kledung',
                'is_waypoint' => true,
                'lat'         => '-7.302004',
                'long'        => '109.997578',
                'elev'        => '3146',
            ],
        ];

        foreach ($point as $index => $item) {
            $longitude = $item['long'];
            $latitude = $item['lat'];

            $point = "POINT({$longitude} {$latitude})";

            Point::create([
                'rute_id'                  => $rute->id,
                'nomor'                    => $index + 1,
                'nama'                     => $item['nama'] ?? null,
                'deskripsi'                => $item['deskripsi'] ?? null,
                'is_waypoint'              => $item['is_waypoint'] ?? false,
                'is_lokasi_prediksi_cuaca' => $item['is_lokasi_prediksi_cuaca'] ?? false,
                'lat'                      => $item['lat'],
                'long'                     => $item['long'],
                'elev'                     => $item['elev'],
                'point'                    => DB::raw("ST_GeomFromText('{$point}')"),
            ]);
        }

        $points = Point::select(DB::raw('ST_X(point) as longitude, ST_Y(point) as latitude'))
            ->where('rute_id', $rute->id)
            ->orderBy('nomor')
            ->get();

        $lineString = "LINESTRING(" . $points->map(function ($point) {
            return "{$point->longitude} {$point->latitude}";
        })->implode(', ') . ")";

        $rute->update([
            'rute' => DB::raw("ST_GeomFromText('{$lineString}')"),
        ]);
    }
}
