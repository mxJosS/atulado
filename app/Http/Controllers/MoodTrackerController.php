<?php

namespace App\Http\Controllers;

use App\Models\MoodLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MoodTrackerController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'score' => ['required', 'integer', 'between:1,5'],
            'primary_emotion' => ['required', 'string', 'max:50'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'journal_entry' => ['nullable', 'string', 'max:2000'],
            'gratitude_note' => ['nullable', 'string', 'max:1000'],
            'energy_level' => ['nullable', 'integer', 'between:1,5'],
            'sleep_hours' => ['nullable', 'integer', 'between:0,24'],
        ]);

        $today = Carbon::today()->format('Y-m-d');
        $user = Auth::user();

        // Check if log already exists for today -> update or create
        $moodLog = MoodLog::updateOrCreate(
            [
                'user_id' => $user->id,
                'logged_date' => $today,
            ],
            [
                'score' => $validated['score'],
                'primary_emotion' => $validated['primary_emotion'],
                'tags' => $validated['tags'] ?? [],
                'journal_entry' => $validated['journal_entry'] ?? null,
                'gratitude_note' => $validated['gratitude_note'] ?? null,
                'energy_level' => $validated['energy_level'] ?? 3,
                'sleep_hours' => $validated['sleep_hours'] ?? null,
            ]
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '¡Tu registro emocional ha sido guardado exitosamente! 🌱',
                'log' => $moodLog,
            ]);
        }

        return redirect()->route('dashboard')->with('success', '¡Tu registro de hoy ha sido guardado! Gracias por dedicar este tiempo a ti.');
    }

    public function history(Request $request)
    {
        $user = Auth::user();
        $query = $user->moodLogs();

        // Filter by month/year if selected
        if ($request->filled('month')) {
            $monthDate = Carbon::createFromFormat('Y-m', $request->month);
            $query->whereYear('logged_date', $monthDate->year)
                  ->whereMonth('logged_date', $monthDate->month);
        }

        // Filter by score / emotion
        if ($request->filled('score')) {
            $query->where('score', $request->score);
        }

        if ($request->filled('emotion')) {
            $query->where('primary_emotion', $request->emotion);
        }

        $logs = $query->paginate(15)->withQueryString();

        // Stats for history charts
        $allLogs = $user->moodLogs()->get();
        $totalLogs = $allLogs->count();
        $averageScore = $totalLogs > 0 ? round($allLogs->avg('score'), 1) : 0;
        
        // Emotion distribution count
        $emotionDistribution = $allLogs->groupBy('primary_emotion')->map->count();
        
        // Score distribution
        $scoreCounts = [
            1 => $allLogs->where('score', 1)->count(),
            2 => $allLogs->where('score', 2)->count(),
            3 => $allLogs->where('score', 3)->count(),
            4 => $allLogs->where('score', 4)->count(),
            5 => $allLogs->where('score', 5)->count(),
        ];

        // 30 days timeline data for chart
        $last30Days = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->format('Y-m-d');
            $log = $allLogs->firstWhere('logged_date', $date);
            $last30Days->push([
                'date' => Carbon::parse($date)->format('d M'),
                'score' => $log ? $log->score : null,
                'emotion' => $log ? $log->primary_emotion : null,
            ]);
        }

        return view('dashboard.historial', compact(
            'logs',
            'totalLogs',
            'averageScore',
            'emotionDistribution',
            'scoreCounts',
            'last30Days'
        ));
    }

    public function destroy(MoodLog $moodLog)
    {
        if ($moodLog->user_id !== Auth::id()) {
            abort(403);
        }

        $moodLog->delete();

        return back()->with('success', 'El registro fue eliminado correctamente.');
    }
}
