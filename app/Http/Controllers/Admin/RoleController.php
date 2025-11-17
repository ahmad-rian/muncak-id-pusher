<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        return view("admin.role.index");
    }

    public function create()
    {
        return view("admin.role.form", [
            "type"  => "Create",
            "data"  => new Role(),
            "route" => route("admin.role.store")
        ]);
    }

    public function store(RoleRequest $req)
    {
        Role::create($req->all());

        return redirect()->route("admin.role.index")->with("toast", [
            "type"    => "success",
            "title"   => "Success: Role Added!",
            "message" => "Role has been successfully added.",
        ]);
    }

    public function show(Role $role)
    {
        return view("admin.role.show", ["data" => $role]);
    }

    public function edit(Role $role)
    {
        return view("admin.role.form", [
            "type"  => "Edit",
            "data"  => $role,
            "route" => route("admin.role.update", $role)
        ]);
    }

    public function update(RoleRequest $req, Role $role)
    {
        $role->update($req->all());

        return redirect()->route("admin.role.index")->with("toast", [
            "type"    => "success",
            "title"   => "Success: Role Updated!",
            "message" => "Role has been successfully updated.",
        ]);
    }

    /**
     * API
     */
    public function apiIndex(Request $req)
    {
        $query = Role::take($req->take)->skip(($req->page - 1) * $req->take);

        if ($req->search) $query
            ->where("name", "like", "%{$req->search}%");

        if ($req->order && $req->direction) $query->orderBy($req->order, $req->direction);
        else $query->orderBy("name");

        $data = $query->get();

        $count = $req->search
            ? Role::where("name", "like", "%{$req->search}%")
            ->count()
            : Role::count();

        return view("admin.role.components.table", ["data" => $data, "count" => $count]);
    }

    public function apiDelete(Role $role)
    {
        try {
            $role->delete();

            session()->flash("toast", [
                "type"    => "success",
                "title"   => "Success: Role Deleted!",
                "message" => "Role has been successfully deleted.",
            ]);

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            session()->flash("toast", [
                "type"    => "error",
                "title"   => "Error: Deletion Failed!",
                "message" => "An error occurred while deleting the role. Please try again.",
            ]);

            return response()->json(["success" => false], 500);
        }
    }
}
