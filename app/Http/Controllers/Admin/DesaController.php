<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DesaRequest;
use App\Models\Desa;
use Illuminate\Http\Request;

class DesaController extends Controller
{
    public function index()
    {
        return view('admin.desa.index');
    }

    public function create()
    {
        return view('admin.desa.form', [
            'type'  => 'Create',
            'data'  => new Desa(),
            'route' => route('admin.desa.store')
        ]);
    }

    public function store(DesaRequest $req)
    {
        Desa::create($req->validated());

        return redirect()->route('admin.desa.index')->with('toast', [
            'type'    => 'success',
            'title'   => 'Success: Item Added!',
            'message' => "The item has been successfully added.",
        ]);
    }

    public function show(Desa $desa)
    {
        return view('admin.desa.show', ['data' => $desa]);
    }

    public function edit(Desa $desa)
    {
        return view('admin.desa.form', [
            'data'  => $desa,
            'type'  => 'Edit',
            'route' => route('admin.desa.update', $desa)
        ]);
    }

    public function update(DesaRequest $req, Desa $desa)
    {
        $desa->update($req->validated());

        return redirect()->route('admin.desa.index')->with('toast', [
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
        $query = Desa::take($req->take)->skip(($req->page - 1) * $req->take);

        if ($req->search) $query
            ->where('nama', 'like', "%{$req->search}%")
            ->orWhere('nama_lain', 'like', "%{$req->search}%")
            ->orWhere('kode', 'like', "%{$req->search}%");

        if ($req->order && $req->direction) $query->orderBy($req->order, $req->direction);
        else $query->orderBy('kode');

        $data = $query->get();

        $count = $req->search
            ? Desa::where('nama', 'like', "%{$req->search}%")
            ->orWhere('nama_lain', 'like', "%{$req->search}%")
            ->orWhere('kode', 'like', "%{$req->search}%")
            ->count()
            : Desa::count();

        return view('admin.desa.components.table', ['data' => $data, 'count' => $count]);
    }

    public function apiDelete(Desa $desa)
    {
        try {
            $desa->delete();

            session()->flash("toast", [
                "type"    => "success",
                "title"   => "Success: Desa Deleted!",
                "message" => "Desa has been successfully deleted.",
            ]);

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            session()->flash("toast", [
                "type"    => "error",
                "title"   => "Error: Deletion Failed!",
                "message" => "An error occurred while deleting the desa. Please try again.",
            ]);

            return response()->json(["success" => false], 500);
        }
    }

    public function apiSelect(Request $req)
    {
        $query = Desa::with([
            'kecamatan:kode,kode_kabupaten_kota,nama,nama_lain',
            'kecamatan.kabupatenKota:kode,kode_provinsi,nama,nama_lain',
            'kecamatan.kabupatenKota.provinsi:kode,nama,nama_lain'
        ])
            ->select(['kode', 'nama', 'nama_lain', 'kode_kecamatan'])
            ->take(10)
            ->orderBy('nama', 'asc');

        $query = $req->value
            ? $query->where(['kode' => $req->value])
            : $query
            ->where('nama', 'LIKE', (string) '%' . $req->search . '%')
            ->orWhere('kode', '=', $req->search)
            ->orWhereHas(
                'kecamatan',
                fn($query) => $query
                    ->where('nama', 'like', "%{$req->search}%")
                    ->orWhere('nama_lain', 'like', "%{$req->search}%")
            )
            ->orWhereHas(
                'kecamatan.kabupatenKota',
                fn($query) => $query
                    ->where('nama', 'like', "%{$req->search}%")
                    ->orWhere('nama_lain', 'like', "%{$req->search}%")
            )
            ->orWhereHas(
                'kecamatan.kabupatenKota.provinsi',
                fn($query) => $query
                    ->where('nama', 'like', "%{$req->search}%")
                    ->orWhere('nama_lain', 'like', "%{$req->search}%")
            );

        $data = $query->get();
        $option = collect($data)->map(function ($item) {
            $nama              = $item->nama_lain ?? $item->nama;
            $kecamatanNama     = $item->kecamatan->nama_lain ?? $item->kecamatan->nama;
            $kabupatenKotaNama = $item->kecamatan->kabupatenKota->nama_lain ?? $item->kecamatan->kabupatenKota->nama;
            $provinsiNama      = $item->kecamatan->kabupatenKota->provinsi->nama_lain ?? $item->kecamatan->kabupatenKota->provinsi->nama;
            $label             = "{$nama}, {$kecamatanNama}, {$kabupatenKotaNama}, {$provinsiNama}";

            return [
                'id'    => $item->kode,
                'label' => $label,
            ];
        })->all();

        return response()->json($option);
    }
}
