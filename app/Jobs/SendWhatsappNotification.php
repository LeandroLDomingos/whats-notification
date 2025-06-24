<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Notifications\InstallmentReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsappNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Contact $contact;
    protected string $message;

    /**
     * O Job recebe o contato e a mensagem a ser enviada.
     */
    public function __construct(Contact $contact, string $message)
    {
        $this->contact = $contact;
        $this->message = $message;
    }

    /**
     * Quando o Job for executado, ele envia a notificação para o contato.
     */
    public function handle(): void
    {
        $this->contact->notify(new InstallmentReminderNotification($this->message));
    }
}