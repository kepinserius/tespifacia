<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\CategoriesExport;
use App\Imports\CategoriesImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessCategoryExport;
use App\Jobs\ProcessCategoryImport;

class CategoryController extends Controller
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
        $categories = Category::when($request->search, function($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        })
        ->when($request->sort_by, function($query, $sortBy) use ($request) {
            return $query->orderBy($sortBy, $request->sort_order ?? 'asc');
        }, function($query) {
            return $query->orderBy('name', 'asc');
        })
        ->paginate($request->per_page ?? 10);

        return response()->json($categories);
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'metadata' => 'nullable|json',
            'published_at' => 'nullable|date',
        ]);

        try {
            $category = Category::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
                'metadata' => $request->metadata,
                'published_at' => $request->published_at,
            ]);

            return response()->json([
                'message' => 'Category created successfully',
                'category' => $category
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating category: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        $category->load('projects');
        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'metadata' => 'nullable|json',
            'published_at' => 'nullable|date',
        ]);

        try {
            $category->update([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active ?? $category->is_active,
                'metadata' => $request->metadata ?? $category->metadata,
                'published_at' => $request->published_at ?? $category->published_at,
            ]);

            return response()->json([
                'message' => 'Category updated successfully',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating category: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        try {
            $category->delete();
            return response()->json(['message' => 'Category deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting category: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export categories to Excel.
     *
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
        try {
            return Excel::download(new CategoriesExport, 'categories.xlsx');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error exporting categories: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Queue export job for categories.
     *
     * @return \Illuminate\Http\Response
     */
    public function queueExport()
    {
        try {
            $job = new ProcessCategoryExport();
            $this->dispatch($job);
            
            return response()->json(['message' => 'Category export queued successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error queuing category export: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Import categories from Excel.
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
            Excel::import(new CategoriesImport, $request->file('file'));
            
            return response()->json(['message' => 'Categories imported successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error importing categories: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Queue import job for categories.
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
            $job = new ProcessCategoryImport($path);
            $this->dispatch($job);
            
            return response()->json(['message' => 'Category import queued successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error queuing category import: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get audit history for the category.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function audits(Category $category)
    {
        $audits = $category->audits()->with('user')->latest()->get();
        
        return response()->json($audits);
    }
}
