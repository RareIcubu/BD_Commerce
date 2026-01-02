<script lang="ts">
    import { searchTerm, fetchProducts } from '../lib/productStores'
    import { onMount } from 'svelte';
    import { user } from '../stores';
    import '../app.css';

    onMount(() => {
        let sessionId = localStorage.getItem('session_id');
        if (!sessionId) {
            // Prosty generator ID sesji dla przeglądarki
            sessionId = Math.random().toString(36).substring(2) + Date.now().toString(36);
            localStorage.setItem('session_id', sessionId);
        }
    });
    function logout() {
        user.set(null);
        window.location.href = '/';
    }
</script>

<div class="min-h-screen bg-gray-50 flex flex-col">
    <nav class="bg-white shadow-sm border-b">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="/" class="text-xl font-bold text-blue-600">Sklep.io</a>
            
            <div class="flex gap-4 text-sm font-medium text-gray-600 items-center">
                <!-- <a href="/" class="hover:text-blue-600">Produkty</a>-->
                 <a href="/products" on:click|preventDefault={() => { 
                    searchTerm.set('');
                    fetchProducts('');
                    }}
                    class="nav-link">Produkty</a>
                <a href="/cart" class="hover:text-blue-600">Koszyk</a>
                
                {#if $user}
                    <a href="/orders" class="hover:text-blue-600">Moje Zamówienia</a>
                    <span class="text-gray-400">|</span>
                    <span class="text-gray-800">Cześć, <b>{$user.name}</b></span>
                    <button on:click={logout} class="text-red-500 hover:text-red-700">Wyloguj</button>
                    
                    {#if $user.role_id === 2 || $user.role_id === 3} <a href="/seller" class="ml-2 text-green-600">Panel Sprzedawcy</a>
                    {/if}
                {:else}
                    <a href="/login" class="text-blue-600 font-bold">Zaloguj się</a>
                    <a href="/register" class="hover:text-blue-600">Rejestracja</a>
                {/if}
            </div>
        </div>
    </nav>

    <main class="flex-grow container mx-auto px-4 py-8">
        <slot />
    </main>
    
</div>
