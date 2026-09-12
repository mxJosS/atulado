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
        $totalAdmins = User::where('is_admin', true)->orWhere('role', 'admin')->count();
        $totalProfessionals = User::where('role', 'profesional')->where('is_admin', false)->count();
        $totalRegularUsers = User::where(function ($q) {
            $q->where('role', 'usuario')->orWhereNull('role');
        })->where('is_admin', false)->count();

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
            'totalAdmins',
            'totalProfessionals',
            'totalRegularUsers',
            'pendingVerifications',
            'approvedVerifications',
            'totalArticles',
            'totalMoodLogs',
            'recentVerifications',
            'recentUsers'
        ));
    }
}
