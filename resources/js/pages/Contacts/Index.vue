<script setup>
import CrmLayout from '@/layouts/CrmLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref } from 'vue';

// Importando os componentes do diálogo e o botão
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
    DialogClose,
} from '@/components/ui/dialog';

const { contacts } = usePage().props;

// Ref para controlar o estado do diálogo
const isConfirmingDelete = ref(false);
const contactToDelete = ref(null);

const confirmDelete = (contact) => {
    contactToDelete.value = contact;
    isConfirmingDelete.value = true;
};

const destroy = () => {
    if (contactToDelete.value) {
        router.delete(route('contacts.destroy', contactToDelete.value.id), {
            preserveScroll: true, // Chave da solução!
            onSuccess: () => {
                isConfirmingDelete.value = false;
                contactToDelete.value = null;
            },
            onError: () => {
                 // Opcional: Adicionar feedback de erro, se necessário
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
    <div class="px-4">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Contatos</h1>
        <Link :href="route('contacts.create')" class="bg-cyan-500 hover:bg-cyan-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">
          Novo Contato
        </Link>
      </div>

      <div v-if="$page.props.flash.success" class="mb-4 p-3 bg-green-500/20 text-green-300 rounded-lg text-center">
        {{ $page.props.flash.success }}
      </div>

      <div class="bg-gray-800 rounded-lg shadow overflow-x-auto">
        <table class="w-full whitespace-nowrap">
          <thead class="text-left font-bold">
            <tr>
              <th class="px-6 pt-6 pb-4">Nome</th>
              <th class="px-6 pt-6 pb-4">Telefone</th>
              <th class="px-6 pt-6 pb-4">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="contact in contacts" :key="contact.id" class="hover:bg-gray-700 focus-within:bg-gray-700">
              <td class="border-t border-gray-700 px-6 py-4">
                {{ contact.name }}
              </td>
              <td class="border-t border-gray-700 px-6 py-4">
                {{ contact.phone || '-' }}
              </td>
              <td class="border-t border-gray-700 px-6 py-4 flex items-center gap-4">
                <Link :href="route('contacts.edit', contact.id)" class="text-cyan-400 hover:underline">Editar</Link>
                <button @click="confirmDelete(contact)" class="text-red-500 hover:underline">Excluir</button>
              </td>
            </tr>
            <tr v-if="contacts.length === 0">
              <td class="border-t border-gray-700 px-6 py-4" colspan="4">Nenhum contato encontrado.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Dialog v-model:open="isConfirmingDelete">
      <DialogContent class="sm:max-w-md bg-gray-800 border-gray-700 text-white">
        <DialogHeader>
          <DialogTitle class="text-lg font-bold">Confirmar Exclusão</DialogTitle>
          <DialogDescription class="text-gray-400 mt-2">
            Você tem certeza que deseja excluir o contato "{{ contactToDelete?.name }}"? Esta ação não pode ser desfeita.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="mt-6 flex justify-end gap-4">
          <DialogClose as-child>
            <Button type="button" variant="secondary" class="bg-gray-600 hover:bg-gray-500">
              Cancelar
            </Button>
          </DialogClose>
          <Button type="button" @click="destroy" variant="destructive" class="bg-red-600 hover:bg-red-700">
            Confirmar Exclusão
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </CrmLayout>
</template>