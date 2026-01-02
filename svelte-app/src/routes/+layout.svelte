<script lang="ts">
    import { searchTerm, fetchProducts, categories, fetchCategories } from '../lib/productStores'
    import { onMount } from 'svelte';
    import { user } from '../stores';
    import { goto } from '$app/navigation';
    import '../app.css';

    onMount(() => {
        let sessionId = localStorage.getItem('session_id');
        if (!sessionId) {
            // Prosty generator ID sesji dla przeglądarki
            sessionId = Math.random().toString(36).substring(2) + Date.now().toString(36);
            localStorage.setItem('session_id', sessionId);
        }
        fetchCategories(); 
    });
    function logout() {
        user.set(null);
        window.location.href = '/';
    }

    async function resetToProducts() {
    searchTerm.set('');
    await goto('/');
    fetchProducts('');
  }

  async function filterByCategory(id: number) {
        searchTerm.set('');
        await goto('/');
        fetchProducts(`category_id=${id}`);
    }
</script>

<div class="min-h-screen bg-gray-50 flex flex-col">
    <nav class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="/products" on:click|preventDefault={resetToProducts} 
                    class="text-xl font-bold text-blue-600">Sklep.io</a>
            
            <div class="flex gap-4 text-sm font-medium text-gray-600 items-center">
                 <a href="/products" on:click|preventDefault={resetToProducts}
                    class="nav-link">Produkty</a>
                    
                <a href="/cart" class="hover:text-blue-600">Koszyk</a>
                
                {#if $user}
                    <a href="/orders" class="hover:text-blue-600">Moje Zamówienia</a>
                    <span class="text-gray-400">|</span>
                    <span class="text-gray-800">Cześć, <b>{$user.name}</b></span>
                    <button on:click={logout} class="text-red-500 hover:text-red-700">Wyloguj</button>
                    
                    {#if $user.role_id === 2 || $user.role_id === 3} 
                        <a href="/seller" class="ml-2 text-green-600">Panel Sprzedawcy</a>
                    {/if}
                {:else}
                    <a href="/login" class="text-blue-600 font-bold">Zaloguj się</a>
                    <a href="/register" class="hover:text-blue-600">Rejestracja</a>
                {/if}
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 flex flex-col md:flex-row gap-8">
        
        <aside class="w-full md:w-64 flex-shrink-0">
            <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
                <h2 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">Kategorie</h2>
                <nav class="flex flex-col gap-1">
                    <button 
                        on:click={resetToProducts}
                        class="text-left px-3 py-2 rounded-md transition hover:bg-blue-50 hover:text-blue-600 {$searchTerm === '' ? 'text-blue-600 font-semibold' : 'text-gray-600'}"
                    >
                        Wszystkie produkty
                    </button>

                    {#each $categories as category}
                        <button 
                            on:click={() => filterByCategory(category.category_id)}
                            class="text-left px-3 py-2 rounded-md transition hover:bg-blue-50 hover:text-blue-600 text-gray-600"
                        >
                            {category.name}
                        </button>
                    {/each}
                </nav>
            </div>
        </aside>

        <main class="flex-grow">
            <slot />
        </main>
    </div>
</div>
