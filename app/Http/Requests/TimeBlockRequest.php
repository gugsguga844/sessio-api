<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TimeBlockRequest extends FormRequest
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
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'color' => 'nullable|string|max:7',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'title.max' => 'O título não pode ter mais de 255 caracteres.',
            'start_time.required' => 'A data/hora de início é obrigatória.',
            'start_time.date' => 'A data/hora de início deve ser uma data válida.',
            'end_time.required' => 'A data/hora de fim é obrigatória.',
            'end_time.date' => 'A data/hora de fim deve ser uma data válida.',
            'end_time.after' => 'A data/hora de fim deve ser posterior à data/hora de início.',
            'color.max' => 'A cor não pode ter mais de 7 caracteres.',
        ];
    }
}
