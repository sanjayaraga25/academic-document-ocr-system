<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Verification;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = now();

        $stats = [
            'total' => Document::count(),
            'verified' => Document::where('status', 'verified')->count(),
            'rejected' => Document::where('status', 'rejected')->count(),
            'pending' => Document::where('status', 'pending')->count(),
            'processing' => Document::where('status', 'processing')->count(),
            'error' => Document::where('status', 'error')->count(),
        ];

        $stuckDocuments = Document::where('status', 'processing')
            ->where('updated_at', '<=', $now->copy()->subMinutes(2))
            ->count();

        $recentDocuments = Document::with('uploader')
            ->latest()
            ->take(5)
            ->get();

        $verificationTrend = Document::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total')
        )
            ->where('created_at', '>=', $now->copy()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('pages.dashboard', compact('stats', 'recentDocuments', 'verificationTrend', 'stuckDocuments'));
    }
}
