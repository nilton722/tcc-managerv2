<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class OrientadorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Autorização é feita via Policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome_completo' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'bi' => 'required|string|size:14|unique:usuarios,bi',
            'telefone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'departamento_id' => 'required|uuid|exists:departamentos,id',
            'titulacao' => 'required|in:ESPECIALISTA,MESTRE,DOUTOR,POS_DOUTOR',
            'areas_atuacao' => 'required|array|min:1',
            'lattes_url' => 'nullable|url|max:500',
            'orcid' => 'nullable|string|max:50',
            'max_orientandos' => 'nullable|integer|min:1|max:20',
            'aceita_coorientacao' => 'nullable|boolean',
        ];
    }



    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Erro de validação.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
