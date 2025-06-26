<script setup>
import CrmLayout from '@/layouts/CrmLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ArrowLeft, Check, CheckCheck, Clock, XCircle, Send, RefreshCw, CircleDollarSign } from 'lucide-vue-next';

// Recebe as props do MessageController@show
const { selectedContact, messages } = usePage().props;

const openMessageId = ref(null);

const toggleMessageActions = (messageId) => {
    if (openMessageId.value === messageId) {
        openMessageId.value = null;
    } else {
        openMessageId.value = messageId;
    }
};

// A função de status agora trata 'cancelled' com um ícone de pagamento.
const messageStatus = computed(() => {
    return (status) => {
        if (status === 'sent') return { icon: CheckCheck, class: 'text-cyan-400' };
        if (status === 'resent') return { icon: CheckCheck, class: 'text-sky-400' };
        if (status === 'pending') return { icon: Clock, class: 'text-gray-500' };
        if (status === 'failed') return { icon: XCircle, class: 'text-red-500' };
        if (status === 'cancelled') return { icon: XCircle, class: 'text-red-500' };
        // Ícone de pagamento para mensagens canceladas
        if (status === 'paid') return { icon: CircleDollarSign, class: 'text-green-500' }; 
        return { icon: Check, class: 'text-gray-500' };
    };
});

const sendNow = (messageId) => {
    router.post(route('messages.sendNow', messageId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            openMessageId.value = null;
        }
    });
};
</script>

<template>
  <CrmLayout>
    <Head :title="`Mensagens de ${selectedContact.name}`" />
    <div class="h-full w-full flex flex-col bg-gray-900 text-white">
        <!-- Cabeçalho do Contato Selecionado -->
        <div class="p-4 border-b border-gray-700 flex items-center bg-gray-800 flex-shrink-0">
           <Link :href="route('messages.index')" class="mr-2 p-2 rounded-full hover:bg-gray-700">
              <ArrowLeft class="w-5 h-5" />
           </Link>
           <img class="w-10 h-10 rounded-full mr-4 object-cover" :src="`https://ui-avatars.com/api/?name=${selectedContact.name}&background=random&color=fff`" alt="Avatar">
           <div>
              <h2 class="text-lg font-bold">{{ selectedContact.name }}</h2>
              <p class="text-sm text-gray-400">{{ selectedContact.phone }}</p>
           </div>
        </div>

        <!-- Área das Mensagens -->
        <div class="flex-grow p-4 sm:p-6 overflow-y-auto bg-black/20">
            <div v-if="$page.props.flash.success" class="bg-green-500/20 text-green-300 text-sm p-3 rounded-lg mb-4 text-center">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="messages && messages.length > 0">
                <div v-for="message in messages" :key="message.id" class="mb-2 flex justify-end">
                    <div class="w-full max-w-lg">
                        <!-- Balão da Mensagem -->
                        <button @click="toggleMessageActions(message.id)" :disabled="message.status === 'cancelled'" class="w-full bg-cyan-900/80 rounded-lg p-3 text-left hover:bg-cyan-900/100 transition-colors duration-200 disabled:opacity-50 disabled:hover:bg-cyan-900/80">
                            <p class="text-white whitespace-pre-wrap">{{ message.message_body }}</p>
                            <!-- LÓGICA DO TIMESTAMP ATUALIZADA -->
                            <div class="text-right text-xs text-gray-400 mt-2 flex items-center justify-end gap-1">
                                <span v-if="message.status === 'resent'">Reenviado em {{ message.sent_at }}</span>
                                <span v-else-if="message.status === 'sent'">Enviado em {{ message.sent_at }}</span>
                                <span v-else-if="message.status === 'pending'">Agendado para {{ message.scheduled_for }}</span>
                                <span v-else-if="message.status === 'cancelled'">Cancelado</span>
                                <span v-else-if="message.status === 'failed'">Falhou</span>
                                <component :is="messageStatus(message.status).icon" :class="messageStatus(message.status).class" class="w-4 h-4" />
                            </div>
                        </button>
                        <!-- Painel de Ações (Accordion) -->
                        <div v-if="openMessageId === message.id" class="mt-1 bg-gray-800 rounded-lg p-2 flex justify-center">
                            <button @click="sendNow(message.id)" class="flex items-center gap-2 text-sm font-semibold text-cyan-300 hover:text-cyan-200 px-4 py-2">
                                <Send v-if="message.status === 'pending'" class="w-4 h-4" />
                                <RefreshCw v-else class="w-4 h-4" />
                                {{ message.status === 'pending' ? 'Enviar Agora' : 'Reenviar Mensagem' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="text-center text-gray-500 mt-8">
                Não há mensagens para este contato.
            </div>
        </div>
    </div>
  </CrmLayout>
</template>
