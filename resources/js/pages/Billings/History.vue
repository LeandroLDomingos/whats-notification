<script setup>
import CrmLayout from '@/layouts/CrmLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const { billings } = usePage().props;

/**
 * Função que utiliza a API de histórico do navegador para voltar à página anterior.
 */
const goBack = () => {
  window.history.back();
};
</script>

<template>
  <CrmLayout>
    <Head title="Histórico de Cobranças" />
    <div class="px-4 py-4">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl md:text-2xl font-bold">Cobranças Quitadas</h1>
        
        <!-- BOTÃO CORRIGIDO -->
        <button @click="goBack" class="flex items-center gap-2 text-cyan-400 hover:underline font-semibold">
          <ArrowLeft class="w-4 h-4" />
          Voltar
        </button>

      </div>
       <div class="bg-gray-800 rounded-lg shadow overflow-x-auto">
        <table class="w-full whitespace-nowrap text-sm">
          <thead class="text-left font-bold bg-gray-700/50">
            <tr>
              <th class="px-6 py-3">Contato</th>
              <th class="px-6 py-3">Total (R$)</th>
              <th class="px-6 py-3">Parcelas</th>
              <th class="px-6 py-3 text-right">Ação</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="billing in billings.data" :key="billing.id" class="border-b border-gray-700 hover:bg-gray-700/50">
              <td class="px-6 py-4 font-semibold">{{ billing.contact.name }}</td>
              <td class="px-6 py-4">R$ {{ (billing.total).toLocaleString('pt-BR', {minimumFractionDigits: 2}) }}</td>
              <td class="px-6 py-4">{{ billing.number_of_installments }}x</td>
              <td class="px-6 py-4 text-right">
                <Link :href="route('billings.show', billing.id)" class="text-green-400 hover:underline font-semibold">Ver Histórico</Link>
              </td>
            </tr>
             <tr v-if="billings.data.length === 0">
              <td class="px-6 py-4 text-center" colspan="4">Nenhuma cobrança quitada encontrada.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </CrmLayout>
</template>
