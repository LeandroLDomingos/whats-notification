<?php
// Arquivo: app/Services/WhatsappService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response; // Importe a classe Response

class WhatsappService
{
    /**
     * Envia uma mensagem de texto usando a Evolution API.
     *
     * @param string $number O número do destinatário.
     * @param string $message A mensagem a ser enviada.
     * @return Response A resposta da API.
     */
    public static function sendMessage(string $number, string $message): Response
    {
        $apiUrl = config('services.whatsapp.url');
        $instance = config('services.whatsapp.instance');
        $apiKey = config('services.whatsapp.apikey');

        // Monta a URL completa da API
        $requestUrl = "{$apiUrl}/message/sendText/{$instance}";

        // Monta o corpo da requisição (payload) conforme a documentação
        $payload = [
            'number' => $number,
            'text' => $message,
        ];

        // Realiza a chamada HTTP POST com os cabeçalhos e o corpo corretos
        return Http::withHeaders([
            'apikey' => $apiKey,
        ])->post($requestUrl, $payload);
    }
}