<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
        $rules = (new StoreBookRequest)->rules();

        $bookId = $this->route('book')?->id ?? $this->route('book');

        $rules['isbn'] = ['required', 'string', 'max:13', "unique:books,isbn,{$bookId}"];

        return $rules;
    }

    public function messages(): array
    {
        return (new StoreBookRequest)->messages();
    }
}
