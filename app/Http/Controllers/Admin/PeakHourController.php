<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\Admin\{
    StorePeakHourRequest, UpdatePeakHourRequest
};

use App\Models\PeakHour;

class PeakHourController extends Controller
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
        $this->authorize('list peakHours', PeakHour::class);
        $search = $request->get('search', '');

        $peakHours = PeakHour::search($search)
            ->latest()
            ->paginate();
            
        return view('admin.peakHour.index', compact('peakHours', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.peakHour.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePeakHourRequest $request)
    {
        $this->authorize('create peakHours', PeakHour::class);
        $credentials = $request->validated();
        PeakHour::create($credentials);

        return redirect()
            ->route('admin.peakHour.index')
            ->with('success', __('crud.admin.peakHours.name')." ".__('crud.general.created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(PeakHour $peakHour)
    {
        abort(403);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(PeakHour $peakHour)
    {
        $this->authorize('view peakHours', $peakHour);
        return view('admin.peakHour.edit', compact('peakHour'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePeakHourRequest $request, PeakHour $peakHour)
    {
        $credentials = $request->validated();
        $peakHour->update($credentials);
        
        return redirect()
            ->route('admin.peakHour.index')
            ->with('success', __('crud.admin.peakHours.name')." ".__('crud.general.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(PeakHour $peakHour)
    {
        $this->authorize('delete peakHours', $peakHour);
        try {
            $peakHour->delete();
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
