<?php

namespace App\Http\Controllers;

use App\Jobs\SendWhatsappNotification;
use App\Models\ScheduledMessage;

class ScheduledMessageController extends Controller
{
    /**
     * Despacha o job para enviar uma mensagem agendada imediatamente,
     * seja pela primeira vez ou como um reenvio.
     *
     * @param  \App\Models\ScheduledMessage  $scheduledMessage
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendNow(ScheduledMessage $scheduledMessage)
    {
        // A verificação de status foi removida para permitir o reenvio.
        // O job será colocado na fila para execução imediata.
        SendWhatsappNotification::dispatch($scheduledMessage);

        $feedbackMessage = $scheduledMessage->status === 'pending'
            ? 'Mensagem colocada na fila para envio imediato!'
            : 'Solicitação de reenvio registrada com sucesso!';

        return back()->with('success', $feedbackMessage);
    }
}
