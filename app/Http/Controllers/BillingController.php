<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Carbon\Carbon;

class BillingController extends Controller
{
    /**
     * Exibe uma lista das cobranças que possuem parcelas pendentes.
     */
    public function index()
    {
        $billings = Billing::whereHas('installments', function ($query) {
            $query->where('status', 'unpaid');
        })
        ->with('contact')
        ->latest()
        ->get()
        ->map(fn ($billing) => [
            'id' => $billing->id,
            'contact_name' => $billing->contact->name,
            'total' => number_format($billing->total, 2, ',', '.'),
            'installments' => $billing->number_of_installments,
            'first_due_date' => $billing->first_due_date->format('d/m/Y'),
        ]);

        return Inertia::render('Billings/Index', ['billings' => $billings]);
    }

    /**
     * Mostra o formulário para criar uma nova cobrança.
     */
    public function create()
    {
        return Inertia::render('Billings/Create', [
            'contacts' => Contact::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Salva uma nova cobrança e suas respectivas parcelas no banco de dados.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'total' => 'required|numeric|min:0.01',
            'number_of_installments' => 'required|integer|min:1',
            'first_due_date' => 'required|date',
            'notifications_per_installment' => 'required|integer|in:1,2',
            'notify_days_before' => 'required|integer|min:1',
            'notify_days_before_secondary' => 'nullable|required_if:notifications_per_installment,2|integer|min:1',
        ]);

        DB::transaction(function () use ($validated) {
            $billing = Billing::create($validated);
            $this->createInstallmentsForBilling($billing);
        });

        return Redirect::route('billings.index')->with('success', 'Cobrança criada com sucesso.');
    }

    /**
     * Exibe os detalhes de uma cobrança específica, incluindo todas as suas parcelas.
     */
    public function show(Billing $billing)
    {
        $billing->load('contact', 'installments');
        return Inertia::render('Billings/Show', [
            'billing' => $billing,
        ]);
    }

    /**
     * Mostra o formulário para editar uma cobrança existente.
     */
    public function edit(Billing $billing)
    {
        return Inertia::render('Billings/Edit', [
            'billing' => $billing,
            'contacts' => Contact::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Atualiza uma cobrança e recria suas parcelas caso dados essenciais tenham sido alterados.
     */
    public function update(Request $request, Billing $billing)
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'total' => 'required|numeric|min:0.01',
            'number_of_installments' => 'required|integer|min:1',
            'first_due_date' => 'required|date',
            'notifications_per_installment' => 'required|integer|in:1,2',
            'notify_days_before' => 'required|integer|min:1',
            'notify_days_before_secondary' => 'nullable|required_if:notifications_per_installment,2|integer|min:1',
        ]);

        DB::transaction(function () use ($validated, $billing) {
            $billing->update($validated);
            
            // Apaga as parcelas antigas e recria com os novos dados
            $billing->installments()->delete();
            $this->createInstallmentsForBilling($billing->fresh());
        });

        return Redirect::route('billings.show', $billing->id)->with('success', 'Cobrança atualizada com sucesso.');
    }

    /**
     * Exclui uma cobrança e todas as suas parcelas associadas.
     */
    public function destroy(Billing $billing)
    {
        $billing->delete();
        return Redirect::route('billings.index')->with('success', 'Cobrança excluída com sucesso.');
    }

    /**
     * Método privado para criar as parcelas de uma cobrança.
     */
    private function createInstallmentsForBilling(Billing $billing)
    {
        $installmentValue = $billing->total / $billing->number_of_installments;

        for ($i = 0; $i < $billing->number_of_installments; $i++) {
            $billing->installments()->create([
                'installment_number' => $i + 1,
                'value' => $installmentValue,
                'due_date' => $billing->first_due_date->copy()->addMonthsNoOverflow($i),
                'status' => 'unpaid',
            ]);
        }
    }
}