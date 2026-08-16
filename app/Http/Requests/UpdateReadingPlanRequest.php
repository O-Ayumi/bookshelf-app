<?php

namespace App\Http\Requests;

use App\Enums\ReadingPlanStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

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
        return [
            'target_date' => ['required', 'date'],
            'status' => ['nullable', new Enum(ReadingPlanStatus::class)],
        ];
    }
}
