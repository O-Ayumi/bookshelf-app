<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $book = $this->route('book');

        return [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'published_date' => 'required|date',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|url',
            'isbn' => [
                'required',
                'string',
                'max:13',
                Rule::unique('books', 'isbn')->ignore($book),
            ],
            'genres' => 'required|array',
            'genres,*' => 'exists:genres,id',
            'book_id' => 'required|exists:books,id',
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
            'genres.required' => 'ジャンル名を入力してください',
            'genres.*.exists' => '指定されたジャンルは存在しません',
            'book_id.exists' => '指定された書籍が見つかりません',
        ];
    }
}
