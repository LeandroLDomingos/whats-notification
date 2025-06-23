<script setup>
import CrmLayout from '@/layouts/CrmLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref } from 'vue';
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

const { billings } = usePage().props;

const isConfirmingDelete = ref(false);
const billingToDelete = ref(null);

const confirmDelete = (billing) => {
  billingToDelete.value = billing;
  isConfirmingDelete.value = true;
};

const destroy = () => {
  if (billingToDelete.value) {
    router.delete(route('billings.destroy', billingToDelete.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        isConfirmingDelete.value = false;
        billingToDelete.value = null;
      }
    });
  }
};
</script>

<template>
  <CrmLayout>

    <Head title="Cobranças" />
    <div class="px-4">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Cobranças</h1>
        <Link :href="route('billings.create')"
          class="bg-cyan-500 hover:bg-cyan-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">
        Nova Cobrança
        </Link>
      </div>

      <div v-if="$page.props.flash.success" class="mb-4 p-3 bg-green-500/20 text-green-300 rounded-lg text-center">
        {{ $page.props.flash.success }}
      </div>

      <div class="bg-gray-800 rounded-lg shadow overflow-x-auto">
        <table class="w-full whitespace-nowrap">
          <thead class="text-left font-bold">
            <tr>
              <th class="px-6 pt-6 pb-4">Contato</th>
              <th class="px-6 pt-6 pb-4">Total (R$)</th>
              <th class="px-6 pt-6 pb-4">Parcelas</th>
              <th class="px-6 pt-6 pb-4">1º Venc.</th>
              <th class="px-6 pt-6 pb-4">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="billing in billings" :key="billing.id" class="hover:bg-gray-700 focus-within:bg-gray-700">
              <td class="border-t border-gray-700 px-6 py-4">{{ billing.contact_name }}</td>
              <td class="border-t border-gray-700 px-6 py-4">{{ billing.total }}</td>
              <td class="border-t border-gray-700 px-6 py-4">{{ billing.installments }}</td>
              <td class="border-t border-gray-700 px-6 py-4">{{ billing.first_due_date }}</td>
              <td class="border-t border-gray-700 px-6 py-4 flex items-center gap-4">
                <Link :href="route('billings.show', billing.id)" class="text-green-400 hover:underline">Detalhes</Link>
                <Link :href="route('billings.edit', billing.id)" class="text-cyan-400 hover:underline">Editar</Link>
                <button @click="confirmDelete(billing)" class="text-red-500 hover:underline">Excluir</button>
              </td>
            </tr>
            <tr v-if="billings.length === 0">
              <td class="border-t border-gray-700 px-6 py-4" colspan="5">Nenhuma cobrança encontrada.</td>
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
            Tem certeza que deseja excluir esta cobrança para "{{ billingToDelete?.contact_name }}"?
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="mt-6 flex justify-end gap-4">
          <DialogClose as-child>
            <Button type="button" variant="secondary" class="bg-gray-600 hover:bg-gray-500">Cancelar</Button>
          </DialogClose>
          <Button type="button" @click="destroy" variant="destructive"
            class="bg-red-600 hover:bg-red-700">Confirmar</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </CrmLayout>
</template>