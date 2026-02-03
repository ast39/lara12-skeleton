<?php

namespace App\Http\Requests\Api\Test;

use Illuminate\Foundation\Http\FormRequest;

class TestCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:1', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Название является обязательным',
            'title.string' => 'Название должно быть строкой',
            'title.min' => 'Название должно быть не менее 1 символа',
            'title.max' => 'Название должно быть не более 255 символов',
            'description.string' => 'Описание должно быть строкой',
            'description.max' => 'Описание должно быть не более 255 символов',
            'description.nullable' => 'Описание может быть пустым',
        ];
    }
}
