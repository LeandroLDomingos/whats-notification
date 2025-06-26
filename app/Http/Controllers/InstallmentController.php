<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\ScheduledMessage; // Importar o model
use Carbon\Carbon;
use Redirect;

class InstallmentController extends Controller
{
    public function toggleStatus(Installment $installment)
    {
        // Se a parcela está sendo marcada como PAGA
        if ($installment->status !== 'paid') {
            $installment->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Encontra todas as mensagens PENDENTES para esta parcela e as CANCELA.
            ScheduledMessage::where('installment_id', $installment->id)
                            ->where('status', 'pending')
                            ->update(['status' => 'paid']);
        
        // Se a parcela está sendo revertida para PENDENTE
        } else {
            $installment->update([
                'status' => 'unpaid',
                'paid_at' => null,
            ]);
            // Opcional: Você poderia recriar os jobs aqui se quisesse.
            // Por enquanto, apenas revertemos o status da parcela.
        }

        return Redirect::route('billings.index')->with('success', 'Status da parcela alterado com sucesso!');
    }
}
