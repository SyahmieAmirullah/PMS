<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Staff;
use App\Models\StaffProject;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with(['staff', 'phases', 'tasks'])
            ->withCount([
                'tasks',
                'phases',
                'feedback',
            ])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('ProjectNAME')) {
            $query->where('ProjectNAME', 'like', '%' . $request->input('ProjectNAME') . '%');
        }

        if ($request->filled('ProjectSTATUS')) {
            $query->where('ProjectSTATUS', $request->input('ProjectSTATUS'));
        }

        if ($request->filled('ClientNAME')) {
            $query->where('ClientNAME', 'like', '%' . $request->input('ClientNAME') . '%');
        }

        if ($request->filled('StaffID')) {
            $query->whereHas('staff', function($q) use ($request) {
                $q->where('staff.id', $request->input('StaffID'));
            });
        }

        $perPage = $request->input('per_page', 10);
        $projects = $query->paginate((int)$perPage)->withQueryString();

        // Get all staff for filter dropdown
        $staffList = Staff::select('id', 'StaffNAME')->orderBy('StaffNAME')->get();

        // Statistics
        $stats = [
            'total' => Project::count(),
            'active' => Project::where('ProjectSTATUS', 'in_progress')->count(),
            'completed' => Project::where('ProjectSTATUS', 'completed')->count(),
            'onHold' => Project::where('ProjectSTATUS', 'on_hold')->count(),
        ];

        return Inertia::render('Project/Index', [
            'projects' => $projects,
            'staffList' => $staffList,
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        $staffList = Staff::select('id', 'StaffNAME', 'StaffEMAIL')
            ->with(['roles'])
            ->orderBy('StaffNAME')
            ->get();

        return Inertia::render('Project/Create', [
            'staffList' => $staffList,
        ]);
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $statusMap = [
            'Planning' => 'planning',
            'In Progress' => 'in_progress',
            'On Hold' => 'on_hold',
            'Completed' => 'completed',
            'Cancelled' => 'cancelled',
            'planning' => 'planning',
            'in_progress' => 'in_progress',
            'on_hold' => 'on_hold',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
        ];

        if ($request->filled('ProjectSTATUS')) {
            $incomingStatus = $request->input('ProjectSTATUS');
            if (isset($statusMap[$incomingStatus])) {
                $request->merge(['ProjectSTATUS' => $statusMap[$incomingStatus]]);
            }
        }

        $validated = $request->validate([
            'ProjectNAME' => 'required|string|max:255',
            'ProjectDESC' => 'nullable|string',
            'ProjectSTATUS' => ['required', Rule::in(['planning', 'in_progress', 'on_hold', 'completed', 'cancelled'])],
            'ClientNAME' => 'required|string|max:255',
            'ClientPHONE' => 'nullable|string|max:20',
            'ClientEMAIL' => 'nullable|email|max:255',
            'staff_ids' => 'nullable|array',
            'staff_ids.*' => 'exists:staff,id',
            'ProjectSTART' => 'nullable|date',
            'ProjectDUE' => 'nullable|date|after_or_equal:ProjectSTART',
        ]);

        $project = Project::create([
            'ProjectNAME' => $validated['ProjectNAME'],
            'ProjectDESC' => $validated['ProjectDESC'],
            'ProjectSTATUS' => $validated['ProjectSTATUS'],
            'ClientNAME' => $validated['ClientNAME'],
            'ClientPHONE' => $validated['ClientPHONE'],
            'ClientEMAIL' => $validated['ClientEMAIL'],
        ]);

        // Attach staff to project with dates
        if (!empty($validated['staff_ids'])) {
            $staffIds = array_map('intval', $validated['staff_ids']);
            
            // Create pivot data with dates
            $pivotData = [];
            foreach ($staffIds as $staffId) {
                $pivotData[$staffId] = [
                    'ProjectSTART' => $validated['ProjectSTART'] ?? now(),
                    'ProjectDUE' => $validated['ProjectDUE'] ?? now()->addMonths(1),
                ];
            }
            
            $project->staff()->attach($pivotData);
        }

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project created successfully!');
    }

    public function show($id)
    {
        $project = Project::with([
            'staff',
            'phases' => function($query) {
                $query->orderBy('created_at', 'asc');
            },
            'tasks' => function($query) {
                $query->orderBy('TaskDUE', 'asc');
            },
            'feedback' => function($query) {
                $query->orderBy('FeedbackTIME', 'desc');
            },
            'projectLogs' => function($query) {
                $query->orderBy('ProjectDATE', 'desc');
            }
        ])
        ->withCount(['tasks', 'phases', 'feedback'])
        ->findOrFail($id);

        return Inertia::render('Project/Show', [
            'project' => $project,
        ]);
    }

    public function edit($id)
    {
        $project = Project::with(['staff', 'phases', 'tasks'])
            ->findOrFail($id);

        $staffList = Staff::select('id', 'StaffNAME', 'StaffEMAIL')
            ->orderBy('StaffNAME')
            ->get();

        return Inertia::render('Project/Edit', [
            'project' => $project,
            'staffList' => $staffList,
        ]);
    }

    public function update(Request $request, $id)
    {
        $statusMap = [
            'Planning' => 'planning',
            'In Progress' => 'in_progress',
            'On Hold' => 'on_hold',
            'Completed' => 'completed',
            'Cancelled' => 'cancelled',
            'planning' => 'planning',
            'in_progress' => 'in_progress',
            'on_hold' => 'on_hold',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
        ];

        if ($request->filled('ProjectSTATUS')) {
            $incomingStatus = $request->input('ProjectSTATUS');
            if (isset($statusMap[$incomingStatus])) {
                $request->merge(['ProjectSTATUS' => $statusMap[$incomingStatus]]);
            }
        }

        $validated = $request->validate([
            'ProjectNAME' => 'required|string|max:255',
            'ProjectDESC' => 'nullable|string',
            'ProjectSTATUS' => ['required', Rule::in(['planning', 'in_progress', 'on_hold', 'completed', 'cancelled'])],
            'ClientNAME' => 'required|string|max:255',
            'ClientPHONE' => 'nullable|string|max:20',
            'ClientEMAIL' => 'nullable|email|max:255',
            'staff_ids' => 'nullable|array',
            'staff_ids.*' => 'exists:staff,id',
            'ProjectSTART' => 'nullable|date',
            'ProjectDUE' => 'nullable|date|after_or_equal:ProjectSTART',
        ]);

        $project = Project::findOrFail($id);

        $project->update([
            'ProjectNAME' => $validated['ProjectNAME'],
            'ProjectDESC' => $validated['ProjectDESC'],
            'ProjectSTATUS' => $validated['ProjectSTATUS'],
            'ClientNAME' => $validated['ClientNAME'],
            'ClientPHONE' => $validated['ClientPHONE'],
            'ClientEMAIL' => $validated['ClientEMAIL'],
        ]);

        // Sync staff with dates
        if (isset($validated['staff_ids'])) {
            $staffIds = array_map('intval', $validated['staff_ids']);
            
            // Create pivot data with dates
            $pivotData = [];
            foreach ($staffIds as $staffId) {
                $pivotData[$staffId] = [
                    'ProjectSTART' => $validated['ProjectSTART'] ?? now(),
                    'ProjectDUE' => $validated['ProjectDUE'] ?? now()->addMonths(1),
                ];
            }
            
            $project->staff()->sync($pivotData);
        } else {
            // If no staff selected, detach all
            $project->staff()->sync([]);
        }

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project updated successfully!');
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted successfully!');
    }
}
