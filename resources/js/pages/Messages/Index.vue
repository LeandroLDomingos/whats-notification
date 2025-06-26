<script setup>
import CrmLayout from '@/layouts/CrmLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

// Recebe a lista de conversas do MessageController@index
const { conversations } = usePage().props;
</script>

<template>
  <CrmLayout>
    <Head title="Mensagens" />
    <!-- O componente é uma única coluna que ocupa todo o espaço -->
    <div class="h-full w-full flex flex-col bg-gray-900 text-white">
      
      <!-- Cabeçalho -->
      <div class="p-4 border-b border-gray-700 flex-shrink-0">
          <h1 class="text-xl font-bold">Conversas</h1>
      </div>

      <!-- Lista de Conversas -->
      <div class="flex-grow overflow-y-auto">
        <!-- Cada item é um link para a tela de detalhes (Show.vue) -->
        <Link v-for="convo in conversations" :key="convo.id" :href="route('messages.show', convo.id)"
          class="flex items-center p-4 border-b border-gray-800 hover:bg-gray-700/50 transition-colors duration-200">
          <img class="w-12 h-12 rounded-full mr-4 object-cover flex-shrink-0" :src="`https://ui-avatars.com/api/?name=${convo.name}&background=random&color=fff`" alt="Avatar">
          <div class="flex-grow overflow-hidden">
            <div class="flex justify-between items-center">
              <h3 class="font-bold truncate">{{ convo.name }}</h3>
              <p class="text-xs text-gray-400 flex-shrink-0 ml-2">{{ convo.latest_message_time }}</p>
            </div>
            <p class="text-sm text-gray-400 truncate">{{ convo.latest_message }}</p>
          </div>
        </Link>
        <div v-if="!conversations || conversations.length === 0" class="p-4 text-center text-gray-500">
          Nenhuma conversa encontrada.
        </div>
      </div>

    </div>
  </CrmLayout>
</template>
