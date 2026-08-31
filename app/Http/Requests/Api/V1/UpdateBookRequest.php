<?php

namespace App\Http\Requests\Api\V1;

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
        $rules = (new StoreBookRequest)->rules();

        $bookParam = $this->route('book') ?? $this->route('books');
        $bookId = is_object($bookParam) ? $bookParam->id : $bookParam;

        if ($bookId) {
            $rules['isbn'] = [
                'required',
                'string',
                'size:13',
                Rule::unique('books', 'isbn')->ignore($bookId),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return (new StoreBookRequest)->messages();
    }
}
