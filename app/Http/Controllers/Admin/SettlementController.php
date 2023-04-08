<?php

namespace App\Http\Controllers\Admin;

use stdClass;
use App\Models\User;
use App\Models\Agent;
use App\Models\Provider;
use App\Models\AdminWallet;
use Illuminate\Http\Request;
use App\Models\WalletRequest;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Provider\TripController;

class SettlementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view settlements');

        $search = $request->get('search', '');
        $settlements = WalletRequest::search($search)
            ->latest()
            ->paginate();

        return view('admin.payment.providerSettlement', compact('settlements'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WalletRequest  $walletRequest
     * @return \Illuminate\Http\Response
     */
    public function show(WalletRequest $walletRequest)
    {
        abort(403);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WalletRequest  $walletRequest
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create settlements');

        return view('admin.payment.create');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WalletRequest  $walletRequest
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate Credentials
        $credentials = $request->validate([
            'request_from' => [
                'required',
                Rule::in(['Provider', 'Agent', 'User']),
            ],
            'from_id' => 'required',
            'type' => [
                'required',
                Rule::in(['C', 'D'])
            ],
            'amount' => 'required|numeric',
            'send_by' => [
                'required',
                Rule::in(['Online', 'Offline'])
            ],
        ]);

        if($credentials['request_from'] == 'Provider') {
            // Valid if the provider is a valid provider
            $request->validate([
                'from_id' => 'exists:providers,id'
            ]);
            $user = User::where('id', $request->get('from_id'))->first();
        }
        else if($credentials['request_from'] == 'User') {
            // Valid if the provider is a valid user
            $request->validate([
                'from_id' => 'exists:users,id'
            ]);
            $user = Provider::where('id', $request->get('from_id'))->first();
        }
        else if($credentials['request_from'] == 'Agent') {
            // Valid if the provider is a valid agent
            $request->validate([
                'from_id' => 'exists:agents,id'
            ]);
            $user = Agent::where('id', $request->get('from_id'))->first();
        }

        // Update the wallet balance of the appropriate user
        // $updateWallet['wallet_balance'] = $credentials['type'] == 'D' ? $user->wallet_balance - $credentials['amount'] : $user->wallet_balance + $credentials['amount'];
        // $user->update($updateWallet);

        $credentials['alias_id'] = generate_request_id($request->type);
        $credentials['status'] = '1';

        $credentials['request_from'] = strtolower($credentials['request_from']);

        $wallet_request = WalletRequest::create($credentials);
        (new TripController())->settlements($wallet_request->id);
        
        return redirect()
            ->route('admin.settlement.index')
            ->with('success', __('crud.admin.settlements.name')." ".__('crud.general.updated'));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WalletRequest  $walletRequest
     * @return \Illuminate\Http\Response
     */
    public function destroy(WalletRequest $walletRequest)
    {
        //
    }
}
