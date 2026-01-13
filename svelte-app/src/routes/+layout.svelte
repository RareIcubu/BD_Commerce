<script lang="ts">
    import { onMount } from 'svelte';
    import { user } from '../stores';
    import '../app.css';
    import ToastContainer from "$lib/components/ToastContainer.svelte";

    onMount(() => {
        let sessionId = localStorage.getItem('session_id');
        if (!sessionId) {
            sessionId = Math.random().toString(36).substring(2) + Date.now().toString(36);
            localStorage.setItem('session_id', sessionId);
        }
    });

    function logout() {
        user.set(null);
        window.location.href = '/';
    }
</script>

<ToastContainer />

<div class="min-h-screen bg-gray-50 flex flex-col font-sans">
    
    <nav class="bg-white shadow-sm border-b sticky top-0 z-40">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            
            <a href="/" class="text-2xl font-extrabold text-blue-600 tracking-tight hover:text-blue-700 transition">
                Sklep.io
            </a>
            
            <div class="flex items-center gap-6 text-sm font-medium text-gray-600">
                 <a href="/" class="hover:text-blue-600 transition">Produkty</a>
                 
                 <a href="/cart" class="hover:text-blue-600 transition flex items-center gap-1">
                    <span>Koszyk</span>
                 </a>
                
                {#if $user}
                    <div class="flex items-center gap-4 pl-4 border-l border-gray-200">
                        <a href="/orders" class="hover:text-blue-600 transition">Moje Zamówienia</a>
                        <span class="text-gray-800">Cześć, <b>{$user.name}</b></span>
                        
                        {#if $user.role_id === 2 || $user.role_id === 3} 
                            <a href="/seller" class="text-green-600 font-bold bg-green-50 px-2 py-1 rounded">
                                Panel Sprzedawcy
                            </a>
                        {/if}

                        <button on:click={logout} class="text-red-500 hover:text-red-700 font-bold">Wyloguj</button>
                    </div>
                {:else}
                    <div class="flex items-center gap-4 pl-4 border-l border-gray-200">
                        <a href="/login" class="text-blue-600 font-bold">Zaloguj się</a>
                    </div>
                {/if}
            </div>
        </div>
    </nav>

    <div class="flex-grow">
        <slot />
    </div>

    <footer class="bg-white border-t mt-auto py-8 text-center text-gray-500 text-sm">
        &copy; 2026 Sklep.io - Projekt Studencki
    </footer>
</div>
