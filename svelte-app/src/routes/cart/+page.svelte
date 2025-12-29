<script lang="ts">
    import { onMount } from 'svelte';

    let cartItems: any[] = [];
    let total = 0;
    let loading = true;
    let message = '';

    // Pobierz koszyk
    async function fetchCart() {
        const sessionId = localStorage.getItem('session_id') || '';
        try {
            const res = await fetch('http://localhost:8000/api/cart', {
                headers: { 'X-Session-ID': sessionId }
            });
            const data = await res.json();
            cartItems = data.items;
            total = data.total;
        } catch (e) {
            console.error(e);
        } finally {
            loading = false;
        }
    }

    // Złóż zamówienie
    async function checkout() {
        const sessionId = localStorage.getItem('session_id') || '';
        if(!confirm(`Czy na pewno chcesz złożyć zamówienie na kwotę ${total} PLN?`)) return;

        try {
            const res = await fetch('http://localhost:8000/api/checkout', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Session-ID': sessionId 
                },
                body: JSON.stringify({ user_id: 1 }) // Hardcoded user dla testów
            });
            
            const data = await res.json();
            
            if (res.ok) {
                message = `Sukces! Zamówienie nr #${data.order_id} zostało złożone.`;
                cartItems = [];
                total = 0;
            } else {
                message = `Błąd: ${data.error || 'Nieznany błąd'}`;
            }
        } catch (e) {
            message = 'Wystąpił błąd połączenia.';
        }
    }

    onMount(fetchCart);
</script>

<h1 class="text-2xl font-bold mb-6">Twój Koszyk</h1>

{#if message}
    <div class="p-4 mb-4 bg-green-100 text-green-700 rounded border border-green-200">
        {message}
    </div>
{/if}

{#if loading}
    <p>Ładowanie koszyka...</p>
{:else if cartItems.length === 0 && !message}
    <p class="text-gray-500">Twój koszyk jest pusty.</p>
    <a href="/" class="text-blue-600 underline mt-2 inline-block">Wróć do sklepu</a>
{:else if cartItems.length > 0}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="p-4">Produkt</th>
                    <th class="p-4">Cena</th>
                    <th class="p-4">Ilość</th>
                    <th class="p-4">Suma</th>
                </tr>
            </thead>
            <tbody>
                {#each cartItems as item}
                    <tr class="border-b">
                        <td class="p-4 font-medium">{item.name}</td>
                        <td class="p-4">{item.price} PLN</td>
                        <td class="p-4">{item.pivot.quantity}</td>
                        <td class="p-4 font-bold">{(item.price * item.pivot.quantity).toFixed(2)} PLN</td>
                    </tr>
                {/each}
            </tbody>
        </table>
        
        <div class="p-6 bg-gray-50 flex justify-between items-center">
            <div class="text-xl">
                Łącznie do zapłaty: <span class="font-bold text-blue-600">{total.toFixed(2)} PLN</span>
            </div>
            <button 
                on:click={checkout}
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-bold transition">
                Zamawiam i płacę
            </button>
        </div>
    </div>
{/if}
