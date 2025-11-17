<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Rute;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
{
    public function store(CommentRequest $req, $ruteSlug)
    {
        try {
            DB::beginTransaction();

            $rute = Rute::select(['id', 'comment_rating', 'comment_count'])
                ->where('slug', $ruteSlug)
                ->firstOrFail();

            $valid            = $req->all();
            $valid['user_id'] = Auth::user()->id;
            $valid['rute_id'] = $rute->id;

            $comment = Comment::create($valid);

            if ($req->hasFile('gallery')) {
                foreach ($req->file('gallery') as $item) {
                    $galleryName = uniqid('comment-gallery-') . $rute->id . '.png';
                    $comment
                        ->addMedia($item)
                        ->usingFileName($galleryName)
                        ->usingName($galleryName)
                        ->toMediaCollection('comment-gallery');
                }
            }

            $commentCount = $rute->comment()->count();
            $avgRating = $rute->comment()->avg('rating');

            $rute->update([
                'comment_count' => $commentCount,
                'comment_rating' => round($avgRating, 2),
            ]);

            DB::commit();

            return redirect()->back()->with(
                "toast",
                [
                    "type"    => "success",
                    "title"   => "Ulasan Berhasil Disimpan!",
                    "message" => "Ulasan Anda telah berhasil disimpan ke dalam sistem.",
                ]
            );
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->withInput()->with(
                "toast",
                [
                    "type"    => "error",
                    "title"   => "Gagal Menyimpan Ulasan!",
                    "message" => "Terjadi kesalahan saat menyimpan ulasan Anda. Silakan coba lagi nanti.",
                ]
            );
        }
    }

    /**
     * API
     */
    public function apiIndex(Request $req, $ruteId)
    {
        try {
            $rute = Rute::select(['id', 'comment_rating'])->findOrFail($ruteId);

            $req->validate([
                'take'      => ['nullable', 'integer', 'min:1'],
                'page'      => ['nullable', 'integer', 'min:1'],
                'order'     => ['nullable', 'string', 'in:creation,rating'],
                'direction' => ['nullable', 'string', 'in:asc,desc'],
            ]);

            $take = $req->take ?? 20;
            $page = $req->page ?? 1;
            $order = $req->order ?? 'creation';
            $direction = $req->direction ?? 'asc';

            $data = $rute->comment()
                ->select(['id', 'user_id', 'point_id', 'content', 'rating', 'kondisi_rute', 'created_at'])
                ->where('is_approved', true)
                ->with([
                    'user:id,name,username',
                    'user.media' => fn($query) => $query->where('collection_name', 'photo-profile'),
                    'media' => fn($query) => $query->where('collection_name', 'comment-gallery')
                ])
                ->limit($take)
                ->skip($take * ($page - 1))
                ->orderBy(
                    $order === 'rating' ? 'rating' : 'created_at',
                    $direction
                )
                ->get();

            $data->map(function ($comment) {
                $comment->created_at_id = Carbon::parse($comment->created_at)
                    ->setTimezone('Asia/Jakarta')
                    ->format('d M Y, H:i');
                $comment->gallery_urls     = $comment->getGalleryUrls();
                $comment->user->avatar_url = $comment->user->getAvatarUrl();

                unset($comment->created_at, $comment->media, $comment->user->media);

                return $comment;
            });

            $count = $rute->comment()->where('is_approved', true)->count();

            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => $count,
                'rating' => number_format($rute->comment_rating, 1)
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->errors()
            ], 422);
        }
    }

    public function apiDelete($id)
    {
        try {
            $data = Comment::findOrFail($id);

            if ($data->user_id !== Auth::user()->id) {
                session()->flash('toast', [
                    'type' => 'error',
                    'title' => 'Unauthorized',
                    'message' => 'Anda tidak memiliki izin untuk menghapus ulasan ini.',
                ]);

                return response()->json(['success' => false]);
            }

            $data->delete();

            session()->flash(
                "toast",
                [
                    "type"    => "success",
                    "title"   => "Ulasan Telah Dihapus!",
                    "message" => "Ulasan Anda telah berhasil dihapus dari sistem.",
                ]
            );

            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            session()->flash(
                "toast",
                [
                    "type"    => "error",
                    "title"   => "Gagal Menghapus Ulasan!",
                    "message" => "Terjadi kesalahan saat mencoba menghapus ulasan Anda. Silakan coba lagi.",
                ]
            );

            return response()->json(['success' => false]);
        }
    }
}
