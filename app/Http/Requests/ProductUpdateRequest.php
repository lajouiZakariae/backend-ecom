<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductUpdateRequest extends FormRequest
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
            'image' => ['nullabe', 'image', 'max:2048'],

            'name' => ['required', 'min:1', 'max:255'],

            'description' => ['nullable', 'max:1000'],

            'price' => ['nullable', 'numeric', 'min:0'],

            'published_at' => ['nullable', 'date'],

            'category_id' => ['nullable', Rule::exists(Category::class, 'id')],
        ];
    }
}
