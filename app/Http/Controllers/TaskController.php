<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['project'])
            ->orderBy('TaskDUE', 'asc')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('TaskNAME')) {
            $query->where('TaskNAME', 'like', '%' . $request->input('TaskNAME') . '%');
        }

        if ($request->filled('ProjectID')) {
            $query->where('ProjectID', $request->input('ProjectID'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('TaskDUE', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('TaskDUE', '<=', $request->input('date_to'));
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'overdue') {
                $query->where('TaskDUE', '<', now());
            } elseif ($status === 'upcoming') {
                $query->where('TaskDUE', '>=', now());
            }
        }

        $perPage = $request->input('per_page', 10);
        $tasks = $query->paginate((int)$perPage)->withQueryString();

        // Get all projects for filter dropdown
        $projects = Project::select('ProjectID', 'ProjectNAME')
            ->orderBy('ProjectNAME')
            ->get();

        // Statistics
        $stats = [
            'total' => Task::count(),
            'upcoming' => Task::where('TaskDUE', '>=', now())->count(),
            'overdue' => Task::where('TaskDUE', '<', now())->count(),
            'thisWeek' => Task::whereBetween('TaskDUE', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
        ];

        return Inertia::render('Task/Index', [
            'tasks' => $tasks,
            'projects' => $projects,
            'stats' => $stats,
        ]);
    }

    public function create(Request $request)
    {
        $projects = Project::select('ProjectID', 'ProjectNAME', 'ClientNAME')
            ->orderBy('ProjectNAME')
            ->get();

        $preselectedProjectId = $request->query('project_id');

        return Inertia::render('Task/Create', [
            'projects' => $projects,
            'preselectedProjectId' => $preselectedProjectId,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'TaskNAME' => 'required|string|max:255',
            'TaskDESC' => 'nullable|string',
            'TaskDUE' => 'required|date',
            'ProjectID' => 'required|exists:project,ProjectID',
        ]);

        Task::create($validated);

        return redirect()
            ->route('task.index')
            ->with('success', 'Task created successfully!');
    }

    public function show($id)
    {
        $task = Task::with(['project.staff'])
            ->findOrFail($id);

        return Inertia::render('Task/ShowTask', [
            'task' => $task,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'TaskNAME' => 'required|string|max:255',
            'TaskDESC' => 'nullable|string',
            'TaskDUE' => 'required|date',
            'ProjectID' => 'required|exists:projects,id',
        ]);

        $task = Task::findOrFail($id);
        $task->update($validated);

        return redirect()
            ->route('task.index')
            ->with('success', 'Task updated successfully!');
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return redirect()
            ->route('task.index')
            ->with('success', 'Task deleted successfully!');
    }

    // Get tasks for a specific project (API endpoint)
    public function getByProject($projectId)
    {
        $tasks = Task::where('ProjectID', $projectId)
            ->orderBy('TaskDUE', 'asc')
            ->get();

        return response()->json($tasks);
    }
}