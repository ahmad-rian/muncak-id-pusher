<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Gunung;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class GunungController extends Controller
{
    public function index(Request $req): JsonResponse
    {
        $gunung = QueryBuilder::for(Gunung::class)
            ->select([
                'id',
                'negara_id',
                'kode_kabupaten_kota',
                'lokasi',
                'nama',
                'slug',
                'deskripsi',
                'long',
                'lat',
                'elev',
                'created_at',
                'updated_at'
            ])
            ->with([
                'negara:id,nama',
                'kabupatenKota:kode,kode_provinsi,nama,nama_lain',
                'kabupatenKota.provinsi:kode,nama,nama_lain',
                'media',
            ])
            ->allowedFilters([
                AllowedFilter::partial('nama'),
                AllowedFilter::partial('lokasi'),
                AllowedFilter::partial('deskripsi'),
                AllowedFilter::exact('negara_id'),
                AllowedFilter::exact('kode_kabupaten_kota'),
                AllowedFilter::scope('elev'),
                AllowedFilter::partial('negara.nama'),
                AllowedFilter::partial('kabupatenKota.nama'),
            ])
            ->allowedSorts([
                AllowedSort::field('nama'),
                AllowedSort::field('elev'),
                AllowedSort::field('created_at'),
                AllowedSort::field('updated_at'),
                AllowedSort::field('negara.nama'),
                AllowedSort::field('kabupatenKota.nama'),
            ])
            ->defaultSort('nama')
            ->withCount('rute')
            ->defaultSort('nama')
            ->simplePaginate($req->get('per_page', 20))
            ->appends($req->query());

        $gunung->getCollection()->transform(function ($item) {
            $item->image_url        = $item->getImageUrl();
            $item->created_at_human = $item->created_at->diffForHumans();

            unset($item->media);
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Mountains retrieved successfully',
            'data'    => $gunung,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $gunung = QueryBuilder::for(Gunung::class)
            ->select([
                'id',
                'negara_id',
                'kode_kabupaten_kota',
                'lokasi',
                'nama',
                'slug',
                'deskripsi',
                'long',
                'lat',
                'elev',
                'created_at',
                'updated_at'
            ])
            ->with([
                'negara:id,nama',
                'kabupatenKota:kode,kode_provinsi,nama,nama_lain',
                'kabupatenKota.provinsi:kode,nama,nama_lain',
                'media',
            ])
            ->withCount('rute')
            ->findOrFail($id);

        $gunung->image_url        = $gunung->getImageUrl();
        $gunung->created_at_human = $gunung->created_at->diffForHumans();

        unset($gunung->media);

        return response()->json([
            'success' => true,
            'message' => 'Mountain retrieved successfully',
            'data'    => $gunung,
        ]);
    }
}
