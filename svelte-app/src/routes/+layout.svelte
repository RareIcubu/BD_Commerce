<script lang="ts">
    import { onMount } from 'svelte';
    import '../app.css';

    onMount(() => {
        let sessionId = localStorage.getItem('session_id');
        if (!sessionId) {
            // Prosty generator ID sesji dla przeglądarki
            sessionId = Math.random().toString(36).substring(2) + Date.now().toString(36);
            localStorage.setItem('session_id', sessionId);
        }
    });
</script>

<div class="min-h-screen bg-gray-50 flex flex-col">
    <nav class="bg-white shadow-sm border-b">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="/" class="text-xl font-bold text-blue-600">Sklep.io</a>
            
            <div class="flex gap-4 text-sm font-medium text-gray-600">
                <a href="/" class="hover:text-blue-600">Produkty</a>
                <a href="/cart" class="hover:text-blue-600">Koszyk</a>
                <a href="/orders" class="hover:text-blue-600">Moje Zamówienia</a>
                <div class="border-l pl-4 ml-2">
                    <a href="/seller" class="text-green-600 hover:text-green-700">Panel Sprzedawcy</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow container mx-auto px-4 py-8">
        <slot />
    </main>

    <footer class="bg-gray-800 text-gray-400 text-center py-4 text-sm">
        &copy; 2025 Projekt Zaliczeniowy
    </footer>
</div>
