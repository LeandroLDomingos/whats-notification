<script setup>
import CrmLayout from '@/layouts/CrmLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import { Search } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogClose,
} from '@/components/ui/dialog';

// Recebe as props, incluindo os filtros de busca
const { contacts, filters } = usePage().props;

// Cria uma referência reativa para o campo de busca
const search = ref(filters.search || '');

/**
 * Observa a variável 'search'. Quando ela muda, espera 300ms (debounce)
 * e depois faz uma nova requisição ao servidor com o novo termo de busca.
 */
watch(search, debounce((value) => {
    router.get(route('contacts.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));

/**
 * Formata o número de telefone para exibição.
 * Ex: 5531999998888 -> (31) 99999-8888
 */
const formatPhone = (phone) => {
  if (!phone) return '-';
  const numericPhone = phone.replace(/\D/g, '');
  
  // Remove o '55' do Brasil se existir
  const numberWithoutCountryCode = numericPhone.startsWith('55') ? numericPhone.substring(2) : numericPhone;

  if (numberWithoutCountryCode.length === 11) {
    return `(${numberWithoutCountryCode.substring(0, 2)}) ${numberWithoutCountryCode.substring(2, 7)}-${numberWithoutCountryCode.substring(7)}`;
  }
  if (numberWithoutCountryCode.length === 10) {
    return `(${numberWithoutCountryCode.substring(0, 2)}) ${numberWithoutCountryCode.substring(2, 6)}-${numberWithoutCountryCode.substring(6)}`;
  }
  return phone; // Retorna o original se não corresponder ao formato esperado
};


// --- Lógica para exclusão ---
const isConfirmingDelete = ref(false);
const contactToDelete = ref(null);

const confirmDelete = (contact) => {
    contactToDelete.value = contact;
    isConfirmingDelete.value = true;
};

const destroy = () => {
    if (contactToDelete.value) {
        router.delete(route('contacts.destroy', contactToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isConfirmingDelete.value = false;
                contactToDelete.value = null;
            }
        });
    }
};
</script>

<template>
  <CrmLayout>
    <Head title="Contatos" />
    <div class="px-4 py-4 flex flex-col h-full">
        <!-- Cabeçalho -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl md:text-2xl font-bold">Contatos</h1>
            <Link :href="route('contacts.create')" class="bg-cyan-500 hover:bg-cyan-600 text-white font-bold py-2 px-4 rounded-lg text-sm">
            Novo Contato
            </Link>
        </div>
        <div v-if="$page.props.flash.success" class="mb-4 p-3 bg-green-500/20 text-green-300 rounded-lg text-center text-sm">
            {{ $page.props.flash.success }}
        </div>

        <!-- Conteúdo Principal -->
        <div class="flex-grow overflow-y-auto">
            <!-- Tabela para Desktop -->
            <div class="hidden sm:block bg-gray-800 rounded-lg shadow overflow-x-auto">
                <table class="w-full whitespace-nowrap text-sm">
                    <thead class="text-left font-bold bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3">Nome</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">Telefone</th>
                            <th class="px-6 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="contact in contacts.data" :key="contact.id" class="border-b border-gray-700 hover:bg-gray-700/50">
                            <td class="px-6 py-4 font-semibold">{{ contact.name }}</td>
                            <td class="px-6 py-4">{{ contact.email || '-' }}</td>
                            <td class="px-6 py-4">{{ formatPhone(contact.phone) }}</td>
                            <td class="px-6 py-4 flex items-center justify-end gap-4">
                                <Link :href="route('contacts.edit', contact.id)" class="text-cyan-400 hover:underline font-semibold">Editar</Link>
                                <button @click="confirmDelete(contact)" class="text-red-500 hover:underline font-semibold">Excluir</button>
                            </td>
                        </tr>
                        <tr v-if="!contacts.data || contacts.data.length === 0">
                            <td class="px-6 py-4 text-center text-gray-500" colspan="4">Nenhum contato encontrado.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Lista de Cartões para Telemóvel -->
            <div class="block sm:hidden space-y-4">
                <div v-if="!contacts.data || contacts.data.length === 0" class="p-4 text-center text-gray-500">
                    Nenhum contato encontrado.
                </div>
                <div v-for="contact in contacts.data" :key="contact.id" class="bg-gray-800 rounded-lg shadow p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-bold text-base">{{ contact.name }}</p>
                            <p v-if="contact.email" class="text-sm text-gray-300">{{ contact.email }}</p>
                            <p v-if="contact.phone" class="text-sm text-gray-400">{{ formatPhone(contact.phone) }}</p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-700 flex justify-end gap-4 text-sm">
                        <Link :href="route('contacts.edit', contact.id)" class="text-cyan-400 font-semibold">Editar</Link>
                        <button @click="confirmDelete(contact)" class="text-red-500 font-semibold">Excluir</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Campo de Busca na Parte de Baixo -->
        <div class="mt-4 flex-shrink-0">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <Search class="w-5 h-5 text-gray-400" />
                </span>
                <input 
                    v-model="search" 
                    type="text" 
                    placeholder="Buscar por nome, email ou telefone..."
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg py-3 pl-10 pr-4 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500"
                />
            </div>
        </div>
    </div>

     <!-- Modal de Confirmação de Exclusão -->
    <Dialog v-model:open="isConfirmingDelete">
      <DialogContent class="sm:max-w-md bg-gray-800 border-gray-700 text-white">
        <DialogHeader>
          <DialogTitle class="text-lg font-bold">Confirmar Exclusão</DialogTitle>
          <DialogDescription class="text-gray-400 mt-2">
            Tem certeza que deseja excluir o contato "{{ contactToDelete?.name }}"? Esta ação não pode ser desfeita.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="mt-6 flex justify-end gap-4">
          <DialogClose as-child>
            <Button type="button" variant="secondary" class="bg-gray-600 hover:bg-gray-500">Cancelar</Button>
          </DialogClose>
          <Button type="button" @click="destroy" variant="destructive" class="bg-red-600 hover:bg-red-700">Confirmar</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </CrmLayout>
</template>
