<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * 通知機能の一覧表示
     */
    public function index(): View
    {
        $notifications = Auth::user()->notifications()->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * 通知の既読機能
     *
     * @param  string  $id  通知のID
     * @return RedirectResponse　成功メッセージと戻り遷移
     */
    public function read(string $id): RedirectResponse
    {
        $notification = DatabaseNotification::findOrFail($id);

        if ($notification->notifiable_id !== Auth::id()) {
            abort(403, 'この操作の権限がありません');
        }

        $notification->markAsRead();

        return redirect()->back()->with('success', '通知を既読にしました');
    }
}
