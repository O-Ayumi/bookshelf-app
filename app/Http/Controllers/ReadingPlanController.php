<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReadingPlanController extends Controller
{
    /**
     * 読書計画の絞り込み条件を含む一覧表示
     */
    public function index(Request $request): View
    {
        $currentStatus = $request->input('status');
        $user = Auth::user();

        $query = $user->readingPlans()->with('book');

        if ($request->filled('status')) {
            $query->where('status', $currentStatus);
        }

        $readingPlans = $query->orderBy('target_date', 'asc')
            ->get();

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
        $user = Auth::user();

        Auth::user()->readingPlans()->create([
            'book_id' => $validated['book_id'],
            'target_date' => $validated['target_date'],
            'status' => ReadingPlanStatus::Reading,
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を作成しました');
    }

    /**
     * 読書計画編集画面表示
     *
     * @param  ReadingPlan  $reading_plan  特定の計画
     */
    public function edit(ReadingPlan $reading_plan): View
    {
        $this->authorize('update', $reading_plan);

        if ($reading_plan->status->value === 'completed') {
            abort(403, '完了済の読書計画は変更できません');
        }

        return view('reading-plans.edit', ['readingPlan' => $reading_plan]);
    }

    /**
     * 読書計画編集
     *
     * @param  UpdateReadingPlanRequest  $request  バリデーション済のリクエスト
     * @param  ReadingPlan  $reading_plan  特定の計画
     * @return RedirectResponse　読書計画一覧へ遷移
     */
    public function update(UpdateReadingPlanRequest $request, ReadingPlan $reading_plan): RedirectResponse
    {
        $this->authorize('update', $reading_plan);

        if ($reading_plan->status->value === 'completed') {
            throw ValidationException::withMessages([
                'status' => '完了済の読書計画は変更できません',
            ]);
        }

        $validated = $request->validated();
        $user = Auth::user();

        $newTargetDate = Carbon::parse($request->input('target_date'));
        $today = Carbon::today();

        if ($reading_plan->status->value !== 'completed' && $newTargetDate->greaterThanOrEqualTo($today)) {
            $exists = $user->readingPlans()
                ->where('book_id', $reading_plan->book_id)
                ->where('id', '!=', $reading_plan->id)
                ->whereIn('status', [ReadingPlanStatus::Reading])
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'target_date' => 'この書籍はすでに読書計画が進行中のため、期日を延長できません',
                ]);
            }
        }

        $updateData = [
            'target_date' => $validated['target_date'],
        ];

        if ($reading_plan->status->value !== 'completed' && $newTargetDate->greaterThanOrEqualTo($today)) {
            $updateData['status'] = 'in_progress';
        }

        $reading_plan->update($updateData);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を更新しました');
    }

    /**
     * 読書計画の削除
     *
     * @param  ReadingPlan  $reading-plan  特定の計画
     * @return RedirectResponse　読書計画一覧へ遷移
     */
    public function destroy(ReadingPlan $reading_plan): RedirectResponse
    {
        $this->authorize('delete', $reading_plan);

        DB::transaction(function () use ($reading_plan) {
            DatabaseNotification::where('notifiable_id', $reading_plan->user_id)
                ->where('notifiable_type', get_class($reading_plan->user))
                ->get()
                ->filter(function ($notification) use ($reading_plan) {
                    $dataString = is_array($notification->data) || is_object($notification->data) ? json_encode($notification->data) : (string) $notification->data;

                    $pattern = '/"reading_plan_id"\s*:\s*"?'.preg_quote($reading_plan->id, '/').'"?/';

                    return (bool) preg_match($pattern, $dataString);
                })
                ->each(function ($notification) {
                    $notification->delete();
                });

            $reading_plan->delete();
        });

        return redirect()->route('reading-plans.index')->with('success', '読書計画を削除しました');
    }

    /**
     * 読了時の処理
     *
     * @param  ReadingPlan  $reading_plan  特定の計画
     * @return RedirectResponse　読書計画一覧へ遷移
     */
    public function complete(ReadingPlan $reading_plan): RedirectResponse
    {
        $this->authorize('update', $reading_plan);

        $reading_plan->update([
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読了しました');
    }
}
