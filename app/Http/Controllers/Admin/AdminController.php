<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\{
    StoreAdminRequest, UpdateAdminRequest
};

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use App\Models\Admin;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
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
        $this->authorize('list admins', Admin::class);
        $search = $request->get('search', '');

        $admins = Admin::search($search)
            ->latest()
            ->paginate();

        return view('admin.admin.index', compact('admins', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create admins', Admin::class);
        $roles = Role::get();
        return view('admin.admin.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAdminRequest $request)
    {
        $credentials = $request->except('roles');
        $credentials['password'] = Hash::make($request['password']);

        if ($request->hasFile('avatar')) {
            $credentials['avatar'] = $request->file('avatar')->store('public/admin/avatars');
        }

        $admin = Admin::create($credentials);
        $admin->syncRoles($request->roles);
        
        return redirect()
            ->route('admin.admin.index')
            ->with('success', __('crud.general.created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Admin $admin)
    {
        abort(403);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Admin $admin)
    {
        $this->authorize('view admins', $admin);
        $roles = Role::get();
        return view('admin.admin.edit', compact('admin', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAdminRequest $request, Admin $admin)
    {
        $this->authorize('delete admins', $admin);
        $credentials = $request->except('roles');

        if (empty($credentials['password'])) {
            unset($credentials['password']);
        } else {
            $credentials['password'] = Hash::make($credentials['password']);
        }

        if ($request->hasFile('avatar')) {
            if ($admin->avatar) {
                Storage::delete($admin->avatar);
            }

            $credentials['avatar'] = $request->file('avatar')->store('public/admin');
        }

        $admin->update($credentials);

        $admin->syncRoles($request->roles);

        return redirect()
            ->route('admin.admin.index')
            ->withSuccess(__('crud.general.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Admin $admin)
    {
        if($admin->id == 1) {
            return redirect()
                    ->back()
                    ->withErrors(__('crud.general.not_done'));
        }

        try {
            $admin->delete();
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
