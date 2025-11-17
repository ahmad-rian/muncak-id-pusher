<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Rute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class RuteController extends Controller
{
    public function index(Request $req): JsonResponse
    {
        $rute = QueryBuilder::for(Rute::class)
            ->select([
                'id',
                'gunung_id',
                'negara_id',
                'kode_desa',
                'lokasi',
                'nama',
                'slug',
                'deskripsi',
                'informasi',
                'aturan_dan_larangan',
                'segmentasi',
                'rute_tingkat_kesulitan_id',
                'comment_count',
                'comment_rating',
                'created_at',
                'updated_at'
            ])
            ->with([
                'gunung:id,nama,slug,elev',
                'negara:id,nama',
                'desa:kode,nama,nama_lain,kode_kecamatan',
                'desa.kecamatan:kode,nama,nama_lain,kode_kabupaten_kota',
                'desa.kecamatan.kabupatenKota:kode,nama,nama_lain,kode_provinsi',
                'desa.kecamatan.kabupatenKota.provinsi:kode,nama,nama_lain',
                'ruteTingkatKesulitan:id,nama,deskripsi',
                'media',
            ])
            ->allowedFilters([
                AllowedFilter::partial('nama'),
                AllowedFilter::partial('lokasi'),
                AllowedFilter::partial('deskripsi'),
                AllowedFilter::exact('gunung_id'),
                AllowedFilter::exact('negara_id'),
                AllowedFilter::exact('kode_desa'),
                AllowedFilter::partial('gunung.nama'),
                AllowedFilter::partial('negara.nama'),
                AllowedFilter::partial('desa.nama'),
            ])
            ->allowedSorts([
                AllowedSort::field('nama'),
                AllowedSort::field('created_at'),
                AllowedSort::field('updated_at'),
                AllowedSort::field('comment_count'),
                AllowedSort::field('comment_rating'),
                AllowedSort::field('gunung.nama'),
            ])
            ->defaultSort('nama')
            ->simplePaginate($req->get('per_page', 20))
            ->appends($req->query());

        $rute->getCollection()->transform(function ($item) {
            $item->image_url = $item->getImageUrl();
            $item->created_at_human = $item->created_at->diffForHumans();

            unset($item->media);
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Routes retrieved successfully',
            'data' => $rute,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $rute = QueryBuilder::for(Rute::class)
            ->select([
                'id',
                'gunung_id',
                'negara_id',
                'kode_desa',
                'lokasi',
                'nama',
                'slug',
                'deskripsi',
                'informasi',
                'aturan_dan_larangan',
                'segmentasi',
                'rute_tingkat_kesulitan_id',
                'comment_count',
                'comment_rating',
                'created_at',
                'updated_at'
            ])
            ->with([
                'gunung:id,nama,slug,elev',
                'negara:id,nama',
                'desa:kode,nama,nama_lain,kode_kecamatan',
                'desa.kecamatan:kode,nama,nama_lain,kode_kabupaten_kota',
                'desa.kecamatan.kabupatenKota:kode,nama,nama_lain,kode_provinsi',
                'desa.kecamatan.kabupatenKota.provinsi:kode,nama,nama_lain',
                'ruteTingkatKesulitan:id,nama,deskripsi',
                'media',
            ])
            ->findOrFail($id);

        $rute->image_url        = $rute->getImageUrl();
        $rute->gallery_urls     = $rute->getGalleryUrls();
        $rute->created_at_human = $rute->created_at->diffForHumans();

        unset($rute->media);

        return response()->json([
            'success' => true,
            'message' => 'Route retrieved successfully',
            'data' => $rute,
        ]);
    }

    public function geojson(int $id): JsonResponse
    {
        $rute = Rute::selectRaw('ST_AsGeoJSON(rute) as rute_geojson')
            ->where('id', $id)
            ->firstOrFail();

        if (!$rute->rute_geojson) {
            return response()->json([
                'success' => false,
                'message' => 'Route geometry not available',
            ], 404);
        }

        $geometry = json_decode($rute->rute_geojson, true);

        return response()->json($geometry, 200, [
            'Content-Type' => 'application/geo+json'
        ]);
    }
}
