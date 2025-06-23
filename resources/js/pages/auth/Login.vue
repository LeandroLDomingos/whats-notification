<script setup>
import { Head, useForm } from '@inertiajs/vue3';

// O useForm do Inertia ajuda a gerir os dados do formulário,
// validação de erros e o estado de envio.
const form = useForm({
    email: '', // Preenchido para facilitar o teste
    password: '',   // Preenchido para facilitar o teste
    remember: true
});

// Função para submeter o formulário de login
const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Login" />

    <div class="font-sans bg-gray-900 text-white w-full h-screen overflow-hidden flex flex-col antialiased items-center justify-center p-8">
        <div class="w-full max-w-xs">
            <!-- Cabeçalho -->
            <div class="text-center mb-10">
                <h1 class="text-3xl font-bold text-cyan-400 mb-2">Whats Notificações
                </h1>
                <p class="text-gray-400">Gestão de clientes na palma da mão.</p>
            </div>

            <!-- Mensagem de erro de validação -->
            <div v-if="form.errors.email" class="mb-4 p-3 bg-red-500/20 text-red-300 rounded-lg text-center">
                {{ form.errors.email }}
            </div>

            <!-- Formulário -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Campo de Email (sem label, com placeholder) -->
                <div>
                    <input 
                        v-model="form.email" 
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg py-3 px-4 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 placeholder-gray-500" 
                        id="email" 
                        type="email" 
                        placeholder="Email" 
                        required 
                    />
                </div>
                
                <!-- Campo de Senha (sem label, com placeholder) -->
                <div>
                    <input 
                        v-model="form.password" 
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg py-3 px-4 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 placeholder-gray-500" 
                        id="password" 
                        type="password" 
                        placeholder="Senha" 
                        required 
                    />
                </div>

                <!-- Botão de Submissão -->
                <div>
                    <button 
                        :disabled="form.processing" 
                        class="w-full bg-cyan-500 hover:bg-cyan-600 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-colors duration-200 disabled:opacity-50"
                    >
                        Entrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
