<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\facades\Auth;
use Illuminate\Validation\Rule;

class StoreReadingPlanRequest extends FormRequest
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
        return [
            'book_id' => [
                'required',
                'integer',
                'exists:books,id',
                Rule::unique('reading_plans', 'book_id')
                    ->where(function ($query) {
                        return $query->where('user_id', Auth::id())
                            ->where('status', 'in_progress');
                    }),
            ],
            'target_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください',
            'book_id.unique' => 'この書籍は既に進行中の読書計画が存在します',
            'target_date.required' => '期日を設定してください',
            'target_date.date' => '期日は有効な日付形式で入力してください',
            'target_date.after_or_equal' => '期日は今日以降の日付を指定してください',
        ];
    }
}
