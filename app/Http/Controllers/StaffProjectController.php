<?php

namespace App\Http\Controllers;

use App\Models\StaffProject;
use App\Models\Staff;
use App\Models\Project;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StaffProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = StaffProject::with(['staff', 'project'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('StaffID')) {
            $query->where('StaffID', $request->input('StaffID'));
        }

        if ($request->filled('ProjectID')) {
            $query->where('ProjectID', $request->input('ProjectID'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('ProjectSTART', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('ProjectDUE', '<=', $request->input('date_to'));
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->whereHas('project', function($q) {
                    $q->where('ProjectSTATUS', 'in_progress');
                });
            } elseif ($status === 'overdue') {
                $query->where('ProjectDUE', '<', now())
                    ->whereHas('project', function($q) {
                        $q->whereNotIn('ProjectSTATUS', ['completed', 'cancelled']);
                    });
            }
        }

        $perPage = $request->input('per_page', 10);
        $staffProjects = $query->paginate((int)$perPage)->withQueryString();

        // Get all staff and projects for filters
        $staffList = Staff::select('id', 'StaffNAME')->orderBy('StaffNAME')->get();
        $projectList = Project::select('id', 'ProjectNAME')->orderBy('ProjectNAME')->get();

        // Statistics
        $stats = [
            'total' => StaffProject::count(),
            'active' => StaffProject::whereHas('project', function($q) {
                $q->where('ProjectSTATUS', 'in_progress');
            })->count(),
            'overdue' => StaffProject::where('ProjectDUE', '<', now())
                ->whereHas('project', function($q) {
                    $q->whereNotIn('ProjectSTATUS', ['completed', 'cancelled']);
                })->count(),
            'thisMonth' => StaffProject::whereBetween('ProjectSTART', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])->count(),
        ];

        return Inertia::render('StaffProject/Index', [
            'staffProjects' => $staffProjects,
            'staffList' => $staffList,
            'projectList' => $projectList,
            'stats' => $stats,
        ]);
    }

    public function create(Request $request)
    {
        $staffList = Staff::select('id', 'StaffNAME', 'StaffEMAIL')
            ->orderBy('StaffNAME')
            ->get();

        $projectList = Project::select('id', 'ProjectNAME', 'ClientNAME', 'ProjectSTATUS')
            ->orderBy('ProjectNAME')
            ->get();

        $preselectedStaffId = $request->query('staff_id');
        $preselectedProjectId = $request->query('project_id');

        return Inertia::render('StaffProject/Create', [
            'staffList' => $staffList,
            'projectList' => $projectList,
            'preselectedStaffId' => $preselectedStaffId,
            'preselectedProjectId' => $preselectedProjectId,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'StaffID' => 'required|exists:staff,id',
            'ProjectID' => 'required|exists:project,id',
            'ProjectSTART' => 'required|date',
            'ProjectDUE' => 'required|date|after_or_equal:ProjectSTART',
        ]);

        // Check if assignment already exists
        $exists = StaffProject::where('StaffID', $validated['StaffID'])
            ->where('ProjectID', $validated['ProjectID'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'error' => 'This staff member is already assigned to this project.'
            ]);
        }

        StaffProject::create($validated);

        return redirect()
            ->route('staff-project.index')
            ->with('success', 'Staff assigned to project successfully!');
    }

    public function show($id)
    {
        $staffProject = StaffProject::with(['staff', 'project'])
            ->findOrFail($id);

        return Inertia::render('StaffProject/Show', [
            'staffProject' => $staffProject,
        ]);
    }

    public function edit($id)
    {
        $staffProject = StaffProject::with(['staff', 'project'])
            ->findOrFail($id);

        $staffList = Staff::select('id', 'StaffNAME', 'StaffEMAIL')
            ->orderBy('StaffNAME')
            ->get();

        $projectList = Project::select('id', 'ProjectNAME', 'ClientNAME', 'ProjectSTATUS')
            ->orderBy('ProjectNAME')
            ->get();

        return Inertia::render('StaffProject/Edit', [
            'staffProject' => $staffProject,
            'staffList' => $staffList,
            'projectList' => $projectList,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'StaffID' => 'required|exists:staff,id',
            'ProjectID' => 'required|exists:project,id',
            'ProjectSTART' => 'required|date',
            'ProjectDUE' => 'required|date|after_or_equal:ProjectSTART',
        ]);

        $staffProject = StaffProject::findOrFail($id);

        // Check if new assignment already exists (excluding current record)
        $exists = StaffProject::where('StaffID', $validated['StaffID'])
            ->where('ProjectID', $validated['ProjectID'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'error' => 'This staff member is already assigned to this project.'
            ]);
        }

        $staffProject->update($validated);

        return redirect()
            ->route('staff-project.index')
            ->with('success', 'Staff-Project assignment updated successfully!');
    }

    public function destroy($id)
    {
        $staffProject = StaffProject::findOrFail($id);
        $staffProject->delete();

        return redirect()
            ->route('staff-project.index')
            ->with('success', 'Staff-Project assignment deleted successfully!');
    }

    // API endpoint to get staff projects by staff ID
    public function getByStaff($staffId)
    {
        $staffProjects = StaffProject::with('project')
            ->where('StaffID', $staffId)
            ->orderBy('ProjectSTART', 'desc')
            ->get();

        return response()->json($staffProjects);
    }

    // API endpoint to get staff projects by project ID
    public function getByProject($projectId)
    {
        $staffProjects = StaffProject::with('staff')
            ->where('ProjectID', $projectId)
            ->orderBy('ProjectSTART', 'desc')
            ->get();

        return response()->json($staffProjects);
    }

    // Bulk assign staff to project
    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            'ProjectID' => 'required|exists:project,id',
            'staff_ids' => 'required|array|min:1',
            'staff_ids.*' => 'exists:staff,id',
            'ProjectSTART' => 'required|date',
            'ProjectDUE' => 'required|date|after_or_equal:ProjectSTART',
        ]);

        $created = 0;
        $skipped = 0;

        foreach ($validated['staff_ids'] as $staffId) {
            $exists = StaffProject::where('StaffID', $staffId)
                ->where('ProjectID', $validated['ProjectID'])
                ->exists();

            if (!$exists) {
                StaffProject::create([
                    'StaffID' => $staffId,
                    'ProjectID' => $validated['ProjectID'],
                    'ProjectSTART' => $validated['ProjectSTART'],
                    'ProjectDUE' => $validated['ProjectDUE'],
                ]);
                $created++;
            } else {
                $skipped++;
            }
        }

        return redirect()
            ->route('staff-project.index')
            ->with('success', "Bulk assignment completed! Created: {$created}, Skipped: {$skipped}");
    }
}
