<?php

namespace App\Jobs;

use App\Models\ScheduledMessage;
use App\Notifications\InstallmentReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendWhatsappNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * O Job guarda a mensagem agendada que precisa ser processada.
     */
    public function __construct(public ScheduledMessage $scheduledMessage)
    {
    }

    /**
     * Executa o Job: envia a notificação e atualiza o status no banco.
     */
    public function handle(): void
    {
        // Verifica se a mensagem foi cancelada
        if ($this->scheduledMessage->status === 'cancelled') {
            return;
        }

        // Se a mensagem foi cancelada antes do envio, o job para aqui.
        if ($this->scheduledMessage->status === 'paid') {
            return;
        }

        // Guarda o status original para decidir se é um envio ou reenvio.
        $originalStatus = $this->scheduledMessage->status;
        $updateData = [];

        try {
            // Tenta enviar a notificação através do contato associado.
            $this->scheduledMessage->contact->notify(new InstallmentReminderNotification($this->scheduledMessage->message_body));

            // Determina o novo status com base no original.
            $updateData['status'] = ($originalStatus === 'pending') ? 'sent' : 'resent';
            $updateData['sent_at'] = now();

            // Se for um reenvio, incrementa o contador.
            if ($originalStatus !== 'pending') {
                $this->scheduledMessage->increment('resent_count');
            }

        } catch (Throwable $e) {
            // Se falhar, atualiza o status para 'failed'.
            $updateData['status'] = 'failed';
            // Opcional: Você pode registrar o erro para depuração futura.
            // \Log::error("Falha ao enviar mensagem ID {$this->scheduledMessage->id}: " . $e->getMessage());
        }

        // Aplica as atualizações no banco de dados.
        $this->scheduledMessage->update($updateData);
    }
}
