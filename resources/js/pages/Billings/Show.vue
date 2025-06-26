<script setup>
import CrmLayout from '@/layouts/CrmLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { CheckCircle, Circle, ArrowLeft } from 'lucide-vue-next';

// Recebe as props (billing) do BillingController
const { billing } = usePage().props;

// --- LÓGICA PARA O FORMULÁRIO DE PAGAMENTO ---
const paymentFormVisibleFor = ref(null); // Guarda o ID da parcela que está com o formulário aberto
const paymentDate = ref(new Date().toISOString().slice(0, 10)); // Data para o input, inicia com hoje

// Abre o formulário para uma parcela específica
const showPaymentForm = (installmentId) => {
    paymentFormVisibleFor.value = installmentId;
    paymentDate.value = new Date().toISOString().slice(0, 10); // Reinicia a data para hoje
};

// Cancela e fecha o formulário
const cancelPayment = () => {
    paymentFormVisibleFor.value = null;
};

// Confirma o pagamento e envia os dados
const confirmPayment = (installmentId) => {
    router.patch(route('installments.toggleStatus', installmentId), {
        paid_at: paymentDate.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            paymentFormVisibleFor.value = null; // Fecha o formulário em caso de sucesso
        }
    });
};

// Função para desmarcar uma parcela paga
const unpayInstallment = (installmentId) => {
     router.patch(route('installments.toggleStatus', installmentId), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <CrmLayout>
        <Head :title="`Detalhes da Cobrança - ${billing?.contact?.name || 'Carregando...'}`" />
        
        <!-- Wrapper principal com verificação v-if para garantir que 'billing' existe -->
        <div v-if="billing" class="px-4 py-4">
            <!-- Cabeçalho -->
            <div class="flex items-center mb-6">
                 <Link :href="route('billings.index')" class="mr-2 p-2 rounded-full hover:bg-gray-700">
                    <ArrowLeft class="w-5 h-5" />
                 </Link>
                <div>
                    <h1 class="text-xl md:text-2xl font-bold">Detalhes da Cobrança</h1>
                    <p class="text-sm text-gray-400">para {{ billing.contact?.name }}</p>
                </div>
            </div>

            <!-- Resumo da Cobrança -->
            <div class="bg-gray-800 p-4 md:p-6 rounded-lg shadow mb-6">
                <h2 class="text-lg md:text-xl font-semibold mb-4 border-b border-gray-700 pb-2">Resumo</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6 text-sm">
                    <div>
                        <p class="text-gray-400">Valor Total</p>
                        <p class="font-bold text-base md:text-lg">R$ {{ (billing.total).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Parcelas</p>
                        <p class="font-bold text-base md:text-lg">{{ billing.number_of_installments }}x de R$ {{ (billing.total / billing.number_of_installments).toLocaleString('pt-BR', {minimumFractionDigits: 2}) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">1º Vencimento</p>
                        <p class="font-bold text-base md:text-lg">{{ new Date(billing.first_due_date).toLocaleDateString('pt-BR', {timeZone: 'UTC'}) }}</p>
                    </div>
                    <div class="col-span-2">
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

            <!-- Detalhes das Parcelas (Interativo) -->
            <div class="bg-gray-800 rounded-lg shadow overflow-x-auto">
                 <h2 class="text-lg md:text-xl font-semibold p-4 md:p-6">Extrato das Parcelas</h2>
                <table class="w-full text-sm">
                    <thead class="text-left font-bold bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Nº</th>
                            <th class="px-4 py-3">Vencimento</th>
                            <th class="px-4 py-3">Pago em</th>
                            <th class="px-4 py-3 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="installment in billing.installments" :key="installment.id" class="border-b border-gray-700 hover:bg-gray-700/50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2 font-semibold">
                                    <template v-if="installment.status === 'paid'">
                                        <CheckCircle class="w-4 h-4 text-green-400" />
                                        <div>
                                            <span class="hidden sm:inline">Paga</span>
                                            <p v-if="new Date(installment.paid_at) > new Date(installment.due_date)" class="text-xs text-orange-400 font-normal">Com atraso</p>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <Circle class="w-4 h-4 text-yellow-400" />
                                        <span class="hidden sm:inline">Pendente</span>
                                    </template>
                                </div>
                            </td>
                            <td class="px-4 py-3">{{ installment.installment_number }}</td>
                            <td class="px-4 py-3">{{ new Date(installment.due_date).toLocaleDateString('pt-BR', {timeZone: 'UTC'}) }}</td>
                            <td class="px-4 py-3">{{ installment.paid_at ? new Date(installment.paid_at).toLocaleDateString('pt-BR', {timeZone: 'UTC'}) : '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <div v-if="paymentFormVisibleFor === installment.id">
                                    <div class="flex justify-end items-center gap-2">
                                        <input type="date" v-model="paymentDate" class="bg-gray-700 border-gray-600 rounded-md py-1 px-2 text-xs">
                                        <button @click="confirmPayment(installment.id)" class="bg-green-600 text-white px-3 py-1 rounded-md text-xs font-semibold hover:bg-green-500">Confirmar</button>
                                        <button @click="cancelPayment" class="bg-gray-600 text-white px-3 py-1 rounded-md text-xs font-semibold hover:bg-gray-500">Cancelar</button>
                                    </div>
                                </div>
                                <div v-else>
                                    <button 
                                      v-if="installment.status === 'paid'" 
                                      @click="unpayInstallment(installment.id)" 
                                      class="text-orange-400 hover:underline font-semibold"
                                    >
                                        Desmarcar
                                    </button>
                                    <button 
                                      v-else 
                                      @click="showPaymentForm(installment.id)" 
                                      class="text-cyan-400 hover:underline font-semibold"
                                    >
                                        Pagar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Ecrã de carregamento para o caso de a prop 'billing' ainda não estar disponível -->
        <div v-else class="flex items-center justify-center h-full text-gray-500">
            <p>A carregar detalhes da cobrança...</p>
        </div>
    </CrmLayout>
</template>
