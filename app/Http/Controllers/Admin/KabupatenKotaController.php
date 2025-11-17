<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KabupatenKotaRequest;
use App\Models\KabupatenKota;
use Illuminate\Http\Request;

class KabupatenKotaController extends Controller
{
    public function index()
    {
        return view('admin.kabupaten-kota.index');
    }

    public function create()
    {
        return view('admin.kabupaten-kota.form', [
            'type'  => 'Create',
            'data'  => new KabupatenKota(),
            'route' => route('admin.kabupaten-kota.store')
        ]);
    }

    public function store(KabupatenKotaRequest $req)
    {
        KabupatenKota::create($req->validated());

        return redirect()->route('admin.kabupaten-kota.index')->with('toast', [
            'type'    => 'success',
            'title'   => 'Success: Item Added!',
            'message' => "The item has been successfully added.",
        ]);
    }

    public function show(KabupatenKota $kabupatenKota)
    {
        return view('admin.kabupaten-kota.show', ['data' => $kabupatenKota]);
    }

    public function edit(KabupatenKota $kabupatenKota)
    {
        return view('admin.kabupaten-kota.form', [
            'data'  => $kabupatenKota,
            'type'  => 'Edit',
            'route' => route('admin.kabupaten-kota.update', $kabupatenKota)
        ]);
    }

    public function update(KabupatenKotaRequest $req, KabupatenKota $kabupatenKota)
    {
        $kabupatenKota->update($req->validated());

        return redirect()->route('admin.kabupaten-kota.index')->with('toast', [
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
        $query = KabupatenKota::take($req->take)->skip(($req->page - 1) * $req->take);

        if ($req->search) $query
            ->where('nama', 'like', "%{$req->search}%")
            ->orWhere('nama_lain', 'like', "%{$req->search}%")
            ->orWhere('kode', 'like', "%{$req->search}%");

        if ($req->order && $req->direction) $query->orderBy($req->order, $req->direction);
        else $query->orderBy('kode');

        $data = $query->get();

        $count = $req->search
            ? KabupatenKota::where('nama', 'like', "%{$req->search}%")
            ->orWhere('nama_lain', 'like', "%{$req->search}%")
            ->orWhere('kode', 'like', "%{$req->search}%")
            ->count()
            : KabupatenKota::count();

        return view('admin.kabupaten-kota.components.table', ['data' => $data, 'count' => $count]);
    }

    public function apiDelete(KabupatenKota $kabupatenKota)
    {
        try {
            $kabupatenKota->delete();

            session()->flash("toast", [
                "type"    => "success",
                "title"   => "Success: Kabupaten Kota Deleted!",
                "message" => "Kabupaten Kota has been successfully deleted.",
            ]);

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            session()->flash("toast", [
                "type"    => "error",
                "title"   => "Error: Deletion Failed!",
                "message" => "An error occurred while deleting the kabupaten/kota. Please try again.",
            ]);

            return response()->json(["success" => false], 500);
        }
    }

    public function apiSelect(Request $req)
    {
        $query = KabupatenKota::with(['provinsi:kode,nama,nama_lain'])
            ->select(['kode', 'nama', 'nama_lain', 'kode_provinsi'])
            ->take(10)
            ->orderBy('kode', 'asc');

        $searchValue = "%{$req->search}%";

        $query = $req->value
            ? $query->where(['kode' => $req->value])
            : $query
            ->where('nama', 'LIKE', $searchValue)
            ->orWhere('nama_lain', 'LIKE', $searchValue)
            ->orWhereHas(
                'provinsi',
                fn($query) => $query
                    ->where('nama', 'LIKE', $searchValue)
                    ->orWhere('nama_lain', 'LIKE', $searchValue)
            );

        $data = $query->get();
        $option = collect($data)->map(function ($item) {
            $nama         = $item->nama_lain ?? $item->nama;
            $provinsiNama = $item->provinsi->nama_lain ?? $item->provinsi->nama;
            $label        = "{$item->kode} - {$nama}, {$provinsiNama}";

            return [
                'id'    => $item->kode,
                'label' => $label,
            ];
        })->all();

        return response()->json($option);
    }
}
