<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGenreRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:genres,name',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'ジャンル名を入力してください',
            'name.unique' => 'このジャンル名はすでに登録されています',
        ];
    }
}
