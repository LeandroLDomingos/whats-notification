<script setup>
import CrmLayout from '@/layouts/CrmLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue'; // Importar

const { billing, contacts } = usePage().props;

const form = useForm({
    contact_id: billing.contact_id,
    total: billing.total,
    installments: billing.installments,
    due_day: billing.due_day,
    notifications_per_installment: billing.notifications_per_installment,
    notify_days_before: billing.notify_days_before,
    notify_days_before_secondary: billing.notify_days_before_secondary || '',
});

// Propriedade computada para o valor da parcela
const installmentValue = computed(() => {
    const total = parseFloat(form.total);
    const installments = parseInt(form.installments, 10);

    if (total > 0 && installments > 0) {
        const value = total / installments;
        return value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    return '0,00';
});

const update = () => {
    form.put(route('billings.update', billing.id));
};
</script>

<template>
    <CrmLayout>

        <Head title="Editar Cobrança" />
        <div class="px-4">
            <h1 class="text-2xl font-bold mb-4">Editar Cobrança</h1>
            <div class="max-w-3xl bg-gray-800 p-6 rounded-lg shadow">
                <form @submit.prevent="update">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div>
                                <label for="first_due_date" class="block mb-2 text-sm font-medium text-gray-300">Data do
                                    Primeiro Vencimento</label>
                                <input v-model="form.first_due_date" type="date" id="first_due_date"
                                    class="w-full bg-gray-700 border border-gray-600 rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500"
                                    required>
                                <div v-if="form.errors.first_due_date" class="text-red-500 text-sm mt-1">{{
                                    form.errors.first_due_date }}</div>
                            </div>
                            <input v-model="form.installments" type="number" id="installments"
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500"
                                required>
                            <div v-if="form.errors.installments" class="text-red-500 text-sm mt-1">{{
                                form.errors.installments }}</div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </CrmLayout>
</template>