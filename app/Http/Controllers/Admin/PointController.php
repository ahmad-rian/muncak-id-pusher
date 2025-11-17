<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PointRequest;
use App\Models\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PointController extends Controller
{
    public function index()
    {
        return view('admin.point.index');
    }

    public function edit(Point $point)
    {
        return view('admin.point.form', [
            'data'  => $point,
            'type'  => 'Edit',
            'route' => route('admin.point.update', $point)
        ]);
    }

    public function update(Point $point, PointRequest $req)
    {
        try {
            DB::beginTransaction();

            $point->update($req->except(['gallery']));

            if ($req->hasFile('gallery')) {
                if ($point->hasMedia('point-gallery')) {
                    $point->clearMediaCollection('point-gallery');
                }

                foreach ($req->file('gallery') as $item) {
                    $galleryName = uniqid('point-gallery-') . $point->id . '.png';
                    $point
                        ->addMedia($item)
                        ->usingFileName($galleryName)
                        ->usingName($galleryName)
                        ->toMediaCollection('point-gallery');
                }
            }

            DB::commit();

            return redirect()->route('admin.rute.show', $point->rute)->with('toast', [
                'type'    => 'success',
                'title'   => 'Success: Item Updated!',
                'message' => "The item has been successfully updated.",
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('toast', [
                "type"    => "error",
                "title"   => "Error: Modification Failed!",
                "message" => "An error occurred while updating the point. Please try again.",
            ]);
        }
    }

    /**
     * API
     */

    public function apiIndex(Request $req)
    {
        $query = Point::with([
            'rute:id,nama,gunung_id',
            'rute.gunung:id,nama'
        ])
            ->take($req->take)
            ->skip(($req->page - 1) * $req->take)
            ->orderBy('rute_id', 'desc')
            ->orderBy('nomor', 'desc');

        if ($req->search) $query
            ->where('nama', 'like', "%{$req->search}%")
            ->orWhereHas('rute', fn($query) => $query->where('nama', 'like', "%{$req->search}%"))
            ->orWhereHas('rute.gunung', fn($query) => $query->where('nama', 'like', "%{$req->search}%"));

        $data = $query->get();

        $count = Point::count();
        return view('admin.point.components.table', ['data' => $data, 'count' => $count]);
    }

    public function apiUpdate(Point $point, Request $req)
    {
        $point->update($req->all());
        return response()->json([
            'success' => true,
            'toast' => [
                'type' => 'success',
                'title' => 'Success: Item Updated!',
                'message' => "The item has been successfully updated.",
            ]
        ]);
    }
}
