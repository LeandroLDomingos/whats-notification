<script setup>
import CrmLayout from '@/layouts/CrmLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// Recebe os dados da cobrança específica e a lista de contatos do controller
const { billing, contacts } = usePage().props;

// Preenche o formulário com os dados existentes da cobrança
const form = useForm({
  contact_id: billing.contact_id,
  total: billing.total,
  number_of_installments: billing.number_of_installments,
  first_due_date: billing.first_due_date,
  notifications_per_installment: billing.notifications_per_installment,
  notify_days_before: billing.notify_days_before,
  notify_days_before_secondary: billing.notify_days_before_secondary || '',
});

// Propriedade computada para calcular e formatar o valor da parcela dinamicamente
const installmentValue = computed(() => {
  const total = parseFloat(form.total);
  const installments = parseInt(form.number_of_installments, 10);

  if (total > 0 && installments > 0) {
    const value = total / installments;
    return value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  return '0,00';
});

// Função para submeter a atualização
const update = () => {
  form.put(route('billings.update', billing.id));
};
</script>

<template>
  <CrmLayout>
    <Head title="Editar Cobrança" />
    <div class="px-4">
      <h1 class="text-2xl font-bold mb-4">Editar Cobrança</h1>
      <div class="max-w-3xl bg-gray-800 p-6 rounded-lg shadow">
        <form @submit.prevent="update">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div>
              <label for="contact_id" class="block mb-2 text-sm font-medium text-gray-300">Contato</label>
              <select v-model="form.contact_id" id="contact_id" class="w-full bg-gray-700 border border-gray-600 rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500" required>
                <option v-for="contact in contacts" :key="contact.id" :value="contact.id">{{ contact.name }}</option>
              </select>
              <div v-if="form.errors.contact_id" class="text-red-500 text-sm mt-1">{{ form.errors.contact_id }}</div>
            </div>

            <div>
              <label for="total" class="block mb-2 text-sm font-medium text-gray-300">Total (R$)</label>
              <input v-model="form.total" type="number" step="0.01" id="total" class="w-full bg-gray-700 border border-gray-600 rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500" required>
              <div v-if="form.errors.total" class="text-red-500 text-sm mt-1">{{ form.errors.total }}</div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label for="number_of_installments" class="text-sm font-medium text-gray-300">Parcelas</label>
                    <span v-if="form.total > 0 && form.number_of_installments > 0" class="text-sm text-gray-400">
                      R$ {{ installmentValue }} / parcela
                    </span>
                </div>
                <input v-model="form.number_of_installments" type="number" id="number_of_installments" class="w-full bg-gray-700 border border-gray-600 rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500" required>
                <div v-if="form.errors.number_of_installments" class="text-red-500 text-sm mt-1">{{ form.errors.number_of_installments }}</div>
            </div>
            
            <div>
              <label for="first_due_date" class="block mb-2 text-sm font-medium text-gray-300">Data do Primeiro Vencimento</label>
              <input v-model="form.first_due_date" type="date" id="first_due_date" class="w-full bg-gray-700 border border-gray-600 rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500" required>
              <div v-if="form.errors.first_due_date" class="text-red-500 text-sm mt-1">{{ form.errors.first_due_date }}</div>
            </div>

            <div>
              <label for="notifications_per_installment" class="block mb-2 text-sm font-medium text-gray-300">Notificações por Parcela</label>
              <select v-model="form.notifications_per_installment" id="notifications_per_installment" class="w-full bg-gray-700 border border-gray-600 rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500" required>
                <option :value="1">1</option>
                <option :value="2">2</option>
              </select>
              <div v-if="form.errors.notifications_per_installment" class="text-red-500 text-sm mt-1">{{ form.errors.notifications_per_installment }}</div>
            </div>

            <div>
              <label for="notify_days_before" class="block mb-2 text-sm font-medium text-gray-300">
                {{ form.notifications_per_installment == 2 ? '1ª Notif. (dias antes)' : 'Notificar (dias antes)' }}
              </label>
              <input v-model="form.notify_days_before" type="number" id="notify_days_before" class="w-full bg-gray-700 border border-gray-600 rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500" required>
              <div v-if="form.errors.notify_days_before" class="text-red-500 text-sm mt-1">{{ form.errors.notify_days_before }}</div>
            </div>

            <div v-if="form.notifications_per_installment == 2">
              <label for="notify_days_before_secondary" class="block mb-2 text-sm font-medium text-gray-300">2ª Notif. (dias antes)</label>
              <input v-model="form.notify_days_before_secondary" type="number" id="notify_days_before_secondary" class="w-full bg-gray-700 border border-gray-600 rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
              <div v-if="form.errors.notify_days_before_secondary" class="text-red-500 text-sm mt-1">{{ form.errors.notify_days_before_secondary }}</div>
            </div>
          </div>
          
          <div class="flex justify-end mt-8 gap-4">
            <Link :href="route('billings.show', billing.id)" as="button" type="button" class="bg-gray-600 hover:bg-gray-500 text-white font-bold py-2 px-6 rounded-lg transition-colors duration-200">Cancelar</Link>
            <button type="submit" :disabled="form.processing" class="bg-cyan-500 hover:bg-cyan-600 text-white font-bold py-2 px-6 rounded-lg disabled:opacity-50 transition-colors duration-200">Atualizar</button>
          </div>
        </form>
      </div>
    </div>
  </CrmLayout>
</template>