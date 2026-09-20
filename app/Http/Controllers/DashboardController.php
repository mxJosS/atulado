<?php

namespace App\Http\Controllers;

use App\Models\MoodLog;
use App\Models\Resource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Calculate time greeting
        $hour = Carbon::now()->hour;
        if ($hour >= 5 && $hour < 12) {
            $greeting = 'Buenos días';
        } elseif ($hour >= 12 && $hour < 19) {
            $greeting = 'Buenas tardes';
        } else {
            $greeting = 'Buenas noches';
        }

        // Streak
        $streak = $user->calculateStreak();

        // Check-in of today
        $todayLog = $user->today_mood_log;

        // Weekly days data
        $weeklyData = $user->getWeeklySummary();

        // Overall stats
        $totalLogs = $user->moodLogs()->count();
        $averageScore = $totalLogs > 0 ? round($user->moodLogs()->avg('score'), 1) : null;

        // Suggested & favorite resources
        $favorites = $user->favoriteResources()->take(4)->get();
        $featuredResources = Resource::where('is_featured', true)->take(4)->get();

        // Safety Plan exists?
        $hasSafetyPlan = $user->safetyPlan()->exists();

        // Recent reflections
        $recentLogs = $user->moodLogs()->take(5)->get();

        return view('dashboard.index', compact(
            'user',
            'greeting',
            'streak',
            'todayLog',
            'weeklyData',
            'totalLogs',
            'averageScore',
            'favorites',
            'featuredResources',
            'hasSafetyPlan',
            'recentLogs'
        ));
    }
}
