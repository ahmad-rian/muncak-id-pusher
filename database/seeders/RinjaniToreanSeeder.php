<?php

namespace Database\Seeders;

use App\Models\Gunung;
use App\Models\KabupatenKota;
use App\Models\Point;
use App\Models\Rute;
use App\Models\RuteKabupatenKota;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use proj4php\Point as Proj4phpPoint;
use proj4php\Proj;
use proj4php\Proj4php;
use Spatie\SimpleExcel\SimpleExcelReader;

class RinjaniToreanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->rinjani_torean();
    }

    private function rinjani_torean()
    {
        $gunungLat   = '-8.41196114070152';
        $gunungLong  = '116.4579010559627';
        $gunungPoint = "POINT({$gunungLong} {$gunungLat})";

        $gunung = Gunung::firstOrCreate(
            [
                'slug' => 'rinjani',
            ],
            [
                'kode_kabupaten_kota' => "52.08",
                'nama'                => 'Gunung Rinjani',
                'deskripsi'           => 'Gunung Rinjani adalah gunung berapi aktif yang terletak di Pulau Lombok, Nusa Tenggara Barat, Indonesia. Dengan ketinggian mencapai 3.146 meter di atas permukaan laut, Gunung Rinjani adalah gunung tertinggi kedua di Indonesia setelah Gunung Semeru. Gunung ini terkenal dengan kaldera luasnya yang membentuk Danau Segara Anak, sebuah danau vulkanik yang merupakan salah satu daya tarik utama bagi para pendaki. Rinjani merupakan bagian dari Taman Nasional Gunung Rinjani yang menawarkan berbagai jenis ekosistem, termasuk hutan hujan tropis, padang rumput alpine, dan kawasan vulkanik. Pendakian ke puncak Gunung Rinjani menawarkan pemandangan spektakuler dan merupakan salah satu pengalaman mendaki yang paling populer di Indonesia.',
                'lat'                 => $gunungLat,
                'long'                => $gunungLong,
                'point'               => DB::raw("ST_GeomFromText('{$gunungPoint}')"),
                'elev'                => '3726',
            ]
        );

        $rute = Rute::create([
            'gunung_id' => $gunung->id,
            'kode_desa' => "52.08.04.2002",
            'nama'      => 'Torean',
            'slug'      => 'torean',
            'deskripsi' => 'Rute Torean merupakan jalur pendakian yang jarang dijelajahi dan menawarkan keindahan alam yang masih alami. Memulai perjalanan dari desa Torean, para pendaki akan menyusuri jalur yang melalui hutan lebat dan medan berbatu. Jalur ini dikenal dengan suasananya yang tenang dan jauh dari keramaian, membuat setiap langkah terasa lebih intim dengan alam sekitar. Selama perjalanan, pendaki akan melewati sungai kecil dan menanjak di medan yang menantang, sambil menikmati pemandangan alam yang spektakuler. Rute Torean adalah pilihan ideal bagi mereka yang mencari pengalaman trekking yang lebih mendalam dan mendekatkan diri dengan keindahan alam yang belum terjamah.',
        ]);

        $filePath = base_path('database/seeders/file/rinjani-torean.xlsx');
        $reader = SimpleExcelReader::create($filePath);
        $points = $reader->getRows()->toArray();

        foreach ($points as $index => $point) {
            $converted = $this->convert_utm($point['x'], $point['y']);
            $longitude = $converted['longitude'];
            $latitude  = $converted['latitude'];

            $geo = "POINT({$longitude} {$latitude})";

            Point::create([
                'rute_id' => $rute->id,
                'nomor'   => $index + 1,
                'lat'     => $converted['latitude'],
                'long'    => $converted['longitude'],
                'elev'    => $point['z'],
                'point'   => DB::raw("ST_GeomFromText('{$geo}')"),
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

    private function convert_utm($utmX, $utmY)
    {
        $proj4 = new Proj4php();
        $projUTM = new Proj('EPSG:32750', $proj4);
        $projWGS84 = new Proj('EPSG:4326', $proj4);

        $pointUTM = new Proj4phpPoint($utmX, $utmY, $projUTM);
        $pointLatLon = $proj4->transform($projWGS84, $pointUTM);

        return [
            'latitude' => $pointLatLon->y,
            'longitude' => $pointLatLon->x,
        ];
    }
}
