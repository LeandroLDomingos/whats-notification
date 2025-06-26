<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ScheduledMessage;
use Inertia\Inertia;

class MessageController extends Controller
{
    /**
     * Exibe a lista de contatos que têm mensagens.
     * Esta é a versão simplificada que apenas busca as conversas.
     */
    public function index()
    {
        return Inertia::render('Messages/Index', [
            'conversations' => $this->getConversations(),
        ]);
    }

    /**
     * Exibe o histórico de mensagens para um contato específico.
     * Este método é chamado ao clicar em uma conversa.
     */
    public function show($contact)
    {
        $contact = Contact::findOrFail($contact); // Busca o contato pelo ID
        $messages = ScheduledMessage::where('contact_id', $contact->id)
            ->latest('scheduled_for')
            ->get()
            ->map(fn($message) => [
                'id' => $message->id,
                'message_body' => $message->message_body,
                'scheduled_for' => $message->scheduled_for->format('d/m/Y H:i'),
                'status' => $message->status,
                'sent_at' => $message->sent_at?->format('d/m/Y H:i'),
            ]);
        return Inertia::render('Messages/Show', [ // Renderiza uma nova view para os detalhes
            'selectedContact' => $contact->only('id', 'name', 'phone'),
            'messages' => $messages,
        ]);
    }

    /**
     * Função auxiliar para buscar a lista de conversas.
     */
    private function getConversations()
    {
        $contacts = Contact::whereHas('scheduledMessages')
            ->with('latestMessage')
            ->get()
            ->sortByDesc(function ($contact) {
                return $contact->latestMessage?->scheduled_for;
            });

        return $contacts->map(fn($contact) => [
            'id' => $contact->id,
            'name' => $contact->name,
            'latest_message' => $contact->latestMessage?->message_body,
            'latest_message_time' => $contact->latestMessage?->scheduled_for->diffForHumans(),
        ])->values();
    }
}
