<script setup>
import CrmLayout from '@/layouts/CrmLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { CheckCircle, Circle } from 'lucide-vue-next'; // Ícones para o status

// Props recebidas do BillingController
const { billing } = usePage().props;

// Função para alterar o status de uma parcela
const toggleInstallmentStatus = (installmentId) => {
    router.patch(route('installments.toggleStatus', installmentId), {}, {
        preserveScroll: true, // Mantém a posição da página após a atualização
    });
};
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
                        <p class="font-bold text-lg">R$ {{ (billing.total).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Parcelas</p>
                        <p class="font-bold text-lg">{{ billing.number_of_installments }}x</p>
                    </div>
                     <div>
                        <p class="text-gray-400">1º Vencimento</p>
                        <p class="font-bold text-lg">{{ new Date(billing.first_due_date).toLocaleDateString('pt-BR', {timeZone: 'UTC'}) }}</p>
                    </div>
                    <div class="col-span-2 md:col-span-2">
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
            </div>

            <div class="bg-gray-800 rounded-lg shadow overflow-x-auto">
                 <h2 class="text-xl font-semibold p-6">Extrato das Parcelas</h2>
                <table class="w-full whitespace-nowrap">
                    <thead class="text-left font-bold">
                        <tr>
                            <th class="px-6 pb-4">Status</th>
                            <th class="px-6 pb-4">Nº Parcela</th>
                            <th class="px-6 pb-4">Valor (R$)</th>
                            <th class="px-6 pb-4">Vencimento</th>
                            <th class="px-6 pb-4">Pago em</th>
                            <th class="px-6 pb-4">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="installment in billing.installments" :key="installment.id" class="hover:bg-gray-700">
                            <td class="border-t border-gray-700 px-6 py-4">
                                <span :class="installment.status === 'paid' ? 'text-green-400' : 'text-yellow-400'" class="flex items-center gap-2 text-sm font-semibold">
                                    <CheckCircle v-if="installment.status === 'paid'" class="w-4 h-4" />
                                    <Circle v-else class="w-4 h-4" />
                                    {{ installment.status === 'paid' ? 'Paga' : 'Pendente' }}
                                </span>
                            </td>
                            <td class="border-t border-gray-700 px-6 py-4">{{ installment.installment_number }} de {{ billing.number_of_installments }}</td>
                            <td class="border-t border-gray-700 px-6 py-4">{{ (installment.value).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}</td>
                            <td class="border-t border-gray-700 px-6 py-4">{{ new Date(installment.due_date).toLocaleDateString('pt-BR', {timeZone: 'UTC'}) }}</td>
                            <td class="border-t border-gray-700 px-6 py-4">{{ installment.paid_at ? new Date(installment.paid_at).toLocaleString('pt-BR', {timeZone: 'UTC'}) : '-' }}</td>
                            <td class="border-t border-gray-700 px-6 py-4">
                                <button @click="toggleInstallmentStatus(installment.id)" class="text-cyan-400 hover:underline text-sm font-semibold">
                                    {{ installment.status === 'paid' ? 'Marcar como pendente' : 'Marcar como paga' }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </CrmLayout>
</template>