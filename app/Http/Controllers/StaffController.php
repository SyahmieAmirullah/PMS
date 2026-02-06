<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\StaffRole;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = Staff::with(['roles'])
            ->withCount(['projects'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('StaffNAME')) {
            $query->where('StaffNAME', 'like', '%' . $request->input('StaffNAME') . '%');
        }

        if ($request->filled('StaffEMAIL')) {
            $query->where('StaffEMAIL', 'like', '%' . $request->input('StaffEMAIL') . '%');
        }

        if ($request->filled('RoleTYPE')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('RoleTYPE', $request->input('RoleTYPE'));
            });
        }

        $perPage = $request->input('per_page', 10);
        $staff = $query->paginate((int)$perPage)->withQueryString();

        // Get distinct role types for filter
        $roleTypes = StaffRole::select('RoleTYPE')
            ->distinct()
            ->whereNotNull('RoleTYPE')
            ->pluck('RoleTYPE');

        // Statistics
        $stats = [
            'total' => Staff::count(),
            'withProjects' => Staff::has('projects')->count(),
            'roles' => StaffRole::count(),
            'activeProjects' => Staff::whereHas('projects', function($q) {
                $q->where('ProjectSTATUS', 'in_progress');
            })->count(),
        ];

        return Inertia::render('Staff/Index', [
            'staff' => $staff,
            'roleTypes' => $roleTypes,
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        return Inertia::render('Staff/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'StaffNAME' => 'required|string|max:255',
            'StaffPHONE' => 'nullable|string|max:20',
            'StaffEMAIL' => ['required', 'email', 'max:255', Rule::unique('staff', 'StaffEMAIL')],
            'StaffPASSWORD' => 'required|string|min:6',
            'roles' => 'nullable|array',
            'roles.*.RoleTYPE' => 'nullable|string|max:255',
            'roles.*.RoleDESC' => 'nullable|string',
            'roles.*.RolePRO' => 'nullable|string',
        ]);

        $staff = Staff::create([
            'StaffNAME' => $validated['StaffNAME'],
            'StaffPHONE' => $validated['StaffPHONE'] ?? null,
            'StaffEMAIL' => $validated['StaffEMAIL'],
            'StaffPASSWORD' => Hash::make($validated['StaffPASSWORD']),
        ]);

        // Create roles if provided (ensure required DB fields are not null)
        $roles = collect($validated['roles'] ?? [])
            ->filter(function ($role) {
                return !empty($role['RoleTYPE']);
            })
            ->map(function ($role) {
                return [
                    'RoleTYPE' => trim($role['RoleTYPE']),
                    'RoleDESC' => isset($role['RoleDESC']) ? trim($role['RoleDESC']) : '',
                    'RolePRO' => $role['RolePRO'] ?? null,
                ];
            })
            ->values()
            ->all();

        if (!empty($roles)) {
            $staff->roles()->createMany($roles);
        }

        return redirect()
            ->route('staff.index')
            ->with('success', 'Staff member created successfully!');
    }

   public function show($id)
    {
        $staff = Staff::with([
            'roles',
            'projects' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
        ])
        ->withCount(['projects']) // Remove 'attendances'
        ->findOrFail($id);

        return Inertia::render('Staff/Show', [
            'staff' => $staff,
        ]);
    }

    public function edit($id)
    {
        $staff = Staff::with('roles')->findOrFail($id);

        return Inertia::render('Staff/Edit', [
            'staff' => $staff,
        ]);
    }

    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        $validated = $request->validate([
            'StaffNAME' => 'required|string|max:255',
            'StaffPHONE' => 'nullable|string|max:20',
            'StaffEMAIL' => ['required', 'email', 'max:255', Rule::unique('staff', 'StaffEMAIL')->ignore($id)],
            'StaffPASSWORD' => 'nullable|string|min:6',
            'roles' => 'nullable|array',
            'roles.*.RoleTYPE' => 'nullable|string|max:255',
            'roles.*.RoleDESC' => 'nullable|string',
            'roles.*.RolePRO' => 'nullable|string',
        ]);

        $staff->update([
            'StaffNAME' => $validated['StaffNAME'],
            'StaffPHONE' => $validated['StaffPHONE'] ?? null,
            'StaffEMAIL' => $validated['StaffEMAIL'],
        ]);

        // Update password only if provided
        if (!empty($validated['StaffPASSWORD'])) {
            $staff->update([
                'StaffPASSWORD' => Hash::make($validated['StaffPASSWORD']),
            ]);
        }

        // Update roles
        if (isset($validated['roles'])) {
            // Delete existing roles
            $staff->roles()->delete();

            // Create new roles
            $roles = collect($validated['roles'])
                ->filter(function ($role) {
                    return !empty($role['RoleTYPE']);
                })
                ->map(function ($role) {
                    return [
                        'RoleTYPE' => trim($role['RoleTYPE']),
                        'RoleDESC' => isset($role['RoleDESC']) ? trim($role['RoleDESC']) : '',
                        'RolePRO' => $role['RolePRO'] ?? null,
                    ];
                })
                ->values()
                ->all();

            if (!empty($roles)) {
                $staff->roles()->createMany($roles);
            }
        }

        return redirect()
            ->route('staff.index')
            ->with('success', 'Staff member updated successfully!');
    }

    public function destroy($id)
    {
        $staff = Staff::findOrFail($id);
        $staff->delete();

        return redirect()
            ->route('staff.index')
            ->with('success', 'Staff member deleted successfully!');
    }
}
