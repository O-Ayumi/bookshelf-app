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
            'genres' => 'required|array|min:1',
            'genres.*' => 'integer|exists:genres,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを入力してください',
            'title.string' => 'タイトルは文字列で入力してください',
            'title.max' => 'タイトルは255文字以内で入力してください',
            'author.required' => '著者を入力してください',
            'author.string' => '著者名は文字列で入力してください',
            'author.max' => '著者は255文字以内で入力してください',
            'isbn.required' => 'ISBNコードを入力してください',
            'isbn.string' => 'ISBNは文字列で入力してください',
            'isbn.max' => 'ISBNコードは13桁で入力してください',
            'isbn.unique' => 'このISBNコードは既に登録されています',
            'published_date.required' => '出版日を選択してください',
            'published_date.date' => '出版日は有効な日付形式で入力してください',
            'description.string' => '説明は文字列でに入力してください',
            'image_url.string' => '画像URLは文字列で入力してください',
            'image_url.url' => '画像URLは有効なURL形式で入力してください',
            'genres.required' => 'ジャンル名を入力してください',
            'genres.array' => 'ジャンルは配列で入力してください',
            'genres.min' => 'ジャンルは一つ以上選択してください',
            'genres.*.exists' => '指定されたジャンルは存在しません',
        ];
    }
}
