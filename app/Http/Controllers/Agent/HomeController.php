<?php

namespace App\Http\Controllers\Agent;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Agent;
use App\Models\Provider;
use App\Models\AgentWallet;
use App\Models\UserRequest;
use Illuminate\Http\Request;
use App\Models\WalletRequest;
use App\Helpers\ControllerHelper;
use App\Models\UserRequestPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\Provider\TripController;

class HomeController extends Controller
{
    private $perPage;

    public function __construct()
    {
        // Update: Enable demo mode to all the included
        $this->middleware('auth:agent');
        // $this->middleware('demo', ['only' => ['profile_update', 'password_update', 'destory_provider_service']]);
        $this->perPage = config('constants.per_page', '10');
    }

    public function dashboard()
    {
        try {

            // $getting_ride = UserRequest::has('user')
            //         ->whereHas('provider_id', function($query) {
            //                 $query->where('provider_id', Auth::user()->id );
            //             })
            //         ->orderBy('id','desc');

            $providers = auth('agent')->user()->provider->pluck('id')->toArray();

            $rides = UserRequest::whereNotNull('user_id')
                    ->whereIn('provider_id', $providers)
                    ->get();

            $all_rides = $rides->pluck('id');
            
            // $cancel_rides = UserRequest::where('status','CANCELLED') 
            //                 ->whereHas('provider', function($query) {
            //                     $query->where('provider_id', Auth::user()->id );
            //                 })->count();
            

            $cancel_rides = UserRequest::has('user')
                    ->whereIn('provider_id', $providers)
                    ->where('status' , 'CANCELLED')
                    ->count();

            // $service = ServiceType::where('agent_id', auth()->user()->id)->get()->count();

            $revenue = UserRequestPayment::whereIn('request_id', $all_rides)->sum('total');

            // $wallet = Provider::where('agent_id', auth('agent')->user()->id)->sum('wallet_balance');

            $providers = Provider::where('agent_id', Auth::user()->id)
                ->get()->count();

            // $passengers = User::get()->count();

            return view('agent.dashboard', compact('providers', 'rides', 'cancel_rides', 'revenue'));
        } catch (Exception $e) {
            Log::warning("Loading Agent Dashboard Error:- ".$e->getMessage());
            dd($e->getMessage());
            // return redirect()->route('agent.dashboard')->withErrors(__('admin.something_wrong_dashboard'));
        }
    }

    public function language($lang = 'en')
    {
        try {
            $admin = Agent::find(auth()->user()->id);
            $admin->update(['language' => $lang]);
            App::setLocale($lang);

            return redirect()->back()
                ->with('success', __('crud.general.updated'));
            
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withErrors('Some Error Occurred.');
                //route('admin.home')
        }
    }

    public function showUserRequest(UserRequest $userRequest)
    {
        return view('agent.requestDetail_template', compact('userRequest'));
    }

    public function statement(Request $request, $type = '')
    {
        try {
            if ((isset($request->provider_id) && $request->provider_id != null) || (isset($request->user_id) && $request->user_id != null) || (isset($request->agent_id) && $request->agent_id != null)) {
                $pages = trans('admin.include.overall_ride_statements');
                $listName = trans('admin.include.overall_ride_earnings');
                if ($type == 'individual') {
                    $pages = trans('admin.include.provider_statement');
                    $listName = trans('admin.include.provider_earnings');
                } elseif ($type == 'today') {
                    $pages = trans('admin.include.today_statement') . ' - ' . date('d M Y');
                    $listName = trans('admin.include.today_earnings');
                } elseif ($type == 'monthly') {
                    $pages = trans('admin.include.monthly_statement') . ' - ' . date('F');
                    $listName = trans('admin.include.monthly_earnings');
                } elseif ($type == 'yearly') {
                    $pages = trans('admin.include.yearly_statement') . ' - ' . date('Y');
                    $listName = trans('admin.include.yearly_earnings');
                } elseif ($type == 'range') {
                    $pages = trans('admin.include.statement_from') . ' ' . Carbon::createFromFormat('Y-m-d', $request->from_date)->format('d M Y') . '  ' . trans('admin.include.statement_to') . ' ' . Carbon::createFromFormat('Y-m-d', $request->to_date)->format('d M Y');
                }

                if (isset($request->provider_id) && $request->provider_id != null) {
                    $id = $request->provider_id;
                    $statement_for = "provider";
                    $rides = UserRequest::where('provider_id', $id)
                        ->whereHas('provider', function ($query) {
                            $query->where('agent_id', Auth::user()->id);
                        })
                        ->with('payment')
                        ->orderBy('id', 'desc');
                    $cancel_rides = UserRequest::where('status', 'CANCELLED')
                        ->whereHas('provider', function ($query) {
                            $query->where('agent_id', Auth::user()->id);
                        })
                        ->where('provider_id', $id);
                    $Provider = Provider::find($id);
                    $revenue = UserRequestPayment::whereHas('request', function ($query) use ($id) {
                        $query->where('provider_id', $id);
                    })->select(DB::raw(
                        'SUM(ROUND(provider_pay)) as overall, SUM(ROUND(provider_commission)) as commission'
                    ));
                    $page = $Provider->first_name . "'s " . $pages;
                } elseif (isset($request->user_id) && $request->user_id != null) {
                    $id = $request->user_id;
                    $statement_for = "user";
                    $rides = UserRequest::where('user_id', $id)->with('payment')->orderBy('id', 'desc');
                    $cancel_rides = UserRequest::where('status', 'CANCELLED')->where('user_id', $id);
                    $user = User::find($id);
                    $revenue = UserRequestPayment::whereHas('request', function ($query) use ($id) {
                        $query->where('user_id', $id);
                    })->select(DB::raw(
                        'SUM(ROUND(total)) as overall'
                    ));
                    $page = $user->first_name . "'s " . $pages;
                } else {
                    $id = $request->agent_id;
                    $statement_for = "agent";
                    $rides = UserRequestPayment::where('agent_id', $id)->whereHas('request', function ($query) use ($id) {
                        $query->with('payment')->orderBy('id', 'desc');
                    });
                    $cancel_rides = UserRequestPayment::where('agent_id', $id)->whereHas('request', function ($query) use ($id) {
                        $query->where('status', 'CANCELLED');
                    });
                    $agent = Agent::find($id);
                    $revenue = UserRequestPayment::where('agent_id', $id)
                        ->select(DB::raw(
                            'SUM(ROUND(agent)) as overall'
                        ));
                    $page = $agent->name . "'s " . $pages;
                }
            } else {
                $id = '';
                $statement_for = "";
                $page = trans('admin.include.overall_ride_statements');
                $listName = trans('admin.include.overall_ride_earnings');
                if ($type == 'individual') {
                    $page = trans('admin.include.provider_statement');
                    $listName = trans('admin.include.provider_earnings');
                } elseif ($type == 'today') {
                    $page = trans('admin.include.today_statement') . ' - ' . date('d M Y');
                    $listName = trans('admin.include.today_earnings');
                } elseif ($type == 'monthly') {
                    $page = trans('admin.include.monthly_statement') . ' - ' . date('F');
                    $listName = trans('admin.include.monthly_earnings');
                } elseif ($type == 'yearly') {
                    $page = trans('admin.include.yearly_statement') . ' - ' . date('Y');
                    $listName = trans('admin.include.yearly_earnings');
                } elseif ($type == 'range') {
                    $page = trans('admin.include.statement_from') . ' ' . Carbon::createFromFormat('Y-m-d', $request->from_date)->format('d M Y') . '  ' . trans('admin.include.statement_to') . ' ' . Carbon::createFromFormat('Y-m-d', $request->to_date)->format('d M Y');
                }

                $rides = UserRequest::with('payment')
                    ->whereHas('provider', function ($query) {
                        $query->where('agent_id', Auth::user()->id);
                    })
                    ->orderBy('id', 'desc');

                $cancel_rides = UserRequest::where('status', 'CANCELLED')
                    ->whereHas('provider', function ($query) {
                        $query->where('agent_id', Auth::user()->id);
                    });
                $revenue = UserRequestPayment::whereHas('provider', function ($query) {
                    $query->where('agent_id', Auth::user()->id);
                })->select(DB::raw(
                    'SUM(ROUND(fixed) + ROUND(distance)) as overall, SUM(ROUND(commision)) as commission'
                ));
            }


            if ($type == 'today') {

                $rides->where('created_at', '>=', Carbon::today());
                $cancel_rides->where('created_at', '>=', Carbon::today());
                $revenue->where('created_at', '>=', Carbon::today());
            } elseif ($type == 'monthly') {

                $rides->where('created_at', '>=', Carbon::now()->month);
                $cancel_rides->where('created_at', '>=', Carbon::now()->month);
                $revenue->where('created_at', '>=', Carbon::now()->month);
            } elseif ($type == 'yearly') {

                $rides->where('created_at', '>=', Carbon::now()->year);
                $cancel_rides->where('created_at', '>=', Carbon::now()->year);
                $revenue->where('created_at', '>=', Carbon::now()->year);
            } elseif ($type == 'range') {
                if ($request->from_date == $request->to_date) {
                    $rides->whereDate('created_at', date('Y-m-d', strtotime($request->from_date)));
                    $cancel_rides->whereDate('created_at', date('Y-m-d', strtotime($request->from_date)));
                    $revenue->whereDate('created_at', date('Y-m-d', strtotime($request->from_date)));
                } else {
                    $rides->whereBetween('created_at', [Carbon::createFromFormat('Y-m-d', $request->from_date), Carbon::createFromFormat('Y-m-d', $request->to_date)]);
                    $cancel_rides->whereBetween('created_at', [Carbon::createFromFormat('Y-m-d', $request->from_date), Carbon::createFromFormat('Y-m-d', $request->to_date)]);
                    $revenue->whereBetween('created_at', [Carbon::createFromFormat('Y-m-d', $request->from_date), Carbon::createFromFormat('Y-m-d', $request->to_date)]);
                }
            }

            $rides = $rides->paginate($this->perPage);
            if ($type == 'range') {
                $path = 'range?from_date=' . $request->from_date . '&to_date=' . $request->to_date;
                $rides->setPath($path);
            }
            $pagination = (new ControllerHelper)->formatPagination($rides);
            $cancel_rides = $cancel_rides->count();
            $revenue = $revenue->get();

            $dates['yesterday'] = Carbon::yesterday()->format('Y-m-d');
            $dates['today'] = Carbon::today()->format('Y-m-d');
            $dates['pre_week_start'] = date("Y-m-d", strtotime("last week monday"));
            $dates['pre_week_end'] = date("Y-m-d", strtotime("last week sunday"));
            $dates['cur_week_start'] = Carbon::today()->startOfWeek()->format('Y-m-d');
            $dates['cur_week_end'] = Carbon::today()->endOfWeek()->format('Y-m-d');
            $dates['pre_month_start'] = Carbon::parse('first day of last month')->format('Y-m-d');
            $dates['pre_month_end'] = Carbon::parse('last day of last month')->format('Y-m-d');
            $dates['cur_month_start'] = Carbon::parse('first day of this month')->format('Y-m-d');
            $dates['cur_month_end'] = Carbon::parse('last day of this month')->format('Y-m-d');
            $dates['pre_year_start'] = date("Y-m-d", strtotime("last year January 1st"));
            $dates['pre_year_end'] = date("Y-m-d", strtotime("last year December 31st"));
            $dates['cur_year_start'] = Carbon::parse('first day of January')->format('Y-m-d');
            $dates['cur_year_end'] = Carbon::parse('last day of December')->format('Y-m-d');
            $dates['nextWeek'] = Carbon::today()->addWeek()->format('Y-m-d');

            return view('agent.provider.statements', compact('rides', 'cancel_rides', 'revenue', 'pagination', 'dates', 'id', 'statement_for'))
                ->with('page', $page)->with('listName', $listName);
        } catch (Exception $e) {
            Log::warning('Agent Statement Error:- '.$e->getMessage());
            return back()->with('flash_error', 'Something went wrong!');
        }
    } 

    /**
     * account statements.
     *
     * @param  \App\Provider $provider
     * @return \Illuminate\Http\Response
     */
    public function statement_provider()
    {

        try {

            $Providers = Provider::where('fleet', Auth::user()->id)->paginate($this->perPage);

            $pagination = (new ControllerHelper)->formatPagination($Providers);

            foreach ($Providers as $index => $Provider) {

                $Rides = UserRequest::where('provider_id', $Provider->id)
                    ->where('status', '<>', 'CANCELLED')
                    ->whereHas('provider', function ($query) {
                        $query->where('fleet', Auth::user()->id);
                    })
                    ->get()->pluck('id');

                $Providers[$index]->rides_count = $Rides->count();

                $Providers[$index]->payment = UserRequestPayment::whereHas('provider', function ($query) {
                    $query->where('fleet', Auth::user()->id);
                })->whereIn('request_id', $Rides)
                    ->select(DB::raw(
                        'SUM(ROUND(provider_pay)) as overall, SUM(ROUND(provider_commission)) as commission'
                    ))->get();
            }

            return view('fleet.providers.provider-statement', compact('Providers', 'pagination'))->with('page', 'Providers Statement');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Something went wrong!');
        }
    }

    /**
     * Map of all Users and Drivers.
     *
     * @return \Illuminate\Http\Response
     */
    public function map_index()
    {
        return view('agent.map.index');
    }

    /**
     * Map of all Users and Drivers.
     *
     * @return \Illuminate\Http\Response
     */
    public function map_ajax()
    {
        try {

            $Providers = Provider::where('latitude', '!=', 0)
                ->where('longitude', '!=', 0)
                ->where('agent_id', Auth::guard('agent')->user()->id)
                ->with('service')
                ->get();

            $Users = User::where('latitude', '!=', 0)
                ->where('longitude', '!=', 0)
                ->get();

            for ($i = 0; $i < sizeof($Users); $i++) {
                $Users[$i]->status = 'user';
            }

            $all = $Users->merge($Providers);

            return $all;
        } catch (Exception $e) {
            return [];
        }
    }

    public function wallet(Request $request)
    {
        try {
            $wallet_transaction = AgentWallet::where('agent_id', Auth::user()->id)
                ->orderBy('id', 'desc')
                ->paginate(config('constants.per_page', '10'));

            $pagination = (new ControllerHelper)->formatPagination($wallet_transaction);

            $wallet_balance = Auth::user()->wallet_balance;

            return view('agent.wallet.wallet_transaction', compact('wallet_transaction', 'pagination', 'wallet_balance'));

        } catch (Exception $e) {
            return back()->with(['flash_error' => trans('admin.something_wrong')]);
        }
    }

    public function transfer(Request $request)
    {
        $pendingList = WalletRequest::where('from_id', Auth::user()->id)->where('request_from', 'agent')->where('status', 0)->get();
        $wallet_balance = Auth::user()->wallet_balance;
        return view('agent.wallet.transfer', compact('pendingList', 'wallet_balance'));
    }

    public function requestAmount(Request $request)
    {


        $send = (new TripController())->requestAmount($request);
        $response = json_decode($send->getContent(), true);

        if (!empty($response['error']))
            $result['flash_error'] = $response['error'];
        if (!empty($response['success']))
            $result['flash_success'] = $response['success'];

        return redirect()
            ->back()
            ->with($result);
    }

    public function cancel(Request $request)
    {

        $cancel = (new TripController())->requestCancel($request);
        $response = json_decode($cancel->getContent(), true);

        if (!empty($response['error']))
            $result['flash_error'] = $response['error'];
        if (!empty($response['success']))
            $result['flash_success'] = $response['success'];

        return redirect()
            ->back()
            ->with($result);
    }

    public function agentIndex()
    {
        try {
            $requests = UserRequest::RequestHistory()
                        ->whereHas('provider', function($query) {
                            $query->where('agent_id', Auth::user()->id );
                        })->get();
            return view('agent.request.index', compact('requests'));
        } catch (Exception $e) {
            dd($e->getMessage());
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    public function agentScheduled()
    {
        try{
            $requests = UserRequest::where('status' , 'SCHEDULED')
                         ->whereHas('provider', function($query) {
                            $query->where('agent_id', Auth::user()->id );
                        })
                        ->get();

            return view('agent.request.scheduled', compact('requests'));
        } catch (\Exception $e) {
             return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    public function agentShow($id)
    {
        try {
            $request = UserRequest::findOrFail($id);
            if(!$request->provider || $request->provider->agent_id != auth('agent')->user()->id) {
                return redirect()
                    ->route('agent.requests.index')
                    ->withErrors('Invalid Request');
            }
            return view('agent.request.show', compact('request'));
        } catch (Exception $e) {
             return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    public function agentDestroy($id)
    {
        try {
            $Request = UserRequest::findOrFail($id);
            $Request->delete();
            return back()->with('flash_success', trans('admin.request_delete'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }
}