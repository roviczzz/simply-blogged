<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\View\View;

class PostController extends Controller
{
    public function show(string $slug): View
    {
        $post = Post::published()
            ->with(['tags', 'author'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('posts.show', ['post' => $post]);
    }
}
