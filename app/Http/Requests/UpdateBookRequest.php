<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
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
        $book = $this->route('book');

        return [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'published_date' => 'required|date',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url|max:255',
            'isbn' => [
                'nullable',
                'string',
                'max:13',
                Rule::unique('books', 'isbn')->ignore($book),
            ],
            'genres' => 'required|array',
            'genres.*' => 'exists:genres,id',
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
            'isbn.string' => 'ISBNコードは文字列で入力してください',
            'isbn.max' => 'ISBNコードは13桁で入力してください',
            'isbn.unique' => 'このISBNコードは既に登録されています',
            'published_date.required' => '出版日を入力してください',
            'published_date.date' => '出版日は有効な日付形式で入力してください',
            'image_url.max' => '画像URLは255文字以内で入力してください',
            'image_url.url' => '画像URLは有効なURL形式で入力してください',
            'genres.required' => 'ジャンル名を入力してください',
            'genres.array' => 'ジャンル名は配列で入力してください',
            'genres.*.exists' => '指定されたジャンルは存在しません',
        ];
    }
}
