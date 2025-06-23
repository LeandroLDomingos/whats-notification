<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;

class InstallmentController extends Controller
{
    public function toggleStatus(Installment $installment)
    {
        if ($installment->status === 'paid') {
            $installment->update([
                'status' => 'unpaid',
                'paid_at' => null,
            ]);
        } else {
            $installment->update([
                'status' => 'paid',
                'paid_at' => Carbon::now(),
            ]);
        }

        // Adiciona uma mensagem de sucesso na sessão antes de redirecionar
        return Redirect::route('billings.index')->with('success', 'Status da parcela alterado com sucesso!');
    }
}