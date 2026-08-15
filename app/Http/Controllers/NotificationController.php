<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    /**
     * 通知機能の一覧表示
     * @return View
     */
    public function index(): View
    {
        $notifications = auth()->user()->notifications;

        return view('notifications.index', compact('notifications'));
    }

    /**
     * 通知の既読機能
     * 
     * @param string $id　通知のID
     * @return RedirectResponse　成功メッセージと戻り遷移
     */
    public function read(string $id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);

        $notification->markAsRead();

        return redirect()->back()->with('success', '通知を既読にしました');
    }
}
