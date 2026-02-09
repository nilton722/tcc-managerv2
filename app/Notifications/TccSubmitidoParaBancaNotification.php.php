<?php

namespace App\Notifications;

use App\Models\Tcc;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TccSubmitidoParaBancaNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Tcc $tcc
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('TCC Submetido para Banca')
            ->greeting('Olá, ' . $notifiable->nome_completo)
            ->line('Um novo TCC foi submetido para avaliação de banca.')
            ->line('Título: ' . $this->tcc->titulo)
            ->line('Aluno: ' . $this->tcc->aluno->usuario->nome_completo)
            ->line('Curso: ' . $this->tcc->curso->nome)
            ->action('Visualizar TCC', url('/tccs/' . $this->tcc->id))
            ->line('Por favor, revise o trabalho e aprove ou solicite correções.');
    }

    public function toArray($notifiable): array
    {
        return [
            'tcc_id' => $this->tcc->id,
            'titulo' => $this->tcc->titulo,
            'aluno' => $this->tcc->aluno->usuario->nome_completo,
            'curso' => $this->tcc->curso->nome,
            'tipo' => 'TCC_SUBMETIDO',
        ];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'tipo' => 'APROVACAO',
            'titulo' => 'TCC Submetido para Banca',
            'mensagem' => 'O TCC "' . $this->tcc->titulo . '" foi submetido para avaliação.',
            'link_referencia' => '/tccs/' . $this->tcc->id,
            'entidade_tipo' => 'Tcc',
            'entidade_id' => $this->tcc->id,
        ];
    }
}

