<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NegaraRequest;
use App\Models\Negara;
use Illuminate\Http\Request;

class NegaraController extends Controller
{
    public function index()
    {
        return view('admin.negara.index');
    }

    public function create()
    {
        return view('admin.negara.form', [
            'type'  => 'Create',
            'data'  => new Negara(),
            'route' => route('admin.negara.store')
        ]);
    }

    public function store(NegaraRequest $req)
    {
        Negara::create($req->validated());

        return redirect()->route('admin.negara.index')->with('toast', [
            'type'    => 'success',
            'title'   => 'Success: Item Added!',
            'message' => "The item has been successfully added.",
        ]);
    }

    public function show(Negara $negara)
    {
        return view('admin.negara.show', ['data' => $negara]);
    }

    public function edit(Negara $negara)
    {
        return view('admin.negara.form', [
            'data'  => $negara,
            'type'  => 'Edit',
            'route' => route('admin.negara.update', $negara)
        ]);
    }

    public function update(NegaraRequest $req, Negara $negara)
    {
        $negara->update($req->validated());

        return redirect()->route('admin.negara.index')->with('toast', [
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
        $query = Negara::take($req->take)->skip(($req->page - 1) * $req->take);

        if ($req->search) $query
            ->where('nama', 'like', "%{$req->search}%")
            ->orWhere('nama_lain', 'like', "%{$req->search}%")
            ->orWhere('kode', 'like', "%{$req->search}%");

        if ($req->order && $req->direction) $query->orderBy($req->order, $req->direction);
        else $query->orderBy('nama');

        $data = $query->get();

        $count = $req->search
            ? Negara::where('nama', 'like', "%{$req->search}%")
            ->orWhere('nama_lain', 'like', "%{$req->search}%")
            ->orWhere('kode', 'like', "%{$req->search}%")
            ->count()
            : Negara::count();

        return view('admin.negara.components.table', ['data' => $data, 'count' => $count]);
    }

    public function apiDelete(Negara $negara)
    {
        try {
            $negara->delete();

            session()->flash("toast", [
                "type"    => "success",
                "title"   => "Success: Negara Deleted!",
                "message" => "Negara has been successfully deleted.",
            ]);

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            session()->flash("toast", [
                "type"    => "error",
                "title"   => "Error: Deletion Failed!",
                "message" => "An error occurred while deleting the negara. Please try again.",
            ]);

            return response()->json(["success" => false], 500);
        }
    }

    public function apiSelect(Request $req)
    {
        $query = Negara::select(['id', 'nama', 'nama_lain'])
            ->take(10)
            ->orderBy('nama', 'asc');

        $searchValue = "%{$req->search}%";

        $query = $req->value
            ? $query->where(['id' => $req->value])
            : $query
            ->where('nama', 'LIKE', $searchValue)
            ->orWhere('nama_lain', 'LIKE', $searchValue);

        $data = $query->get();
        $option = collect($data)->map(function ($item) {
            return [
                'id' => $item->id,
                'label' => "{$item->nama}" . ($item->nama_lain ? " - {$item->nama_lain}" : ""),
            ];
        })->all();

        return response()->json($option);
    }
}