<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    /**
     * ユーザーがこのリクエストを実行する権限があるか判定する
     *
     * @return bool 認可結果
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * バリデーションルールを定義する
     *
     * @return array<string, mixed> バリデーションルールの配列
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|digits:13|unique:books,isbn',
            'published_date' => 'required|date',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|url',
            'genre_ids' => 'required|array',
            'genre_ids.*' => 'exists:genres,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを入力してください',
            'title.max' => 'タイトルは255文字以内で入力してください',
            'author.required' => '著者を入力してください',
            'author.max' => '著者は255文字以内で入力してください',
            'isbn.required' => 'ISBNコードを入力してください',
            'isbn.max' => 'ISBNコードは13桁で入力してください',
            'isbn.unique' => 'このISBNコードは既に登録されています',
            'published_date.required' => '出版日を選択してください',
            'genre_ids.required' => 'ジャンル名を入力してください',
            'genre_ids.*.exists' => '指定されたジャンルは存在しません',
        ];
    }
}
