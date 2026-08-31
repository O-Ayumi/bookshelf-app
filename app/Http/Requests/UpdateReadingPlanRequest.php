<?php

namespace App\Http\Requests;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateReadingPlanRequest extends FormRequest
{
    /**
     * ユーザーがこのリクエストを実行する権限があるか判定する
     *
     * @return bool 認可結果
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルールを定義する
     *
     * @return array<string, mixed> バリデーションルールの配列
     */
    public function rules(): array
    {
        $currentPlan = $this->route('reading_plan');

        $planModel = is_object($currentPlan) ? $currentPlan : ReadingPlan::find($currentPlan);

        if ($planModel && $planModel->status === ReadingPlanStatus::Completed->value) {
            throw ValidationException::withMessages([
                'status' => '完了済の読書計画は変更できません',
            ]);
        }

        $currentPlanId = $planModel ? $planModel->id : null;

        $bookId = $this->input('book_id') ?? ($planModel ? $planModel->book_id : null);

        return [
            'target_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'book_id' => [
                'nullable',
                Rule::unique('reading_plans', 'book_id')
                    ->where(function ($query) use ($bookId) {
                        return $query->where('user_id', Auth::id())
                            ->where('book_id', $bookId)
                            ->where('status', ReadingPlanStatus::Reading->value);
                    })
                    ->ignore($currentPlanId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'target_date.required' => '期日を設定してください',
            'target_date.date' => '期日は有効な日付形式で入力してください',
            'target_date.after_or_equal' => '期日は今日以降の日付を指定してください',
            'book_id.unique' => 'この書籍は既に進行中の読書計画が存在します',
        ];
    }
}
