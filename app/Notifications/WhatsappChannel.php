<?php

namespace App\Notifications;

use App\Services\WhatsappService;
use Illuminate\Notifications\Notification;

class WhatsappChannel
{
    public function send(object $notifiable, Notification $notification)
    {
        $message = $notification->toMessage($notifiable);

        WhatsappService::sendMessage(
            $notifiable->phone,
            $message['message']
        );
    }
}