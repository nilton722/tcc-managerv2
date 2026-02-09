<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seed do banco de dados...');

        $this->call([
            PermissionSeeder::class,
            InstituicaoSeeder::class,
            DepartamentoSeeder::class,
            UsuarioSeeder::class,
            CursoSeeder::class,
            LinhaPesquisaSeeder::class,
            AlunoSeeder::class,
            OrientadorSeeder::class,
            TipoDocumentoSeeder::class,
            TemplateCronogramaSeeder::class,
        ]);

        $this->command->info('✅ Seed concluído com sucesso!');
        $this->command->info('');
        $this->command->info('📋 Credenciais de acesso:');
        $this->command->info('   Admin:        admin@tccmanager.com / password123');
        $this->command->info('   Coordenador:  coordenador@tccmanager.com / password123');
        $this->command->info('   Orientador:   orientador@tccmanager.com / password123');
        $this->command->info('   Aluno:        aluno@tccmanager.com / password123');
    }
}
