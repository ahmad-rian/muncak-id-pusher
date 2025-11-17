<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RuteTingkatKesulitanRequest;
use App\Models\Rute;
use App\Models\RuteTingkatKesulitan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RuteTingkatKesulitanController extends Controller
{
    public function index()
    {
        return view("admin.rute-tingkat-kesulitan.index");
    }

    public function create()
    {
        return view("admin.rute-tingkat-kesulitan.form", [
            "type"  => "Create",
            "data"  => new RuteTingkatKesulitan(),
            "route" => route("admin.rute-tingkat-kesulitan.store")
        ]);
    }

    public function store(RuteTingkatKesulitanRequest $req)
    {
        $valid         = $req->all();
        $valid['slug'] = Str::slug($valid['nama']);

        RuteTingkatKesulitan::create($valid);

        return redirect()->route("admin.rute-tingkat-kesulitan.index")->with("toast", [
            "type"    => "success",
            "title"   => "Success: Rute Tingkat Kesulitan Added!",
            "message" => "Rute Tingkat Kesulitan has been successfully added.",
        ]);
    }

    public function show(RuteTingkatKesulitan $ruteTingkatKesulitan)
    {
        return view("admin.rute-tingkat-kesulitan.show", ["data" => $ruteTingkatKesulitan]);
    }

    public function edit(RuteTingkatKesulitan $ruteTingkatKesulitan)
    {
        return view("admin.rute-tingkat-kesulitan.form", [
            "type"  => "Edit",
            "data"  => $ruteTingkatKesulitan,
            "route" => route("admin.rute-tingkat-kesulitan.update", $ruteTingkatKesulitan)
        ]);
    }

    public function update(RuteTingkatKesulitanRequest $req, RuteTingkatKesulitan $ruteTingkatKesulitan)
    {
        $valid         = $req->all();
        $valid['slug'] = Str::slug($valid['nama']);

        $ruteTingkatKesulitan->update($valid);

        return redirect()->route("admin.rute-tingkat-kesulitan.index")->with("toast", [
            "type"    => "success",
            "title"   => "Success: Rute Tingkat Kesulitan Updated!",
            "message" => "Rute Tingkat Kesulitan has been successfully updated.",
        ]);
    }

    /**
     * API
     */
    public function apiIndex(Request $req)
    {
        $query = RuteTingkatKesulitan::take($req->take)->skip(($req->page - 1) * $req->take);

        if ($req->search) $query
            ->where("nama", "like", "%{$req->search}%");

        if ($req->order && $req->direction) $query->orderBy($req->order, $req->direction);
        else $query->orderBy("nama");

        $data = $query->get();

        $count = $req->search
            ? RuteTingkatKesulitan::where("nama", "like", "%{$req->search}%")
            ->count()
            : RuteTingkatKesulitan::count();

        return view("admin.rute-tingkat-kesulitan.components.table", ["data" => $data, "count" => $count]);
    }

    public function apiDelete(RuteTingkatKesulitan $ruteTingkatKesulitan)
    {
        try {
            $ruteTingkatKesulitan->delete();

            session()->flash("toast", [
                "type"    => "success",
                "title"   => "Success: Rute Tingkat Kesulitan Deleted!",
                "message" => "Rute Tingkat Kesulitan has been successfully deleted.",
            ]);

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            session()->flash("toast", [
                "type"    => "error",
                "title"   => "Error: Deletion Failed!",
                "message" => "An error occurred while deleting the rute. Please try again",
            ]);

            return response()->json(["success" => false], 500);
        }
    }

    public function apiSelect(Request $req)
    {
        $query = RuteTingkatKesulitan::select(['id', 'nama'])
            ->take(10)
            ->orderBy('nama', 'asc');

        $searchValue = (string) '%' . $req->search . '%';

        $query = $req->value
            ? $query->where(['id' => $req->value])
            : $query
            ->where('nama', 'LIKE', $searchValue);

        $data = $query->get();
        $option = collect($data)->map(function ($item) {
            return [
                'id' => $item->id,
                'label' => $item->nama,
            ];
        })->all();

        return response()->json($option);
    }
}
