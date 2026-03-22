<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTravelOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'destination'       =>  ['required', 'string', 'max:255'],
            'departure_date'    =>  ['required', 'date', 'after_or_equal:today'],
            'return_date'       =>  ['required', 'date', 'after_or_equal:departure_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'destination.required' => 'O destino é obrigatório.',
            'destination.string' => 'O destino deve ser um texto.',
            'destination.max' => 'O destino deve ter no máximo 255 caracteres.',

            'departure_date.required' => 'A data de ida é obrigatória.',
            'departure_date.date' => 'A data de ida deve ser uma data válida.',
            'departure_date.after_or_equal' => 'A data de ida não pode ser anterior a hoje.',

            'return_date.required' => 'A data de volta é obrigatória.',
            'return_date.date' => 'A data de volta deve ser uma data válida.',
            'return_date.after_or_equal' => 'A data de volta deve ser igual ou posterior à data de ida.',
        ];
    }
}
