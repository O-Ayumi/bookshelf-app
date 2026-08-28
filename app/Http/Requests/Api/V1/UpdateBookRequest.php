<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
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
        $rules = (new StoreBookRequest)->rules();

        $bookParam = $this->route('book');
        $bookId = is_object($bookParam) ? $bookParam->id : $bookParam;

        $rules['isbn'] = ['required', 'string', 'max:13', "unique:books,isbn,{$bookId}"];

        return $rules;
    }

    public function messages(): array
    {
        return (new StoreBookRequest)->messages();
    }
}
