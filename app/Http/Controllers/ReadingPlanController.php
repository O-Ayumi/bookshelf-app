<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReadingPlanController extends Controller
{
    /**
     * 読書計画の絞り込み条件を含む一覧表示
     */
    public function index(Request $request): View
    {
        $currentStatus = $request->input('status');

        $query = Auth::user()->readingPlans()->with('book');

        if ($request->filled('status')) {
            $query->where('status', $currentStatus);
        }

        $readingPlans = $query->orderBy('target_date', 'asc')
            ->get()
            ->map(function (ReadingPlan $plan) {
                return $plan;
            });

        return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
    }

    /**
     * 読書計画の作成画面
     */
    public function create(): View
    {
        $books = Book::orderBy('title', 'asc')->get();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * 読書計画の保存
     *
     * @param  StoreReadingPlanRequest  $request  バリデーション済のリクエスト
     * @return RedirectResponse　読書計画一覧へ遷移
     */
    public function store(StoreReadingPlanRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Auth::user()->readingPlans()->create([
            'book_id' => $validated['book_id'],
            'target_date' => $validated['target_date'],
            'status' => ReadingPlanStatus::Unread,
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を作成しました');
    }

    /**
     * 読書計画編集画面表示
     *
     * @param  ReadingPlan  $readingPlan  特定の計画
     */
    public function edit(ReadingPlan $readingPlan): View
    {
        $this->authorize('update', $readingPlan);

        return view('reading-plans.edit', compact('readingPlan'));
    }

    /**
     * 読書計画編集
     *
     * @param  UpdateReadingPlanRequest  $request  バリデーション済のリクエスト
     * @param  ReadingPlan  $readingPlan  特定の計画
     * @return RedirectResponse　読書計画一覧へ遷移
     */
    public function update(UpdateReadingPlanRequest $request, ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('update', $readingPlan);

        $validated = $request->validated();

        $readingPlan->update([
            'target_date' => $validated['target_date'],
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を更新しました');
    }

    /**
     * 読書計画の削除
     *
     * @param  ReadingPlan  $readingPlan  特定の計画
     * @return RedirectResponse　読書計画一覧へ遷移
     */
    public function destroy(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('delete', $readingPlan);

        $readingPlan->delete();

        return redirect()->route('reading-plans.index')->with('success', '読書計画を削除しました');
    }

    /**
     * 読了時の処理
     *
     * @param  ReadingPlan  $readingPlan  特定の計画
     * @return RedirectResponse　書籍詳細へ遷移
     */
    public function complete(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        return redirect()->route('books.show', $readingPlan->book_id)->with('success', '読了しました');
    }
}
