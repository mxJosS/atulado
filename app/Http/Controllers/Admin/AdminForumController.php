<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\TopicArea;
use Illuminate\Http\Request;

class AdminForumController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::with(['topicArea', 'user']);

        if ($request->filled('estado')) {
            $query->where('status', $request->estado);
        }

        if ($request->filled('buscar')) {
            $term = $request->buscar;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%$term%")
                  ->orWhere('author_name', 'like', "%$term%");
            });
        }

        $articles = $query->latest()->paginate(12)->withQueryString();
        $topicAreas = TopicArea::all();

        return view('admin.forums.index', compact('articles', 'topicAreas'));
    }
}
