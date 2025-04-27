<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Exports\TasksExport;
use App\Imports\TasksImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessTaskExport;
use App\Jobs\ProcessTaskImport;

class TaskController extends Controller
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
        $tasks = Task::with('project.category')
            ->when($request->project_id, function($query, $projectId) {
                return $query->where('project_id', $projectId);
            })
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->is_priority, function($query, $isPriority) {
                return $query->where('is_priority', $isPriority);
            })
            ->when($request->search, function($query, $search) {
                return $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->sort_by, function($query, $sortBy) use ($request) {
                return $query->orderBy($sortBy, $request->sort_order ?? 'asc');
            }, function($query) {
                return $query->orderBy('due_date', 'asc');
            })
            ->paginate($request->per_page ?? 10);

        return response()->json($tasks);
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
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'is_priority' => 'boolean',
            'metadata' => 'nullable|json',
            'due_date' => 'nullable|date',
        ]);

        try {
            $task = Task::create([
                'project_id' => $request->project_id,
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status,
                'is_priority' => $request->is_priority ?? false,
                'metadata' => $request->metadata,
                'due_date' => $request->due_date,
            ]);

            return response()->json([
                'message' => 'Task created successfully',
                'task' => $task->load('project')
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating task: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        $task->load('project.category');
        return response()->json($task);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'is_priority' => 'boolean',
            'metadata' => 'nullable|json',
            'due_date' => 'nullable|date',
        ]);

        try {
            $task->update([
                'project_id' => $request->project_id,
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status,
                'is_priority' => $request->is_priority ?? $task->is_priority,
                'metadata' => $request->metadata ?? $task->metadata,
                'due_date' => $request->due_date ?? $task->due_date,
            ]);

            return response()->json([
                'message' => 'Task updated successfully',
                'task' => $task->load('project')
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating task: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        try {
            $task->delete();
            return response()->json(['message' => 'Task deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting task: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get projects for select dropdown.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProjects()
    {
        $projects = Project::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'category_id']);
            
        return response()->json($projects);
    }

    /**
     * Export tasks to Excel.
     *
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
        try {
            return Excel::download(new TasksExport, 'tasks.xlsx');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error exporting tasks: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Queue export job for tasks.
     *
     * @return \Illuminate\Http\Response
     */
    public function queueExport()
    {
        try {
            $job = new ProcessTaskExport();
            $this->dispatch($job);
            
            return response()->json(['message' => 'Task export queued successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error queuing task export: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Import tasks from Excel.
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
            Excel::import(new TasksImport, $request->file('file'));
            
            return response()->json(['message' => 'Tasks imported successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error importing tasks: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Queue import job for tasks.
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
            $job = new ProcessTaskImport($path);
            $this->dispatch($job);
            
            return response()->json(['message' => 'Task import queued successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error queuing task import: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get audit history for the task.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function audits(Task $task)
    {
        $audits = $task->audits()->with('user')->latest()->get();
        
        return response()->json($audits);
    }
}
