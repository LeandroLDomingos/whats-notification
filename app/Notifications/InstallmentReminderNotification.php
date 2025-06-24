<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InstallmentReminderNotification extends Notification
{
    use Queueable;

    protected string $messageBody;

    /**
     * Cria uma nova instância da notificação.
     *
     * @param string $message O corpo da mensagem a ser enviada.
     */
    public function __construct(string $message)
    {
        $this->messageBody = $message;
    }

    /**
     * Obtém os canais de entrega da notificação.
     *
     * @param  object  $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Define que esta notificação deve ser enviada através do seu canal customizado.
        return [WhatsappChannel::class];
    }

    /**
     * Formata a notificação para ser compatível com o seu WhatsappChannel.
     * ESTE É O MÉTODO CORRIGIDO.
     *
     * @param  object  $notifiable
     * @return array<string, mixed>
     */
    public function toMessage(object $notifiable): array
    {
        // Retorna um array com a chave 'message', exatamente como o seu canal espera.
        return [
            'message' => $this->messageBody,
        ];
    }
}
