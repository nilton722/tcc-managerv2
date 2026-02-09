<?php

namespace App\Http\Requests\Tcc;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTccRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'linha_pesquisa_id' => 'sometimes|nullable|uuid|exists:linhas_pesquisa,id',
            'titulo' => 'sometimes|string|max:500',
            'titulo_ingles' => 'nullable|string|max:500',
            'tipo_trabalho' => 'sometimes|in:TCC,MONOGRAFIA,DISSERTACAO,TESE',
            'resumo' => 'nullable|string',
            'abstract' => 'nullable|string',
            'palavras_chave' => 'nullable|array',
            'keywords' => 'nullable|array',
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
