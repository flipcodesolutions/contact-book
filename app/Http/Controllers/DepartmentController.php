<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * List departments with how many leads each one has.
     */
    public function index(Request $request)
    {
        abort_unless($request->user()->canManageDepartments(), 403);

        $departments = Department::query()->withCount('leads')->orderBy('name')->get();

        return view('departments.index', compact('departments'));
    }

    /**
     * Show the form for a new department.
     */
    public function create(Request $request)
    {
        abort_unless($request->user()->canManageDepartments(), 403);

        return view('departments.create', ['department' => new Department]);
    }

    /**
     * Save a new department.
     */
    public function store(DepartmentRequest $request)
    {
        abort_unless($request->user()->canManageDepartments(), 403);

        $department = new Department;
        $department->name = $request->input('name');
        $department->is_active = $request->boolean('is_active');
        $department->save();

        return redirect()->route('departments.index')->with('success', 'Department created.');
    }

    /**
     * Show the form for an existing department.
     */
    public function edit(Request $request, Department $department)
    {
        abort_unless($request->user()->canManageDepartments(), 403);

        return view('departments.edit', compact('department'));
    }

    /**
     * Save changes to a department.
     */
    public function update(DepartmentRequest $request, Department $department)
    {
        abort_unless($request->user()->canManageDepartments(), 403);

        $department->name = $request->input('name');
        $department->is_active = $request->boolean('is_active');
        $department->save();

        return redirect()->route('departments.index')->with('success', 'Department updated.');
    }

    /**
     * Flip a department between active and inactive. Inactive ones stay on old leads
     * but disappear from the create-lead form.
     */
    public function toggle(Request $request, Department $department)
    {
        abort_unless($request->user()->canManageDepartments(), 403);

        $department->is_active = ! $department->is_active;
        $department->save();

        return back()->with('success', 'Department updated.');
    }
}
