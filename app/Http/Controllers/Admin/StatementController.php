<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Agent;
use App\Models\Provider;
use App\Models\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\UserRequestPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class StatementController extends Controller
{
    /**
     * Returns overall statements
     * @return Illuminate\Http\Response
     */
    public function index(Request $request, $for = NULL, $id = NULL)
    {
        $credentials = $request->validate([
            'start_date' => 'nullable|date|before_or_equal:today',
            'end_date' => 'nullable|date|before_or_equal:today',
            'status' => [
                'nullable',
                Rule::in(['ALL', 'ACCEPTED', 'CANCELLED', 'COMPLETED'])
            ],
        ]);

        // According to developer.android.com month ranges from 0-11. So you need to increment it by 1.

        $start_date = $request->start_date ? $request->start_date : UserRequest::first()->created_at;
        $end_date = $request->end_date ? $request->end_date : Carbon::now();


        $status = !$request->status || $request->status == 'ALL' ? ['CANCELLED', 'ACCEPTED', 'STARTED', 'ARRIVED', 'PICKEDUP', 'DROPPED', 'COMPLETED', 'SCHEDULED', 'SCHEDULES'] : [$request->status];
        
        if($for == 'provider') {
            $statements = UserRequest::where('provider_id', $id)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->whereIn('status', $status)
                ->latest()
                ->paginate();
        }
        else if($for == 'user') {
            $statements = UserRequest::where('user_id', $id)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->whereIn('status', $status)
                ->latest()
                ->paginate();
        }
        else {
            $statements = UserRequest::whereBetween('created_at', [$start_date, $end_date])
                ->whereIn('status', $status)
                ->latest()
                ->paginate();
        }

        $total_jobs = UserRequest::count();

        $cancelled_jobs = UserRequest::where('status', 'CANCELLED')->count();

        $revenue = UserRequestPayment::sum('total');
        $overall_commission = UserRequestPayment::sum('commision');
        $overall_agent_commission = UserRequestPayment::sum('agent');

        return view('admin.statement.index', compact('statements', 'total_jobs', 'cancelled_jobs', 'revenue', 'overall_commission', 'overall_agent_commission'));
    }

    /**
     * Returns Provider statements
     * @return Illuminate\Http\Response
     */
    public function providerStatements()
    {
        try {
            $providers = Provider::latest()->paginate();

            foreach ($providers as $index => $provider) {

                $providers[$index]->payment = UserRequestPayment::where('provider_id', $provider->id)
                        ->select(DB::raw(
                            'SUM(provider_pay) as overall, SUM(provider_commission) as commission, COUNT(id) as jobs_count'
                        ))->first();
            }

            return view('admin.statement.provider', compact('providers'));
        } catch (Exception $e) {
            Log::error('Error Showing Provider Statements:- '.$e->getMessage());
            return redirect()
                ->back()
                ->withErrors('Something went wrong!');
        }
    }

    /**
     * Returns User statements
     * @return Illuminate\Http\Response
     */
    public function userStatements()
    {
        try {
            $users = User::paginate();

            foreach ($users as $index => $user) {

                $users[$index]->payment = UserRequestPayment::where('user_id', $user->id)
                    ->select(DB::raw(
                        'SUM(provider_pay) as overall, count(id) as jobs_posted'
                    ))->get();
            }

            return view('admin.statement.user', compact('users'));
        } catch (Exception $e) {
            Log::error('Error Showing User Statements:- '.$e->getMessage());
            return redirect()
                ->back()
                ->withErrors('Something went wrong!');
        }
    }

    public function agentStatements()
    {
        try {
            $agents = Agent::paginate();

            foreach ($agents as $index => $Agent) {
                $Rides = UserRequest::get()->pluck('id');

                $agents[$index]->rides_count = $Rides->count();

                $agents[$index]->payment = UserRequestPayment::where('agent_id', $Agent->id)
                                ->select(DB::raw(
                                    'SUM(agent) as overall, COUNT(id) as jobs_count'
                                ))->first();
            }
            return view('admin.statement.agent', compact('agents'));
        } catch (Exception $e) {
            Log::error('Error Showing Agent Statements:- '.$e->getMessage());
            return back()->with('flash_error', 'Something went wrong!');
        }
    }
}