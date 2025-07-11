<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

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
            'title' => ['required', 'string', 'max:100'],
            'start_time' => [
                'required', 
                'date',
                'after_or_equal:now',
                function ($attribute, $value, $fail) {
                    $startTime = Carbon::parse($value);
                    $endTime = Carbon::parse($this->end_time);
                    
                    if ($endTime->diffInMinutes($startTime) < 1) {
                        $fail('O bloco de tempo deve ter pelo menos 1 minuto de duração.');
                    }
                    
                    if ($endTime->diffInHours($startTime) > 24) {
                        $fail('O bloco de tempo não pode ter mais de 24 horas de duração.');
                    }
                },
            ],
            'end_time' => ['required', 'date', 'after:start_time'],
            'color_hex' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'emoji' => ['nullable', 'string', 'max:5'],
        ];
    }
    
    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'title.max' => 'O título não pode ter mais de 100 caracteres.',
            'start_time.required' => 'A data/hora de início é obrigatória.',
            'start_time.date' => 'A data/hora de início deve ser uma data válida.',
            'start_time.after_or_equal' => 'A data/hora de início deve ser igual ou posterior ao horário atual.',
            'end_time.required' => 'A data/hora de término é obrigatória.',
            'end_time.date' => 'A data/hora de término deve ser uma data válida.',
            'end_time.after' => 'A data/hora de término deve ser posterior à data/hora de início.',
            'color_hex.regex' => 'A cor deve estar no formato hexadecimal (ex: #FF5733).',
            'emoji.max' => 'O emoji não pode ter mais de 5 caracteres.',
        ];
    }
    
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Garantir que os campos de data/hora tenham o fuso horário correto
        if ($this->start_time) {
            $this->merge([
                'start_time' => Carbon::parse($this->start_time)->setTimezone(config('app.timezone'))->toDateTimeString(),
            ]);
        }
        
        if ($this->end_time) {
            $this->merge([
                'end_time' => Carbon::parse($this->end_time)->setTimezone(config('app.timezone'))->toDateTimeString(),
            ]);
        }
    }
}
