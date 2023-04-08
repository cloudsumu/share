<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Http\Requests\Admin\{
    StoreNotificationRequest, UpdateNotificationRequest
};

class NotificationController extends Controller
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
        $this->authorize('list notifications', Notification::class);
        $search = $request->get('search', '');

        $notifications = Notification::search($search)
            ->latest()
            ->paginate();

        return view('admin.notification.index', compact('notifications'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create notifications', Notification::class);
        return view('admin.notification.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreNotificationRequest $request)
    {
        $credentials = $request->validated();

        if($request->hasFile('image')) {
            $credentials['image'] = $request->file('image')->store('public/notifications');
        }

        Notification::create($credentials);

        return redirect()
            ->route('admin.notification.index')
            ->with('success', (__('crud.general.created')));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $notification
     * @return \Illuminate\Http\Response
     */
    public function show(Notification $notification)
    {
        abort(403);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Request  $notification
     * @return \Illuminate\Http\Response
     */
    public function edit(Notification $notification)
    {
        $this->authorize('view notifications', $notification);
        return view('admin.notification.edit', compact('notification'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $notification
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateNotificationRequest $request, Notification $notification)
    {
        $credentials = $request->validated();

        if($request->hasFile('image')) {
            if($notification->image) {
                Storage::delete($notification->image);
            }
            $credentials['image'] = $request->file('image')->store('public/notifications');
        }

        $notification->update($credentials);

        return redirect()
            ->route('admin.notification.index')
            ->with('success', (__('crud.general.updated')));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $notification
     * @return \Illuminate\Http\Response
     */
    public function destroy(Notification $notification)
    {
        $this->authorize('delete notifications', $notification);
        try {
            $notification->delete();
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
