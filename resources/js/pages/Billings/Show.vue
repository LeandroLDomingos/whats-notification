<script setup>
import CrmLayout from '@/layouts/CrmLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const { billing, installments_details } = usePage().props;

const markAsPaid = () => {
    if (confirm('Deseja marcar esta cobrança como totalmente paga?')) {
        router.patch(route('billings.markAsPaid', billing.id), {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <CrmLayout>
        <Head :title="`Cobrança de ${billing.contact.name}`" />
        <div class="px-4">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-2xl font-bold">Detalhes da Cobrança</h1>
                    <p class="text-gray-400">para {{ billing.contact.name }}</p>
                </div>
                <Link :href="route('billings.index')" class="text-cyan-400 hover:underline">
                    &larr; Voltar para a lista
                </Link>
            </div>

            <div class="bg-gray-800 p-6 rounded-lg shadow mb-6">
                <h2 class="text-xl font-semibold mb-4 border-b border-gray-700 pb-2">Resumo</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6 text-sm">
                    <div>
                        <p class="text-gray-400">Valor Total</p>
                        <p class="font-bold text-lg">R$ {{ billing.total_formatted }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Parcelas</p>
                        <p class="font-bold text-lg">{{ billing.installments }}x de R$ {{ installments_details[0]?.value || '0,00' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Status</p>
                        <p class="font-bold text-lg capitalize" :class="billing.status === 'paid' ? 'text-green-400' : 'text-yellow-400'">{{ billing.status === 'paid' ? 'Paga' : 'Pendente' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Dia do Vencimento</p>
                        <p class="font-bold">Todo dia {{ billing.due_day }}</p>
                    </div>
                    <div class="col-span-2 md:col-span-1">
                        <p class="text-gray-400">Notificações</p>
                        <p class="font-bold">
                            {{ billing.notifications_per_installment }}x por parcela,
                            {{ billing.notify_days_before }}
                            <span v-if="billing.notifications_per_installment == 2 && billing.notify_days_before_secondary">
                                e {{ billing.notify_days_before_secondary }}
                            </span>
                             dias antes do venc.
                        </p>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-700 flex justify-end" v-if="billing.status !== 'paid'">
                    <Button @click="markAsPaid" class="bg-green-600 hover:bg-green-700">
                        Marcar Dívida como Paga
                    </Button>
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg shadow overflow-x-auto">
                 <h2 class="text-xl font-semibold p-6">Extrato das Parcelas</h2>
                <table class="w-full whitespace-nowrap">
                    <thead class="text-left font-bold">
                        <tr>
                            <th class="px-6 pb-4">Nº Parcela</th>
                            <th class="px-6 pb-4">Valor (R$)</th>
                            <th class="px-6 pb-4">Data Vencimento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="installment in installments_details" :key="installment.number" class="hover:bg-gray-700">
                            <td class="border-t border-gray-700 px-6 py-4">{{ installment.number }} de {{ billing.installments }}</td>
                            <td class="border-t border-gray-700 px-6 py-4">{{ installment.value }}</td>
                            <td class="border-t border-gray-700 px-6 py-4">{{ installment.due_date }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </CrmLayout>
</template>