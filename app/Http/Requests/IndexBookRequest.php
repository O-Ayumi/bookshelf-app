<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexBookRequest extends FormRequest
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
        return [
            'keyword' => ['nullable', 'string', 'max:255'],
            'genre' => ['nullable', 'integer', 'exists:genres,id'],
            'sort' => ['nullable', 'string', Rule::in(['latest', 'oldest', 'title', 'rating'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'keyword.max' => 'キーワードは255文字以内で入力してください',
            'genre.exists' => '選択したジャンルが存在しません',
            'sort.in' => '指定されたソート順が正しくありません',
            'page.nteger' => 'ページ番号は整数で指定してください',
        ];
    }
}
