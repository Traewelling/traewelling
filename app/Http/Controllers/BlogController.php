<?php

namespace App\Http\Controllers;

use App\Blogpost;
use Illuminate\Http\Request;

class BlogController extends Controller {
    public function all() {
        $blogposts = Blogpost::where("published_at", "<", new \DateTime())->latest('published_at')->simplePaginate(5);

        return view('blog', ['blogposts' => $blogposts, "page" => "home"]);
    }

    public function show(String $slug) {
        $blogposts = Blogpost::where("slug", $slug)->simplePaginate();

        return view('blog', ['blogposts' => $blogposts, "page" => "single"]);
    }
    public function category(String $cat) {
        $blogposts = Blogpost::where("category", $cat)->simplePaginate(5);

        return view('blog', ['blogposts' => $blogposts, "category" => $cat, "page" => "category"]);
    }
}
