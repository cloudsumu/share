<?php

namespace App\Http\Controllers\Api\Provider;

use Setting;
use Exception;
use Carbon\Carbon;
use App\Models\Agent;
use App\Models\Document;
use App\Models\Provider;
use App\Models\UserRequest;
use App\Models\CancelReason;
use Illuminate\Http\Request;
use App\Models\RequestFilter;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\SendPushNotification;
use App\Http\Controllers\Resource\ReferralResource;
use App\Http\Controllers\TransactionResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProfileController extends Controller
{
    /**
     * Create a new user instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('provider.api', ['except' => ['show', 'store', 'available', 'location_edit', 'location_update', 'stripe', 'verifyCredentials']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            auth()->user()->service = ProviderService::where('provider_id', auth()->user()->id)
                                            ->with('service_type')
                                            ->first();
            auth()->user()->fleet       = Agent::find(auth()->user()->agent_id);
            auth()->user()->currency    = config('constants.currency', '$');
            auth()->user()->sos         = config('constants.sos_number', '911');
            auth()->user()->measurement = config('constants.distance', 'Kms');
            auth()->user()->profile     = ProviderProfile::where('provider_id', auth()->user()->id)
                                            ->first();

            $align = '';

            if (auth()->user()->profile != null) {
                app()->setLocale(auth()->user()->profile->language);
                $align = (auth()->user()->profile->language == 'ar') ? 'text-align: right' : '';
            }
            
            auth()->user()->cash =(int)config('constants.cash_payment');
            auth()->user()->online =(int)config('constants.online_payment');
            auth()->user()->card =(int)config('constants.stripe_payment');

            //TODO ALLAN - Alterações débito na máquina e voucher
            auth()->user()->debit_machine =(int)config('constants.debit_machine');
            auth()->user()->voucher       =(int)config('constants.voucher');

            auth()->user()->stripe_secret_key      = config('constants.stripe_secret_key');
            auth()->user()->stripe_publishable_key = config('constants.stripe_publishable_key');
            auth()->user()->stripe_currency        = config('constants.stripe_currency');

            auth()->user()->payumoney       =(int)config('constants.payumoney');
            auth()->user()->paypal          =(int)config('constants.paypal');
            auth()->user()->paypal_adaptive =(int)config('constants.paypal_adaptive');
            auth()->user()->braintree       =(int)config('constants.braintree');
            auth()->user()->paytm           =(int)config('constants.paytm');

            auth()->user()->stripe_secret_key      = config('constants.stripe_secret_key');
            auth()->user()->stripe_publishable_key = config('constants.stripe_publishable_key');
            auth()->user()->stripe_currency        = config('constants.stripe_currency');

            auth()->user()->payumoney_environment = config('constants.payumoney_environment');
            auth()->user()->payumoney_key         = config('constants.payumoney_key');
            auth()->user()->payumoney_salt        = config('constants.payumoney_salt');
            auth()->user()->payumoney_auth        = config('constants.payumoney_auth');

            auth()->user()->paypal_environment   = config('constants.paypal_environment');
            auth()->user()->paypal_currency      = config('constants.paypal_currency');
            auth()->user()->paypal_client_id     = config('constants.paypal_client_id');
            auth()->user()->paypal_client_secret = config('constants.paypal_client_secret');

            auth()->user()->braintree_environment = config('constants.braintree_environment');
            auth()->user()->braintree_merchant_id = config('constants.braintree_merchant_id');
            auth()->user()->braintree_public_key  = config('constants.braintree_public_key');
            auth()->user()->braintree_private_key = config('constants.braintree_private_key');

            auth()->user()->referral_count        = config('constants.referral_count', '0');
            auth()->user()->referral_amount       = config('constants.referral_amount', '0');
            auth()->user()->referral_text         = trans('api.provider.invite_friends');
            auth()->user()->referral_total_count  = (new ReferralResource())->get_referral('provider', auth()->user()->id)[0]->total_count;
            auth()->user()->referral_total_amount = (new ReferralResource())->get_referral('provider', auth()->user()->id)[0]->total_amount;
            auth()->user()->referral_total_text   = "<p style='font-size:16px; color: #000; $align'>" . trans('api.provider.referral_amount') . ': ' . (new ReferralResource())->get_referral('user', auth()->user()->id)[0]->total_amount . '<br>' . trans('api.provider.referral_count') . ': ' . (new ReferralResource())->get_referral('user', auth()->user()->id)[0]->total_count . '</p>';
            auth()->user()->ride_otp              =(int)config('constants.ride_otp');
            auth()->user()->ride_toll              =(int)config('constants.ride_toll');
            auth()->user()->cancel_charge              =(int)config('constants.cancel_charge');
            //(new ReferralResource)->get_referral('provider', auth()->user()->id)
            return auth()->user();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'first_name'        => 'required|max:255',
            'last_name'         => 'required|max:255',
            'avatar'            => 'mimes:jpg,jpeg,bmp,png',
            'language'          => 'max:255',
            'address'           => 'max:255',
            'address_secondary' => 'max:255',
            'city'              => 'max:255',
            'country'           => 'max:255',
            'postal_code'       => 'max:255',
        ]);

        try {
            $Provider = auth()->user();

            if ($request->has('first_name')) {
                $Provider->first_name = $request->first_name;
            }

            if ($request->has('last_name')) {
                $Provider->last_name = $request->last_name;
            }
            if ($request->hasFile('avatar')) {
                Storage::delete($Provider->avatar);
                $Provider->avatar = $request->avatar->store('provider/profile');
            }

            if ($request->has('service_type')) {
                if ($Provider->service) {
                    if ($Provider->service->service_type_id != $request->service_type) {
                        $Provider->status = 'banned';
                    }
                    //$ProviderService = ProviderService::where('provider_id',auth()->user()->id);
                    $Provider->service->service_type_id = $request->service_type;
                    $Provider->service->service_number  = $request->service_number;
                    $Provider->service->service_model   = $request->service_model;
                    $Provider->service->save();
                } else {
                    ProviderService::create([
                        'provider_id'     => $Provider->id,
                        'service_type_id' => $request->service_type,
                        'service_number'  => $request->service_number,
                        'service_model'   => $request->service_model,
                    ]);
                    $Provider->status = 'banned';
                }
            }

            if ($Provider->profile) {
                $Provider->profile->update([
                    'language'          => $request->language ?: $Provider->profile->language,
                    'address'           => $request->address ?: $Provider->profile->address,
                    'address_secondary' => $request->address_secondary ?: $Provider->profile->address_secondary,
                    'city'              => $request->city ?: $Provider->profile->city,
                    'country'           => $request->country ?: $Provider->profile->country,
                    'postal_code'       => $request->postal_code ?: $Provider->profile->postal_code,
                ]);
            } else {
                ProviderProfile::create([
                    'provider_id'       => $Provider->id,
                    'language'          => $request->language,
                    'address'           => $request->address,
                    'address_secondary' => $request->address_secondary,
                    'city'              => $request->city,
                    'country'           => $request->country,
                    'postal_code'       => $request->postal_code,
                ]);
            }

            $Provider->save();

            return back()->with('flash_success', trans('api.user.profile_updated'));
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.provider.provider_not_found')], 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        return view('provider.profile.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'first_name'        => 'required|max:255',
            'last_name'         => 'required|max:255',
            'avatar'            => 'mimes:jpg,jpeg,bmp,png',
            'language'          => 'max:255',
            'address'           => 'max:255',
            'address_secondary' => 'max:255',
            'city'              => 'max:255',
            'country'           => 'max:255',
            'postal_code'       => 'max:255',
        ]);

        try {
            $Provider = auth()->user();

            if ($request->has('first_name')) {
                $Provider->first_name = $request->first_name;
            }

            if ($request->has('last_name')) {
                $Provider->last_name = $request->last_name;
            }

            if ($request->has('mobile') && $request->mobile != null) {
                $Provider->mobile = $request->mobile;
            }

            if ($request->hasFile('avatar')) {
                Storage::delete($Provider->avatar);
                $Provider->avatar = $request->avatar->store('provider/profile');
            }

            if ($Provider->profile) {
                $Provider->profile->update([
                    'language'          => $request->language ?: $Provider->profile->language,
                    'address'           => $request->address ?: $Provider->profile->address,
                    'address_secondary' => $request->address_secondary ?: $Provider->profile->address_secondary,
                    'city'              => $request->city ?: $Provider->profile->city,
                    'country'           => $request->country ?: $Provider->profile->country,
                    'postal_code'       => $request->postal_code ?: $Provider->profile->postal_code,
                ]);
            } else {
                ProviderProfile::create([
                    'provider_id'       => $Provider->id,
                    'language'          => $request->language,
                    'address'           => $request->address,
                    'address_secondary' => $request->address_secondary,
                    'city'              => $request->city,
                    'country'           => $request->country,
                    'postal_code'       => $request->postal_code,
                ]);
            }

            $Provider->save();
            $align = '';
            if (isset($Provider->profile->languag) && $Provider->profile->language != null) {
                app()->setLocale($Provider->profile->language);
            }
            if (isset($Provider->profile->languag)) {
                $align = ($Provider->profile->language == 'ar') ? 'text-align: right' : '';
            }

            $Provider->service = ProviderService::where('provider_id', $Provider->id)
                                            ->with('service_type')
                                            ->first();

            $Provider->referral_count        = config('constants.referral_count', '0');
            $Provider->referral_amount       = config('constants.referral_amount', '0');
            $Provider->referral_text         = trans('api.provider.invite_friends');
            $Provider->referral_total_count  = (new ReferralResource())->get_referral('provider', auth()->user()->id)[0]->total_count;
            $Provider->referral_total_amount = (new ReferralResource())->get_referral('provider', auth()->user()->id)[0]->total_amount;
            $Provider->referral_total_text   = "<p style='font-size:16px; color: #000; $align'>" . trans('api.provider.referral_amount') . ': ' . (new ReferralResource())->get_referral('user', auth()->user()->id)[0]->total_amount . '<br>' . trans('api.provider.referral_count') . ': ' . (new ReferralResource())->get_referral('user', auth()->user()->id)[0]->total_count . '</p>';

            return $Provider;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.provider.provider_not_found')], 404);
        }
    }

    public function update_details(Request $request)
    {
        if ($request->has('callid')) {
            if (is_null(auth()->user()->callid) || empty(auth()->user()->callid)) {
                $user         = Provider::find(auth()->user()->id);
                $user->callid = $request->get('callid');
                $user->save();
            } elseif (auth()->user()->callid != $request->get('callid')) {
                $user         = Provider::find(auth()->user()->id);
                $user->callid = $request->get('callid');
                $user->save();
            }
        }
    }

    /**
     * Update latitude and longitude of the user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function location(Request $request)
    {
        $this->validate($request, [
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($Provider = auth()->user()) {
            $Provider->latitude  = $request->latitude;
            $Provider->longitude = $request->longitude;
            $Provider->save();

            return response()->json(['message' => trans('api.provider.location_updated')]);
        } else {
            return response()->json(['error' => trans('api.provider.provider_not_found')]);
        }
    }

    public function update_language(Request $request)
    {
        $this->validate($request, [
            'language' => 'required',
        ]);

        try {
            $Provider = auth()->user();

            if ($Provider->profile) {
                $Provider->profile->update([
                    'language' => $request->language ?: $Provider->profile->language,
                ]);
            } else {
                ProviderProfile::create([
                    'provider_id' => $Provider->id,
                    'language'    => $request->language,
                ]);
            }

            return response()->json(['message' => trans('api.provider.language_updated'), 'language'=>$request->language]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.provider.provider_not_found')], 404);
        }
    }

    /**
     * Toggle service availability of the provider.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function available(Request $request)
    {
        $this->validate($request, [
            'service_status' => 'required|in:active,offline',
        ]);

        $Provider = Provider::where('id', auth()->user()->id)->with('service')->first();
        if ($Provider->service) {
            $provider           = $Provider->id;
            $OfflineOpenRequest = RequestFilter::with(['request.provider', 'request'])
            ->where('provider_id', $provider)
            ->whereHas('request', function ($query) use ($provider) {
                $query->where('status', 'SEARCHING');
                $query->where('current_provider_id', '<>', $provider);
                $query->orWhereNull('current_provider_id');
            })->pluck('id');

            if (count($OfflineOpenRequest) > 0) {
                RequestFilter::whereIn('id', $OfflineOpenRequest)->delete();
            }
            if ($Provider->status == 'approved') {
                foreach ($Provider->service as $post) {
                    //\Log::alert("on status to change ".$post);
                    $post->update(['status' => $request->service_status]);
                    //\Log::alert("on status change ".$post);
                }
            }
            //\Log::alert("final ".$Provider);
            //$Provider->service->update(['status' => $request->service_status]);
        } else {
            return response()->json(['error' => trans('api.provider.not_approved')]);
        }

        return $Provider;
    }

    /**
     * Update password of the provider.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function password(Request $request)
    {
        $this->validate($request, [
            'password'     => 'required|confirmed',
            'password_old' => 'required',
        ]);

        $Provider = auth()->user();

        if (password_verify($request->password_old, $Provider->password)) {
            $Provider->password = bcrypt($request->password);
            $Provider->save();

            return response()->json(['message' => trans('api.provider.password_updated')]);
        } else {
            return response()->json(['error' => trans('api.provider.change_password')], 422);
        }
    }

    /**
     * Show providers daily target.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function target(Request $request)
    {
        try {
            $Rides = UserRequest::where('provider_id', auth()->user()->id)
                    ->where('status', 'COMPLETED')
                    ->where('created_at', '>=', Carbon::today())
                    ->with('payment', 'service_type')
                    ->get();

            //\Log::info($Rides);

            return response()->json([
                'rides'       => $Rides,
                'rides_count' => $Rides->count(),
                'target'      => config('constants.payment_daily_target', '0'),
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    public function chatPush(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|numeric',
            'message' => 'required',
        ]);

        try {
            $user_id=$request->user_id;
            $message=$request->message;

            (new SendPushNotification())->sendPushToUserChat($user_id, $message);
            //(new SendPushNotification)->sendPushToProvider($user_id, $message);

            return response()->json(['success' => 'true']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    //provider document list
    public function documents(Request $request)
    {
        try {
            $provider_id=auth()->user()->id;

            $Documents=Document::select('id', 'name', 'type')
                        ->with(['providerdocuments' => function ($query) use ($provider_id) {
                            $query->where('provider_id', $provider_id);
                        }])->get();

            return response()->json(['documents' => $Documents]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    //provider document list
    public function documentstore(Request $request)
    {
        $this->validate($request, [
            'document'   => 'required',
            'document.*' => 'mimes:jpg,jpeg,png|max:2048',
        ]);
        try {
            //\Log::info($request->all());

            if ($request->hasFile('document')) {
                foreach ($request->file('document') as $ikey=> $image) {
                    $ids        =$request->input('id');
                    $doc_id     =$ids[$ikey];
                    $provider_id=auth()->user()->id;
                    (new DocumentController())->documentupdate($image, $doc_id, $provider_id);
                }

                if (config('constants.card', 0) == 1) {
                    Provider::where('id', auth()->user()->id)->where('status', 'document')->update(['status'=>'onboarding']);
                } else {
                    if (config('constants.demo_mode', 0) == 1) {
                        Provider::where('id', auth()->user()->id)->where('status', 'document')->update(['status'=>'approved']);
                    } else {
                        Provider::where('id', auth()->user()->id)->where('status', 'document')->update(['status'=>'onboarding']);
                    }
                }

                return $this->documents($request);
            }
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 422);
        }
    }

    public function stripe(Request $request)
    {
        if (isset($request->code)) {
            $post = [
                'client_secret' => config('constants.stripe_secret_key'),
                'code'          => $request->code,
                'grant_type'    => 'authorization_code',
            ];
            $curl = curl_init('https://connect.stripe.com/oauth/token');
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result     = curl_exec($curl);
            $curl_error = curl_error($curl);
            curl_close($curl);
            $stripe = json_decode($result);

            if ($stripe->stripe_user_id) {
                $provider                = Provider::where('id', auth()->user()->id)->first();
                $provider->stripe_acc_id = $stripe->stripe_user_id;
                $provider->save();

                if ($request->ajax()) {
                    return response()->json(['message' => 'Your stripe account connected successfully']);
                } else {
                    return redirect('/provider')->with('flash_success', 'Your stripe account connected successfully');
                }
            } else {
                if ($request->ajax()) {
                    return response()->json(['message' => $curl_error]);
                } else {
                    return redirect('/provider')->with('flash_error', $curl_error);
                }
            }
        } else {
            if ($request->ajax()) {
                return response()->json(['message' => $request->error_description]);
            } else {
                return redirect('/provider')->with('flash_error', $request->error_description);
            }
        }
    }

    public function reasons(Request $request)
    {
        $reason = CancelReason::where('for', 'provider')->where('status', 1)->get();

        return $reason;
    }

    public function verifyCredentials(Request $request)
    {
        if ($request->has('mobile')) {
            $Provider = Provider::where([['country_code', $request->input('country_code')], ['mobile', $request->input('mobile')]])
                                  ->first();
            if ($Provider != null) {
                return response()->json(['message' => trans('api.mobile_exist')], 422);
            }
        }

        if ($request->has('email')) {
            $Provider = Provider::where('email', $request->input('email'))->first();
            if ($Provider != null) {
                return response()->json(['message' => trans('api.email_exist')], 422);
            }
        }

        return response()->json(['message' => trans('api.available')]);
    }

    public function addMoneyRazPro(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required',
        ]);

        $User   = auth()->user();
        $wallet = (new TransactionResource())->providerCreditDebit($request->amount, $User->id, 1);
        (new SendPushNotification())->WalletMoney($User->id, currency($request->amount));
        auth()->user()->fresh();
        return response()->json(['message' => currency($request->amount) . trans('api.added_to_your_wallet'), 'user' => auth()->user(), 'wallet_balance' => auth()->user()->wallet_balance]);
    }
}
