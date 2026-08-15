<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Resource;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $featuredArticle = Article::where('is_featured', true)->first() ?? Article::latest()->first();
        $articles = $query->where('id', '!=', $featuredArticle?->id ?? 0)
                          ->orderBy('published_at', 'desc')
                          ->paginate(9);

        $categories = Article::select('category')->distinct()->pluck('category');

        return view('revista.index', compact('articles', 'featuredArticle', 'categories'));
    }

    public function show(string $slug)
    {
        $article = Article::where('slug', $slug)->firstOrFail();
        $relatedArticles = Article::where('id', '!=', $article->id)
            ->where('category', $article->category)
            ->take(3)
            ->get();

        if ($relatedArticles->isEmpty()) {
            $relatedArticles = Article::where('id', '!=', $article->id)->take(3)->get();
        }

        $recommendedResources = Resource::where('is_featured', true)->take(2)->get();

        return view('revista.show', compact('article', 'relatedArticles', 'recommendedResources'));
    }
}
