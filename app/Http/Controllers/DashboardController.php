<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Installment;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total a receber de todas as parcelas pendentes
        $totalReceivable = Installment::where('status', 'unpaid')->sum('value');

        // 2. Receita recebida no mês atual
        $monthlyRevenue = Installment::where('status', 'paid')
            ->whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month)
            ->sum('value');

        // 3. Contagem de clientes com parcelas pendentes
        $activeClientsCount = Contact::whereHas('billings.installments', function ($query) {
            $query->where('status', 'unpaid');
        })->count();
        
        // NOVO: Contagem total de clientes
        $totalClientsCount = Contact::count();

        // 4. Próximas 5 parcelas a vencer
        $upcomingInstallments = Installment::with('billing.contact')
            ->where('status', 'unpaid')
            ->where('due_date', '>=', now())
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get()
            ->map(fn ($installment) => [
                'contact_name' => $installment->billing->contact->name,
                'value' => number_format($installment->value, 2, ',', '.'),
                'due_date' => Carbon::parse($installment->due_date)->diffForHumans(),
                'due_date_full' => Carbon::parse($installment->due_date)->format('d/m/Y'),
            ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_receivable' => number_format($totalReceivable, 2, ',', '.'),
                'monthly_revenue' => number_format($monthlyRevenue, 2, ',', '.'),
                'active_clients' => $activeClientsCount,
                'total_clients' => $totalClientsCount, // Envia a nova estatística para a view
            ],
            'upcoming_installments' => $upcomingInstallments,
        ]);
    }

    /**
     * Mostra um relatório detalhado do faturamento do mês corrente.
     */
    public function monthlyRevenueReport()
    {
        $paidInstallments = Installment::where('status', 'paid')
            ->whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month)
            ->with('billing.contact')
            ->orderBy('paid_at', 'desc')
            ->get()
            ->map(fn ($installment) => [
                'id' => $installment->id,
                'contact_name' => $installment->billing->contact->name,
                'value' => number_format($installment->value, 2, ',', '.'),
                'paid_at' => Carbon::parse($installment->paid_at)->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Dashboard/MonthlyRevenueReport', [
            'paid_installments' => $paidInstallments,
            'current_month_name' => now()->translatedFormat('F'),
        ]);
    }
}
