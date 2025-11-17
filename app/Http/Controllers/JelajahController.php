<?php

namespace App\Http\Controllers;

use App\Models\Rute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\SchemaOrg\Schema;

class JelajahController extends Controller
{
    public function index()
    {
        $webPage = Schema::webPage()
            ->url(url('/jelajah'))
            ->name('Jelajahi Jalur Pendakian di Sekitarmu')
            ->description('Jelajahi jalur pendakian gunung di sekitar lokasi Anda melalui peta interaktif yang menampilkan jalur pendakian lengkap dengan daftar rute yang dapat Anda pilih dan rencanakan perjalanan Anda.')
            ->mainEntityOfPage(Schema::webPage()->url(url('/jelajah')))
            ->addAdditionalType('https://schema.org/InteractiveWebPage');

        $schemaOrg = $webPage->toScript();
        return view('jelajah.index', [
            'schemaOrg' => $schemaOrg,
        ]);
    }

    public function apiRute(Request $req)
    {
        $minLng = $req->minLng;
        $maxLng = $req->maxLng;
        $minLat = $req->minLat;
        $maxLat = $req->maxLat;

        $polygon = "POLYGON(({$minLng} {$minLat}, {$maxLng} {$minLat}, {$maxLng} {$maxLat}, {$minLng} {$maxLat}, {$minLng} {$minLat}))";

        $data = Rute::select([
            'id',
            'kode_desa',
            'negara_id',
            'lokasi',
            'gunung_id',
            'deskripsi',
            'nama',
            'slug',
            'rute_tingkat_kesulitan_id',
            'comment_rating',
            'comment_count'
        ])
            ->selectRaw('ST_AsGeoJSON(rute) as rute_geo')
            ->whereNotNull('rute')
            ->whereRaw("ST_Within(ST_StartPoint(rute), ST_GeomFromText('{$polygon}'))")
            ->limit(50)
            ->with([
                'gunung:id,nama,elev,slug',
                'negara:id,nama,nama_lain',
                'desa:kode,nama,nama_lain,kode_kecamatan',
                'desa.kecamatan:kode,nama,nama_lain,kode_kabupaten_kota',
                'desa.kecamatan.kabupatenKota:kode,nama,nama_lain,kode_provinsi',
                'desa.kecamatan.kabupatenKota.provinsi:kode,nama,nama_lain',
                'ruteTingkatKesulitan:id,nama',
                'lastPoint:id,rute_id,jarak_total,waktu_tempuh_kumulatif',
                'point' => function ($query) {
                    $query
                        ->select(DB::raw('rute_id, ST_AsGeoJSON(point) as point_geo'))
                        ->orderBy('nomor');
                }
            ])
            ->get()
            ->map(function ($item) {
                // Determine location display based on available data
                $lokasi = '';
                
                if ($item->negara_id && $item->negara && $item->lokasi) {
                    // Use negara + lokasi approach for international routes
                    $negaraNama = $item->negara->nama_lain ?? $item->negara->nama;
                    $lokasi = "{$item->lokasi}, {$negaraNama}";
                } elseif ($item->kode_desa && $item->desa) {
                    // Use traditional Indonesian administrative hierarchy
                    $desaNama = $item->desa->nama_lain ?? $item->desa->nama;
                    $kabupatenKotaNama = $item->desa->kecamatan->kabupatenKota->nama_lain ?? $item->desa->kecamatan->kabupatenKota->nama;
                    $provinsiNama = $item->desa->kecamatan->kabupatenKota->provinsi->nama_lain ?? $item->desa->kecamatan->kabupatenKota->provinsi->nama;
                    $lokasi = "$desaNama, $kabupatenKotaNama, $provinsiNama";
                } elseif ($item->negara_id && $item->negara) {
                    // Fallback to just negara if lokasi is not available
                    $lokasi = $item->negara->nama_lain ?? $item->negara->nama;
                }

                return [
                    'id'                => $item->id,
                    'nama'              => "Gunung {$item->gunung->nama} via {$item->nama}",
                    'path'              => route('jalur-pendakian.slug', $item->slug),
                    'deskripsi'         => $item->deskripsi,
                    'lokasi'            => $lokasi,
                    'jarak_total'       => number_format($item->lastPoint->jarak_total / 1000, 1) . " km",
                    'waktu_tempuh'      => number_format($item->lastPoint->waktu_tempuh_kumulatif / 60, 1) . " jam",
                    'tingkat_kesulitan' => $item->ruteTingkatKesulitan?->nama,
                    'comment_rating'    => number_format($item->comment_rating, 1),
                    'comment_count'     => $item->comment_count,
                    'image'             => $item->getImageUrl(),
                    'rute_geo'          => $item->rute_geo,
                    'point'             => $item->point,
                ];
            });

        return response()->json($data);
    }
}
