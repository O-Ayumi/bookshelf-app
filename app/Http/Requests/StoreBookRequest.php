<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|max:13|unique:books,isbn',
            'published_date' => 'required|date',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|url',
            'genres' => 'required|array',
            'genres.*' => 'exists:genres,id',
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
        ];
    }
}
