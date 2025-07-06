<?php

namespace App\Http\Controllers;

use App\Jobs\SendWhatsappNotification;
use App\Models\Installment;
use App\Models\ScheduledMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Redirect;// 1. Importar a classe Request

class InstallmentController extends Controller
{
    public function toggleStatus(Request $request, Installment $installment)
    {
        if ($installment->status !== 'paid') {
            $validated = $request->validate([
                'paid_at' => 'required|date',
            ]);

            $installment->update([
                'status' => 'paid',
                'paid_at' => Carbon::parse($validated['paid_at']),
            ]);

            // Cancela mensagens pendentes
            ScheduledMessage::where('installment_id', $installment->id)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            // Envia mensagem de confirmação de pagamento
            $contact = $installment->billing->contact;
            $installmentValue = number_format($installment->value, 2, ',', '.');
            $message = "Olá {$contact->name}, confirmamos o recebimento do pagamento da sua parcela nº {$installment->installment_number} no valor de R$ {$installmentValue}. Obrigado!";

            $scheduled = ScheduledMessage::create([
                'contact_id' => $contact->id,
                'installment_id' => $installment->id,
                'message_body' => $message,
                'scheduled_for' => now()->addSeconds(5),
            ]);

            SendWhatsappNotification::dispatch($scheduled)->delay(now()->addSeconds(5));

            // ✅ Verifica se TODAS as parcelas da cobrança foram pagas
            $billing = $installment->billing;
            $allPaid = $billing->installments()->where('status', '!=', 'paid')->doesntExist();

            if ($allPaid) {
                $limiteMsg = "Olá {$contact->name}, todas as suas parcelas foram pagas com sucesso. Seu limite está liberado!";

                $finalMsg = ScheduledMessage::create([
                    'contact_id' => $contact->id,
                    'installment_id' => null, // mensagem geral
                    'message_body' => $limiteMsg,
                    'scheduled_for' => now()->addDays(3),
                ]);

                SendWhatsappNotification::dispatch($finalMsg)->delay(now()->addDays(3));
            }

        } else {
            // Reverte para pendente
            $installment->update([
                'status' => 'unpaid',
                'paid_at' => null,
            ]);
        }

        return Redirect::route('billings.index')->with('success', 'Status da parcela alterado com sucesso!');
    }

}