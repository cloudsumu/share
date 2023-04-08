<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
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
        $this->authorize('list permissions', Permission::class);
        $search      = $request->get('search', '');
        $permissions = Permission::where('name', 'like', "%{$search}%")->paginate(10);

        return view('admin.permission.index', compact('permissions', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create permissions', Permission::class);
        $roles = Role::all();
        return view('admin.permission.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create permissions', Permission::class);
        $data = $this->validate($request, [
            'name'  => 'required|max:64|unique:permissions,name',
            'roles' => 'array',
        ]);

        $permission = Permission::create($data);

        $roles = Role::find($request->roles);
        $permission->syncRoles($roles);

        return redirect()
            ->route('admin.permission.index')
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
    public function edit(Permission $permission)
    {
        abort(403);
        $roles = Role::get();

        return view('admin.permission.edit', compact('permission', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Permission $permission)
    {
        abort(403);
        $data = $this->validate($request, [
            'name'  => 'required|max:40',
            'roles' => 'array',
        ]);

        $permission->update($data);

        $roles = Role::find($request->roles);
        $permission->syncRoles($roles);

        return redirect()
            ->route('admin.permission.index')
            ->withSuccess(__('crud.general.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permission $permission)
    {
        abort(403);
    }
}
