<script setup>
import CrmLayout from '@/layouts/CrmLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const { paid_installments, current_month_name } = usePage().props;
</script>

<template>
    <CrmLayout>
        <Head :title="`Faturamento de ${current_month_name}`" />
        <div class="px-4 py-4">
            <div class="flex items-center mb-6">
                 <Link :href="route('dashboard')" class="mr-2 p-2 rounded-full hover:bg-gray-700">
                    <ArrowLeft class="w-5 h-5" />
                 </Link>
                <div>
                    <h1 class="text-xl md:text-2xl font-bold">Faturamento de {{ current_month_name }}</h1>
                    <p class="text-sm text-gray-400">Lista de todos os pagamentos recebidos este mês.</p>
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg shadow overflow-x-auto">
                <table class="w-full whitespace-nowrap text-sm">
                    <thead class="text-left font-bold bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3">Contacto</th>
                            <th class="px-6 py-3">Valor Pago (R$)</th>
                            <th class="px-6 py-3">Data do Pagamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="installment in paid_installments" :key="installment.id" class="border-b border-gray-700">
                            <td class="px-6 py-4 font-semibold">{{ installment.contact_name }}</td>
                            <td class="px-6 py-4 text-green-400 font-semibold">{{ installment.value }}</td>
                            <td class="px-6 py-4">{{ installment.paid_at }}</td>
                        </tr>
                        <tr v-if="paid_installments.length === 0">
                            <td class="px-6 py-4 text-center text-gray-500" colspan="3">
                                Nenhum pagamento recebido este mês.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </CrmLayout>
</template>
