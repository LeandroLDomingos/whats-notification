<script setup>
import CrmLayout from '@/layouts/CrmLayout.vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
import { DollarSign, Users, TrendingUp, Calendar, History } from 'lucide-vue-next';

// A prop stats agora incluirá total_clients
const { stats, upcoming_installments } = usePage().props;
</script>

<template>
  <CrmLayout title="Dashboard">
    <Head title="Dashboard" />
    <div class="space-y-6">
      <!-- Cartões de Estatísticas - grid ajustado para até 4 colunas -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total a Receber -->
        <div class="bg-gray-800 p-6 rounded-lg flex items-center gap-4">
          <div class="p-3 rounded-full bg-green-500/20">
            <DollarSign class="w-6 h-6 text-green-400" />
          </div>
          <div>
            <p class="text-sm text-gray-400">Total a Receber</p>
            <p class="text-2xl font-bold">R$ {{ stats.total_receivable }}</p>
          </div>
        </div>
        <!-- Receita do Mês (Clicável) -->
        <Link :href="route('dashboard.monthlyRevenue')" class="bg-gray-800 p-6 rounded-lg flex items-center gap-4 hover:bg-gray-700/50 transition-colors duration-200">
          <div class="p-3 rounded-full bg-cyan-500/20">
            <TrendingUp class="w-6 h-6 text-cyan-400" />
          </div>
          <div>
            <p class="text-sm text-gray-400">Receita do Mês</p>
            <p class="text-2xl font-bold">R$ {{ stats.monthly_revenue }}</p>
          </div>
        </Link>
        <!-- Clientes Ativos -->
        <div class="bg-gray-800 p-6 rounded-lg flex items-center gap-4">
          <div class="p-3 rounded-full bg-yellow-500/20">
            <Users class="w-6 h-6 text-yellow-400" />
          </div>
          <div>
            <p class="text-sm text-gray-400">Clientes Ativos</p>
            <p class="text-2xl font-bold">{{ stats.active_clients }}</p>
          </div>
        </div>
        <!-- NOVO CARD: Total de Clientes -->
        <div class="bg-gray-800 p-6 rounded-lg flex items-center gap-4">
          <div class="p-3 rounded-full bg-purple-500/20">
            <Users class="w-6 h-6 text-purple-400" />
          </div>
          <div>
            <p class="text-sm text-gray-400">Total de Clientes</p>
            <p class="text-2xl font-bold">{{ stats.total_clients }}</p>
          </div>
        </div>
      </div>

      <!-- Ações Rápidas -->
      <div class="grid grid-cols-1 gap-6">
         <Link :href="route('billings.history')" class="bg-gray-800 p-4 rounded-lg flex items-center gap-4 hover:bg-gray-700/50 transition-colors duration-200">
            <div class="p-3 rounded-full bg-indigo-500/20">
                <History class="w-6 h-6 text-indigo-400" />
            </div>
            <div>
                <p class="font-semibold">Histórico de Cobranças</p>
                <p class="text-sm text-gray-400">Veja todas as cobranças que já foram quitadas</p>
            </div>
        </Link>
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
