<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogRequest;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index()
    {
        return view('admin.blog.index');
    }

    public function create()
    {
        return view('admin.blog.form', [
            'type'  => 'Create',
            'data'  => new Blog(),
            'route' => route('admin.blog.store')
        ]);
    }

    public function store(BlogRequest $req)
    {
        $valid = $req->except(['image']);
        $blog  = Blog::create($valid);

        if ($req->hasFile('image')) {
            $blog
                ->addMediaFromRequest('image')
                ->usingFileName(uniqid('blog-') . '.png')
                ->toMediaCollection('blog');
        }

        return redirect()->route('admin.blog.index')->with('toast', [
            'type'    => 'success',
            'title'   => 'Success: Blog Added!',
            'message' => "The blog has been successfully added.",
        ]);
    }

    public function show(Blog $blog)
    {
        $blog->load([
            'user:id,name,username',
        ]);
        return view('admin.blog.show', ['data' => $blog]);
    }

    public function edit(Blog $blog)
    {
        return view('admin.blog.form', [
            'data'  => $blog,
            'type'  => 'Edit',
            'route' => route('admin.blog.update', $blog)
        ]);
    }

    public function update(BlogRequest $req, Blog $blog)
    {
        $valid = $req->except(['image']);
        $blog->update($valid);

        if ($req->hasFile('image')) {
            $blog
                ->addMediaFromRequest('image')
                ->usingFileName(uniqid('blog-') . '.png')
                ->toMediaCollection('blog');
        }

        return redirect()->route('admin.blog.index')->with('toast', [
            'type'    => 'success',
            'title'   => 'Success: Blog Updated!',
            'message' => "The blog has been successfully updated.",
        ]);
    }

    /**
     * API
     */
    public function apiIndex(Request $req)
    {
        $query = Blog::take($req->take)
            ->skip(($req->page - 1) * $req->take);

        if ($req->search) $query->where('title', 'like', "%{$req->search}%");

        if ($req->order && $req->direction) $query->orderBy($req->order, $req->direction);
        else $query->latest();

        $data = $query->get();

        $count = $req->search
            ? Blog::where('title', 'like', "%{$req->search}%")->count()
            : Blog::count();

        return view('admin.blog.components.table', ['data' => $data, 'count' => $count]);
    }

    public function apiUpdate(Blog $blog, Request $req)
    {
        $blog->update($req->all());
        return response()->json([
            'success' => true,
            'toast' => [
                'type' => 'success',
                'title' => 'Success: Item Updated!',
                'message' => "The item has been successfully updated.",
            ]
        ]);
    }

    public function apiDelete(Blog $blog)
    {
        try {
            $blog->delete();

            session()->flash("toast", [
                "type"    => "success",
                "title"   => "Success: Blog Deleted!",
                "message" => "The blog has been successfully deleted.",
            ]);

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            session()->flash("toast", [
                "type"    => "error",
                "title"   => "Error: Deletion Failed!",
                "message" => "An error occurred while deleting the blog. Please try again.",
            ]);

            return response()->json(["success" => false], 500);
        }
    }
}
