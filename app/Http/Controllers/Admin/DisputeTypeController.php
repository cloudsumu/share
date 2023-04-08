<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Dispute as DisputeType;

use App\Http\Requests\Admin\{
    StoreDisputeTypeRequest, UpdateDisputeTypeRequest
};

class DisputeTypeController extends Controller
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
        $this->authorize('list disputeTypes', DisputeType::class);
        $search = $request->get('search', '');

        $disputeTypes = DisputeType::search($search)
            ->latest()
            ->paginate();

        return view('admin.disputeType.index', compact('disputeTypes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create disputeTypes', DisputeType::class);
        return view('admin.disputeType.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDisputeTypeRequest $request)
    {
        $credentials = $request->validated();
        DisputeType::create($credentials);

        return redirect()
            ->route('admin.disputeType.index')
            ->with('success', (__('crud.general.created')));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $disputeType)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(DisputeType $disputeType)
    {
        $this->authorize('view disputeTypes', $disputeType);
        return view('admin.disputeType.edit', compact('disputeType'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDisputeTypeRequest $request, DisputeType $disputeType)
    {
        $credentials = $request->validated();
        $disputeType->update($credentials);

        return redirect()
            ->route('admin.disputeType.index')
            ->with('success', (__('crud.general.updated')));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DisputeType $disputeType)
    {
        $this->authorize('delete disputeTypes', $disputeType);
        try {
            $disputeType->delete();
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
            ->withSuccess(__('crud.general.deleted'));
    }
}
