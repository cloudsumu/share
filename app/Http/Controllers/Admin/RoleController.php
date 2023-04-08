<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('demo')->only(['update', 'destroy']);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('list roles', Role::class);
        $search = $request->get('search', '');
        $roles  = Role::where('name', 'like', "%{$search}%")->paginate(10);

        return view('admin.role.index')
            ->with('roles', $roles)
            ->with('search', $search);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create roles', Role::class);
        $permissions = Permission::all();
        return view('admin.role.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create roles', Role::class);
        $data = $this->validate($request, [
            'name'        => 'required|unique:roles|max:32',
            'permissions' => 'array',
        ]);

        $role = Role::create($data);

        $permissions = Permission::find($request->permissions);
        $role->syncPermissions($permissions);

        return redirect()
            ->route('admin.role.index')
            ->withSuccess(__('crud.general.created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        abort(403);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        $this->authorize('view roles', $role);
        $permissions = Permission::all();

        return view('admin.role.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        abort(403);
        $this->authorize('update roles', $role);
        $data = $this->validate($request, [
            'name'        => 'required|max:32|unique:roles,name,' . $role->id,
            'permissions' => 'array',
        ]);

        $role->update($data);

        $permissions = Permission::find($request->permissions);
        $role->syncPermissions($permissions);

        return redirect()
            ->route('admin.role.index')
            ->withSuccess(__('crud.general.saved'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        abort(403);
        $this->authorize('delete roles', $role);
        if($role->id == 1) {
            return redirect()
                ->back()
                ->withErrors(__('crud.general.not_done'));
        }
        try {
            $role->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            if($e->getCode() == 23000)
            {
                return redirect()
                    ->back()
                    ->withErrors(__('crud.general.integrity_violation'));
            }
            return redirect()
                ->back()
                ->withErrors(__('crud.general.not_done'));
        }

        return redirect()
            ->back()
            ->withSuccess(__('crud.general.deleted'));
    }
}
