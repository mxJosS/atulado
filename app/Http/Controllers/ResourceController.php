<?php

namespace App\Http\Controllers;

use App\Models\CrisisLine;
use App\Models\Resource;
use App\Models\UserResourceFavorite;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResourceController extends Controller
{
    public function index(Request $request)
    {
        $query = Resource::query();

        if ($request->filled('category') && $request->category !== 'todos') {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $resources = $query->orderBy('order_index')->paginate(12)->withQueryString();
        $crisisLines = CrisisLine::where('is_featured', true)->take(4)->get();
        $userFavorites = Auth::check() ? Auth::user()->favorites()->pluck('resource_id')->toArray() : [];

        return view('recursos.index', compact('resources', 'crisisLines', 'userFavorites'));
    }

    public function show(string $slug)
    {
        $resource = Resource::where('slug', $slug)->firstOrFail();
        $relatedResources = Resource::where('category', $resource->category)
            ->where('id', '!=', $resource->id)
            ->take(3)
            ->get();

        $isFavorite = false;
        $isCompleted = false;
        $userPivot = null;

        if (Auth::check()) {
            $userPivot = UserResourceFavorite::where('user_id', Auth::id())
                ->where('resource_id', $resource->id)
                ->first();
            $isFavorite = $userPivot !== null;
            $isCompleted = $userPivot?->is_completed ?? false;
        }

        return view('recursos.show', compact('resource', 'relatedResources', 'isFavorite', 'isCompleted', 'userPivot'));
    }

    public function toggleFavorite(Request $request, Resource $resource)
    {
        $user = Auth::user();
        $existing = UserResourceFavorite::where('user_id', $user->id)
            ->where('resource_id', $resource->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $status = 'removed';
            $message = 'Recurso eliminado de tus favoritos.';
        } else {
            UserResourceFavorite::create([
                'user_id' => $user->id,
                'resource_id' => $resource->id,
            ]);
            $status = 'added';
            $message = '¡Recurso guardado en tus favoritos!';
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $status,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    public function toggleCompleted(Request $request, Resource $resource)
    {
        $user = Auth::user();
        $fav = UserResourceFavorite::firstOrCreate(
            ['user_id' => $user->id, 'resource_id' => $resource->id]
        );

        $fav->is_completed = !$fav->is_completed;
        $fav->completed_at = $fav->is_completed ? Carbon::now() : null;
        $fav->save();

        $message = $fav->is_completed
            ? '¡Felicitaciones por completar este ejercicio!'
            : 'Ejercicio marcado como pendiente.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_completed' => $fav->is_completed,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    public function userFavorites()
    {
        $user = Auth::user();
        $favorites = $user->favoriteResources()->paginate(12);

        return view('dashboard.favoritos', compact('favorites'));
    }
}
