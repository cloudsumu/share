<?php

namespace App\Http\Controllers\Resource;

use App\Models\Dispute;
use App\Models\UserRequest;
use Illuminate\Http\Request;
use App\Notifications\WebPush;
use App\Models\UserRequestDispute;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Provider\TripController;
use App\Http\Controllers\TransactionResource;
use App\Models\DisputeType;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DisputeResource extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function dispute_list(Request $request)
    {
        $this->validate($request, [
            'dispute_type' => 'required',
        ]);
        $dispute = Dispute::select('dispute_name')->where('dispute_type' , $request->dispute_type)->where('status' , 'active')->get();

        return $dispute;
    }

    public function userdisputes()
    {
        $disputes  = UserRequestDispute::with('request')->with('user')->with('provider')->orderBy('created_at', 'desc')->paginate($this->perpage);

        return view('admin.userdispute.index', compact('disputes', 'pagination'));
    }

    public function userdisputecreate()
    {
        return view('admin.userdispute.create');
    }

    public function userdisputeedit($id)
    {
        try {
            $dispute = UserRequestDispute::with('request')->with('user')->with('provider')->findOrFail($id);
            return view('admin.userdispute.edit', compact('dispute'));
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    public function create_dispute(Request $request)
    {
        $this->validate($request, [
            'request_id'   => 'required',
            'dispute_type' => 'required|in:user,provider,agent,app',
            'dispute_name' => 'required',
        ]);

        try {
            $Dispute               = new UserRequestDispute();
            $Dispute->request_id   = $request->request_id;
            $Dispute->dispute_type = $request->dispute_type == 'app' ? 'user' : $request->dispute_type;
            $Dispute->user_id      = $request->user_id;
            $Dispute->provider_id  = $request->provider_id;
            $Dispute->dispute_name = $request->dispute_name;
            if (!empty($request->dispute_other)) {
                $Dispute->dispute_name = $request->dispute_other;
            }
            $Dispute->comments = $request->comments;
            $Dispute->save();

            UserRequest::where('id', $request->request_id)->update(['is_dispute' => 1]);

            $admin = \App\Models\Admin::find(auth()->user()->id);

            if ($admin == null) {
                $admin = \App\Models\Admin::whereNotNull('name')->first();
            }

            // if ($admin != null) {
            //     $admin->notify(new WebPush('Notifications', trans('admin.dispute.new_dispute'), url('/')));
            // }

            if ($request->ajax()) {
                return response()->json(['message' => trans('admin.dispute_msgs.saved')]);
            } else {
                return back()->with('flash_success', trans('admin.dispute_msgs.saved'));
            }
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.dispute_msgs.not_found'));
        }
    }

    public function update_dispute(Request $request, $id)
    {
        $this->validate($request, [
            'comments' => 'required',
            'status'   => 'required',
        ]);

        try {
            $Dispute                = UserRequestDispute::findOrFail($id);
            $Dispute->comments      = $request->comments;
            $Dispute->refund_amount = $request->refund_amount;

            if (!empty($request->refund_amount)) {
                //create the dispute transactions
                if ($Dispute->dispute_type == 'user') {
                    $type         =1;
                    $request_by_id=$Dispute->user_id;
                } else {
                    $type         =0;
                    $request_by_id=$Dispute->provider_id;
                }

                (new TransactionResource())->disputeCreditDebit($request->refund_amount, $request_by_id, $type);
            }

            $Dispute->status = $request->status;
            $Dispute->save();

            if ($request->ajax()) {
                return response()->json(['message' => trans('admin.dispute_msgs.saved')]);
            } else {
                return back()->with('flash_success', trans('admin.dispute_msgs.saved'));
            }
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.dispute_msgs.not_found'));
        }
    }
}
