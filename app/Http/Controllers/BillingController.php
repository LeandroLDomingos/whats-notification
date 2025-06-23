<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Contact;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class BillingController extends Controller
{
    public function index()
    {
        $billings = Billing::with('contact')
            ->where('status', 'unpaid')
            ->latest()
            ->get()
            ->map(function ($billing) {
                return [
                    'id' => $billing->id,
                    'contact_name' => $billing->contact->name,
                    'total' => number_format($billing->total, 2, ',', '.'),
                    'installments' => $billing->installments,
                    'first_due_date' => $billing->first_due_date->format('d/m/Y'), // Atualizado
                ];
            });

        return Inertia::render('Billings/Index', ['billings' => $billings]);
    }

    public function create()
    {
        return Inertia::render('Billings/Create', [
            'contacts' => Contact::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'total' => 'required|numeric|min:0',
            'installments' => 'required|integer|min:1',
            'first_due_date' => 'required|date',
            'notifications_per_installment' => 'required|integer|in:1,2',
            'notify_days_before' => 'required|integer|min:1',
            'notify_days_before_secondary' => 'nullable|required_if:notifications_per_installment,2|integer|min:1',
        ]);

        Billing::create($request->all());

        return Redirect::route('billings.index')->with('success', 'Cobrança criada com sucesso.');
    }
    public function show(Billing $billing)
    {
        $billing->load('contact');
        $installmentsDetails = [];
        $installmentValue = $billing->installments > 0 ? $billing->total / $billing->installments : 0;

        // Usa a data armazenada como ponto de partida
        $firstDueDate = $billing->first_due_date;

        for ($i = 0; $i < $billing->installments; $i++) {
            $installmentsDetails[] = [
                'number' => $i + 1,
                'value' => number_format($installmentValue, 2, ',', '.'),
                'due_date' => $firstDueDate->copy()->addMonthsNoOverflow($i)->format('d/m/Y'),
            ];
        }

        $billingData = $billing->toArray();
        $billingData['contact'] = $billing->contact;
        $billingData['total_formatted'] = number_format($billing->total, 2, ',', '.');
        $billingData['first_due_date_formatted'] = $billing->first_due_date->format('d/m/Y');


        return Inertia::render('Billings/Show', [
            'billing' => $billingData,
            'installments_details' => $installmentsDetails,
        ]);
    }

    public function edit(Billing $billing)
    {
        return Inertia::render('Billings/Edit', [
            'billing' => $billing,
            'contacts' => Contact::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Billing $billing)
    {
        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'total' => 'required|numeric|min:0',
            'installments' => 'required|integer|min:1',
            'first_due_date' => 'required|date',
            'notifications_per_installment' => 'required|integer|in:1,2',
            'notify_days_before' => 'required|integer|min:1',
            'notify_days_before_secondary' => 'nullable|required_if:notifications_per_installment,2|integer|min:1',
        ]);

        $billing->update($request->all());

        return Redirect::route('billings.index')->with('success', 'Cobrança atualizada com sucesso.');
    }

    public function markAsPaid(Billing $billing)
    {
        $billing->update(['status' => 'paid']);
        return Redirect::route('billings.index')->with('success', 'Cobrança marcada como paga!');
    }

    public function destroy(Billing $billing)
    {
        $billing->delete();

        return Redirect::route('billings.index')->with('success', 'Cobrança excluída com sucesso.');
    }
}