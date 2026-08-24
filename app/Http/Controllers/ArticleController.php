<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Resource;
use App\Models\TopicArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::with(['topicArea', 'user']);

        if ($request->filled('topic_area')) {
            $topicSlug = $request->topic_area;
            $query->whereHas('topicArea', function ($q) use ($topicSlug) {
                $q->where('slug', $topicSlug)->orWhere('name', $topicSlug);
            });
        } elseif ($request->filled('category')) {
            $cat = $request->category;
            $query->where(function ($q) use ($cat) {
                $q->where('category', $cat)
                  ->orWhereHas('topicArea', function ($subQ) use ($cat) {
                      $subQ->where('slug', $cat)->orWhere('name', $cat);
                  });
            });
        }

        if ($request->filled('publication_type')) {
            $query->where('publication_type', $request->publication_type);
        }

        if ($request->filled('target_audience')) {
            $query->where('target_audience', $request->target_audience);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('author_name', 'like', "%{$search}%")
                  ->orWhere('author_credentials', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $featuredArticle = (clone $query)->where('is_featured', true)->first()
            ?? (clone $query)->orderBy('published_at', 'desc')->first()
            ?? Article::where('is_featured', true)->first()
            ?? Article::latest('published_at')->first();

        $articles = $query->where('id', '!=', $featuredArticle?->id ?? 0)
                          ->orderBy('published_at', 'desc')
                          ->paginate(9);

        $topicAreas = TopicArea::withCount('articles')->get();
        $categories = $topicAreas->pluck('name');

        return view('revista.index', compact('articles', 'featuredArticle', 'topicAreas', 'categories'));
    }

    public function show(string $slug)
    {
        $article = Article::with(['topicArea', 'user'])->where('slug', $slug)->firstOrFail();

        $relatedArticles = Article::with(['topicArea', 'user'])
            ->where('id', '!=', $article->id)
            ->where(function ($q) use ($article) {
                if ($article->topic_area_id) {
                    $q->where('topic_area_id', $article->topic_area_id);
                } else {
                    $q->where('category', $article->category);
                }
            })
            ->take(3)
            ->get();

        if ($relatedArticles->isEmpty()) {
            $relatedArticles = Article::with(['topicArea', 'user'])->where('id', '!=', $article->id)->take(3)->get();
        }

        $recommendedResources = Resource::where('is_featured', true)->take(2)->get();

        return view('revista.show', compact('article', 'relatedArticles', 'recommendedResources'));
    }

    public function create()
    {
        $topicAreas = TopicArea::orderBy('name')->get();

        // If no topic areas exist in DB yet, create defaults
        if ($topicAreas->isEmpty()) {
            $topicAreas = collect([
                TopicArea::create(['name' => 'Terapia DBT & Conductual', 'slug' => 'terapia-dbt-conductual', 'icon' => 'fa-brain', 'color' => 'lav']),
                TopicArea::create(['name' => 'Neurobiología & Fisiología', 'slug' => 'neurobiologia-fisiologia', 'icon' => 'fa-dna', 'color' => 'sky']),
                TopicArea::create(['name' => 'Regulación Emocional', 'slug' => 'regulacion-emocional', 'icon' => 'fa-heart-pulse', 'color' => 'sage']),
                TopicArea::create(['name' => 'Ansiedad & Pánico', 'slug' => 'ansiedad-panico', 'icon' => 'fa-wind', 'color' => 'sky']),
                TopicArea::create(['name' => 'Prevención del Suicidio & Crisis', 'slug' => 'prevencion-suicidio-crisis', 'icon' => 'fa-life-ring', 'color' => 'terra']),
                TopicArea::create(['name' => 'Mindfulness & Atención Plena', 'slug' => 'mindfulness-atencion-plena', 'icon' => 'fa-seedling', 'color' => 'sage']),
                TopicArea::create(['name' => 'Psicoeducación & Hábitos', 'slug' => 'psicoeducacion-habitos', 'icon' => 'fa-book-open-reader', 'color' => 'amber']),
            ]);
        }

        return view('revista.create', compact('topicAreas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'topic_area_id' => ['required', 'exists:topic_areas,id'],
            'author_name' => ['required', 'string', 'max:150'],
            'author_credentials' => ['required', 'string', 'max:255'],
            'visual_theme' => ['nullable', 'string', 'max:50'],
            'publication_type' => ['required', 'string', 'in:divulgacion,revision,caso_estudio,guia'],
            'target_audience' => ['required', 'string', 'in:general,estudiantes,profesionales'],
            'summary' => ['required', 'string'],
            'content' => ['required', 'string', 'min:100'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'cover_image_path' => ['nullable', 'string', 'max:2048'],
            'references' => ['nullable', 'string'],
            'discussion_prompt' => ['nullable', 'string', 'max:500'],
            'reading_time_min' => ['nullable', 'integer', 'min:1', 'max:120'],
            'allow_comments' => ['nullable'],
            'is_disclaimer_accepted' => ['required', 'accepted'],
        ], [
            'title.required' => 'El título del artículo es obligatorio.',
            'topic_area_id.required' => 'Selecciona un área temática principal.',
            'topic_area_id.exists' => 'El área temática seleccionada no es válida.',
            'author_name.required' => 'Indica el nombre público del autor o profesional.',
            'author_credentials.required' => 'Indica las credenciales académicas o clínicas del autor.',
            'publication_type.required' => 'Selecciona el tipo de rigor o enfoque de la publicación.',
            'target_audience.required' => 'Selecciona la audiencia o nivel de lectura recomendado.',
            'summary.required' => 'Escribe un resumen científico o extracto breve.',
            'content.required' => 'El cuerpo completo del artículo debe tener al menos 100 caracteres.',
            'cover_image.image' => 'El archivo de portada debe ser una imagen válida (JPG, PNG, WEBP o GIF).',
            'cover_image.max' => 'La imagen de portada no debe superar los 5 MB.',
            'is_disclaimer_accepted.accepted' => 'Debes confirmar la aceptación del deslinde clínico y ético para publicar.',
        ]);

        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug ?: 'articulo-' . Str::random(6);
        $counter = 1;
        while (Article::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        // Calculate approximate read time if not explicitly provided
        $readingTime = $validated['reading_time_min'] ?? null;
        if (!$readingTime) {
            $wordCount = str_word_count(strip_tags($validated['content']));
            $readingTime = max(1, ceil($wordCount / 200));
        }

        // Handle cover image upload or URL
        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('articles', $filename, 'public');
            $coverImagePath = '/storage/' . $path;
        } elseif ($request->filled('cover_image_path')) {
            $coverImagePath = $request->input('cover_image_path');
        }

        $topicArea = TopicArea::find($validated['topic_area_id']);

        $article = Article::create([
            'user_id' => Auth::id(),
            'topic_area_id' => $validated['topic_area_id'],
            'title' => $validated['title'],
            'slug' => $slug,
            'author_name' => $validated['author_name'],
            'author_credentials' => $validated['author_credentials'],
            'visual_theme' => $validated['visual_theme'] ?? ($topicArea?->color ?? 'salvia'),
            'publication_type' => $validated['publication_type'],
            'target_audience' => $validated['target_audience'],
            'summary' => $validated['summary'],
            'content' => $validated['content'],
            'cover_image_path' => $coverImagePath,
            'references' => $validated['references'] ?? null,
            'discussion_prompt' => $validated['discussion_prompt'] ?? null,
            'reading_time_min' => $readingTime,
            'allow_comments' => $request->has('allow_comments') ? (bool) $request->input('allow_comments') : true,
            'is_disclaimer_accepted' => true,
            'status' => 'published',
            'is_featured' => false,
            'is_peer_reviewed' => true,
            'category' => $topicArea?->name ?? 'Psicoeducación',
            'published_at' => now(),
        ]);

        return redirect()->route('revista.show', $article->slug)
            ->with('success', '¡Artículo científico publicado con éxito en la Revista A tu lado!');
    }
}
