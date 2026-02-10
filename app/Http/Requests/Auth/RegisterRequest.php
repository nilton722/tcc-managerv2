<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'instituicao_id' => 'required|uuid|exists:instituicoes,id',
            'nome_completo' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'bi' => 'required|string|size:14|unique:usuarios,bi',
            'telefone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'tipo_usuario' => 'required|in:ALUNO,ORIENTADOR',
            'curso_id' => 'required_if:tipo_usuario,ALUNO|uuid|exists:cursos,id',
            'matricula' => 'required_if:tipo_usuario,ALUNO|string|unique:alunos,matricula',
        ];
    }

    public function messages(): array
    {
        return [
            // Instituição
            'instituicao_id.required' => 'A instituição é obrigatória.',
            'instituicao_id.uuid' => 'O identificador da instituição é inválido.',
            'instituicao_id.exists' => 'A instituição selecionada não existe.',

            // Nome
            'nome_completo.required' => 'O nome completo é obrigatório.',
            'nome_completo.string' => 'O nome completo deve ser um texto válido.',
            'nome_completo.max' => 'O nome completo não pode ter mais de 255 caracteres.',

            // Email
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',

            // Número de matrícula (usuário)
            'bi.required' => 'O número de matrícula é obrigatório.',
            'bi.string' => 'O número de matrícula deve ser um texto.',
            'bi.size' => 'O número de matrícula deve conter exatamente 14 caracteres.',
            'bi.unique' => 'Este número de matrícula já está cadastrado.',

            // Telefone
            'telefone.string' => 'O telefone deve ser um texto válido.',
            'telefone.max' => 'O telefone não pode ter mais de 20 caracteres.',

            // Senha
            'password.required' => 'A senha é obrigatória.',
            'password.string' => 'A senha deve ser um texto válido.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',

            // Tipo de usuário
            'tipo_usuario.required' => 'O tipo de usuário é obrigatório.',
            'tipo_usuario.in' => 'O tipo de usuário selecionado é inválido.',

            // Curso (somente ALUNO)
            'curso_id.required_if' => 'O curso é obrigatório para usuários do tipo aluno.',
            'curso_id.uuid' => 'O identificador do curso é inválido.',
            'curso_id.exists' => 'O curso selecionado não existe.',

            // Matrícula do aluno
            'matricula.required_if' => 'A matrícula é obrigatória para usuários do tipo aluno.',
            'matricula.string' => 'A matrícula deve ser um texto válido.',
            'matricula.unique' => 'Esta matrícula já está cadastrada para outro aluno.',
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



