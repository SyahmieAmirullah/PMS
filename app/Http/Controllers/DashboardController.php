<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $staffUser = Auth::guard('staff')->user();

        $baseQuery = Project::query();
        if ($staffUser) {
            $baseQuery->whereHas('staff', function ($q) use ($staffUser) {
                $q->where('staff.id', $staffUser->id);
            });
        }

        $projects = (clone $baseQuery)
            ->withCount(['tasks', 'phases', 'feedback'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $meetingsQuery = Meeting::with(['project'])->upcoming();
        if ($staffUser) {
            $meetingsQuery->whereHas('project.staff', function ($q) use ($staffUser) {
                $q->where('staff.id', $staffUser->id);
            });
        }

        $upcomingMeetings = $meetingsQuery
            ->take(5)
            ->get();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('ProjectSTATUS', 'in_progress')->count(),
            'completed' => (clone $baseQuery)->where('ProjectSTATUS', 'completed')->count(),
            'onHold' => (clone $baseQuery)->where('ProjectSTATUS', 'on_hold')->count(),
        ];

        return Inertia::render('Dashboard', [
            'projects' => $projects,
            'meetings' => $upcomingMeetings,
            'stats' => $stats,
        ]);
    }
}
