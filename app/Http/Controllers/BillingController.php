<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Contact;
use App\Models\ScheduledMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use App\Jobs\SendWhatsappNotification;

class BillingController extends Controller
{
    public function index()
    {
        $billings = Billing::whereHas('installments', function ($query) {
            $query->where('status', 'unpaid');
        })
            ->with('contact')
            ->latest()
            ->get()
            ->map(fn($billing) => [
                'id' => $billing->id,
                'contact_name' => $billing->contact->name,
                'total' => number_format($billing->total, 2, ',', '.'),
                'original_total' => number_format($billing->original_total, 2, ',', '.'),
                'installments' => $billing->number_of_installments,
                'first_due_date' => $billing->first_due_date->format('d/m/Y'),
            ]);

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
        $validated = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'total' => 'required|numeric|min:0.01', // esse é o valor original
            'number_of_installments' => 'required|integer|min:1',
            'first_due_date' => 'required|date',
            'notifications_per_installment' => 'required|integer|in:1,2',
            'notify_days_before' => 'required|integer|min:1',
            'notify_days_before_secondary' => 'nullable|required_if:notifications_per_installment,2|integer|min:1',
        ]);

        DB::transaction(function () use ($validated) {
            $originalTotal = $validated['total'];
            $totalWithInterest = $originalTotal * 1.3;

            $billing = Billing::create([
                'contact_id' => $validated['contact_id'],
                'original_total' => $originalTotal,
                'total' => $totalWithInterest,
                'number_of_installments' => $validated['number_of_installments'],
                'first_due_date' => $validated['first_due_date'],
                'notifications_per_installment' => $validated['notifications_per_installment'],
                'notify_days_before' => $validated['notify_days_before'],
                'notify_days_before_secondary' => $validated['notify_days_before_secondary'],
            ]);

            $this->createInstallmentsForBilling($billing);
        });

        return Redirect::route('billings.index')->with('success', 'Cobrança criada com sucesso.');
    }

    public function show(Billing $billing)
    {
        $billing->load('contact', 'installments');
        return Inertia::render('Billings/Show', [
            'billing' => $billing,
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
            $originalTotal = $validated['total'];
            $totalWithInterest = $originalTotal * 1.3;

            $billing->update([
                'contact_id' => $validated['contact_id'],
                'original_total' => $originalTotal,
                'total' => $totalWithInterest,
                'number_of_installments' => $validated['number_of_installments'],
                'first_due_date' => $validated['first_due_date'],
                'notifications_per_installment' => $validated['notifications_per_installment'],
                'notify_days_before' => $validated['notify_days_before'],
                'notify_days_before_secondary' => $validated['notify_days_before_secondary'],
            ]);

            $billing->installments()->delete();
            $this->createInstallmentsForBilling($billing->fresh());
        });

        return Redirect::route('billings.show', $billing->id)->with('success', 'Cobrança atualizada com sucesso.');
    }

    public function destroy(Billing $billing)
    {
        $billing->delete();
        return Redirect::route('billings.index')->with('success', 'Cobrança excluída com sucesso.');
    }

    private function createInstallmentsForBilling(Billing $billing)
    {
        $contact = $billing->contact;
        $installmentValue = number_format($billing->total / $billing->number_of_installments, 2, ',', '.');
        $notificationTime = now()->addMinutes(10);

        for ($i = 0; $i < $billing->number_of_installments; $i++) {
            $dueDate = $billing->first_due_date->copy()->addMonthsNoOverflow($i);

            $installment = $billing->installments()->create([
                'installment_number' => $i + 1,
                'value' => $billing->total / $billing->number_of_installments,
                'due_date' => $dueDate,
                'status' => 'unpaid',
            ]);

            // Primeira notificação
            $notificationDate1 = $dueDate->copy()->subDays($billing->notify_days_before);
            $finalDispatchDateTime1 = $notificationDate1->setTime(
                $notificationTime->hour,
                $notificationTime->minute,
                $notificationTime->second
            );

            if ($finalDispatchDateTime1->isFuture()) {
                $message1 = "Olá {$contact->name}. Lembrete da sua parcela nº {$installment->installment_number} no valor de R$ {$installmentValue}, que vence em {$dueDate->format('d/m/Y')}.";

                $scheduledMessage = ScheduledMessage::create([
                    'contact_id' => $contact->id,
                    'installment_id' => $installment->id,
                    'message_body' => $message1,
                    'scheduled_for' => $finalDispatchDateTime1,
                ]);

                SendWhatsappNotification::dispatch($scheduledMessage)->delay($finalDispatchDateTime1);
            }

            // Segunda notificação, se houver
            if ($billing->notifications_per_installment == 2 && $billing->notify_days_before_secondary) {
                $notificationDate2 = $dueDate->copy()->subDays($billing->notify_days_before_secondary);
                $finalDispatchDateTime2 = $notificationDate2->setTime(
                    $notificationTime->hour,
                    $notificationTime->minute,
                    $notificationTime->second
                );

                if ($finalDispatchDateTime2->isFuture()) {
                    $message2 = "Atenção, {$contact->name}! Faltam poucos dias para o vencimento da sua parcela nº {$installment->installment_number} no valor de R$ {$installmentValue} (venc. {$dueDate->format('d/m/Y')}).";

                    $scheduledMessage2 = ScheduledMessage::create([
                        'contact_id' => $contact->id,
                        'installment_id' => $installment->id,
                        'message_body' => $message2,
                        'scheduled_for' => $finalDispatchDateTime2,
                    ]);

                    SendWhatsappNotification::dispatch($scheduledMessage2)->delay($finalDispatchDateTime2);
                }
            }
        }
    }

    public function history()
    {
        $paidBillings = Billing::whereDoesntHave('installments', function ($query) {
            $query->where('status', 'unpaid');
        })
            ->with('contact')
            ->latest('updated_at')
            ->paginate(15);

        return Inertia::render('Billings/History', [
            'billings' => $paidBillings,
        ]);
    }
}
