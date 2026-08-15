<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\CrisisLine;
use App\Models\Resource;

class HomeController extends Controller
{
    public function index()
    {
        $featuredResources = Resource::where('is_featured', true)->take(6)->get();
        $latestArticles = Article::orderBy('published_at', 'desc')->take(3)->get();
        $crisisLines = CrisisLine::where('is_featured', true)->take(4)->get();

        return view('home', compact('featuredResources', 'latestArticles', 'crisisLines'));
    }
}
