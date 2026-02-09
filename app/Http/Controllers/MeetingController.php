<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Meeting;
use App\Models\Project;
use App\Services\ProjectLogService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        $staffUser = $request->user('staff');

        $query = Meeting::with(['project'])
            ->withCount(['attendances'])
            ->orderBy('MeetingDATE', 'desc')
            ->orderBy('MeetingTIME', 'desc');

        if ($staffUser) {
            $query->with(['attendances' => function ($q) use ($staffUser) {
                $q->where('StaffID', $staffUser->id);
            }]);
        }

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

        ProjectLogService::log(
            $meeting->ProjectID,
            'Meeting created',
            'update',
            "Meeting: {$meeting->MeetingTITLE}",
            optional($request->user('staff'))->id
        );

        $project = Project::with('staff')->find($validated['ProjectID']);
        if ($project) {
            $attendances = $project->staff->map(function ($staff) use ($meeting) {
                return [
                    'MeetingID' => $meeting->id,
                    'StaffID' => $staff->id,
                    'AttandanceSTATUS' => 'pending',
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
        $staffUser = request()->user('staff');

        $meeting = Meeting::with(['project', 'project.staff', 'attendances.staff'])
            ->findOrFail($id);

        $canSelfAttend = true;
        if ($staffUser) {
            $canSelfAttend = $meeting->project
                && $meeting->project->staff()
                    ->where('staff.id', $staffUser->id)
                    ->exists();
        }

        $assignedStaffIds = $meeting->project
            ? $meeting->project->staff->pluck('id')->all()
            : [];

        return Inertia::render('Meeting/Show', [
            'meeting' => $meeting,
            'canSelfAttend' => $canSelfAttend,
            'assignedStaffIds' => $assignedStaffIds,
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
                    'AttandanceSTATUS' => 'pending',
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
            'attendances.*.AttandanceSTATUS' => 'required|in:present,absent,late,excused,pending',
            'attendances.*.AttandanceTIME' => 'nullable',
            'attendances.*.AbsentREASON' => 'nullable|string',
            'attendances.*.AttandanceLOCATION' => 'nullable|string',
            'attendances.*.AttandanceLAT' => 'nullable|numeric',
            'attendances.*.AttandanceLNG' => 'nullable|numeric',
        ]);

        $meeting = Meeting::findOrFail($id);
        $original = $meeting->getOriginal();
        $meeting->update([
            'ProjectID' => $validated['ProjectID'],
            'MeetingTITLE' => $validated['MeetingTITLE'],
            'MeetingOBJECTIVE' => $validated['MeetingOBJECTIVE'] ?? null,
            'MeetingDATE' => $validated['MeetingDATE'],
            'MeetingTIME' => $validated['MeetingTIME'],
            'MeetingLINK' => $validated['MeetingLINK'] ?? null,
        ]);

        $changes = [];
        foreach (['MeetingTITLE', 'MeetingOBJECTIVE', 'MeetingDATE', 'MeetingTIME', 'MeetingLINK'] as $field) {
            $oldValue = $original[$field] ?? null;
            $newValue = $meeting->$field ?? null;
            if ((string) $oldValue !== (string) $newValue) {
                $changes[] = "{$field}: {$oldValue} -> {$newValue}";
            }
        }
        if (!empty($changes)) {
            ProjectLogService::log(
                $meeting->ProjectID,
                'Meeting updated',
                'update',
                implode('; ', $changes),
                optional($request->user('staff'))->id
            );
        }

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

    public function updateSelfAttendance(Request $request, $id)
    {
        $staffUser = $request->user('staff');
        if (! $staffUser) {
            return back()->with('error', 'You must be logged in as staff to update attendance.');
        }

        $validated = $request->validate([
            'AttandanceSTATUS' => 'required|in:present,absent,late,excused,pending',
            'AbsentREASON' => 'nullable|string|max:255',
            'AttandanceLOCATION' => 'nullable|string|max:255',
            'AttandanceLAT' => 'nullable|numeric',
            'AttandanceLNG' => 'nullable|numeric',
        ]);

        $meeting = Meeting::with('project.staff')->findOrFail($id);
        $isAssigned = $meeting->project
            && $meeting->project->staff->contains('id', $staffUser->id);

        if (! $isAssigned) {
            return back()->with('error', 'You are not assigned to this meeting project.');
        }

        $attendance = Attendance::firstOrNew([
            'MeetingID' => $meeting->id,
            'StaffID' => $staffUser->id,
        ]);

        $attendance->AttandanceSTATUS = $validated['AttandanceSTATUS'];
        $attendance->AttandanceDATE = $meeting->MeetingDATE;
        $attendance->AttandanceTIME = $meeting->MeetingTIME;
        $attendance->AbsentREASON = $validated['AbsentREASON'] ?? null;
        $attendance->AttandanceLOCATION = $validated['AttandanceLOCATION'] ?? null;
        $attendance->AttandanceLAT = $validated['AttandanceLAT'] ?? null;
        $attendance->AttandanceLNG = $validated['AttandanceLNG'] ?? null;
        $attendance->save();

        return back()->with('success', 'Attendance updated successfully!');
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
