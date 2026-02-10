<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Services\ProjectLogService;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $staffUser = $request->user('staff');

        $query = Task::with(['project', 'staff'])
            ->orderBy('TaskDUE', 'asc')
            ->orderBy('created_at', 'desc');

        if ($staffUser) {
            $query->where('StaffID', $staffUser->id);
        }

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
        $projects = Project::select('id', 'ProjectNAME')
            ->with(['staff:id,StaffNAME'])
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
        $projects = Project::select('id', 'ProjectNAME', 'ClientNAME')
            ->with(['staff' => function ($query) {
                $query->select('staff.id', 'StaffNAME')
                    ->with(['roles' => function ($roleQuery) {
                        $roleQuery->select('id', 'StaffID', 'RoleTYPE');
                    }]);
            }])
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
            'ProjectID' => 'required|exists:project,id',
            'StaffID' => 'nullable|integer|exists:staff,id',
            'TaskSTATUS' => 'nullable|in:pending,in_progress,completed,cancelled',
        ]);

        $project = Project::with('staff:id')->find($validated['ProjectID']);
        if ($project) {
            if ($project->staff->isNotEmpty() && empty($validated['StaffID'])) {
                return back()->withErrors(['StaffID' => 'Please select staff for this project.'])->withInput();
            }
            if (!empty($validated['StaffID']) && ! $project->staff->contains('id', $validated['StaffID'])) {
                return back()->withErrors(['StaffID' => 'Selected staff is not assigned to this project.'])->withInput();
            }
        }

        $task = Task::create($validated);

        ProjectLogService::log(
            $task->ProjectID,
            'Task created',
            'update',
            "Task: {$task->TaskNAME}",
            optional($request->user('staff'))->id
        );

        return redirect()
            ->route('task.index')
            ->with('success', 'Task created successfully!');
    }

    public function show($id)
    {
        $task = Task::with(['project.staff'])
            ->findOrFail($id);

        $staffUser = request()->user('staff');
        if ($staffUser && (int) $task->StaffID !== (int) $staffUser->id) {
            return back()->with('error', 'This task is not assigned to your account.');
        }

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
            'ProjectID' => 'required|exists:project,id',
            'StaffID' => 'nullable|integer|exists:staff,id',
            'TaskSTATUS' => 'nullable|in:pending,in_progress,completed,cancelled',
        ]);

        $project = Project::with('staff:id')->find($validated['ProjectID']);
        if ($project) {
            if ($project->staff->isNotEmpty() && empty($validated['StaffID'])) {
                return back()->withErrors(['StaffID' => 'Please select staff for this project.'])->withInput();
            }
            if (!empty($validated['StaffID']) && ! $project->staff->contains('id', $validated['StaffID'])) {
                return back()->withErrors(['StaffID' => 'Selected staff is not assigned to this project.'])->withInput();
            }
        }

        $task = Task::findOrFail($id);
        $staffUser = $request->user('staff');
        if ($staffUser && (int) $task->StaffID !== (int) $staffUser->id) {
            return back()->with('error', 'You can only update tasks assigned to you.');
        }
        $original = $task->getOriginal();
        $task->update($validated);

        $changes = [];
        foreach (['TaskNAME', 'TaskDESC', 'TaskDUE', 'TaskSTATUS', 'StaffID'] as $field) {
            $oldValue = $original[$field] ?? null;
            $newValue = $task->$field ?? null;
            if ((string) $oldValue !== (string) $newValue) {
                $changes[] = "{$field}: {$oldValue} -> {$newValue}";
            }
        }
        if (!empty($changes)) {
            ProjectLogService::log(
                $task->ProjectID,
                'Task updated',
                'update',
                implode('; ', $changes),
                optional($request->user('staff'))->id
            );
        }

        return redirect()
            ->route('task.index')
            ->with('success', 'Task updated successfully!');
    }

    public function markDone(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $staffUser = $request->user('staff');
        if ($staffUser && (int) $task->StaffID !== (int) $staffUser->id) {
            return back()->with('error', 'You can only mark tasks assigned to you.');
        }
        $task->update(['TaskSTATUS' => 'completed']);

        ProjectLogService::log(
            $task->ProjectID,
            'Task marked done',
            'update',
            "Task: {$task->TaskNAME}",
            optional($request->user('staff'))->id
        );

        return redirect()
            ->back()
            ->with('success', 'Task marked as done.');
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $staffUser = request()->user('staff');
        if ($staffUser && (int) $task->StaffID !== (int) $staffUser->id) {
            return back()->with('error', 'You can only delete tasks assigned to you.');
        }
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

        $staffUser = request()->user('staff');
        if ($staffUser) {
            $tasks = $tasks->where('StaffID', $staffUser->id)->values();
        }

        return response()->json($tasks);
    }
}
