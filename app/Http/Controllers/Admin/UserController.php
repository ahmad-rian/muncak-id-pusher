<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController as PublicUserController;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        return view("admin.user.index");
    }

    public function create()
    {
        $role = Role::oldest()
            ->pluck('name', 'id')
            ->toArray();

        return view("admin.user.form", [
            "type"  => "Create",
            "data"  => new User(),
            "role"  => $role,
            "route" => route("admin.user.store")
        ]);
    }

    public function store(UserRequest $req)
    {
        try {
            DB::beginTransaction();

            $role = Role::findOrFail($req->role_id);
            $user = User::create($req->all());
            $user->assignRole($role);
            $controller = new PublicUserController();
            $controller->createPhotoProfile($user);

            DB::commit();

            return redirect()->route("admin.user.index")->with("toast", [
                "type"    => "success",
                "title"   => "Success: User Added!",
                "message" => "User has been successfully added.",
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->withInput()->with("toast", [
                "type"    => "error",
                "title"   => "Error: User Not Added!",
                "message" => "An error occurred while adding the user",
            ]);
        }
    }

    public function show(User $user)
    {
        return view("admin.user.show", ["data" => $user->load([
            "userProvider:user_id,provider",
            "roles:name"
        ])]);
    }

    public function edit(User $user)
    {
        $role = Role::oldest()
            ->pluck('name', 'id')
            ->toArray();

        return view("admin.user.form", [
            "type"     => "Edit",
            "role"     => $role,
            "data"     => $user,
            "route"    => route("admin.user.update", $user)
        ]);
    }

    public function update(UserRequest $req, User $user)
    {
        $valid = $req->except(["old_password", "new_password", "new_password_confirmation"]);

        if ($req->old_password && $req->new_password) {
            $valid['password'] = Hash::make($req->new_password);
        }

        $user->update($valid);

        return redirect()->route("admin.user.index")->with("toast", [
            "type"    => "success",
            "title"   => "Success: User Updated!",
            "message" => "User has been successfully updated.",
        ]);
    }

    /**
     * API
     */
    public function apiIndex(Request $req)
    {
        $query = User::take($req->take)
            ->skip(($req->page - 1) * $req->take)
            ->with([
                "userProvider:user_id,provider",
                "roles:name"
            ]);

        if ($req->search) $query
            ->where("name", "like", "%{$req->search}%");

        if ($req->order && $req->direction) $query->orderBy($req->order, $req->direction);
        else $query->orderBy("name");

        $data = $query->get();

        $count = $req->search
            ? User::where("name", "like", "%{$req->search}%")
            ->count()
            : User::count();

        return view("admin.user.components.table", ["data" => $data, "count" => $count]);
    }

    public function apiDelete(User $user)
    {
        try {
            if (Auth::user()->id == $user->id) throw new \Exception(0, 1);

            $user->delete();

            session()->flash("toast", [
                "type"    => "success",
                "title"   => "Success: User Deleted!",
                "message" => "User has been successfully deleted.",
            ]);

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            session()->flash("toast", [
                "type"    => "error",
                "title"   => "Error: Deletion Failed!",
                "message" => "An error occurred while deleting the user. Please try again.",
            ]);

            return response()->json(["success" => false], 500);
        }
    }
}
