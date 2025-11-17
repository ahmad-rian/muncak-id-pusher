<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CommentRequest;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function index()
    {
        return view('admin.comment.index');
    }

    public function create()
    {
        //
    }

    public function store(CommentRequest $request)
    {
        //
    }

    public function show(Comment $comment)
    {
        $comment->load([
            'user:id,name,username',
            'user.media' => fn($query) => $query->where('collection_name', 'photo-profile'),
            'rute:id,nama,gunung_id',
            'rute.gunung:id,nama',
            'media' => fn($query) => $query->where('collection_name', 'comment-gallery')
        ]);
        $comment->gallery = $comment->getGalleryUrls();
        $comment->user->avatar = $comment->user->getAvatarUrl();
        return view('admin.comment.show', ['data' => $comment]);
    }

    public function edit($id)
    {
        //
    }

    public function update(CommentRequest $request, $id)
    {
        //
    }

    /**
     * API
     */
    public function apiIndex(Request $req)
    {
        $take = $req->take ?? 10;
        $page = $req->page ?? 1;

        $query = Comment::with([
            'user:id,name,username',
            'user.media' => fn($query) => $query->where('collection_name', 'photo-profile'),
            'rute:id,nama,gunung_id',
            'rute.gunung:id,nama'
        ])
            ->take($take)
            ->skip(($page - 1) * $take);

        if ($req->search) $query
            ->whereHas('user', fn($query) => $query->where('name', 'like', "%{$req->search}%"))
            ->orWhereHas(
                'rute',
                fn($query) => $query
                    ->where('nama', 'like', "%{$req->search}%")
                    ->where('slug', 'like', "%{$req->search}%")
            );

        if ($req->order && $req->direction) $query->orderBy($req->order, $req->direction);
        else $query->latest();

        $data = $query->get()->map(function ($item) {
            $item->user->avatar = $item->user->getAvatarUrl();
            unset($item->user->media);
            return $item;
        });

        $count = $req->search
            ? Comment::whereHas('user', fn($query) => $query->where('name', 'like', "%{$req->search}%"))
            ->orWhereHas(
                'rute',
                fn($query) => $query
                    ->where('nama', 'like', "%{$req->search}%")
                    ->where('slug', 'like', "%{$req->search}%")
            )
            ->count()
            : Comment::count();

        return view('admin.comment.components.table', ['data' => $data, 'count' => $count]);
    }

    public function apiDelete(Comment $comment)
    {
        try {
            $rute = $comment->rute;

            DB::beginTransaction();

            $comment->delete();

            $commentCount = $rute->comment()->count();
            $avgRating = $rute->comment()->avg('rating');

            $rute->update([
                'comment_count' => $commentCount,
                'comment_rating' => round($avgRating, 2),
            ]);

            DB::commit();

            session()->flash("toast", [
                "type"    => "success",
                "title"   => "Success: Comment Deleted!",
                "message" => "Comment has been successfully deleted.",
            ]);

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            DB::rollBack();

            session()->flash("toast", [
                "type"    => "error",
                "title"   => "Error: Deletion Failed!",
                "message" => "An error occurred while deleting the comment. Please try again.",
            ]);

            return response()->json(["success" => false], 500);
        }
    }
}
