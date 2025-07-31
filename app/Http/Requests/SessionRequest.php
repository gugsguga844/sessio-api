<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SessionRequest extends FormRequest
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
            'client_id' => 'sometimes|exists:clients,id', // compatibilidade
            'client_ids' => 'sometimes|array',
            'client_ids.*' => 'exists:clients,id',
            'start_time' => 'required|date',
            'duration_min' => 'required|integer|min:1|max:1440', // Máximo 24 horas em minutos
            'focus_topic' => 'nullable|string|max:500',
            'session_notes' => 'nullable|string',
            'type' => 'required|string|in:' . \App\Models\Session::TYPE_IN_PERSON . ',' . \App\Models\Session::TYPE_ONLINE,
            'payment_status' => 'required|string|in:' . \App\Models\Session::PAYMENT_PAID . ',' . \App\Models\Session::PAYMENT_PENDING,
            'payment_method' => 'sometimes|nullable|string|max:50',
            'session_status' => 'sometimes|string|in:' . \App\Models\Session::STATUS_SCHEDULED . ',' . \App\Models\Session::STATUS_COMPLETED . ',' . \App\Models\Session::STATUS_CANCELED,
            'meeting_url' => 'sometimes|nullable|url|max:255',
            'price' => 'sometimes|nullable|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Se o usuário enviar um timezone, podemos usá-lo para converter para UTC
        $timezone = $this->input('timezone', config('app.timezone'));
        
        if ($this->has('start_time')) {
            try {
                $startTime = new \DateTime($this->input('start_time'), new \DateTimeZone($timezone));
                $this->merge([
                    'start_time' => $startTime->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
                ]);
            } catch (\Exception $e) {
                // Se houver erro na conversão, mantém o valor original
            }
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'client_id.required' => 'O cliente é obrigatório.',
            'client_id.exists' => 'O cliente selecionado não existe.',
            'start_time.required' => 'A data/hora de início é obrigatória.',
            'start_time.date' => 'A data/hora de início deve ser uma data válida.',
            'duration_min.required' => 'A duração da sessão é obrigatória.',
            'duration_min.integer' => 'A duração deve ser um número inteiro de minutos.',
            'duration_min.min' => 'A duração mínima da sessão é de 1 minuto.',
            'duration_min.max' => 'A duração máxima da sessão é de 1440 minutos (24 horas).',
            'focus_topic.max' => 'O tópico de foco não pode ter mais de 500 caracteres.',
            'type.required' => 'O tipo de sessão é obrigatório.',
            'type.in' => 'O tipo de sessão deve ser "' . \App\Models\Session::TYPE_IN_PERSON . '" ou "' . \App\Models\Session::TYPE_ONLINE . '".',
            'payment_status.required' => 'O status do pagamento é obrigatório.',
            'payment_status.in' => 'O status do pagamento deve ser "' . \App\Models\Session::PAYMENT_PAID . '" ou "' . \App\Models\Session::PAYMENT_PENDING . '".',
            'session_status.in' => 'O status da sessão deve ser "' . \App\Models\Session::STATUS_SCHEDULED . '", "' . \App\Models\Session::STATUS_COMPLETED . '" ou "' . \App\Models\Session::STATUS_CANCELED . '".',
        ];
    }
}
