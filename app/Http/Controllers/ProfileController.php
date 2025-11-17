<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Comment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index()
    {
        /**
         * @var User
         */
        $user     = Auth::user();
        $provider = $user->userProvider;

        return view('profile.index', ['provider' => $provider]);
    }

    public function update(Request $req)
    {
        $req->validate([
            'name'     => ['nullable', 'string', 'max:255'],
            'bio'      => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore(Auth::user()->id)],
            'avatar'   => ['nullable', 'image', 'mimes:jpeg,png,jpg'],
        ]);

        $valid = $req->except('avatar');

        /**
         * @var User
         */
        $user = Auth::user();

        if (!$req->hasFile('avatar')) {
            $user->update($valid);
        }

        if ($req->hasFile('avatar')) {
            $imageName = uniqid('photo-profile-') . $user->id . '.png';
            $user
                ->addMediaFromRequest('avatar')
                ->usingFileName($imageName)
                ->usingName($imageName)
                ->toMediaCollection('photo-profile');
        }

        return redirect()->back()->with(
            "toast",
            [
                "type"    => "success",
                "title"   => "Profil Akun Anda Berhasil Diperbarui!",
                "message" => "Profil akun Anda telah berhasil diperbarui.",
            ]
        );
    }

    public function ulasan()
    {
        return view('profile.ulasan');
    }

    public function ulasanDelete($id)
    {
        try {
            DB::beginTransaction();

            $comment = Comment::findOrFail($id);
            $rute    = $comment->rute;

            $comment->delete();

            $commentCount = $rute->comment()->count();
            $avgRating    = $rute->comment()->avg('rating');

            $rute->update([
                'comment_count' => $commentCount,
                'comment_rating' => round($avgRating, 2),
            ]);

            DB::commit();

            return redirect()->back()->with(
                "toast",
                [
                    "type"    => "success",
                    "title"   => "Ulasan Anda Berhasil Dihapus!",
                    "message" => "Ulasan Anda berhasil dihapus dari sistem.",
                ]
            );
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->withInput()->with(
                "toast",
                [
                    "type"    => "error",
                    "title"   => "Gagal Menghapus Ulasan!",
                    "message" => "Terjadi kesalahan saat menghapus ulasan Anda. Silakan coba lagi nanti.",
                ]
            );
        }
    }

    /**
     * API
     */
    public function apiUlasan(Request $req)
    {
        try {
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

            $data = Comment::select(['id', 'user_id', 'point_id', 'content', 'rating', 'kondisi_rute', 'created_at', 'rute_id'])
                ->where(['is_approved' => true, 'user_id' => Auth::user()->id])
                ->with([
                    'user:id,name,username',
                    'user.media' => fn($query) => $query->where('collection_name', 'photo-profile'),
                    'media' => fn($query) => $query->where('collection_name', 'comment-gallery'),
                    'rute:id,nama,gunung_id,slug',
                    'rute.gunung:id,nama',
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
                $comment->rute->url        = route('jalur-pendakian.slug', $comment->rute->slug);

                unset($comment->created_at, $comment->media, $comment->user->media);

                return $comment;
            });

            $count = Comment::where(['is_approved' => true, 'user_id' => Auth::user()->id])->count();

            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => $count,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->errors()
            ], 422);
        }
    }
}
