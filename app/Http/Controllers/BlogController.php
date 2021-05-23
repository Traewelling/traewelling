<?php

namespace App\Http\Controllers;

use App\Models\Blogpost;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;

class BlogController extends Controller
{
    public function renderMain(): Renderable {
        $blogposts = Blogpost::where("published_at", "<", Carbon::now()->toIso8601String())
                             ->orderByDesc('published_at')
                             ->simplePaginate(5);

        return view('blog.main', [
            'blogposts' => $blogposts
        ]);
    }

    public function renderSingle(string $slug): Renderable {
        return view('blog.single', [
            'blogpost' => Blogpost::where("slug", $slug)->firstOrFail()
        ]);
    }

    public function renderCategory(string $category): Renderable {
        $blogposts = Blogpost::where("category", $category)
                             ->orderByDesc('published_at')
                             ->simplePaginate(5);

        if ($blogposts->count() == 0) {
            abort(404);
        }

        return view('blog.category', [
            'blogposts' => $blogposts,
            "category"  => $category
        ]);
    }
}
