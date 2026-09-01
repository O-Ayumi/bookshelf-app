<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class IndexBookRequest extends FormRequest
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
            'keyword' => ['nullable', 'string', 'max:255'],
            'genre_id' => ['nullable', 'integer', 'exists:genres,id'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'keyword.max' => 'キーワードは255文字以内で入力してください',
            'genre_id.integer' => 'ジャンルIDは整数で指定してください',
            'genre_id.exists' => '選択されたジャンルは存在しません',
            'page.integer' => 'ページ番号は整数で指定してください',
            'page.min' => 'ページ番号は1以上で指定してください',
            'per_page.integer' => '1ページあたりの件数は1以上で指定してください',
            'per_page.min' => '1ページ当たりの件数は1以上で指定してください',
            'per_page.max' => '1ページあたりの件数は10件以内で指定してください',
        ];
    }
}
