<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KecamatanRequest;
use App\Models\Kecamatan;
use Illuminate\Http\Request;

class KecamatanController extends Controller
{
    public function index()
    {
        return view('admin.kecamatan.index');
    }

    public function create()
    {
        return view('admin.kecamatan.form', [
            'type'  => 'Create',
            'data'  => new Kecamatan(),
            'route' => route('admin.kecamatan.store')
        ]);
    }

    public function store(KecamatanRequest $req)
    {
        Kecamatan::create($req->validated());

        return redirect()->route('admin.kecamatan.index')->with('toast', [
            'type'    => 'success',
            'title'   => 'Success: Item Added!',
            'message' => "The item has been successfully added.",
        ]);
    }

    public function show(Kecamatan $kecamatan)
    {
        return view('admin.kecamatan.show', ['data' => $kecamatan]);
    }

    public function edit(Kecamatan $kecamatan)
    {
        return view('admin.kecamatan.form', [
            'data'  => $kecamatan,
            'type'  => 'Edit',
            'route' => route('admin.kecamatan.update', $kecamatan)
        ]);
    }

    public function update(KecamatanRequest $req, Kecamatan $kecamatan)
    {
        $kecamatan->update($req->validated());

        return redirect()->route('admin.kecamatan.index')->with('toast', [
            'type' => 'success',
            'title' => 'Success: Item Updated!',
            'message' => "The item has been successfully updated.",
        ]);
    }

    /**
     * API
     */
    public function apiIndex(Request $req)
    {
        $query = Kecamatan::take($req->take)->skip(($req->page - 1) * $req->take);

        if ($req->search) $query
            ->where('nama', 'like', "%{$req->search}%")
            ->orWhere('nama_lain', 'like', "%{$req->search}%")
            ->orWhere('kode', 'like', "%{$req->search}%");

        if ($req->order && $req->direction) $query->orderBy($req->order, $req->direction);
        else $query->orderBy('kode');

        $data = $query->get();

        $count = $req->search
            ? Kecamatan::where('nama', 'like', "%{$req->search}%")
            ->orWhere('nama_lain', 'like', "%{$req->search}%")
            ->orWhere('kode', 'like', "%{$req->search}%")
            ->count()
            : Kecamatan::count();

        return view('admin.kecamatan.components.table', ['data' => $data, 'count' => $count]);
    }

    public function apiDelete(Kecamatan $kecamatan)
    {
        try {
            $kecamatan->delete();

            session()->flash("toast", [
                "type"    => "success",
                "title"   => "Success: Kecamatan Deleted!",
                "message" => "Kecamatan has been successfully deleted.",
            ]);

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            session()->flash("toast", [
                "type"    => "error",
                "title"   => "Error: Deletion Failed!",
                "message" => "An error occurred while deleting the kecamatan. Please try again.",
            ]);

            return response()->json(["success" => false], 500);
        }
    }

    public function apiSelect(Request $req)
    {
        $query = Kecamatan::with(['kabupatenKota:kode,nama,nama_lain'])
            ->select(['kode', 'nama', 'nama_lain', 'kode_kabupaten_kota'])
            ->take(10)
            ->orderBy('kode', 'asc');

        $searchValue = "%{$req->search}%";

        $query = $req->value
            ? $query->where(['kode' => $req->value])
            : $query
            ->where('nama', 'LIKE', $searchValue)
            ->orWhere('nama_lain', 'LIKE', $searchValue)
            ->orWhereHas(
                'kabupatenKota',
                fn($query) => $query
                    ->where('nama', 'LIKE', $searchValue)
                    ->orWhere('nama_lain', 'LIKE', $searchValue)
            );

        $data = $query->get();
        $option = collect($data)->map(function ($item) {
            $nama              = $item->nama_lain ?? $item->nama;
            $kabupatenKotaNama = $item->kabupatenKota->nama_lain ?? $item->kabupatenKota->nama;
            $label             = "{$item->kode} - {$nama}, {$kabupatenKotaNama}";

            return [
                'id'    => $item->kode,
                'label' => $label,
            ];
        })->all();

        return response()->json($option);
    }
}
