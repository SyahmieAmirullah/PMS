<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Meeting;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        $query = Meeting::with(['project'])
            ->withCount(['attendances'])
            ->orderBy('MeetingDATE', 'desc')
            ->orderBy('MeetingTIME', 'desc');

        if ($request->filled('MeetingTITLE')) {
            $query->where('MeetingTITLE', 'like', '%' . $request->input('MeetingTITLE') . '%');
        }

        if ($request->filled('ProjectID')) {
            $query->where('ProjectID', $request->input('ProjectID'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('MeetingDATE', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('MeetingDATE', '<=', $request->input('date_to'));
        }

        $perPage = $request->input('per_page', 10);
        $meetings = $query->paginate((int) $perPage)->withQueryString();

        $projects = Project::select('id', 'ProjectNAME')->orderBy('ProjectNAME')->get();

        $stats = [
            'total' => Meeting::count(),
            'upcoming' => Meeting::where('MeetingDATE', '>=', now()->toDateString())->count(),
            'past' => Meeting::where('MeetingDATE', '<', now()->toDateString())->count(),
        ];

        return Inertia::render('Meeting/Index', [
            'meetings' => $meetings,
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

        return Inertia::render('Meeting/Create', [
            'projects' => $projects,
            'preselectedProjectId' => $preselectedProjectId,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ProjectID' => 'required|exists:project,id',
            'MeetingTITLE' => 'required|string|max:255',
            'MeetingOBJECTIVE' => 'nullable|string',
            'MeetingDATE' => 'required|date',
            'MeetingTIME' => 'required',
            'MeetingLINK' => 'nullable|url|max:2048',
        ]);

        $meeting = Meeting::create($validated);

        $project = Project::with('staff')->find($validated['ProjectID']);
        if ($project) {
            $attendances = $project->staff->map(function ($staff) use ($meeting) {
                return [
                    'MeetingID' => $meeting->id,
                    'StaffID' => $staff->id,
                    'AttandanceSTATUS' => 'present',
                    'AttandanceDATE' => $meeting->MeetingDATE,
                    'AttandanceTIME' => $meeting->MeetingTIME,
                    'AbsentREASON' => null,
                    'AttandanceLOCATION' => null,
                ];
            })->toArray();

            if (!empty($attendances)) {
                Attendance::insert($attendances);
            }
        }

        return redirect()
            ->route('meetings.index')
            ->with('success', 'Meeting created successfully!');
    }

    public function show($id)
    {
        $meeting = Meeting::with(['project', 'attendances.staff'])
            ->findOrFail($id);

        return Inertia::render('Meeting/Show', [
            'meeting' => $meeting,
        ]);
    }

    public function edit($id)
    {
        $meeting = Meeting::with(['project', 'attendances.staff'])
            ->findOrFail($id);

        $projects = Project::select('id', 'ProjectNAME', 'ClientNAME')
            ->orderBy('ProjectNAME')
            ->get();

        $project = Project::with('staff')->find($meeting->ProjectID);
        if ($project) {
            $existingStaffIds = $meeting->attendances->pluck('StaffID')->all();
            $missingStaff = $project->staff->whereNotIn('id', $existingStaffIds);

            foreach ($missingStaff as $staff) {
                Attendance::create([
                    'MeetingID' => $meeting->id,
                    'StaffID' => $staff->id,
                    'AttandanceSTATUS' => 'present',
                    'AttandanceDATE' => $meeting->MeetingDATE,
                    'AttandanceTIME' => $meeting->MeetingTIME,
                    'AbsentREASON' => null,
                    'AttandanceLOCATION' => null,
                ]);
            }

            $meeting->load(['attendances.staff']);
        }

        return Inertia::render('Meeting/Edit', [
            'meeting' => $meeting,
            'projects' => $projects,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'ProjectID' => 'required|exists:project,id',
            'MeetingTITLE' => 'required|string|max:255',
            'MeetingOBJECTIVE' => 'nullable|string',
            'MeetingDATE' => 'required|date',
            'MeetingTIME' => 'required',
            'MeetingLINK' => 'nullable|url|max:2048',
            'attendances' => 'nullable|array',
            'attendances.*.id' => 'required|integer|exists:attendance,id',
            'attendances.*.AttandanceSTATUS' => 'required|in:present,absent,late,excused',
            'attendances.*.AttandanceTIME' => 'nullable',
            'attendances.*.AbsentREASON' => 'nullable|string',
            'attendances.*.AttandanceLOCATION' => 'nullable|string',
            'attendances.*.AttandanceLAT' => 'nullable|numeric',
            'attendances.*.AttandanceLNG' => 'nullable|numeric',
        ]);

        $meeting = Meeting::findOrFail($id);
        $meeting->update([
            'ProjectID' => $validated['ProjectID'],
            'MeetingTITLE' => $validated['MeetingTITLE'],
            'MeetingOBJECTIVE' => $validated['MeetingOBJECTIVE'] ?? null,
            'MeetingDATE' => $validated['MeetingDATE'],
            'MeetingTIME' => $validated['MeetingTIME'],
            'MeetingLINK' => $validated['MeetingLINK'] ?? null,
        ]);

        if (!empty($validated['attendances'])) {
            foreach ($validated['attendances'] as $attendanceData) {
                Attendance::where('id', $attendanceData['id'])
                    ->where('MeetingID', $meeting->id)
                    ->update([
                        'AttandanceSTATUS' => $attendanceData['AttandanceSTATUS'],
                        'AttandanceDATE' => $meeting->MeetingDATE,
                        'AttandanceTIME' => $attendanceData['AttandanceTIME'] ?? $meeting->MeetingTIME,
                        'AbsentREASON' => $attendanceData['AbsentREASON'] ?? null,
                        'AttandanceLOCATION' => $attendanceData['AttandanceLOCATION'] ?? null,
                        'AttandanceLAT' => $attendanceData['AttandanceLAT'] ?? null,
                        'AttandanceLNG' => $attendanceData['AttandanceLNG'] ?? null,
                    ]);
            }
        }

        return redirect()
            ->route('meetings.index')
            ->with('success', 'Meeting updated successfully!');
    }

    public function destroy($id)
    {
        $meeting = Meeting::findOrFail($id);
        $meeting->delete();

        return redirect()
            ->route('meetings.index')
            ->with('success', 'Meeting deleted successfully!');
    }
}
