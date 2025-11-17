<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProvinsiRequest;
use App\Models\Provinsi;
use Illuminate\Http\Request;

class ProvinsiController extends Controller
{
    public function index()
    {
        return view('admin.provinsi.index');
    }

    public function create()
    {
        return view('admin.provinsi.form', [
            'type'  => 'Create',
            'data'  => new Provinsi(),
            'route' => route('admin.provinsi.store')
        ]);
    }

    public function store(ProvinsiRequest $req)
    {
        Provinsi::create($req->validated());

        return redirect()->route('admin.provinsi.index')->with('toast', [
            'type'    => 'success',
            'title'   => 'Success: Item Added!',
            'message' => "The item has been successfully added.",
        ]);
    }

    public function show(Provinsi $provinsi)
    {
        return view('admin.provinsi.show', ['data' => $provinsi]);
    }

    public function edit(Provinsi $provinsi)
    {
        return view('admin.provinsi.form', [
            'data'  => $provinsi,
            'type'  => 'Edit',
            'route' => route('admin.provinsi.update', $provinsi)
        ]);
    }

    public function update(ProvinsiRequest $req, Provinsi $provinsi)
    {
        $provinsi->update($req->validated());

        return redirect()->route('admin.provinsi.index')->with('toast', [
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
        $query = Provinsi::take($req->take)->skip(($req->page - 1) * $req->take);

        if ($req->search) $query
            ->where('nama', 'like', "%{$req->search}%")
            ->orWhere('nama_lain', 'like', "%{$req->search}%")
            ->orWhere('kode', 'like', "%{$req->search}%");

        if ($req->order && $req->direction) $query->orderBy($req->order, $req->direction);
        else $query->orderBy('kode');

        $data = $query->get();

        $count = $req->search
            ? Provinsi::where('nama', 'like', "%{$req->search}%")
            ->orWhere('nama_lain', 'like', "%{$req->search}%")
            ->orWhere('kode', 'like', "%{$req->search}%")
            ->count()
            : Provinsi::count();

        return view('admin.provinsi.components.table', ['data' => $data, 'count' => $count]);
    }

    public function apiDelete(Provinsi $provinsi)
    {
        try {
            $provinsi->delete();

            session()->flash("toast", [
                "type"    => "success",
                "title"   => "Success: Provinsi Deleted!",
                "message" => "Provinsi has been successfully deleted.",
            ]);

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            session()->flash("toast", [
                "type"    => "error",
                "title"   => "Error: Deletion Failed!",
                "message" => "An error occurred while deleting the provinsi. Please try again.",
            ]);

            return response()->json(["success" => false], 500);
        }
    }

    public function apiSelect(Request $req)
    {
        $query = Provinsi::select(['kode', 'nama', 'nama_lain'])
            ->take(10)
            ->orderBy('nama', 'asc');

        $searchValue = "%{$req->search}%";

        $query = $req->value
            ? $query->where(['kode' => $req->value])
            : $query
            ->where('nama', 'LIKE', $searchValue)
            ->orWhere('nama_Lain', 'LIKE', $searchValue)
            ->orWhere('kode', 'LIKE', $searchValue);

        $data = $query->get();
        $option = collect($data)->map(function ($item) {
            return [
                'id' => $item->kode,
                'label' => "{$item->kode} - {$item->nama} - {$item->nama_lain}",
            ];
        })->all();

        return response()->json($option);
    }
}
