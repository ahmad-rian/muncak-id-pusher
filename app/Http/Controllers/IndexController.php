<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Gunung;
use App\Models\Negara;
use App\Models\Rute;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Spatie\Image\Image;
use Spatie\SchemaOrg\Schema;

class IndexController extends Controller
{
    public function index(Request $req)
    {
        $negaraSlug     = $req->get('negara', 'indonesia');
        $selectedNegara = Negara::where('slug', $negaraSlug)->first();
        $negaraList     = Negara::orderBy('nama', 'asc')->get();

        /**
         * @var Paginator
         */
        $rute = Rute::query()
            ->select([
                "id",
                "kode_desa",
                "negara_id",
                "gunung_id",
                "deskripsi",
                "nama",
                "slug",
                "rute_tingkat_kesulitan_id",
                "comment_rating",
                "comment_count"
            ])
            ->with([
                "gunung:id,nama,elev,slug",
                "negara:id,nama,nama_lain",
                "desa:kode,nama,nama_lain,kode_kecamatan",
                "desa.kecamatan:kode,nama,nama_lain,kode_kabupaten_kota",
                "desa.kecamatan.kabupatenKota:kode,nama,nama_lain,kode_provinsi",
                "desa.kecamatan.kabupatenKota.provinsi:kode,nama,nama_lain",
                "ruteTingkatKesulitan:id,nama",
                "lastPoint:id,rute_id,jarak_total,waktu_tempuh_kumulatif"
            ])
            ->when(
                $selectedNegara,
                function ($query) use ($selectedNegara) {
                    $query->where('negara_id', $selectedNegara->id);
                }
            )
            ->when(
                $req->has('q'),
                function ($query) use ($req) {
                    $query->where(function ($subQuery) use ($req) {
                        $subQuery->where('nama', 'like', "%{$req->q}%")
                            ->orWhereHas('gunung', function ($query) use ($req) {
                                $query->where('nama', 'like', "%{$req->q}%");
                            });
                    });
                }
            )
            ->orderBy('comment_count', 'desc')
            ->paginate(12)
            ->through(function ($item) {
                $lokasi = '';

                if ($item->negara_id && $item->negara && $item->lokasi) {
                    $negaraNama = $item->negara->nama_lain ?? $item->negara->nama;
                    $lokasi     = "{$item->lokasi}, {$negaraNama}";
                } elseif ($item->kode_desa && $item->desa) {
                    $desaNama          = $item->desa->nama_lain ?? $item->desa->nama;
                    $kabupatenKotaNama = $item->desa->kecamatan->kabupatenKota->nama_lain ?? $item->desa->kecamatan->kabupatenKota->nama;
                    $provinsiNama      = $item->desa->kecamatan->kabupatenKota->provinsi->nama_lain ?? $item->desa->kecamatan->kabupatenKota->provinsi->nama;
                    $lokasi            = "$desaNama, $kabupatenKotaNama, $provinsiNama";
                } elseif ($item->negara_id && $item->negara) {
                    $lokasi = $item->negara->nama_lain ?? $item->negara->nama;
                }

                return (object) [
                    "id"                => $item->id,
                    "nama"              => "Gunung {$item->gunung->nama} via {$item->nama}",
                    "deskripsi"         => $item->deskripsi,
                    "lokasi"            => $lokasi,
                    "path"              => route("jalur-pendakian.slug", $item->slug),
                    "jarak_total"       => number_format($item->lastPoint->jarak_total / 1000, 1) . " km",
                    "waktu_tempuh"      => number_format($item->lastPoint->waktu_tempuh_kumulatif / 60, 1) . " jam",
                    "tingkat_kesulitan" => $item->ruteTingkatKesulitan?->nama,
                    "comment_rating"    => number_format($item->comment_rating, 1),
                    "comment_count"     => $item->comment_count,
                    "image"             => $item->getImageUrl(),
                ];
            });

        $currentPage = $rute->currentPage();
        $lastPage    = $rute->lastPage();
        $pagination  = [];

        if ($currentPage > 1) $pagination[] = 'prev';
        if ($currentPage > 2) $pagination[] = 1;
        if ($currentPage > 3) $pagination[] = '...';

        for ($i = max(1, $currentPage - 1); $i <= min($lastPage, $currentPage + 1); $i++) {
            $pagination[] = $i;
        }

        if ($currentPage < $lastPage - 2) $pagination[] = '...';
        if ($currentPage < $lastPage - 1) $pagination[] = $lastPage;
        if ($currentPage < $lastPage) $pagination[] = 'next';

        $website = Schema::website()
            ->name('muncak.id')
            ->url(url('/'))
            ->description('Menyajikan informasi terintegrasi bagi para pendaki yang menginginkan kemudahan dalam merencanakan pendakian gunung dan penjelajahan pegunungan di Indonesia dan luar negeri')
            ->publisher(
                Schema::organization()
                    ->name('muncak.id')
                    ->logo(Schema::imageObject()->url(asset('favicon/favicon-32x32.png')))
            );
        $schemaOrg = $website->toScript();

        return view("index", [
            "rute"           => $rute,
            "page"           => $currentPage,
            "q"              => $req->q,
            "negara"         => $negaraSlug,
            "negaraList"     => $negaraList,
            "selectedNegara" => $selectedNegara,
            "pagination"     => $pagination,
            "schemaOrg"      => $schemaOrg,
        ]);
    }

    public function sitemap()
    {
        $common = [
            route('index'),
            route('jelajah.index'),
            route('blog.index'),
        ];

        $rute = Rute::select([
            "slug",
        ])
            ->with([
                "gunung:id,slug",
            ])
            ->get()
            ->map(fn($item) => [
                route('jalur-pendakian.slug', [$item->slug]),
                route('jalur-pendakian.slug.prediksi-cuaca', [$item->slug]),
                route('jalur-pendakian.slug.segmentasi', [$item->slug]),
            ])
            ->flatten()
            ->toArray();

        $artikel = Blog::select([
            "slug",
        ])
            ->get()
            ->map(fn($item) => [
                route('blog.slug', [$item->slug]),
            ])
            ->flatten()
            ->toArray();

        return response()
            ->view('sitemap', [
                "urls" => array_merge($common, $rute, $artikel),
            ])
            ->header('Content-Type', 'application/xml');
    }
}
