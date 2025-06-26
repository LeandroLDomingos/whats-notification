<script setup>
import { Link, usePage } from '@inertiajs/vue3';

// CORREÇÃO: A propriedade 'icon' foi adicionada de volta a cada item.
const navItems = [
    { name: 'Dashboard', href: route('dashboard'), path: '/dashboard', icon: 'HomeIcon' },
    { name: 'Contatos', href: route('contacts.index'), path: '/contacts', icon: 'UsersIcon' },
    { name: 'Cobranças', href: route('billings.index'), path: '/billings', icon: 'DollarSignIcon' },
    { name: 'Mensagens', href: route('messages.index'), path: '/messages', icon: 'MessageCircleIcon' },
];

// usePage() permite aceder aos dados partilhados e props da página atual.
const page = usePage();
</script>

<template>
    <div class="font-sans bg-gray-900 text-white w-full h-screen overflow-hidden flex flex-col antialiased">
        <div class="flex-grow flex flex-col overflow-y-hidden">
            <!-- 1. CABEÇALHO PADRÃO -->
            <header class="flex items-center justify-between p-4 bg-gray-900 border-b border-gray-800 flex-shrink-0">
                <!-- O título vem das props enviadas pelo controller -->
                <h1 class="text-xl font-bold text-white">{{ page.props.title || 'Dashboard' }}</h1>
                
                <!-- O botão de logout -->
                <Link v-if="page.props.auth.user" :href="route('logout')" method="post" as="button" class="text-gray-300 p-2 rounded-full hover:bg-gray-700">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                </Link>
            </header>

            <!-- 2. CONTEÚDO DA PÁGINA (SLOT) -->
            <main class="flex-grow p-4 overflow-y-auto">
                <!-- É aqui que o conteúdo das páginas será inserido -->
                <slot />
            </main>

            <!-- 3. BARRA DE NAVEGAÇÃO INFERIOR -->
            <nav class="w-full bg-gray-800 border-t border-gray-700 flex justify-around flex-shrink-0">
                 <!-- A lógica da classe agora verifica se a URL atual começa com o caminho do item -->
                 <Link v-for="item in navItems" :key="item.name" :href="item.href" 
                    :class="[
                        'flex-1 py-3 flex items-center justify-center transition-colors duration-200', 
                        page.url.startsWith(item.path) ? 'text-cyan-400 border-t-2 border-cyan-400' : 'text-gray-400 hover:text-white'
                    ]">
                    <!-- Ícones que agora voltarão a aparecer -->
                    <svg v-if="item.icon === 'HomeIcon'" class="w-6 h-6" viewBox="0 0 24 24"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    <svg v-if="item.icon === 'UsersIcon'" class="w-6 h-6" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    <svg v-if="item.icon === 'DollarSignIcon'" class="w-6 h-6" viewBox="0 0 24 24"><line x1="12" x2="12" y1="2" y2="22"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    <svg v-if="item.icon === 'MessageCircleIcon'" class="w-6 h-6" viewBox="0 0 24 24"><path d="m3 21 1.9-5.7a8.5 8.5 0 1 1 3.8 3.8z"></path></svg>
                 </Link>
            </nav>
        </div>
    </div>
</template>
<style>
/* Estilo para que os ícones herdem a cor do texto do link */
svg {
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}
</style>
