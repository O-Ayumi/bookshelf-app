<?php

namespace App\Http\Requests;

use App\Enums\ReadingPlanStatus;
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
                'exists:books,id',
                Rule::unique('reading_plans', 'book_id')
                    ->where('user_id', Auth::id())
                    ->where('status', ReadingPlanStatus::Reading->value),
            ],
            'target_date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください',
            'book_id.unique' => 'すでにこの書籍の読書計画が進行中です',
            'target_date.required' => '期日を設定してください',
        ];
    }
}
