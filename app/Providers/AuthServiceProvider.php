<?php

namespace App\Providers;

use App\Models\{Tcc, Aluno, Orientador, Documento, Banca, Orientacao};
use App\Policies\{TccPolicy, AlunoPolicy, OrientadorPolicy, DocumentoPolicy, BancaPolicy, OrientacaoPolicy};
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Tcc::class => TccPolicy::class,
        Aluno::class => AlunoPolicy::class,
        Orientador::class => OrientadorPolicy::class,
        Documento::class => DocumentoPolicy::class,
        Banca::class => BancaPolicy::class,
        Orientacao::class => OrientacaoPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */

    public function boot(): void
    {
        $this->registerPolicies();
    }
}