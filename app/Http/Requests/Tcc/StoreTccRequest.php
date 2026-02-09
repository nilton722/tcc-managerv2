<?php

namespace App\Http\Requests\Tcc;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTccRequest extends FormRequest
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
            'aluno_id' => 'required|uuid|exists:alunos,id',
            'curso_id' => 'required|uuid|exists:cursos,id',
            'linha_pesquisa_id' => 'nullable|uuid|exists:linhas_pesquisa,id',
            'orientador_id' => 'nullable|uuid|exists:orientadores,id',
            'template_cronograma_id' => 'nullable|uuid|exists:templates_cronograma,id',
            
            'titulo' => 'required|string|max:500',
            'titulo_ingles' => 'nullable|string|max:500',
            'tipo_trabalho' => 'required|in:TCC,MONOGRAFIA,DISSERTACAO,TESE',
            
            'resumo' => 'nullable|string',
            'abstract' => 'nullable|string',
            
            'palavras_chave' => 'nullable|array',
            'palavras_chave.*' => 'string|max:100',
            
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'aluno_id.required' => 'O aluno é obrigatório.',
            'aluno_id.exists' => 'Aluno não encontrado.',
            'curso_id.required' => 'O curso é obrigatório.',
            'curso_id.exists' => 'Curso não encontrado.',
            'titulo.required' => 'O título é obrigatório.',
            'titulo.max' => 'O título não pode exceder 500 caracteres.',
            'tipo_trabalho.required' => 'O tipo de trabalho é obrigatório.',
            'tipo_trabalho.in' => 'Tipo de trabalho inválido.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'aluno_id' => 'aluno',
            'curso_id' => 'curso',
            'linha_pesquisa_id' => 'linha de pesquisa',
            'orientador_id' => 'orientador',
            'titulo' => 'título',
            'titulo_ingles' => 'título em inglês',
            'tipo_trabalho' => 'tipo de trabalho',
            'palavras_chave' => 'palavras-chave',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Se o usuário for aluno, usar seu próprio ID
        if ($this->user()->isAluno() && !$this->has('aluno_id')) {
            $this->merge([
                'aluno_id' => $this->user()->aluno->id,
                'curso_id' => $this->user()->aluno->curso_id,
            ]);
        }
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
