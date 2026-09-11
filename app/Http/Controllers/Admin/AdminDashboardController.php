<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ProfessionalVerification;
use App\Models\User;
use App\Models\MoodLog;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalPatients = User::where('role', '!=', 'profesional')->where('role', '!=', 'admin')->where('is_admin', false)->count();
        $totalProfessionals = User::where(function ($q) {
            $q->where('role', 'profesional')->orWhere('is_admin', true);
        })->count();

        $pendingVerifications = ProfessionalVerification::where('status', 'pendiente')->count();
        $approvedVerifications = ProfessionalVerification::where('status', 'aprobada')->count();

        $totalArticles = Article::count();
        $totalMoodLogs = MoodLog::count();

        $recentVerifications = ProfessionalVerification::with('user')
            ->latest()
            ->take(5)
            ->get();

        $recentUsers = User::latest()
            ->take(6)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalPatients',
            'totalProfessionals',
            'pendingVerifications',
            'approvedVerifications',
            'totalArticles',
            'totalMoodLogs',
            'recentVerifications',
            'recentUsers'
        ));
    }
}
