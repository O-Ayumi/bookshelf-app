<?php

namespace App\Http\Controllers;

use App\Models\ReadingPlan;
use App\Enums\ReadingPlanStatus;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReadingPlanController extends Controller
{
    public function index()
    {
        $readingPlans = Auth::user()->readingPlans()->with('book')->orderBy('target_date', 'asc')->get();

        $currentStatus = 'Unread';

        return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
    }

    public function store(StoreReadingPlanRequest $request)
    {
        $validated = $request->validated();

        Auth::user()->readingPlans()->create([
            'book_id' => $validated['book_id'],
            'target_date' => $validated['target_date'],
            'status' => ReadingPlanStatus::Unread,
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を作成しました');
    }

    public function edit(ReadingPlan $readingplan)
    {
        $this->authorize('update', $readingplan);

        $statuses = ReadingPlanStatus::cases();

        return view('reading-plans.edit', compact('readingplan', 'statuses'));
    }

    public function update(UpdateReadingPlanRequest $request, ReadingPlan $readingPlan)
    {
        $this->authorize('update', $readingPlan);

        $validated = $request->validated();

        $readingPlan->update([
            'target_date' => $validated['target_date'],
            'status' => ReadingPlanStatus::from($validated['status']),
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を更新しました');
    }

    public function complete(ReadingPlan $readingPlan)
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'status' => ReadingPlanStatus::Completed,
        ]);

        return redirect()->route('books.show', $readingPlan->book_id)->with('success', '読了しました');
    }

    public function destroy(ReadingPlan $readingPlan)
    {
        $this->authorize('delete', $readingPlan);

        $readingPlan->delete();

        return redirect()->route('reading-plans.index')->with('success', '読書計画を削除しました');
    }
}
