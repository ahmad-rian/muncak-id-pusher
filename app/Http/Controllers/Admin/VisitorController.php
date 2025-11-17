<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    public function index()
    {
        return view("admin.visitor.index");
    }

    public function cleanup()
    {
        $twoWeeksAgo = now()->subWeeks(4);
        Visitor::where('created_at', '<', $twoWeeksAgo)->delete();

        return redirect()
            ->route('admin.visitor.index')
            ->with('success', 'Old visitor records deleted successfully.');
    }

    /**
     * API
     */
    public function apiIndex(Request $req)
    {
        $query = Visitor::take($req->take)->skip(($req->page - 1) * $req->take);

        if ($req->search) $query
            ->where("ip_address", "like", "%{$req->search}%");

        if ($req->order && $req->direction) $query->orderBy($req->order, $req->direction);
        else $query->orderBy("ip_address");

        $data = $query->get()->each(function ($item) {
            $item->created_at_human = $item->created_at->diffForHumans();
        });

        $count = $req->search
            ? Visitor::where("ip_address", "like", "%{$req->search}%")
            ->count()
            : Visitor::count();

        return view("admin.visitor.components.table", ["data" => $data, "count" => $count]);
    }
}
