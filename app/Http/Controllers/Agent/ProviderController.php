<?php

namespace App\Http\Controllers\Agent;

use App\Models\Agent;
use App\Models\Provider;
use Illuminate\Http\Request;
use App\Models\UserRequestRating;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Agent\StoreProviderRequest;

class ProviderController extends Controller
{    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');

        $providers = Provider::search($search)
            ->where('agent_id', auth('agent')->user()->id)
            ->latest()
            ->paginate();

        return view('agent.provider.index', compact('providers', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $agents = Agent::where('id', auth('agent')->user()->id)->get();

        return view('agent.provider.create', compact('agents'));
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
        $credentials['agent_id'] = auth('agent')->user()->id;

        if ($request->hasFile('avatar')) {
            $credentials['avatar'] = $request->file('avatar')->store('public/provider/avatars');
        }

        Provider::create($credentials);
        return redirect()
            ->route('agent.provider.index')
            ->with('success', __('crud.general.created'));
    }

    public function ratings(Provider $provider)
    {
        $providers = Provider::where('agent_id', auth('agent')->user()->id)
            ->get()
            ->pluck('id')
            ->toArray();

        $ratings = UserRequestRating::whereIn('provider_id', $providers)
            ->latest()
            ->paginate();
        return view('agent.provider.ratings', compact('ratings'));
    }
}
