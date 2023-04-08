<?php

namespace App\Http\Controllers\Admin;

use App\Models\Dispute;
use Illuminate\Http\Request;
use App\Models\UserRequestDispute;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateDisputeRequest;

class DisputeController extends Controller
{
    public function index(Request $request)
    {
        $disputes = UserRequestDispute::latest()
            ->paginate();
        return view('admin.dispute.index', compact('disputes'));
    }

    // public function create()
    // {
    //     $type = ['user', 'provider'];
    //     $dispute_type = array_rand($type, 1);
    //     $dispute = UserRequestDispute::create([
    //         'dispute_name' => Str::random(10),
    //         'request_id' => 1,
    //         'dispute_type' => $type[$dispute_type],
    //         'user_id' => 1,
    //         'provider_id' => 1,
    //     ]);

    //     return redirect()
    //         ->route('admin.dispute.index');
    // }

    public function edit(UserRequestDispute $dispute)
    {
        return view('admin.dispute.edit', compact('dispute'));
    }

    public function update(UpdateDisputeRequest $request, UserRequestDispute $dispute)
    {
        if($dispute->status == 'closed') {
            // Request Was Already Processed.
            return redirect()
                ->back()
                ->withErrors('This Dispute Was Already Closed.');
        }

        $credentials['status'] = 'closed';
        $credentials['comments'] = $request->response;
        $dispute->update($credentials);

        return redirect()
            ->route('admin.dispute.index')
            ->with('success', __('crud.admin.users.name')." ".__('crud.general.updated'));
    }

    public function destroy(Dispute $dispute)
    {
        // 
    }
}
