<script setup>
import CrmLayout from '@/layouts/CrmLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const form = useForm({
  name: '',
  email: '',
  phone: '',
});

const store = () => {
  form.post(route('contacts.store'));
};

// Aplica a máscara no campo de telefone enquanto o usuário digita
watch(() => form.phone, (newValue) => {
  if (newValue) {
    let x = newValue.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
    if (x) {
        const maskedValue = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
        if (maskedValue !== form.phone) {
           form.phone = maskedValue;
        }
    }
  }
});
</script>

<template>
  <CrmLayout>
    <Head title="Novo Contato" />
    <div class="px-4">
        <h1 class="text-2xl font-bold mb-4">Novo Contato</h1>
        <div class="max-w-2xl bg-gray-800 p-6 rounded-lg shadow">
            <form @submit.prevent="store">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-300">Nome</label>
                        <input v-model="form.name" type="text" id="name" class="w-full bg-gray-700 border border-gray-600 rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500" required>
                        <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                    </div>
                    <div>
                        <label for="phone" class="block mb-2 text-sm font-medium text-gray-300">Telefone</label>
                        <input v-model="form.phone" type="text" id="phone" placeholder="(XX) XXXXX-XXXX" maxlength="15" class="w-full bg-gray-700 border border-gray-600 rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <div v-if="form.errors.phone" class="text-red-500 text-sm mt-1">{{ form.errors.phone }}</div>
                    </div>
                </div>
                <div class="mt-6">
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-300">Email (Opcional)</label>
                    <input v-model="form.email" type="email" id="email" class="w-full bg-gray-700 border border-gray-600 rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
                </div>
                <div class="flex justify-end mt-8 gap-4">
                    <Link :href="route('contacts.index')" as="button" type="button" class="bg-gray-600 hover:bg-gray-500 text-white font-bold py-2 px-6 rounded-lg transition-colors duration-200">
                        Cancelar
                    </Link>
                    <button type="submit" :disabled="form.processing" class="bg-cyan-500 hover:bg-cyan-600 text-white font-bold py-2 px-6 rounded-lg transition-colors duration-200 disabled:opacity-50">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
  </CrmLayout>
</template>