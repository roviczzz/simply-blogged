<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $posts = Post::published()
            ->with('tags')
            ->latest('published_at')
            ->paginate(10);

        return view('home', ['posts' => $posts]);
    }
}
