<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
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
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:clients,email,' . $this->client?->id,
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'cpf_nif' => 'nullable|string|max:20',
            'emergency_contact' => 'nullable|string',
            'case_summary' => 'nullable|string',
            'status' => 'nullable|string|in:Active,Inactive',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'O nome completo é obrigatório.',
            'full_name.max' => 'O nome completo não pode ter mais de 255 caracteres.',
            'email.email' => 'O e-mail deve ser um endereço válido.',
            'email.max' => 'O e-mail não pode ter mais de 255 caracteres.',
            'email.unique' => 'Este e-mail já está em uso por outro cliente.',
            'phone.max' => 'O telefone não pode ter mais de 20 caracteres.',
            'birth_date.date' => 'A data de nascimento deve ser uma data válida.',
            'cpf_nif.max' => 'O CPF/NIF não pode ter mais de 20 caracteres.',
            'status.in' => 'O status deve ser "Active" ou "Inactive".',
        ];
    }
}
