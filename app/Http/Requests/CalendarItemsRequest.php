<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalendarItemsRequest extends FormRequest
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
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'start.required' => 'A data de início é obrigatória.',
            'start.date' => 'A data de início deve ser uma data válida.',
            'end.required' => 'A data de fim é obrigatória.',
            'end.date' => 'A data de fim deve ser uma data válida.',
            'end.after_or_equal' => 'A data de fim deve ser igual ou posterior à data de início.',
        ];
    }
}
