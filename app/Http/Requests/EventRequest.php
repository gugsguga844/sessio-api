<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventRequest extends FormRequest
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
            'client_id' => 'required|exists:clients,id',
            'title' => 'nullable|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'notes' => 'nullable|string',
            'type' => 'required|string|in:presencial,online',
            'payment_status' => 'required|string|in:pago,pendente',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'client_id.required' => 'O cliente é obrigatório.',
            'client_id.exists' => 'O cliente selecionado não existe.',
            'title.max' => 'O título não pode ter mais de 255 caracteres.',
            'start_time.required' => 'A data/hora de início é obrigatória.',
            'start_time.date' => 'A data/hora de início deve ser uma data válida.',
            'end_time.required' => 'A data/hora de fim é obrigatória.',
            'end_time.date' => 'A data/hora de fim deve ser uma data válida.',
            'end_time.after' => 'A data/hora de fim deve ser posterior à data/hora de início.',
            'type.required' => 'O tipo é obrigatório.',
            'type.in' => 'O tipo deve ser "presencial" ou "online".',
            'payment_status.required' => 'O status do pagamento é obrigatório.',
            'payment_status.in' => 'O status do pagamento deve ser "pago" ou "pendente".',
        ];
    }
}
