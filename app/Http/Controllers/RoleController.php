<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoleController extends Controller
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
        $roles = Role::with('permissions')->when($request->search, function($query, $search) {
            return $query->where('name', 'like', "%{$search}%");
        })
        ->orderBy($request->sort_by ?? 'name', $request->sort_order ?? 'asc')
        ->paginate($request->per_page ?? 10);

        return response()->json($roles);
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
            'name' => 'required|unique:roles,name',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create(['name' => $request->name]);
            $role->syncPermissions($request->permissions);
            DB::commit();

            return response()->json([
                'message' => 'Role created successfully',
                'role' => $role->load('permissions')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating role: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \Spatie\Permission\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        return response()->json($role->load('permissions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Spatie\Permission\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => ['required', Rule::unique('roles')->ignore($role->id)],
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::beginTransaction();
        try {
            $role->update(['name' => $request->name]);
            $role->syncPermissions($request->permissions);
            DB::commit();

            return response()->json([
                'message' => 'Role updated successfully',
                'role' => $role->load('permissions')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating role: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Spatie\Permission\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        if ($role->name === 'admin') {
            return response()->json(['message' => 'Cannot delete admin role'], 403);
        }

        try {
            $role->delete();
            return response()->json(['message' => 'Role deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting role: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get all permissions.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllPermissions()
    {
        $permissions = Permission::all();
        return response()->json($permissions);
    }
}
