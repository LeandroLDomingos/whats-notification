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

// Pega os dados da página e cria cópia reativa
const { billings: initialBillings } = usePage().props;
const billings = ref([...initialBillings]);

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
                billings.value = billings.value.filter(
                    (b) => b.id !== billingToDelete.value.id
                );
                isConfirmingDelete.value = false;
                billingToDelete.value = null;
            },
        });
    }
};
</script>

<template>
  <CrmLayout>
    <Head title="Cobranças" />
    <div class="px-4 py-4">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-xl md:text-2xl font-bold">Cobranças Pendentes</h1>
          <Link :href="route('billings.history')" class="hidden sm:inline-block text-sm text-cyan-400 hover:underline mt-1">
            Ver Histórico
          </Link>
        </div>
        <Link :href="route('billings.create')" class="bg-cyan-500 hover:bg-cyan-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200 text-sm whitespace-nowrap">
          Nova
        </Link>
      </div>

      <div class="sm:hidden mb-4">
        <Link :href="route('billings.history')" class="text-sm text-cyan-400 hover:underline">
          Ver cobranças quitadas
        </Link>
      </div>

      <div v-if="$page.props.flash.success" class="mb-4 p-3 bg-green-500/20 text-green-300 rounded-lg text-center text-sm">
        {{ $page.props.flash.success }}
      </div>

      <!-- Tabela para Desktop -->
      <div class="hidden sm:block bg-gray-800 rounded-lg shadow overflow-x-auto">
        <table class="w-full whitespace-nowrap text-sm">
          <thead class="text-left font-bold bg-gray-700/50">
            <tr>
              <th class="px-6 py-3">Contacto</th>
              <th class="px-6 py-3">Total (R$)</th>
              <th class="px-6 py-3">Parcelas</th>
              <th class="px-6 py-3">1º Venc.</th>
              <th class="px-6 py-3 text-right">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="billing in billings"
              :key="billing.id"
              class="border-b border-gray-700 hover:bg-gray-700/50"
            >
              <td class="px-6 py-4 font-semibold">{{ billing.contact_name }}</td>
              <td class="px-6 py-4">{{ billing.total }}</td>
              <td class="px-6 py-4">{{ billing.installments }}x</td>
              <td class="px-6 py-4">{{ billing.first_due_date }}</td>
              <td class="px-6 py-4 flex items-center justify-end gap-4">
                <Link :href="route('billings.show', billing.id)" class="text-green-400 hover:underline font-semibold">Detalhes</Link>
                <Link :href="route('billings.edit', billing.id)" class="text-cyan-400 hover:underline font-semibold">Editar</Link>
                <button @click="confirmDelete(billing)" class="text-red-500 hover:underline font-semibold">Excluir</button>
              </td>
            </tr>
            <tr v-if="billings.length === 0">
              <td class="border-t border-gray-700 px-6 py-4 text-center" colspan="5">Nenhuma cobrança pendente encontrada.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Lista de Cartões para Telemóvel -->
      <div class="block sm:hidden space-y-4">
        <div v-if="billings.length === 0" class="p-4 text-center text-gray-500">
          Nenhuma cobrança pendente encontrada.
        </div>
        <div v-for="billing in billings" :key="billing.id" class="bg-gray-800 rounded-lg shadow p-4">
          <div class="flex justify-between items-start">
            <div>
              <p class="font-bold text-base">{{ billing.contact_name }}</p>
              <p class="text-sm text-gray-300">Total: R$ {{ billing.total }}</p>
              <p class="text-xs text-gray-400">{{ billing.installments }}x | 1º Venc: {{ billing.first_due_date }}</p>
            </div>
            <Link :href="route('billings.show', billing.id)" class="bg-green-500/20 text-green-300 px-3 py-1 rounded-full text-xs font-bold flex-shrink-0 ml-2">Ver Detalhes</Link>
          </div>
          <div class="mt-4 pt-4 border-t border-gray-700 flex justify-end gap-4 text-sm">
            <Link :href="route('billings.edit', billing.id)" class="text-cyan-400 font-semibold">Editar</Link>
            <button @click="confirmDelete(billing)" class="text-red-500 font-semibold">Excluir</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <Dialog v-model:open="isConfirmingDelete">
      <DialogContent class="sm:max-w-md bg-gray-800 border-gray-700 text-white">
        <DialogHeader>
          <DialogTitle class="text-lg font-bold">Confirmar Exclusão</DialogTitle>
          <DialogDescription class="text-gray-400 mt-2">
            Tem certeza que deseja excluir esta cobrança para "{{ billingToDelete?.contact_name }}"? Esta ação não pode ser desfeita.
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
