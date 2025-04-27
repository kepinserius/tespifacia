<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Exports\ProjectsExport;
use App\Imports\ProjectsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessProjectExport;
use App\Jobs\ProcessProjectImport;

class ProjectController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $projects = Project::with('category')
            ->when($request->category_id, function($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->when($request->search, function($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->sort_by, function($query, $sortBy) use ($request) {
                return $query->orderBy($sortBy, $request->sort_order ?? 'asc');
            }, function($query) {
                return $query->orderBy('name', 'asc');
            })
            ->paginate($request->per_page ?? 10);

        return response()->json($projects);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'metadata' => 'nullable|json',
            'document' => 'nullable|file|mimes:pdf|min:100|max:500',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        try {
            $documentPath = null;
            if ($request->hasFile('document')) {
                $documentPath = $request->file('document')->store('documents');
            }

            $project = Project::create([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
                'metadata' => $request->metadata,
                'document_path' => $documentPath,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            return response()->json([
                'message' => 'Project created successfully',
                'project' => $project->load('category')
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating project: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        $project->load(['category', 'tasks']);
        return response()->json($project);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'metadata' => 'nullable|json',
            'document' => 'nullable|file|mimes:pdf|min:100|max:500',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        try {
            $documentPath = $project->document_path;
            if ($request->hasFile('document')) {
                // Delete old document if exists
                if ($documentPath) {
                    Storage::delete($documentPath);
                }
                $documentPath = $request->file('document')->store('documents');
            }

            $project->update([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active ?? $project->is_active,
                'metadata' => $request->metadata ?? $project->metadata,
                'document_path' => $documentPath,
                'start_date' => $request->start_date ?? $project->start_date,
                'end_date' => $request->end_date ?? $project->end_date,
            ]);

            return response()->json([
                'message' => 'Project updated successfully',
                'project' => $project->load('category')
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating project: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        try {
            // Delete document if exists
            if ($project->document_path) {
                Storage::delete($project->document_path);
            }
            
            $project->delete();
            return response()->json(['message' => 'Project deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting project: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get categories for select dropdown.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCategories()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
            
        return response()->json($categories);
    }

    /**
     * Export projects to Excel.
     *
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
        try {
            return Excel::download(new ProjectsExport, 'projects.xlsx');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error exporting projects: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Queue export job for projects.
     *
     * @return \Illuminate\Http\Response
     */
    public function queueExport()
    {
        try {
            $job = new ProcessProjectExport();
            $this->dispatch($job);
            
            return response()->json(['message' => 'Project export queued successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error queuing project export: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Import projects from Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new ProjectsImport, $request->file('file'));
            
            return response()->json(['message' => 'Projects imported successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error importing projects: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Queue import job for projects.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function queueImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            $path = $request->file('file')->store('imports');
            $job = new ProcessProjectImport($path);
            $this->dispatch($job);
            
            return response()->json(['message' => 'Project import queued successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error queuing project import: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get audit history for the project.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function audits(Project $project)
    {
        $audits = $project->audits()->with('user')->latest()->get();
        
        return response()->json($audits);
    }
}
