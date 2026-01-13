<script lang="ts">
    import { onMount } from 'svelte';
    import { notifications } from '$lib/notificationStore';

    type CartItem = {
        cart_id: number;
        product_id: number;
        quantity: number;
        product: {
            name: string;
            price: string;
            front_image: string;
        };
    };

    let cartItems: CartItem[] = [];
    let isLoading = true;
    let isUpdating = false; // Blokada przycisków podczas zmiany ilości
    let total = 0;

    const API_URL = 'http://localhost:8000/api';

    // 1. Pobieranie koszyka
    async function loadCart() {
        isLoading = true;
        const sessionId = localStorage.getItem('session_id') || '';
        
        try {
            const res = await fetch(`${API_URL}/cart`, {
                headers: { 'X-Session-ID': sessionId }
            });
            if (res.ok) {
                cartItems = await res.json();
                calculateTotal();
            }
        } catch (e) {
            notifications.add('Błąd ładowania koszyka.', 'error');
        } finally {
            isLoading = false;
        }
    }

    // 2. NOWOŚĆ: Zmiana ilości (+/-)
    async function updateQuantity(productId: number, newQuantity: number) {
        if (newQuantity < 1) return; // Nie pozwalamy zejść poniżej 1 (od tego jest usuń)
        
        isUpdating = true;
        const sessionId = localStorage.getItem('session_id') || '';

        try {
            const res = await fetch(`${API_URL}/cart/${productId}`, {
                method: 'PUT', // Używamy nowej metody PUT
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Session-ID': sessionId 
                },
                body: JSON.stringify({ quantity: newQuantity })
            });

            if (res.ok) {
                // Aktualizujemy lokalnie (szybciej niż przeładowanie całej listy)
                const item = cartItems.find(i => i.product_id === productId);
                if (item) item.quantity = newQuantity;
                cartItems = [...cartItems]; // Wymuszamy odświeżenie Svelte
                calculateTotal();
            } else {
                notifications.add('Błąd aktualizacji ilości.', 'error');
            }
        } catch (e) {
            notifications.add('Błąd połączenia.', 'error');
        } finally {
            isUpdating = false;
        }
    }

    // 3. Usuwanie
    async function removeItem(productId: number) {
        if (!confirm('Usunąć produkt z koszyka?')) return;
        const sessionId = localStorage.getItem('session_id') || '';
        try {
            const res = await fetch(`${API_URL}/cart/${productId}`, {
                method: 'DELETE',
                headers: { 'X-Session-ID': sessionId }
            });
            
            if (res.ok) {
                cartItems = cartItems.filter(item => item.product_id !== productId);
                calculateTotal();
                notifications.add('Usunięto z koszyka.', 'success');
            }
        } catch (e) {
            notifications.add('Nie udało się usunąć.', 'error');
        }
    }

    // 4. Suma
    function calculateTotal() {
        total = cartItems.reduce((sum, item) => {
            return sum + (parseFloat(item.product.price) * item.quantity);
        }, 0);
    }

    // 5. Checkout
    async function checkout() {
        if (!confirm('Czy chcesz złożyć zamówienie?')) return;
        
        const sessionId = localStorage.getItem('session_id') || '';
        try {
            const res = await fetch(`${API_URL}/checkout`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Session-ID': sessionId 
                }
            });

            if (res.ok) {
                notifications.add('Zamówienie złożone pomyślnie!', 'success');
                cartItems = [];
                total = 0;
            } else {
                const data = await res.json();
                notifications.add(data.error || 'Błąd składania zamówienia.', 'error');
            }
        } catch (e) {
            notifications.add('Błąd połączenia.', 'error');
        }
    }

    onMount(loadCart);
</script>

<div class="container mx-auto px-4 py-8 max-w-5xl min-h-[60vh]">
    <h1 class="text-3xl font-bold mb-8 text-gray-800 border-b pb-4">Twój Koszyk</h1>

    {#if isLoading}
        <div class="text-center py-10 text-gray-500">Ładowanie koszyka...</div>
    {:else if cartItems.length === 0}
        <div class="text-center py-20 bg-gray-50 rounded-lg border border-dashed border-gray-300">
            <p class="text-xl text-gray-500 mb-4">Twój koszyk jest pusty.</p>
            <a href="/" class="text-blue-600 font-bold hover:underline">Wróć do sklepu</a>
        </div>
    {:else}
        <div class="flex flex-col lg:flex-row gap-8">
            
            <div class="lg:w-2/3">
                <div class="bg-white rounded-lg shadow overflow-hidden border">
                    {#each cartItems as item (item.cart_id + '_' + item.product_id)}
                        <div class="flex items-center p-4 border-b last:border-0 hover:bg-gray-50 transition">
                            <img 
                                src={item.product.front_image || 'https://placehold.co/100'} 
                                alt={item.product.name} 
                                class="w-20 h-20 object-cover rounded border mr-4"
                            />
                            
                            <div class="flex-grow">
                                <h3 class="font-bold text-gray-800">{item.product.name}</h3>
                                <div class="text-sm text-gray-500 mb-2">Cena jedn.: {item.product.price} zł</div>
                                
                                <div class="flex items-center border rounded w-max bg-white">
                                    <button 
                                        on:click={() => updateQuantity(item.product_id, item.quantity - 1)}
                                        disabled={isUpdating || item.quantity <= 1}
                                        class="px-3 py-1 hover:bg-gray-100 text-gray-600 disabled:opacity-50"
                                    >-</button>
                                    
                                    <span class="px-3 py-1 font-bold w-10 text-center">{item.quantity}</span>
                                    
                                    <button 
                                        on:click={() => updateQuantity(item.product_id, item.quantity + 1)}
                                        disabled={isUpdating}
                                        class="px-3 py-1 hover:bg-gray-100 text-gray-600 disabled:opacity-50"
                                    >+</button>
                                </div>
                            </div>

                            <div class="text-right ml-4">
                                <div class="font-bold text-green-700 block mb-2 text-lg">
                                    {(parseFloat(item.product.price) * item.quantity).toFixed(2)} zł
                                </div>
                                <button 
                                    on:click={() => removeItem(item.product_id)}
                                    class="text-red-500 text-sm hover:text-red-700 underline flex items-center justify-end gap-1"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    Usuń
                                </button>
                            </div>
                        </div>
                    {/each}
                </div>
            </div>

            <div class="lg:w-1/3">
                <div class="bg-white p-6 rounded-lg shadow border sticky top-24">
                    <h2 class="text-xl font-bold mb-4 text-gray-800">Podsumowanie</h2>
                    
                    <div class="flex justify-between mb-2 text-gray-600">
                        <span>Wartość produktów:</span>
                        <span>{total.toFixed(2)} zł</span>
                    </div>
                    <div class="flex justify-between mb-4 text-gray-600">
                        <span>Dostawa:</span>
                        <span class="text-green-600 font-bold">Gratis</span>
                    </div>
                    
                    <div class="border-t pt-4 flex justify-between mb-6 font-bold text-2xl text-gray-900">
                        <span>Suma:</span>
                        <span>{total.toFixed(2)} zł</span>
                    </div>

                    <button 
                        on:click={checkout}
                        class="w-full bg-blue-600 text-white py-4 rounded-lg font-bold hover:bg-blue-700 transition shadow-lg text-lg"
                    >
                        ZAMAWIAM I PŁACĘ
                    </button>
                    
                    <a href="/" class="block text-center mt-4 text-sm text-gray-500 hover:text-gray-800 underline">
                        Kontynuuj zakupy
                    </a>
                </div>
            </div>
        </div>
    {/if}
</div>
