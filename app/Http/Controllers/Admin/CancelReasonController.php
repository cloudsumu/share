<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\{
    StoreCancelReasonRequest, UpdateCancelReasonRequest
};

use Illuminate\Http\Request;

use App\Models\CancelReason;

class CancelReasonController extends Controller
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
        $this->authorize('list cancelReasons', CancelReason::class);
        $search = $request->get('search', '');

        $cancelReasons = CancelReason::search($search)
            ->latest()
            ->paginate();

        return view('admin.cancelReason.index', compact('cancelReasons', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create cancelReasons', CancelReason::class);
        return view('admin.cancelReason.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCancelReasonRequest $request)
    {
        $credentials = $request->validated();
        $cancelReason = CancelReason::create($credentials);

        return redirect()
            ->route('admin.cancelReason.index')
            ->with('success', __('crud.admin.cancelReasons.name')." ".__('crud.general.created'));
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
    public function edit(CancelReason $cancelReason)
    {
        $this->authorize('view cancelReasons', $cancelReason);
        return view('admin.cancelReason.edit', compact('cancelReason'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCancelReasonRequest $request, CancelReason $cancelReason)
    {
        $credentials = $request->validated();
        $cancelReason->update($credentials);

        return redirect()
            ->route('admin.cancelReason.index')
            ->with('success', __('crud.admin.cancelReasons.name')." ".__('crud.general.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(CancelReason $cancelReason)
    {
        $this->authorize('delete cancelReasons', $cancelReason);
        try {
            $cancelReason->delete();
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
            ->withSuccess(__('crud.admin.cancelReasons.name')." ".__('crud.general.deleted'));
    }
}
