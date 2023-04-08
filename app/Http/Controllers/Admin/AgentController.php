<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\{
    StoreAgentRequest, UpdateAgentRequest
};

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use App\Models\Agent;

class AgentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('list agents', Agent::class);
        $search = $request->get('search', '');

        $agents = Agent::search($search)
            ->latest()
            ->paginate();

        return view('admin.agent.index', compact('agents', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create agents', Agent::class);
        return view('admin.agent.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAgentRequest $request)
    {
        $credentials = $request->validated();
        $credentials['password'] = Hash::make($request['password']);

        if ($request->hasFile('avatar')) {
            $credentials['avatar'] = $request->file('avatar')->store('public/agent/avatars');
        }

        $user = Agent::create($credentials);
        return redirect()
            ->route('admin.agent.index')
            ->with('success', __('crud.general.created'));
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
    public function edit(Agent $agent)
    {
        $this->authorize('view agents', $agent);
        return view('admin.agent.edit', compact('agent'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAgentRequest $request, Agent $agent)
    {
        $credentials = $request->validated();

        if ($request->hasFile('avatar')) {
            if ($agent->avatar) {
                Storage::delete($agent->avatar);
            }

            $credentials['avatar'] = $request->file('avatar')->store('public/agent/avatars');
        }
        
        $agent->update($credentials);

        return redirect()
            ->route('admin.agent.index')
            ->with('success', __('crud.admin.agents.name')." ".__('crud.general.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Agent $agent)
    {
        $this->authorize('delete agents', $agent);
        try {
            $agent->mobile = $agent->mobile.'-'.uniqid();
            $agent->email = $agent->email.'-'.uniqid();
            $agent->delete();
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
            ->withSuccess(__('crud.admin.agents.name')." ".__('crud.general.deleted'));
    }
}
