<script lang="ts">
    import { onMount } from 'svelte';
    import { user } from '../../stores';
    let cartItems: any[] = [];
    let total = 0;
    let loading = true;
    let message = '';

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

   async function checkout() {
        if (!$user) {
            alert("Musisz być zalogowany, aby złożyć zamówienie!");
            window.location.href = '/login';
            return;
        }

        const sessionId = localStorage.getItem('session_id') || '';
        if(!confirm(`Potwierdzasz zakup?`)) return;

        try {
            const res = await fetch('http://localhost:8000/api/checkout', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Session-ID': sessionId 
                },
                body: JSON.stringify({ user_id: $user.user_id }) 
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

    async function removeFromCart(productId: number) {
        console.log("Attempting to remove product with ID:", productId);
        if (!productId) {
            console.error("No product ID provided!");
            return;
        }

        const sessionId = localStorage.getItem('session_id') || '';
        
        try {
            const res = await fetch(`http://localhost:8000/api/cart/${productId}`, {
                method: 'DELETE',
                headers: { 
                    'X-Session-ID': sessionId 
                }
            });

            if (res.ok) {
                await fetchCart();
            } else {
                const data = await res.json();
                alert(data.error || 'Nie udało się usunąć produktu.');
            }
        } catch (e) {
            console.error('Błąd przy usuwaniu z koszyka:', e);
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
                    <th class="p-4 text-center">Akcje</th>
                </tr>
            </thead>
            <tbody>
                {#each cartItems as item}
                    <tr class="border-b">
                        <td class="p-4 font-medium">{item.name}</td>
                        <td class="p-4">{item.price} PLN</td>
                        <td class="p-4">{item.pivot.quantity}</td>
                        <td class="p-4 font-bold">{(item.price * item.pivot.quantity).toFixed(2)} PLN</td>
                        <td class="p-4 text-center">
                            <button 
                                on:click={() => removeFromCart(item.product_id)} 
                                class="text-red-500 hover:text-red-700 font-medium p-2 transition"
                                title="Usuń z koszyka"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </td>
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
