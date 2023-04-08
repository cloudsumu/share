<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promocode;

use Illuminate\Http\Request;
use App\Http\Requests\Admin\{
    StorePromocodeRequest, UpdatePromocodeRequest
};

class PromocodeController extends Controller
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
        $this->authorize('list promocodes', Promocode::class);
        $search = $request->get('search', '');

        $promocodes = Promocode::search($search)
            ->latest()
            ->paginate();

        return view('admin.promocode.index', compact('promocodes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create promocodes', Promocode::class);
        return view('admin.promocode.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePromocodeRequest $request)
    {
        $credentials = $request->validated();

        Promocode::create($credentials);

        return redirect()
            ->route('admin.promocode.index')
            ->with('success', (__('crud.general.created')));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Promocode $promocode)
    {
        abort(403);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Promocode $promocode)
    {
        $this->authorize('view promocodes', $promocode);
        return view('admin.promocode.edit', compact('promocode'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePromocodeRequest $request, Promocode $promocode)
    {
        $credentials = $request->validated();

        $promocode->update($credentials);

        return redirect()
            ->route('admin.promocode.index')
            ->with('success', (__('crud.general.updated')));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Promocode $promocode)
    {
        $this->authorize('delete promocodes', $promocode);
        try {
            $promocode->delete();
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
