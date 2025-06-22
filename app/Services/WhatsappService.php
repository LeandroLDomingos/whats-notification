<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsappService
{
    public static function sendMessage(string $number, string $message): void
    {
        return Http::post(config('services.whatsapp.url'). '/message/sendText/'.config('services.whatsapp.instance'), [
            'number' => $number,
            'text' => $message,
        ])->json();
    }
}