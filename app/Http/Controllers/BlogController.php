<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlogRequest;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class BlogController extends Controller
{
    public function index(Request $req)
    {
        $query = Blog::query()
            ->with('user:id,name,username')
            ->where('is_published', true);

        if ($req->has('q')) {
            $query
                ->where('title', 'like', "%{$req->q}%")
                ->whereHas('user', function ($query) use ($req) {
                    $query
                        ->where('name', 'like', "%{$req->q}%")
                        ->orWhere('username', 'like', "%{$req->q}%");
                });
        }

        /**
         * @var Paginator
         */
        $blogs = $query->paginate(10);

        $blogs = $blogs->through(function ($item) {
            $item->created_at_human = $item->created_at->diffForHumans();
            return $item;
        });

        $currentPage = $blogs->currentPage();
        $lastPage    = $blogs->lastPage();
        $pagination  = [];

        if ($currentPage > 1) $pagination[] = 'prev';
        if ($currentPage > 2) $pagination[] = 1;
        if ($currentPage > 3) $pagination[] = '...';

        for ($i = max(1, $currentPage - 1); $i <= min($lastPage, $currentPage + 1); $i++) {
            $pagination[] = $i;
        }

        if ($currentPage < $lastPage - 2) $pagination[] = '...';
        if ($currentPage < $lastPage - 1) $pagination[] = $lastPage;
        if ($currentPage < $lastPage) $pagination[] = 'next';

        return view('blog.index', [
            "blogs"      => $blogs,
            "page"       => $currentPage,
            "q"          => $req->q,
            "pagination" => $pagination,
        ]);
    }

    public function slug($slug)
    {
        $blog = Blog::where('slug', $slug)->firstOrFail();
        $blog->load('user:id,name,username');
        $blog->created_at_human = $blog->created_at->diffForHumans();

        $randomBlogs = Blog::query()
            ->where('is_published', true)
            ->where('id', '!=', $blog->id)
            ->inRandomOrder()
            ->limit(4)
            ->get()
            ->each(function ($item) {
                $item->created_at_human = $item->created_at->diffForHumans();
                return $item;
            });

        return view('blog.slug', [
            'blog'        => $blog,
            'randomBlogs' => $randomBlogs,
        ]);
    }
}
