<?php

namespace App\Http\Requests\Category;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                Rule::unique('categories')->ignore($this->category)
            ],
            'brand' => [
                'nullable',
                \Illuminate\Validation\Rule::in(\App\Models\Category::BRANDS),
            ],
            'slug' => [
                'required',
                'alpha_dash',
                Rule::unique('categories')->ignore($this->category)
            ]
        ];
    }
}
