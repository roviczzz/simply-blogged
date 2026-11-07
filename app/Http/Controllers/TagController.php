<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Contracts\View\View;

class TagController extends Controller
{
    public function show(Tag $tag): View
    {
        $posts = $tag->posts()
            ->published()
            ->with('tags')
            ->latest('published_at')
            ->paginate(10);

        return view('tags.show', ['tag' => $tag, 'posts' => $posts]);
    }
}
