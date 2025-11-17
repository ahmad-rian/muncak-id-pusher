<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GunungRequest;
use App\Models\Gunung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GunungController extends Controller
{
    public function index()
    {
        return view('admin.gunung.index');
    }

    public function create()
    {
        return view('admin.gunung.form', [
            'type'  => 'Create',
            'data'  => new Gunung(),
            'route' => route('admin.gunung.store')
        ]);
    }

    public function store(GunungRequest $req)
    {
        DB::beginTransaction();
        
        try {
            $valid = $req->except(['image']);
            $valid['slug'] = Str::slug($valid['nama']);

            if ($valid['lat'] && $valid['long']) {
                $valid['point'] = DB::raw("ST_GeomFromText('POINT({$valid['long']} {$valid['lat']})')");
            }

            $gunung = Gunung::create($valid);

            if ($req->hasFile('image')) {
                $gunung
                    ->addMediaFromRequest('image')
                    ->usingFileName(uniqid('mountain-') . '.png')
                    ->toMediaCollection('mountain');
            }

            DB::commit();

            return redirect()->route('admin.gunung.index')->with('toast', [
                'type'    => 'success',
                'title'   => 'Success: Item Added!',
                'message' => "The item has been successfully added.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            dd($e);
            
            return redirect()->back()->withInput()->with('toast', [
                'type'    => 'error',
                'title'   => 'Error: Failed to Add Item!',
                'message' => 'An error occurred while adding the item. Please try again.',
            ]);
        }
    }

    public function show(Gunung $gunung)
    {
        $gunung->load([
            'negara:id,nama,nama_lain',
            'kabupatenKota:kode,nama,kode_provinsi',
            'kabupatenKota.provinsi:kode,nama',
        ]);
        return view('admin.gunung.show', ['data' => $gunung]);
    }

    public function edit(Gunung $gunung)
    {
        return view('admin.gunung.form', [
            'data'  => $gunung,
            'type'  => 'Edit',
            'route' => route('admin.gunung.update', $gunung)
        ]);
    }

    public function update(Gunung $gunung, GunungRequest $req)
    {
        DB::beginTransaction();
        
        try {
            $valid = $req->except(['image']);
            $valid['slug'] = Str::slug($valid['nama']);

            if ($valid['lat'] && $valid['long']) {
                $valid['point'] = DB::raw("ST_GeomFromText('POINT({$valid['long']} {$valid['lat']})')");
            }

            $gunung->update($valid);

            if ($req->hasFile('image')) {
                $gunung
                    ->addMediaFromRequest('image')
                    ->usingFileName(uniqid('mountain-') . '.png')
                    ->toMediaCollection('mountain');
            }

            DB::commit();

            return redirect()->route('admin.gunung.index')->with('toast', [
                'type' => 'success',
                'title' => 'Success: Item Updated!',
                'message' => "The item has been successfully updated.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()->withInput()->with('toast', [
                'type'    => 'error',
                'title'   => 'Error: Failed to Update Item!',
                'message' => 'An error occurred while updating the item. Please try again.',
            ]);
        }
    }


    /**
     * API
     */
    public function apiIndex(Request $req)
    {
        $query = Gunung::with([
            'negara:id,nama,nama_lain',
            'kabupatenKota:kode,nama,nama_lain,kode_provinsi',
            'kabupatenKota.provinsi:kode,nama,nama_lain',
        ])
            ->take($req->take)
            ->skip(($req->page - 1) * $req->take);

        if ($req->search) $query
            ->where('nama', 'like', "%{$req->search}%")
            ->orWhere('lokasi', 'like', "%{$req->search}%")
            ->orWhereHas('negara', fn($query) => $query->where('nama', 'like', "%{$req->search}%")->orWhere('nama_lain', 'like', "%{$req->search}%"))
            ->orWhereHas('kabupatenKota', fn($query) => $query->where('nama', 'like', "%{$req->search}%"))
            ->orWhereHas('kabupatenKota.provinsi', fn($query) => $query->where('nama', 'like', "%{$req->search}%"));

        if ($req->order && $req->direction) $query->orderBy($req->order, $req->direction);
        else $query->latest();

        $data = $query->get();

        $count = $req->search
            ? Gunung::where('nama', 'like', "%{$req->search}%")
            ->orWhere('lokasi', 'like', "%{$req->search}%")
            ->orWhereHas('negara', fn($query) => $query->where('nama', 'like', "%{$req->search}%")->orWhere('nama_lain', 'like', "%{$req->search}%"))
            ->orWhereHas('kabupatenKota', fn($query) => $query->where('nama', 'like', "%{$req->search}%"))
            ->orWhereHas('kabupatenKota.provinsi', fn($query) => $query->where('nama', 'like', "%{$req->search}%"))
            ->count()
            : Gunung::count();

        return view('admin.gunung.components.table', ['data' => $data, 'count' => $count]);
    }

    public function apiDelete(Gunung $gunung)
    {
        try {
            $gunung->delete();

            session()->flash("toast", [
                "type"    => "success",
                "title"   => "Success: Gunung Deleted!",
                "message" => "Gunung has been successfully deleted.",
            ]);

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            session()->flash("toast", [
                "type"    => "error",
                "title"   => "Error: Deletion Failed!",
                "message" => "An error occurred while deleting the gunung. Please try again.",
            ]);

            return response()->json(["success" => false], 500);
        }
    }

    public function apiSelect(Request $req)
    {
        $query = Gunung::with([
            'negara:id,nama,nama_lain',
            'kabupatenKota:kode,kode_provinsi,nama,nama_lain',
            'kabupatenKota.provinsi:kode,nama,nama_lain',
        ])
            ->select(['id', 'nama', 'negara_id', 'kode_kabupaten_kota', 'lokasi'])
            ->take(20)
            ->orderBy('nama', 'asc');

        $searchValue = (string) '%' . $req->search . '%';

        $query = $req->value
            ? $query->where(['id' => $req->value])
            : $query
            ->where('nama', 'LIKE', $searchValue)
            ->orWhere('lokasi', 'LIKE', $searchValue)
            ->orWhereHas('negara', fn($query) => $query->where('nama', 'LIKE', $searchValue)->orWhere('nama_lain', 'LIKE', $searchValue))
            ->orWhereHas(
                'kabupatenKota',
                fn($query) => $query
                    ->where('nama', 'LIKE', $searchValue)
                    ->orWhere('nama_lain', 'LIKE', $searchValue)
            )
            ->orWhereHas(
                'kabupatenKota.provinsi',
                fn($query) => $query
                    ->where('nama', 'LIKE', $searchValue)
                    ->orWhere('nama_lain', 'LIKE', $searchValue)
            );

        $data = $query->get();

        $option = collect($data)->map(function ($item) {
            $nama = $item->nama;
            $lokasi = $item->lokasi ? " ({$item->lokasi})" : '';
            $negara = $item->negara ? ($item->negara->nama_lain ?? $item->negara->nama) : '';
            $kabupatenNama = $item->kabupatenKota ? ($item->kabupatenKota->nama_lain ?? $item->kabupatenKota->nama) : '';
            $provinsiNama = $item->kabupatenKota && $item->kabupatenKota->provinsi ? ($item->kabupatenKota->provinsi->nama_lain ?? $item->kabupatenKota->provinsi->nama) : '';
            
            $location = '';
            if ($kabupatenNama && $provinsiNama) {
                $location = " - {$kabupatenNama}, {$provinsiNama}";
            } elseif ($negara) {
                $location = " - {$negara}";
            }
            
            $label = "{$nama}{$lokasi}{$location}";

            return [
                'id' => $item->id,
                'label' => $label,
            ];
        })->all();

        return response()->json($option);
    }
}
