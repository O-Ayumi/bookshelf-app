<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
        $currentPlan = $this->route('reading-plan');
        $currentPlanId = is_object($currentPlan) ? $currentPlan->id : $currentPlan;

        $bookId = $this->input('book_id') ?? ($is_object($currentPlan) ? $currentPlan->book_id : null);

        return [
            'target_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'book_id' => [
                'nullable',
                Rule::unique('reading_plans', 'book_id')
                    ->where(function ($query) {
                        return $query->where('user_id', Auth::id())
                            ->where('status', 'in_progress');
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
