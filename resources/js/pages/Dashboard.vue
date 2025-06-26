<script setup>
import CrmLayout from '@/layouts/CrmLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { DollarSign, Users, TrendingUp, Calendar } from 'lucide-vue-next';

const { stats, upcoming_installments } = usePage().props;
</script>

<template>
  <CrmLayout title="Dashboard">
    <Head title="Dashboard" />
    <div class="space-y-6">
      <!-- Cartões de Estatísticas -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-gray-800 p-6 rounded-lg flex items-center gap-4">
          <div class="p-3 rounded-full bg-green-500/20">
            <DollarSign class="w-6 h-6 text-green-400" />
          </div>
          <div>
            <p class="text-sm text-gray-400">Total a Receber</p>
            <p class="text-2xl font-bold">R$ {{ stats.total_receivable }}</p>
          </div>
        </div>
        <div class="bg-gray-800 p-6 rounded-lg flex items-center gap-4">
          <div class="p-3 rounded-full bg-cyan-500/20">
            <TrendingUp class="w-6 h-6 text-cyan-400" />
          </div>
          <div>
            <p class="text-sm text-gray-400">Receita do Mês</p>
            <p class="text-2xl font-bold">R$ {{ stats.monthly_revenue }}</p>
          </div>
        </div>
        <div class="bg-gray-800 p-6 rounded-lg flex items-center gap-4">
          <div class="p-3 rounded-full bg-yellow-500/20">
            <Users class="w-6 h-6 text-yellow-400" />
          </div>
          <div>
            <p class="text-sm text-gray-400">Clientes Ativos</p>
            <p class="text-2xl font-bold">{{ stats.active_clients }}</p>
          </div>
        </div>
      </div>

      <!-- Próximos Vencimentos -->
      <div class="bg-gray-800 p-6 rounded-lg">
        <h2 class="text-lg font-semibold flex items-center gap-2 mb-4">
          <Calendar class="w-5 h-5 text-gray-400" />
          Próximos Vencimentos
        </h2>
        <div class="space-y-4">
          <div v-if="upcoming_installments.length > 0" v-for="item in upcoming_installments" :key="item.id" class="flex justify-between items-center text-sm">
            <div>
              <p class="font-semibold">{{ item.contact_name }}</p>
              <p class="text-xs text-gray-400">Vence {{ item.due_date }} ({{ item.due_date_full }})</p>
            </div>
            <p class="font-bold text-green-400">R$ {{ item.value }}</p>
          </div>
          <div v-else class="text-center text-gray-500 py-4">
            Nenhuma parcela próxima do vencimento.
          </div>
        </div>
      </div>
    </div>
  </CrmLayout>
</template>
