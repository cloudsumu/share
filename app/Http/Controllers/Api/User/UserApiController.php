<?php

namespace App\Http\Controllers\Api\User;

use Exception;
use Notification;
use App\Models\User;
use App\Models\Banner;
use App\Models\Provider;
use App\Models\Promocode;
use App\Models\GeoFencing;
use App\Models\PaymentLog;
use App\Models\UserWallet;
use App\Models\ServiceType;
use App\Models\UserRequest;
use App\Models\CancelReason;
use Illuminate\Http\Request;
use App\Models\PromocodeUsage;
use App\Services\ServiceTypes;
use App\Models\ProviderService;
use App\Models\PromocodePassbook;
use App\Services\LocationService;
use App\Models\UserRequestDispute;
use App\Models\UserRequestPayment;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\UserRequestLostItems;
use Illuminate\Support\Facades\Hash;
use App\Notifications\ResetPasswordOTP;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\TransactionResource;
use App\Http\Controllers\SendPushNotification;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Resource\ReferralResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('loc');
    }

    /**
     * Verifica a disponiblidade do Email e Telefone para o usuário
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|unique:users',
        ]);
        if ($request->email == '') {
            return response()->json(['message' => 'Please enter an email'], 422);
        }

        $email_case = User::where('email', $request->email)->first();
        //User Already Exists
        if ($email_case) {
            return response()->json(['message' => 'Email already registered, please type another email'], 422);
        }

        try {
            return response()->json(['message' => trans('api.email_available')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function checkUserEmail(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        try {
            $email = $request->email;

            $results = User::where('email', $email)->first();

            if (empty($results)) {
                return response()->json(['message' => trans('api.email_available'), 'is_available' => true]);
            } else {
                return response()->json(['message' => trans('api.email_not_available'), 'is_available' => false]);
            }
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function banners()
    {
        if ($serviceList = Banner::all()) {
            //$addon = Addon::all();
            //Log::alert('message'.response()->json(array(
            //    'serviceList'=> $serviceList,'category'=> $category,'addon'=> $addon,)));
            //return response()->json(array(['serviceList'=> $serviceList,'category'=> $category,'addon'=> $addon]));
            return $serviceList;
        } else {
            return response()->json(['error' => trans('api.services_not_found')], 500);
        }
    }

    /**
     * Realiza autenticação do usuário
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'device_id'       => 'required',
            'device_type'     => 'required|in:android,ios',
            'device_token'    => 'required',
            'username'        => 'required',
            'password'        => 'required|min:8',
        ]);

        $email = $request->username;

        $user = User::where('email', $email)->orWhere('mobile', $email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        //try {

        $user->device_id    = $request->device_id;
        $user->device_type  = $request->device_type;
        $user->device_token = $request->device_token;
        $user->save();
        $user->currency     = config('constants.currency');
        $user->sos          = config('constants.sos_number', '911');
        $user->measurement  = config('constants.distance', 'Kms');
        $user->tokens()->delete();
        $user['access_token'] = $user->createToken('web')->plainTextToken;
        return response()->json($user, Response::HTTP_OK);
        //} catch (Exception $e) {
        //    return response()->json(['error' => trans('api.something_went_wrong')], Response::HTTP_INTERNAL_SERVER_ERROR);
        //}
    }

    /**
     * Realiza cadastro do usuário no sistema.
     *
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function signup(Request $request)
    {
        if ($request->referral_code != null) {
            $validate['referral_unique_id'] = $request->referral_code;
            $validator                      = (new ReferralResource())->checkReferralCode($validate);
            if (!$validator->fails()) {
                $validator->errors()->add('referral_code', 'Invalid Referral Code');
                throw new \Illuminate\Validation\ValidationException($validator);
            }
        }

        $referral_unique_id = (new ReferralResource())->generateCode();

        $this->validate($request, [
            'social_unique_id' => ['required_Tokeif:login_by,facebook,google', 'unique:users'],
            'device_type'      => 'required|in:android,ios',
            'device_token'     => 'required',
            'device_id'        => 'required',
            'login_by'         => 'required|in:manual,facebook,google',
            'first_name'       => 'required|max:255',
            'last_name'        => 'required|max:255',
            'email'            => 'required|email|max:255',
            'country_code'     => 'required',
            'mobile'           => 'required',
            'password'         => 'required|min:8',
        ]);

        $currentUser = null;

        $email_case = User::where('email', $request->email)->where([['country_code', $request->country_code], ['mobile', $request->mobile]])->first();

        $registeredEmail  = User::where('email', $request->email)->where('user_type', 'INSTANT')->first();
        $registeredMobile = User::where([['country_code', $request->country_code], ['mobile', $request->mobile]])->where('user_type', 'INSTANT')->first();

        $registeredEmailNormal  = User::where('email', $request->email)->where('user_type', 'NORMAL')->first();
        $registeredMobileNormal = User::where([['country_code', $request->country_code], ['mobile', $request->mobile]])->where('user_type', 'NORMAL')->first();

        //User Already Exists
        if ($email_case != null) {
            return response()->json(['Email is already registered!'], 422);
        }

        if ($registeredEmail != null && $registeredMobile != null) {
            //User Already Registerd with same credentials
            if ($registeredEmail != null) {
                return response()->json(['User already registered with this email!'], 422);
            } elseif ($registeredMobile != null) {
                return response()->json(['User already registered by this phone number!'], 422);
            }
        } else {
            if ($registeredEmail != null) {
                $currentUser = $registeredEmail;
            } elseif ($registeredMobile != null) {
                $currentUser = $registeredMobile;
            }
        }

        if ($registeredEmailNormal != null) {
            return response()->json(['User already registered with a given email!'], 422);
        } elseif ($registeredMobileNormal != null) {
            return response()->json(['User already registered with a given mobile number!'], 422);
        }
        if ($currentUser == null) {
            $User = [];//$request->all();
            $User['first_name']         = $request->first_name;
            $User['last_name']          = $request->last_name;
            $User['email']              = $request->email;
            $User['country_code']       = $request->country_code;
            $User['mobile']             = $request->mobile;
            $User['login_by']           = 'manual';
            $User['user_type']          = 'NORMAL';

            if ($request->has('gender')) {
                if ($request->gender == 'Male') {
                    $User['gender'] = 'MALE';
                } else {
                    $User['gender'] = 'FEMALE';
                }
            }

            $User['payment_mode']       = 'CASH';
            $User['password']           = bcrypt($request->password);
            $User['referral_unique_id'] = $referral_unique_id;
            $User                       = User::create($User);

            //$User                 = auth()->loginUsingId($User->id);
            //$UserToken            = $User->createToken('AutoLogin');
            //$User['access_token'] = $UserToken->accessToken;

            $User['currency']     = config('constants.currency');
            $User['sos']          = config('constants.sos_number', '911');
            $User['app_contact']  = config('constants.app_contact', '5777');
            $User['measurement']  = config('constants.distance', 'Kms');
        } else {
            $User                     = $currentUser;
            $User->first_name         = $request->first_name;
            $User->last_name          = $request->last_name;
            $User->cpf                = ($request->cpf ? $request->cpf : null);
            $User->email              = $request->email;
            $User->country_code       = $request->country_code;
            $User->mobile             = $request->mobile;
            $User->password           = bcrypt($request->password);
            $User->login_by           = 'manual';
            $User->payment_mode       = 'CASH';
            $User->user_type          = 'NORMAL';
            $User->referral_unique_id = $referral_unique_id;
            $User->save();
        }

        // Envia email de boas vindas para o usuário
        if (config('constants.send_email', 0) == 1) {
            //site_registermail($User);
        }
        //check user referrals
        if ((int)config('constants.referral', 0) == 1) {
            if ($request->referral_code != null) {
                //call referral function
                (new ReferralResource())->create_referral($request->referral_code, $User);
            }
        }
        $User->currency                 = config('constants.currency');
        $User->sos                      = config('constants.sos_number', '911');
        $User->measurement              = config('constants.distance', 'Kms');

        $User['access_token'] = $User->createToken('web')->plainTextToken;
        return $User;
    }

    /**
     * Efetua logout da aplicação
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        try {
            User::where('id', $request->id)->update(['device_id' => '', 'device_token' => '']);
            \Auth::logout();
            return response()->json(['message' => trans('api.logout_success')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    /**
     * Altera senha do usuário
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function change_password(Request $request)
    {
        $this->validate($request, [
            'password'     => 'required|confirmed|min:8',
            'old_password' => 'required',
        ]);

        $User = auth()->user();

        if (Hash::check($request->old_password, $User->password)) {
            $User->password = bcrypt($request->password);
            $User->save();

            if ($request->ajax()) {
                return response()->json(['message' => trans('api.user.password_updated')]);
            } else {
                return back()->with('flash_success', trans('api.user.password_updated'));
            }
        } else {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.user.incorrect_old_password')], 422);
            } else {
                return back()->with('flash_error', trans('api.user.incorrect_old_password'));
            }
        }
    }

    /**
     * Atualiza informações de localização
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update_location(Request $request)
    {
        $this->validate($request, [
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($user = User::find(auth()->user()->id)) {
            $user->latitude  = $request->latitude;
            $user->longitude = $request->longitude;
            $user->save();

            return response()->json(['message' => trans('api.user.location_updated')]);
        } else {
            return response()->json(['error' => trans('api.user.user_not_found')], 422);
        }
    }

    /**
     * Atualiza informações de idioma
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update_language(Request $request)
    {
        $this->validate($request, [
            'language' => 'required',
        ]);

        if ($user = User::find(auth()->user()->id)) {
            $user->language = $request->language;
            $user->save();

            return response()->json(['message' => trans('api.user.language_updated'), 'language' => $request->language]);
        } else {
            return response()->json(['error' => trans('api.user.user_not_found')], 422);
        }
    }

    /**
     * Disponibiliza informações de detalhes do usuário
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function details(Request $request)
    {
        $this->validate($request, [
            'device_type' => 'in:android,ios',
        ]);

        try {
            if ($user = User::find(auth()->user()->id)) {
                if ($request->has('device_token')) {
                    $user->device_token = $request->device_token;
                }

                if ($request->has('device_type')) {
                    $user->device_type = $request->device_type;
                }

                if ($request->has('device_id')) {
                    $user->device_id = $request->device_id;
                }

                $user->save();

                if ($user->language != null) {
                    app()->setLocale($user->language);
                }

                $align = ($user->language == 'ar') ? 'text-align: right' : '';

                $user->currency    = config('constants.currency');
                $user->sos         = config('constants.sos_number', '911');
                $user->app_contact = config('constants.app_contact', '5777');
                $user->measurement = config('constants.distance', 'Kms');

                $user->cash = (int)config('constants.cash_payment');

                //TODO ALLAN - Alterações débito na máquina e voucher
                $user->debit_machine = (int)config('constants.debit_machine');
                $user->voucher       = (int)config('constants.voucher');

                $user->card            = (int)config('constants.stripe_payment');
                $user->payumoney       = (int)config('constants.payumoney');
                $user->paypal          = (int)config('constants.paypal');
                $user->paypal_adaptive = (int)config('constants.paypal_adaptive');
                $user->braintree       = (int)config('constants.braintree');
                $user->paytm           = (int)config('constants.paytm');
                $user->online          = (int)config('constants.online_payment');
                
                $user->stripe_secret_key      = config('constants.stripe_secret_key');
                $user->stripe_publishable_key = config('constants.stripe_publishable_key');
                $user->stripe_currency        = config('constants.stripe_currency');

                $user->payumoney_environment = config('constants.payumoney_environment');
                $user->payumoney_key         = config('constants.payumoney_key');
                $user->payumoney_salt        = config('constants.payumoney_salt');
                $user->payumoney_auth        = config('constants.payumoney_auth');

                $user->paypal_environment   = config('constants.paypal_environment');
                $user->paypal_currency      = config('constants.paypal_currency');
                $user->paypal_client_id     = config('constants.paypal_client_id');
                $user->paypal_client_secret = config('constants.paypal_client_secret');

                $user->braintree_environment = config('constants.braintree_environment');
                $user->braintree_merchant_id = config('constants.braintree_merchant_id');
                $user->braintree_public_key  = config('constants.braintree_public_key');
                $user->braintree_private_key = config('constants.braintree_private_key');

                $user->referral_count        = config('constants.referral_count', '0');
                $user->referral_amount       = config('constants.referral_amount', '0');
                $user->referral_text         = "<p style='font-size:16px; color: #999; $align'>" . trans('api.user.referral_amount') . ': ' . (new ReferralResource())->get_referral('user', auth()->user()->id)[0]->total_amount . '<br>' . trans('api.user.referral_count') . ': ' . (new ReferralResource())->get_referral('user', auth()->user()->id)[0]->total_count . '</p>';
                $user->referral_total_count  = (new ReferralResource())->get_referral('user', auth()->user()->id)[0]->total_count;
                $user->referral_total_amount = (new ReferralResource())->get_referral('user', auth()->user()->id)[0]->total_amount;
                $user->referral_total_text   = "<p style='font-size:16px; color: #999; $align'>" . trans('api.user.referral_amount') . ': ' . (new ReferralResource())->get_referral('user', auth()->user()->id)[0]->total_amount . '<br>' . trans('api.user.referral_count') . ': ' . (new ReferralResource())->get_referral('user', auth()->user()->id)[0]->total_count . '</p>';
                $user->ride_otp              = (int)config('constants.ride_otp');
                $user->ride_toll             = (int)config('constants.ride_toll');
                $user->cancel_charge             = (int)config('constants.cancel_charge');
                return $user;
            } else {
                return response()->json(['error' => trans('api.user.user_not_found')], 422);
            }
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    /**
     * Atualiza informações de ligação
     *
     * @param Request $request
     */
    public function update_details(Request $request)
    {
        if ($request->has('callid')) {
            if (is_null(auth()->user()->callid) || empty(auth()->user()->callid)) {
                $user         = User::find(auth()->user()->id);
                $user->callid = $request->get('callid');
                $user->save();
            } elseif (auth()->user()->callid != $request->get('callid')) {
                $user         = User::find(auth()->user()->id);
                $user->callid = $request->get('callid');
                $user->save();
            }
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function update_profile(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required|max:255',
            'last_name'  => 'max:255',
            'cpf'        => 'max:255',
            'email'      => 'email|unique:users,email,' . auth()->user()->id,
            'picture'    => 'mimes:jpg,jpeg,bmp,png',
        ]);

        try {
            $user = User::findOrFail(auth()->user()->id);

            if ($request->has('first_name')) {
                $user->first_name = $request->first_name;
            }

            if ($request->has('last_name')) {
                $user->last_name = $request->last_name;
            }

            if ($request->has('country_code')) {
                $user->country_code = $request->country_code;
            }
            if ($request->has('gender')) {
                $user->gender = $request->gender;
            }

            if ($request->has('gender')) {
                $user->gender = $request->gender;
            }

            if ($request->has('language')) {
                $user->language = $request->language;
            }

            if ($request->picture != '') {
                Storage::delete($user->picture);
                $user->avatar = $request->picture->store('user/profile');
            }

            $user->save();

            $user->currency    = config('constants.currency');
            $user->sos         = config('constants.sos_number', '911');
            $user->app_contact = config('constants.app_contact', '5777');
            $user->measurement = config('constants.distance', 'Kms');

            if ($user->language != null) {
                app()->setLocale($user->language);
            }

            $align = ($user->language == 'ar') ? 'text-align: right' : '';

            $user->referral_count        = config('constants.referral_count', '0');
            $user->referral_amount       = config('constants.referral_amount', '0');
            $user->referral_text         = trans('api.user.invite_friends');
            $user->referral_total_count  = (new ReferralResource())->get_referral('user', auth()->user()->id)[0]->total_count;
            $user->referral_total_amount = (new ReferralResource())->get_referral('user', auth()->user()->id)[0]->total_amount;
            $user->referral_total_text   = "<p style='font-size:16px; color: #000; $align'>" . trans('api.user.referral_amount') . ': ' . (new ReferralResource())->get_referral('user', auth()->user()->id)[0]->total_amount . '<br>' . trans('api.user.referral_count') . ': ' . (new ReferralResource())->get_referral('user', auth()->user()->id)[0]->total_count . '</p>';

            if ($request->ajax()) {
                return response()->json($user);
            } else {
                return back()->with('flash_success', trans('api.user.profile_updated'));
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.user.user_not_found')], 422);
        }
    }

    /**
     * get all promo code.
     *
     * @return \Illuminate\Http\Response
     */
    public function promocodes()
    {
        try {
            //$this->check_expiry();

            return PromocodeUsage::Active()
                ->where('user_id', auth()->user()->id)
                ->with('promocode')
                ->get();
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    /**
     * add promo code.
     *
     * @return \Illuminate\Http\Response
     */
    public function list_promocode(Request $request)
    {
        try {
            //$locationService = new LocationService();
            //$idCity          = $locationService->getByLatLong(Auth()->user()->latitude, Auth()->user()->longitude);
            // dd(Auth()->user());
            $promo_list = Promocode::where('expiration', '>=', date('Y-m-d H:i'))
                ->whereDoesntHave('promousage', function ($query) {
                    $query->where('user_id', auth()->user()->id);
                })
                ->get();
            // dd($promo_list);
            if ($request->ajax()) {
                return response()->json([
                    'promo_list' => $promo_list,
                ]);
            } else {
                return $promo_list;
            }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong') . $e], 500);
            } else {
                return back()->with('flash_error', trans('api.something_went_wrong') . $e);
            }
        }
    }

    public function add_promocode(Request $request)
    {
        $this->validate($request, [
            'promocode' => 'required|exists:promocodes,promo_code',
        ]);

        try {
            $find_promo = Promocode::where('promo_code', $request->promocode)->first();

            if ($find_promo->status == 'EXPIRED' || (date('Y-m-d') > $find_promo->expiration)) {
                if ($request->ajax()) {
                    return response()->json([
                        'message' => trans('api.promocode_expired'),
                        'code'    => 'promocode_expired',
                    ]);
                } else {
                    return back()->with('flash_error', trans('api.promocode_expired'));
                }
            } elseif (PromocodeUsage::where('promocode_id', $find_promo->id)->where('user_id', auth()->user()->id)->whereIN('status', ['ADDED', 'USED'])->count() > 0) {
                if ($request->ajax()) {
                    return response()->json([
                        'message' => trans('api.promocode_already_in_use'),
                        'code'    => 'promocode_already_in_use',
                    ]);
                } else {
                    return back()->with('flash_error', trans('api.promocode_already_in_use'));
                }
            } else {
                $promo               = new PromocodeUsage();
                $promo->promocode_id = $find_promo->id;
                $promo->user_id      = auth()->user()->id;
                $promo->status       = 'ADDED';
                $promo->save();

                $count_id = PromocodePassbook::where('promocode_id', $find_promo->id)->count();
                //dd($count_id);
                if ($count_id == 0) {
                    PromocodePassbook::create([
                        'user_id'      => auth()->user()->id,
                        'status'       => 'ADDED',
                        'promocode_id' => $find_promo->id,
                    ]);
                }
                if ($request->ajax()) {
                    return response()->json([
                        'message' => trans('api.promocode_applied'),
                        'code'    => 'promocode_applied',
                    ]);
                } else {
                    return back()->with('flash_success', trans('api.promocode_applied'));
                }
            }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            } else {
                return back()->with('flash_error', trans('api.something_went_wrong'));
            }
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function trips()
    {
        try {
            $UserRequests = UserRequest::UserTrips(auth()->user()->id)->get();
            if (!empty($UserRequests)) {
                $map_icon = asset('asset/img/marker-start.png');
                foreach ($UserRequests as $key => $value) {
                    $UserRequests[$key]->static_map = 'https://maps.googleapis.com/maps/api/staticmap?' .
                        'autoscale=10' .
                        '&size=320x130' .
                        '&maptype=terrian' .
                        '&format=png' .
                        '&visual_refresh=true' .
                        '&markers=icon:' . $map_icon . '%7C' . $value->s_latitude . ',' . $value->s_longitude .
                        '&markers=icon:' . $map_icon . '%7C' . $value->d_latitude . ',' . $value->d_longitude .
                        '&path=color:0x191919|zoom=13|weight:3|enc:' . $value->route_key .
                        '&key=' . config('constants.server_map_key');
                }
            }
            return $UserRequests;
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function upcoming_trips()
    {
        try {
            $UserRequests = UserRequest::UserUpcomingTrips(auth()->user()->id)->get();
            if (!empty($UserRequests)) {
                $map_icon = asset('asset/img/marker-start.png');
                foreach ($UserRequests as $key => $value) {
                    $UserRequests[$key]->static_map = 'https://maps.googleapis.com/maps/api/staticmap?' .
                        'autoscale=10' .
                        '&size=320x130' .
                        '&maptype=terrian' .
                        '&format=png' .
                        '&visual_refresh=true' .
                        '&markers=icon:' . $map_icon . '%7C' . $value->s_latitude . ',' . $value->s_longitude .
                        '&markers=icon:' . $map_icon . '%7C' . $value->d_latitude . ',' . $value->d_longitude .
                        '&path=color:0x000000|zoom=13|weight:3|enc:' . $value->route_key .
                        '&key=' . config('constants.server_map_key');
                }
            }
            return $UserRequests;
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function upcoming_trip_details(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|integer|exists:user_requests,id',
        ]);

        try {
            $UserRequests = UserRequest::UserUpcomingTripDetails(auth()->user()->id, $request->request_id)->get();
            if (!empty($UserRequests)) {
                $map_icon = asset('asset/img/marker-start.png');
                foreach ($UserRequests as $key => $value) {
                    $UserRequests[$key]->static_map = 'https://maps.googleapis.com/maps/api/staticmap?' .
                        'autoscale=10' .
                        '&size=320x130' .
                        '&maptype=terrian' .
                        '&format=png' .
                        '&visual_refresh=true' .
                        '&markers=icon:' . $map_icon . '%7C' . $value->s_latitude . ',' . $value->s_longitude .
                        '&markers=icon:' . $map_icon . '%7C' . $value->d_latitude . ',' . $value->d_longitude .
                        '&path=color:0x000000|zoom=13|weight:3|enc:' . $value->route_key .
                        '&key=' . config('constants.server_map_key');
                }
            }
            return $UserRequests;
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function trip_details(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|integer|exists:user_requests,id',
        ]);

        try {
            $UserRequests = UserRequest::UserTripDetails(auth()->user()->id, $request->request_id)->get();

            if (!empty($UserRequests)) {
                $map_icon = asset('asset/img/marker-start.png');
                foreach ($UserRequests as $key => $value) {
                    $UserRequests[$key]->static_map = 'https://maps.googleapis.com/maps/api/staticmap?' .
                        'autoscale=10' .
                        '&size=320x130' .
                        '&maptype=terrian' .
                        '&format=png' .
                        '&visual_refresh=true' .
                        '&markers=icon:' . $map_icon . '%7C' . $value->s_latitude . ',' . $value->s_longitude .
                        '&markers=icon:' . $map_icon . '%7C' . $value->d_latitude . ',' . $value->d_longitude .
                        '&path=color:0x191919|zoom=13|weight:3|enc:' . $value->route_key .
                        '&key=' . config('constants.server_map_key');
                }

                $UserRequests[0]->dispute = UserRequestDispute::where('dispute_type', 'user')->where('request_id', $request->request_id)->where('user_id', auth()->user()->id)->first();

                $UserRequests[0]->lostitem = UserRequestLostItems::where('request_id', $request->request_id)->where('user_id', auth()->user()->id)->first();

                $UserRequests[0]->contact_number = config('constants.contact_number', '');
                $UserRequests[0]->contact_email  = config('constants.contact_email', '');
            }
            return $UserRequests;
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    /**
     * Show the nearby providers.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_providers(Request $request)
    {
        $this->validate($request, [
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'service'   => 'numeric|exists:service_types,id',
        ]);

        try {
            $distance  = config('constants.provider_search_radius', '10');
            $latitude  = $request->latitude;
            $longitude = $request->longitude;

            if ($request->has('service')) {
                $ActiveProviders = ProviderService::AvailableServiceProvider($request->service)
                                        ->get()->pluck('provider_id');

                $Providers = Provider::with('service')->whereIn('id', $ActiveProviders)
                        ->where('status', 'approved')
                        ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                        ->get();
            } else {
                $ActiveProviders = ProviderService::where('status', 'active')
                                        ->get()->pluck('provider_id');

                $Providers = Provider::with('service')->whereIn('id', $ActiveProviders)
                        ->where('status', 'approved')
                        ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                        ->get();
            }

            return $Providers;
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            } else {
                return back()->with('flash_error', 'Something went wrong while sending request. Please try again.');
            }
        }
    }

    /**
     * Forgot Password.
     *
     * @return \Illuminate\Http\Response
     */
    public function forgot_password(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $user = User::where('email', $request->email)->first();
            $otp  = mt_rand(100000, 999999);

            $user->otp = $otp;
            $user->save();

            Notification::send($user, new ResetPasswordOTP($otp));

            return response()->json([
                'message' => 'OTP Send to your mail ID',
                'user'    => $user,
            ]);
        } catch (Exception $e) {
            //Log::info($e->getMessage());
            return response()->json(['error' => trans('api.something_went_wrong') . $e], 500);
        }
    }

    /**
     * Reset Password.
     *
     * @return \Illuminate\Http\Response
     */
    public function reset_password(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|confirmed|min:8',
            'id'       => 'required|numeric|exists:users,id',
        ]);

        try {
            $User = User::findOrFail($request->id);
            // $UpdatedAt = date_create($User->updated_at);
            // $CurrentAt = date_create(date('Y-m-d H:i:s'));
            // $ExpiredAt = date_diff($UpdatedAt,$CurrentAt);
            // $ExpiredMin = $ExpiredAt->i;
            $User->password = bcrypt($request->password);
            $User->save();
            if ($request->ajax()) {
                return response()->json(['message' => trans('api.user.password_updated')]);
            }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            }
        }
    }

    /**
     * help Details.
     *
     * @return \Illuminate\Http\Response
     */
    public function help_details(Request $request)
    {
        try {
            if ($request->ajax()) {
                return response()->json([
                    'contact_number' => config('constants.contact_number', ''),
                    'contact_email'  => config('constants.contact_email', ''),
                ]);
            }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            }
        }
    }

    /**
     * Show the wallet usage.
     *
     * @return \Illuminate\Http\Response
     */
    public function wallet_passbook(Request $request)
    {
        try {
            $start_node = $request->start_node;
            $limit      = $request->limit;

            $wallet_transation = UserWallet::where('user_id', auth()->user()->id);
            if (!empty($limit)) {
                $wallet_transation = $wallet_transation->offset($start_node);
                $wallet_transation = $wallet_transation->limit($limit);
            }

            $wallet_transation = $wallet_transation->orderBy('id', 'desc')->get();

            return response()->json(['wallet_transation' => $wallet_transation, 'wallet_balance' => auth()->user()->wallet_balance]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    /**
     * Show the promo usage.
     *
     * @return \Illuminate\Http\Response
     */
    public function promo_passbook(Request $request)
    {
        try {
            return PromocodePassbook::where('user_id', auth()->user()->id)->with('promocode')->get();
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function pricing_logic($id)
    {
        //return $id;
        $logic = ServiceType::select('calculator')->where('id', $id)->first();
        return $logic;
    }

    public function fare(Request $request)
    {
        $this->validate($request, [
            's_latitude'   => 'required|numeric',
            's_longitude'  => 'numeric',
            'd_latitude'   => 'required|numeric',
            'd_longitude'  => 'numeric',
            'service_type' => 'required|numeric|exists:service_types,id',
        ]);

        try {
            $response     = new ServiceTypes();
            $responsedata = $response->calculateFare($request->all());

            if (!empty($responsedata['errors'])) {
                throw new Exception($responsedata['errors']);
            } else {
                return response()->json($responsedata['data']);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function chatPush(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|numeric',
            'message' => 'required',
        ]);

        try {
            $user_id = $request->user_id;
            $message = $request->message;
            $sender  = $request->sender;

            (new SendPushNotification())->sendPushToProviderChat($user_id, $message);

            //(new SendPushNotification())->sendPushToUser($user_id, $message);

            return response()->json(['success' => 'true']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function CheckVersion(Request $request)
    {
        $this->validate($request, [
            'sender'      => 'in:user,provider',
            'device_type' => 'in:android,ios',
            'version'     => 'required',
        ]);

        //try {
        $sender      = $request->sender;
        $device_type = $request->device_type;
        $version     = $request->version;

        if ($sender == 'user') {
            if ($device_type == 'ios') {
                $curversion = config('constants.version_ios_user');
                if ($curversion == $version) {
                    return response()->json(['force_update' => false]);
                } elseif ($curversion > $version) {
                    return response()->json(['force_update' => true, 'url' => config('constants.store_link_ios_user')]);
                } else {
                    return response()->json(['force_update' => false]);
                }
            } else {
                $curversion = config('constants.version_android_user');
                if ($curversion == $version) {
                    return response()->json(['force_update' => false]);
                } elseif ($curversion > $version) {
                    return response()->json(['force_update' => true, 'url' => config('constants.store_link_android_user')]);
                } else {
                    return response()->json(['force_update' => false]);
                }
            }
        } elseif ($sender == 'provider') {
            if ($device_type == 'ios') {
                $curversion = config('constants.version_ios_provider');
                if ($curversion == $version) {
                    return response()->json(['force_update' => false]);
                } elseif ($curversion > $version) {
                    return response()->json(['force_update' => true, 'url' => config('constants.store_link_ios_provider')]);
                } else {
                    return response()->json(['force_update' => false]);
                }
            } else {
                $curversion = config('constants.version_android_provider');
                if ($curversion == $version) {
                    return response()->json(['force_update' => false]);
                } elseif ($curversion > $version) {
                    return response()->json(['force_update' => true, 'url' => config('constants.store_link_android_provider')]);
                } else {
                    return response()->json(['force_update' => false]);
                }
            }
        }
        //} catch (Exception $e) {
        //    return response()->json(['error' => $e->getMessage()], 500);
        //}
    }

    public function checkapi(Request $request)
    {
        //Log::info('Request Details:', $request->all());
        return response()->json(['sucess' => true]);
    }

    public function reasons(Request $request)
    {
        $reason = CancelReason::where('type', 'USER')->where('status', 1)->get();

        return $reason;
    }

    public function payment_log(Request $request)
    {
        $log           = PaymentLog::where('transaction_code', $request->order)->first();
        $log->response = $request->all();
        $log->save();
        return response()->json(['message' => trans('api.payment_success')]);
    }

    public function payment_online(Request $request)
    {
        $this->validate($request, [
            'request_id'       => 'required|integer|exists:user_requests,id',
            'payment_type'     => 'in:ONLINE',
            'transaction_code' => 'sometimes',
            'tips'             => 'sometimes',
        ]);

        $UserRequest                  = UserRequest::find($request->request_id);
        $RequestPayment               = UserRequestPayment::where('request_id', $request->request_id)->first();
        $UserRequest->payment_mode    = $request->payment_type;
        $RequestPayment->card         = $RequestPayment->payable;
        $RequestPayment->payable      = 0;
        $RequestPayment->tips         = $request->tips ? $request->tips : 0;
        $RequestPayment->provider_pay = $RequestPayment->provider_pay + ($request->tips ? $request->tips : 0);
        $RequestPayment->save();
        $log                   = new PaymentLog();
        $log->user_type        = 'user';
        $log->transaction_code = $request->transaction_code;
        $log->amount           = $RequestPayment->provider_pay;
        $log->transaction_id   = $request->request_id;
        $log->payment_mode     = $request->payment_type;
        $log->user_id          = \auth()->user()->id;
        $log->save();
        $UserRequest->paid   = 1;
        $UserRequest->status = 'COMPLETED';
        $UserRequest->save();

        //for create the transaction
        (new TransactionResource())->callTransaction($request->request_id);
        if ($request->ajax()) {
            return response()->json(['message' => trans('api.paid')]);
        } else {
            return redirect('dashboard')->with('flash_success', trans('api.paid'));
        }
    }

    public function verifyCredentials(Request $request)
    {
        if ($request->has('mobile')) {
            $Provider = User::where([['country_code', $request->country_code], ['mobile', $request->mobile]])->where('user_type', 'NORMAL')->first();
            if ($Provider != null) {
                return response()->json(['message' => trans('api.mobile_exist')], 422);
            }
        }
        if ($request->has('email')) {
            $Provider = User::where('email', $request->input('email'))->first();
            if ($Provider != null) {
                return response()->json(['message' => trans('api.email_exist')], 422);
            }
        }

        return response()->json(['message' => trans('api.available')]);
    }

    public function settings(Request $request)
    {
        $serviceType         = ServiceType::where('status', 1)->get();
        $settings            = [
            'serviceTypes' => $serviceType,
            'referral'     => [
                'online'      => config('constants.online', 0),
                'rental'      => config('constants.rental', 0),
                'outstation'  => config('constants.outstation', 0),
                'referral'  => config('constants.referral', 0),
                'count'     => config('constants.referral_count', 0),
                'amount'    => config('constants.referral_amount', 0),
                'ride_otp'  => (int)config('constants.ride_otp'),
                'ride_toll' => (int)config('constants.ride_toll'),
                'cancel_charge' => (int)config('constants.cancel_charge'),
            ],
        ];
        return response()->json($settings);
    }

    public function poly_check_new($s_latitude, $s_longitude)
    {
        $range_data = GeoFencing::get();
        //dd($range_data);

        $yes = $no =  [];

        $longitude_x = $s_latitude;

        $latitude_y =  $s_longitude;
        if (count($range_data) != 0) {
            foreach ($range_data as $ranges) {
                $vertices_x = $vertices_y = [];

                $range_values = json_decode($ranges['ranges'], true);
                //dd($range_values);
                if ($range_values != '') {
                    foreach ($range_values as $range) {
                        $vertices_x[] = $range['lat'];

                        $vertices_y[] = $range['lng'];
                    }

                    $points_polygon = count($vertices_x);
                    //dd($points_polygon);
                    if (is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)) {
                        $yes[] = $ranges['id'];
                    } else {
                        $no[] = 0;
                    }
                }
            }
            //dd($yes[0]." ".$no[0]);
            if (count($yes) != 0) {
                return $yes[0];
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function poly_check_request($s_latitude, $s_longitude)
    {
        $range_data = GeoFencing::get();
        //Log::alert($range_data);

        $yes = $no =   [];

        $longitude_x = $s_latitude;

        $latitude_y =  $s_longitude;

        if (count($range_data) != 0) {
            foreach ($range_data as $ranges) {
                if (!empty($ranges)) {
                    $vertices_x = $vertices_y = [];

                    $range_values = json_decode($ranges['ranges'], true);
                    //\Log::alert($range_values);
                    if (count($range_values) > 0) {
                        foreach ($range_values as $range) {
                            $vertices_x[] = $range['lat'];
                            $vertices_y[] = $range['lng'];
                        }
                    }

                    $points_polygon = count($vertices_x);
                    if (is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)) {
                        $yes[] =$ranges['id'];
                    } else {
                        $no[] = 0;
                    }
                }
            }
        }

        if (count($yes) != 0) {
            return 'yes';
        } else {
            return 'no';
        }
    }

    public function addMoneyRaz(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required',
        ]);

        $User   = auth()->user();
        $wallet = (new TransactionResource())->userCreditDebit($request->amount, $User->id, 1);
        (new SendPushNotification())->WalletMoney($User->id, currency($request->amount));
        $User  = User::find(auth()->user()->id);
        return response()->json(['message' => currency($request->amount) . trans('api.added_to_your_wallet'), 'user' => $User, 'balance' => $User->wallet_balance]);
    }
}
