<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes;
    use HasRoles;
    protected $table = 'usuarios';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'instituicao_id',
        'email',
        'password',
        'nome_completo',
        'numero_matricula',
        'telefone',
        'foto_perfil_url',
        'tipo_usuario',
        'status',
        'email_verificado',
        'token_verificacao',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'token_verificacao',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'ultimo_acesso' => 'datetime',
        'email_verificado' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Get the route key name.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    // Relacionamentos
    public function instituicao()
    {
        return $this->belongsTo(Instituicao::class);
    }

    public function aluno()
    {
        return $this->hasOne(Aluno::class);
    }

    public function orientador()
    {
        return $this->hasOne(Orientador::class);
    }

    public function notificacoes()
    {
        return $this->hasMany(Notificacao::class);
    }

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class);
    }

    public function documentosUpload()
    {
        return $this->hasMany(Documento::class, 'upload_por');
    }

    public function membrosBanca()
    {
        return $this->hasMany(MembroBanca::class);
    }

    // Scopes
    public function scopeAtivos($query)
    {
        return $query->where('status', 'ATIVO');
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_usuario', $tipo);
    }

    public function scopePorInstituicao($query, string $instituicaoId)
    {
        return $query->where('instituicao_id', $instituicaoId);
    }

    // Accessors & Mutators
    public function getNomeIniciais(): string
    {
        $nomes = explode(' ', $this->nome_completo);
        return count($nomes) > 1 
            ? $nomes[0] . ' ' . end($nomes)
            : $nomes[0];
    }

    public function isAluno(): bool
    {
        return $this->tipo_usuario === 'ALUNO';
    }

    public function isOrientador(): bool
    {
        return $this->tipo_usuario === 'ORIENTADOR';
    }

    public function isCoordenador(): bool
    {
        return $this->tipo_usuario === 'COORDENADOR';
    }

    public function isAdmin(): bool
    {
        return $this->tipo_usuario === 'ADMIN';
    }

    public function ativar(): void
    {
        $this->update(['status' => 'ATIVO']);
    }

    public function desativar(): void
    {
        $this->update(['status' => 'INATIVO']);
    }

    public function bloquear(): void
    {
        $this->update(['status' => 'BLOQUEADO']);
    }

    public function registrarAcesso(): void
    {
        $this->update(['ultimo_acesso' => now()]);
    }

    public function verificarEmail(): void
    {
        $this->update([
            'email_verificado' => true,
            'email_verified_at' => now(),
            'token_verificacao' => null,
        ]);
    }
}
