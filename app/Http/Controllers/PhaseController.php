<?php

namespace App\Http\Controllers;

use App\Models\Phase;
use App\Models\Project;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PhaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Phase::with(['project'])
            ->withCount(['documents'])
            ->orderBy('PhaseORDER', 'asc')
            ->orderBy('created_at', 'desc');

        if ($request->filled('PhaseNAME')) {
            $query->where('PhaseNAME', 'like', '%' . $request->input('PhaseNAME') . '%');
        }

        if ($request->filled('ProjectID')) {
            $query->where('ProjectID', $request->input('ProjectID'));
        }

        $perPage = $request->input('per_page', 10);
        $phases = $query->paginate((int) $perPage)->withQueryString();

        $projects = Project::select('id', 'ProjectNAME')->orderBy('ProjectNAME')->get();

        $stats = [
            'total' => Phase::count(),
            'withDocuments' => Phase::has('documents')->count(),
            'withoutDocuments' => Phase::doesntHave('documents')->count(),
        ];

        return Inertia::render('Phase/Index', [
            'phases' => $phases,
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

        return Inertia::render('Phase/Create', [
            'projects' => $projects,
            'preselectedProjectId' => $preselectedProjectId,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ProjectID' => 'required|exists:project,id',
            'PhaseNAME' => 'required|string|max:255',
            'PhaseDESC' => 'nullable|string',
            'PhaseUPDATE' => 'nullable|string',
            'PhaseORDER' => 'nullable|integer|min:0',
            'documents' => 'nullable|array',
            'documents.*' => 'file|max:10240',
        ]);

        $phase = Phase::create([
            'ProjectID' => $validated['ProjectID'],
            'PhaseNAME' => $validated['PhaseNAME'],
            'PhaseDESC' => $validated['PhaseDESC'] ?? null,
            'PhaseUPDATE' => $validated['PhaseUPDATE'] ?? null,
            'PhaseORDER' => $validated['PhaseORDER'] ?? 0,
        ]);

        if (!empty($validated['documents'])) {
            foreach ($validated['documents'] as $file) {
                $path = $file->store("documents/phases/{$phase->id}", 'public');

                Document::create([
                    'PhaseID' => $phase->id,
                    'DocumentNAME' => $file->getClientOriginalName(),
                    'DocumentDATE' => now()->toDateString(),
                    'DocumentVERSION' => '1.0',
                    'DocumentPATH' => $path,
                ]);
            }
        }

        return redirect()
            ->route('phases.index')
            ->with('success', 'Phase created successfully!');
    }

    public function show($id)
    {
        $phase = Phase::with(['project', 'documents'])
            ->findOrFail($id);

        return Inertia::render('Phase/Show', [
            'phase' => $phase,
        ]);
    }

    public function edit($id)
    {
        $phase = Phase::with('documents')->findOrFail($id);

        $projects = Project::select('id', 'ProjectNAME', 'ClientNAME')
            ->orderBy('ProjectNAME')
            ->get();

        return Inertia::render('Phase/Edit', [
            'phase' => $phase,
            'projects' => $projects,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'ProjectID' => 'sometimes|required|exists:project,id',
            'PhaseNAME' => 'sometimes|required|string|max:255',
            'PhaseDESC' => 'sometimes|nullable|string',
            'PhaseUPDATE' => 'sometimes|nullable|string',
            'PhaseORDER' => 'sometimes|nullable|integer|min:0',
            'documents' => 'nullable|array',
            'documents.*' => 'file|max:10240',
            'delete_document_ids' => 'nullable|array',
            'delete_document_ids.*' => 'integer|exists:document,id',
        ]);

        $phase = Phase::findOrFail($id);

        $phase->update([
            'ProjectID' => $validated['ProjectID'] ?? $phase->ProjectID,
            'PhaseNAME' => $validated['PhaseNAME'] ?? $phase->PhaseNAME,
            'PhaseDESC' => array_key_exists('PhaseDESC', $validated) ? $validated['PhaseDESC'] : $phase->PhaseDESC,
            'PhaseUPDATE' => array_key_exists('PhaseUPDATE', $validated) ? $validated['PhaseUPDATE'] : $phase->PhaseUPDATE,
            'PhaseORDER' => array_key_exists('PhaseORDER', $validated) ? $validated['PhaseORDER'] : $phase->PhaseORDER,
        ]);

        if (!empty($validated['delete_document_ids'])) {
            $documents = Document::whereIn('id', $validated['delete_document_ids'])
                ->where('PhaseID', $phase->id)
                ->get();

            foreach ($documents as $document) {
                if ($document->DocumentPATH) {
                    Storage::disk('public')->delete($document->DocumentPATH);
                }
                $document->delete();
            }
        }

        if (!empty($validated['documents'])) {
            foreach ($validated['documents'] as $file) {
                $path = $file->store("documents/phases/{$phase->id}", 'public');

                Document::create([
                    'PhaseID' => $phase->id,
                    'DocumentNAME' => $file->getClientOriginalName(),
                    'DocumentDATE' => now()->toDateString(),
                    'DocumentVERSION' => '1.0',
                    'DocumentPATH' => $path,
                ]);
            }
        }

        return redirect()
            ->route('phases.index')
            ->with('success', 'Phase updated successfully!');
    }

    public function destroy($id)
    {
        $phase = Phase::with('documents')->findOrFail($id);

        foreach ($phase->documents as $document) {
            if ($document->DocumentPATH) {
                Storage::disk('public')->delete($document->DocumentPATH);
            }
        }

        $phase->delete();

        return redirect()
            ->route('phases.index')
            ->with('success', 'Phase deleted successfully!');
    }
}
