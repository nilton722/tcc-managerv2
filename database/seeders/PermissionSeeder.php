<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Criar Permissões
        $permissions = [
            // TCCs
            'tcc.view',
            'tcc.create',
            'tcc.update',
            'tcc.delete',
            'tcc.submit',
            'tcc.approve',
            'tcc.reject',

            // Orientações
            'orientacao.view',
            'orientacao.create',
            'orientacao.update',
            'orientacao.delete',

            // Documentos
            'documento.view',
            'documento.upload',
            'documento.download',
            'documento.approve',
            'documento.reject',
            'documento.delete',

            // Bancas
            'banca.view',
            'banca.create',
            'banca.update',
            'banca.delete',
            'banca.schedule',
            'banca.evaluate',

            // Usuários
            'usuario.view',
            'usuario.create',
            'usuario.update',
            'usuario.delete',
            'usuario.block',

            // Cursos
            'curso.view',
            'curso.create',
            'curso.update',
            'curso.delete',

            // Relatórios
            'relatorio.view',
            'relatorio.export',

            // Configurações
            'config.view',
            'config.update',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Criar Roles e atribuir permissões

        // ADMIN - Todas as permissões
        $roleAdmin = Role::create(['name' => 'ADMIN']);
        $roleAdmin->givePermissionTo(Permission::all());

        // COORDENADOR
        $roleCoordenador = Role::create(['name' => 'COORDENADOR']);
        $roleCoordenador->givePermissionTo([
            'tcc.view',
            'tcc.approve',
            'tcc.reject',
            'orientacao.view',
            'orientacao.create',
            'orientacao.delete',
            'documento.view',
            'documento.download',
            'documento.approve',
            'documento.reject',
            'banca.view',
            'banca.create',
            'banca.update',
            'banca.schedule',
            'usuario.view',
            'curso.view',
            'curso.update',
            'relatorio.view',
            'relatorio.export',
        ]);

        // ORIENTADOR
        $roleOrientador = Role::create(['name' => 'ORIENTADOR']);
        $roleOrientador->givePermissionTo([
            'tcc.view',
            'tcc.update',
            'tcc.submit',
            'orientacao.view',
            'documento.view',
            'documento.upload',
            'documento.download',
            'documento.approve',
            'documento.reject',
            'banca.view',
            'banca.schedule',
            'banca.evaluate',
            'usuario.view',
            'curso.view',
        ]);

        // ALUNO
        $roleAluno = Role::create(['name' => 'ALUNO']);
        $roleAluno->givePermissionTo([
            'tcc.view',
            'tcc.create',
            'tcc.update',
            'tcc.submit',
            'orientacao.view',
            'documento.view',
            'documento.upload',
            'documento.download',
            'banca.view',
            'curso.view',
        ]);

        $this->command->info('Permissões e roles criados com sucesso!');
    }
}
