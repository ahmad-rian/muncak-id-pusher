<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RuteRequest;
use App\Models\Gunung;
use App\Models\Point;
use App\Models\Rute;
use App\Models\RuteTingkatKesulitan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelReader;
use Illuminate\Support\Str;

class RuteController extends Controller
{
    public function index()
    {
        return view('admin.rute.index');
    }

    public function create()
    {
        $tingkatKesulitan = RuteTingkatKesulitan::orderBy('nama')
            ->pluck('nama', 'id');

        return view('admin.rute.form', [
            'type'             => 'Create',
            'data'             => new Rute(),
            'tingkatKesulitan' => $tingkatKesulitan,
            'route'            => route('admin.rute.store'),
        ]);
    }

    public function store(RuteRequest $req)
    {
        $valid         = $req->except(['image']);
        $gunung        = Gunung::select(['id', 'slug'])->findOrFail($req->gunung_id);
        $slug          = Str::slug($valid['nama']);
        $valid['slug'] = "gunung-{$gunung->slug}-via-{$slug}";

        try {
            DB::beginTransaction();

            $rute = Rute::create($valid);

            if ($req->hasFile('image')) {
                $imageName = uniqid('rute-image-') . $rute->id . '.png';
                $rute
                    ->addMediaFromRequest('image')
                    ->usingFileName($imageName)
                    ->usingName($imageName)
                    ->toMediaCollection('rute-image');
            }

            if ($req->hasFile('gallery')) {
                foreach ($req->file('gallery') as $item) {
                    $galleryName = uniqid('rute-gallery-') . $rute->id . '.png';
                    $rute
                        ->addMedia($item)
                        ->usingFileName($galleryName)
                        ->usingName($galleryName)
                        ->toMediaCollection('rute-gallery');
                }
            }

            DB::commit();

            return redirect()->route('admin.rute.index')->with('toast', [
                'type'    => 'success',
                'title'   => 'Success: Item Added!',
                'message' => "The item has been successfully added.",
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->with('toast', [
                "type"    => "error",
                "title"   => "Error: Creation Failed!",
                "message" => "An error occurred while storing the rute. Please try again.",
            ]);
        }
    }

    public function show(Rute $rute)
    {
        return view('admin.rute.show', [
            'data' => $rute,
            'type' => 'Show',
        ]);
    }

    public function edit(Rute $rute)
    {
        $tingkatKesulitan = RuteTingkatKesulitan::orderBy('nama')
            ->pluck('nama', 'id');

        return view('admin.rute.form', [
            'data'             => $rute,
            'type'             => 'Edit',
            'tingkatKesulitan' => $tingkatKesulitan,
            'route'            => route('admin.rute.update', $rute)
        ]);
    }

    public function update(Rute $rute, RuteRequest $req)
    {
        $valid         = $req->except(['image']);
        $gunung        = Gunung::select(['id', 'slug'])->findOrFail($req->gunung_id);
        $slug          = Str::slug($valid['nama']);
        $valid['slug'] = "gunung-{$gunung->slug}-via-{$slug}";

        try {
            DB::beginTransaction();

            $rute->update($valid);

            if ($req->hasFile('image')) {
                $imageName = uniqid('rute-image-') . $rute->id . '.png';
                $rute
                    ->addMediaFromRequest('image')
                    ->usingFileName($imageName)
                    ->usingName($imageName)
                    ->toMediaCollection('rute-image');
            }

            if ($req->hasFile('gallery')) {
                if ($rute->hasMedia('rute-gallery')) {
                    $rute->clearMediaCollection('rute-gallery');
                }

                foreach ($req->file('gallery') as $item) {
                    $galleryName = uniqid('rute-gallery-') . $rute->id . '.png';
                    $rute
                        ->addMedia($item)
                        ->usingFileName($galleryName)
                        ->usingName($galleryName)
                        ->toMediaCollection('rute-gallery');
                }
            }

            if ($req->has('gallery_new')) {
                foreach ($req->file('gallery_new') as $item) {
                    $galleryName = uniqid('rute-gallery-') . $rute->id . '.png';
                    $rute
                        ->addMedia($item)
                        ->usingFileName($galleryName)
                        ->usingName($galleryName)
                        ->toMediaCollection('rute-gallery');
                }
            }

            DB::commit();

            return redirect()->route('admin.rute.index')->with('toast', [
                'type'    => 'success',
                'title'   => 'Success: Item Updated!',
                'message' => "The item has been successfully updated.",
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->with('toast', [
                "type"    => "error",
                "title"   => "Error: Modification Failed!",
                "message" => "An error occurred while updating the rute. Please try again.",
            ]);
        }
    }

    public function storePoint(Rute $rute, Request $req)
    {
        $req->validate([
            'file' => ['required', 'file', 'mimes:xlsx']
        ]);

        $path = Storage::disk('public')->putFileAs(
            'temp',
            $req->file('file'),
            uniqid("point-{$rute->id}-")  . '.xlsx'
        );

        try {
            DB::beginTransaction();

            Point::select(['id'])->where(['rute_id' => $rute->id])->delete();

            $absolutePath = storage_path("app/public/{$path}");
            $reader       = SimpleExcelReader::create($absolutePath);
            $points       = $reader->getRows()->toArray();

            $points = array_map(function ($row) {
                return array_change_key_case($row, CASE_LOWER);
            }, $points);

            $coordinates = [];
            foreach ($points as $index => $point) {
                $latitude  = $point['x'];
                $longitude = $point['y'];

                $createdPoint = Point::create([
                    'rute_id' => $rute->id,
                    'nomor'   => $index + 1,
                    'lat'     => $latitude,
                    'long'    => $longitude,
                    'elev'    => $point['z'],
                    'point'   => DB::raw("ST_GeomFromText('POINT({$longitude} {$latitude})')"),
                ]);

                $coordinates[] = "{$createdPoint->long} {$createdPoint->lat}";
            }

            $lineString = "LINESTRING(" . implode(', ', $coordinates) . ")";
            $rute       = Rute::select(['id'])->findOrFail($rute->id);

            $rute->update([
                'rute' => DB::raw("ST_GeomFromText('{$lineString}')"),
            ]);

            DB::commit();

            Storage::disk('public')->delete($path);

            return redirect()
                ->back()
                ->with('point', true)
                ->with('toast', [
                    'type'    => 'success',
                    'title'   => 'Success: Points Added Successfully!',
                    'message' => 'The point data has been added successfully.',
                ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Storage::disk('public')->delete($path);

            return redirect()
                ->back()
                ->with('point', true)
                ->with('toast', [
                    'type'    => 'error',
                    'title'   => 'Error: Points Not Added!',
                    'message' => 'The point data could not be added. Please try again.',
                ]);
        }
    }

    /**
     * API
     */
    public function apiIndex(Request $req)
    {
        $query = Rute::with([
            'gunung:id,nama',
            'negara:id,nama,nama_lain',
            'desa:kode,kode_kecamatan,nama,nama_lain',
            'desa.kecamatan:kode,kode_kabupaten_kota,nama,nama_lain',
            'desa.kecamatan.kabupatenKota:kode,kode_provinsi,nama,nama_lain',
            'desa.kecamatan.kabupatenKota.provinsi:kode,nama,nama_lain'
        ])
            ->take($req->take)
            ->skip(($req->page - 1) * $req->take);

        if ($req->search) $query->where('nama', 'like', "%{$req->search}%")
            ->orWhere('lokasi', 'like', "%{$req->search}%")
            ->orWhereHas(
                'gunung',
                fn($query) => $query->where('nama', 'like', "%{$req->search}%")
            )
            ->orWhereHas(
                'negara',
                fn($query) => $query->where('nama', 'like', "%{$req->search}%")
                    ->orWhere('nama_lain', 'like', "%{$req->search}%")
            )
            ->orWhereHas(
                'desa',
                fn($query) => $query->where('nama', 'like', "%{$req->search}%")
            )
            ->orWhereHas(
                'desa.kecamatan',
                fn($query) => $query->where('nama', 'like', "%{$req->search}%")
            )
            ->orWhereHas(
                'desa.kecamatan.kabupatenKota',
                fn($query) => $query->where('nama', 'like', "%{$req->search}%")
            )
            ->orWhereHas(
                'desa.kecamatan.kabupatenKota.provinsi',
                fn($query) => $query->where('nama', 'like', "%{$req->search}%")
            );

        if ($req->order && $req->direction) $query->orderBy($req->order, $req->direction);
        else $query->latest();

        $data = $query->get();

        $count = $req->search
            ? Rute::where('nama', 'like', "%{$req->search}%")
            ->orWhere('lokasi', 'like', "%{$req->search}%")
            ->orWhereHas(
                'gunung',
                fn($query) => $query->where('nama', 'like', "%{$req->search}%")
            )
            ->orWhereHas(
                'negara',
                fn($query) => $query->where('nama', 'like', "%{$req->search}%")
                    ->orWhere('nama_lain', 'like', "%{$req->search}%")
            )
            ->orWhereHas(
                'desa',
                fn($query) => $query->where('nama', 'like', "%{$req->search}%")
            )
            ->orWhereHas(
                'desa.kecamatan',
                fn($query) => $query->where('nama', 'like', "%{$req->search}%")
            )
            ->orWhereHas(
                'desa.kecamatan.kabupatenKota',
                fn($query) => $query->where('nama', 'like', "%{$req->search}%")
            )
            ->orWhereHas(
                'desa.kecamatan.kabupatenKota.provinsi',
                fn($query) => $query->where('nama', 'like', "%{$req->search}%")
            )
            ->count()
            : Rute::count();
        return view('admin.rute.components.table', ['data' => $data, 'count' => $count]);
    }

    public function apiDelete(Rute $rute)
    {
        try {
            $rute->delete();

            session()->flash("toast", [
                "type"    => "success",
                "title"   => "Success: Rute Deleted!",
                "message" => "Rute has been successfully deleted.",
            ]);

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            session()->flash("toast", [
                "type"    => "error",
                "title"   => "Error: Deletion Failed!",
                "message" => "An error occurred while deleting the rute. Please try again.",
            ]);

            return response()->json(["success" => false], 500);
        }
    }

    public function apiUpdate(Rute $rute, Request $req)
    {
        $rute->update($req->all());
        return response()->json([
            'success' => true,
            'toast' => [
                'type' => 'success',
                'title' => 'Success: Item Updated!',
                'message' => "The item has been successfully updated.",
            ]
        ]);
    }

    public function apiSelect(Request $req)
    {
        $query = Rute::with([
            'gunung:id,nama',
            'negara:id,nama,nama_lain',
        ])
            ->select(['id', 'nama', 'kabupaten_kota_id', 'gunung_id', 'negara_id'])
            ->take(10)
            ->orderBy('nama', 'asc');

        $query = $req->value
            ? $query->where(['id' => $req->value])
            : $query->where('nama', 'LIKE', (string) '%' . $req->search . '%');

        $data = $query->get();
        $option = collect($data)->map(function ($item) {
            return [
                'id' => $item->id,
                'label' => "{$item->id} - {$item->nama}, {$item->gunung->nama}",
            ];
        })->all();

        return response()->json($option);
    }

    public function apiPoint(Rute $rute, Request $req)
    {
        $point = $rute->point()
            ->with([
                'rute:id,nama,gunung_id',
                'rute.gunung:id,nama',
            ])
            ->take($req->take)
            ->skip(($req->page - 1) * $req->take)
            ->orderBy('nomor', 'asc')
            ->get();

        $count = $rute->point()->count();

        return view('admin.rute.components.table-point', [
            'data' => $point,
            'count' => $count
        ]);
    }

    public function apiPoints(Rute $rute)
    {
        $rute = Rute::selectRaw('ST_AsGeoJSON(rute) as rute_geojson')->find($rute->id);
        return response()->json($rute->rute_geojson ?? "");
    }
}
