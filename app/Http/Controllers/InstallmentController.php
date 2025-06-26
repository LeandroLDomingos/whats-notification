<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\ScheduledMessage;
use Carbon\Carbon;
use Illuminate\Http\Request; 
use Redirect;// 1. Importar a classe Request

class InstallmentController extends Controller
{
    // 2. Adicionar Request ao método e validar a data
    public function toggleStatus(Request $request, Installment $installment)
    {
        // Se a parcela está sendo marcada como PAGA
        if ($installment->status !== 'paid') {
            $validated = $request->validate([
                'paid_at' => 'required|date',
            ]);

            $installment->update([
                'status' => 'paid',
                'paid_at' => Carbon::parse($validated['paid_at']), // Usa a data do formulário
            ]);

            // Cancela as mensagens pendentes para esta parcela
            ScheduledMessage::where('installment_id', $installment->id)
                            ->where('status', 'pending')
                            ->update(['status' => 'cancelled']);
        
        // Se a parcela está sendo revertida para PENDENTE
        } else {
            $installment->update([
                'status' => 'unpaid',
                'paid_at' => null,
            ]);
        }

        return Redirect::route('billings.index')->with('success', 'Status da parcela alterado com sucesso!');
    }
}
