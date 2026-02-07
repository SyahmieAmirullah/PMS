<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $query = Feedback::with(['project'])
            ->orderBy('FeedbackTIME', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->filled('FeedbackTITLE')) {
            $query->where('FeedbackTITLE', 'like', '%' . $request->input('FeedbackTITLE') . '%');
        }

        if ($request->filled('ProjectID')) {
            $query->where('ProjectID', $request->input('ProjectID'));
        }

        $perPage = $request->input('per_page', 10);
        $feedback = $query->paginate((int) $perPage)->withQueryString();

        $projects = Project::select('id', 'ProjectNAME')
            ->orderBy('ProjectNAME')
            ->get();

        $stats = [
            'total' => Feedback::count(),
            'thisWeek' => Feedback::where('FeedbackTIME', '>=', now()->subDays(7))->count(),
            'thisMonth' => Feedback::where('FeedbackTIME', '>=', now()->startOfMonth())->count(),
        ];

        return Inertia::render('Feedback/Index', [
            'feedback' => $feedback,
            'projects' => $projects,
            'stats' => $stats,
        ]);
    }

    public function create(Request $request)
    {
        $projects = Project::select('id', 'ProjectNAME', 'ClientNAME')
            ->orderBy('ProjectNAME')
            ->get();

        $preselectedProjectId = $request->query('project_id');

        return Inertia::render('Feedback/Create', [
            'projects' => $projects,
            'preselectedProjectId' => $preselectedProjectId,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ProjectID' => 'required|exists:project,id',
            'FeedbackTITLE' => 'required|string|max:255',
            'FeedbackDESC' => 'nullable|string',
            'FeedbackTIME' => 'required|date',
        ]);

        Feedback::create($validated);

        return redirect()
            ->route('feedback.index')
            ->with('success', 'Feedback created successfully!');
    }

    public function show($id)
    {
        $feedback = Feedback::with('project')->findOrFail($id);

        return Inertia::render('Feedback/Show', [
            'feedback' => $feedback,
        ]);
    }

    public function destroy($id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->delete();

        return redirect()
            ->route('feedback.index')
            ->with('success', 'Feedback deleted successfully!');
    }
}
