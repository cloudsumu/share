<?php

namespace App\Http\Controllers\Admin;

use App\Models\Agent;
use App\Models\Document;
use App\Models\Provider;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Admin\StoreProviderRequest;
use App\Http\Requests\Admin\UpdateProviderRequest;

class ProviderController extends Controller
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
        $this->authorize('list providers', Provider::class);
        $search = $request->get('search', '');
        $requiredDocuments = Document::where('status', '1')->count();

        $providers = Provider::search($search)
            ->latest()
            ->paginate();

        return view('admin.provider.index', compact('providers', 'search', 'requiredDocuments'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create providers', Provider::class);
        $agents = Agent::all();
        return view('admin.provider.create', compact('agents'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProviderRequest $request)
    {
        $credentials = $request->validated();

        $credentials['mobile'] = getPhoneNumber($request->get('mobile'), $request->get('country_code'));
        $credentials['password'] = Hash::make($request['password']);

        if ($request->hasFile('avatar')) {
            $credentials['avatar'] = $request->file('avatar')->store('public/provider/avatars');
        }

        $user = Provider::create($credentials);
        return redirect()
            ->route('admin.provider.index')
            ->with('success', __('crud.general.created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Provider $provider)
    {
        abort(403);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Provider $provider)
    {
        $this->authorize('view providers', $provider);
        $agents = Agent::select('id', 'name')->get();
        $providerDocuments = $provider->documents()->select('id', 'provider_id', 'document_id', 'url', 'status')->get();
        return view('admin.provider.edit', compact('agents', 'provider', 'providerDocuments'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProviderRequest $request, Provider $provider)
    {
        $credentials = $request->validated();
        $credentials['mobile'] = getPhoneNumber($request->get('mobile'), $request->get('country_code'));

        if ($request->hasFile('avatar')) {
            if ($provider->avatar) {
                Storage::delete($provider->avatar);
            }

            $credentials['avatar'] = $request->file('avatar')->store('public/provider/avatars');
        }
        
        $provider->update($credentials);

        return redirect()
            ->route('admin.provider.index')
            ->with('success', __('crud.general.updated'));
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Provider $provider)
    {
        $this->authorize('delete providers', $provider);
        try {
            $provider->delete();
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
            ->withSuccess(__('crud.admin.providers.name')." ".__('crud.general.deleted'));
    }

    public function approveProvider(Provider $provider)
    {
        if(!(new ProviderDocumentController)->allDocumentsSubmitted($provider)) {
            return redirect()
                ->back()
                ->withErrors(__('crud.admin.documents.not_verified'));
        }

        $credentials['blocked'] = '1';
        $credentials['status'] = 'approved';
        
        $provider->update($credentials);

        return redirect()
            ->back()
            ->withSuccess(__('crud.admin.providers.name')." ".__('crud.general.blocked'));
    }

    public function blockProvider(Provider $provider)
    {
        $credentials['blocked'] = '0';
        $credentials['status'] = 'banned';
        $provider->update($credentials);

        return redirect()
            ->back()
            ->withSuccess(__('crud.admin.providers.name')." ".__('crud.general.blocked'));
    }
}
