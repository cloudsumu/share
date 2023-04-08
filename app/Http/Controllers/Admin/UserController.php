<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use App\Models\User;

class UserController extends Controller
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
        $this->authorize('list users');

        $search = $request->get('search', '');

        $users = User::search($search)
            ->latest()
            ->paginate();

        return view('admin.user.index', compact('users', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create users', User::class);

        return view('admin.user.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        $this->authorize('create users', User::class);

        $credentials = $request->validated();
        $credentials['password'] = Hash::make($request['password']);

        if ($request->hasFile('avatar')) {
            $credentials['avatar'] = $request->file('avatar')->store('public/user/avatars');
        }

        User::create($credentials);
        
        return redirect()
            ->route('admin.user.index')
            ->with('success', __('crud.admin.users.name')." ".__('crud.general.created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        // $this->authorize('view users', $user);
        abort(404);
        return view('admin.user.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $this->authorize('view users', $user);
        return view('admin.user.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update users', User::class);
        
        $credentials = $request->validated();
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::delete($user->avatar);
            }

            $credentials['avatar'] = $request->file('avatar')->store('public/user/avatars');
        }
        
        $user->update($credentials);

        return redirect()
            ->route('admin.user.index')
            ->with('success', __('crud.admin.users.name')." ".__('crud.general.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $this->authorize('delete users', $user);

        try {
            $avatar = $user->avatar ? $user->avatar : NULL;
            $user->mobile = $user->mobile.'-'.uniqid();
            $user->email = $user->email.'-'.uniqid();
            $user->social_unique_id = $user->social_unique_id.'-'.uniqid();
            $user->save();
            $user->delete();

            if($avatar) {
                Storage::delete($user->avatar);
            }
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
