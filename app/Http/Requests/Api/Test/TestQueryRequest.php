<?php

namespace App\Http\Requests\Api\Test;

use Illuminate\Foundation\Http\FormRequest;

class TestQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'page' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'order' => ['nullable', 'string'],
            'reverse' => ['nullable', 'string', 'in:asc,desc'],
            'query' => ['nullable', 'string', 'min:1', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'page.integer' => 'Номер страницы должен быть числом',
            'page.min' => 'Номер страницы должен быть не менее 1',
            'limit.integer' => 'Количество элементов на странице должно быть числом',
            'limit.min' => 'Количество элементов на странице должно быть не менее 1',
            'limit.max' => 'Количество элементов на странице должно быть не более 100',
            'order.string' => 'Поле для сортировки должно быть строкой',
            'reverse.string' => 'Направление сортировки должно быть строкой',
            'reverse.in' => 'Направление сортировки должно быть asc или desc',
            'query.string' => 'Строка для поиска должна быть строкой',
            'query.min' => 'Строка для поиска должна быть не менее 1 символа',
            'query.max' => 'Строка для поиска должна быть не более 255 символов',
        ];
    }
}
