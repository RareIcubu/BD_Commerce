<script lang="ts">
    import { onMount } from 'svelte';
    import { notifications } from '$lib/notificationStore';

    // Prosty typ zamówienia
    type Order = {
        id: number;
        created_at: string;
        total_price: string;
        status: string;
    };

    let orders: Order[] = [];
    let isLoading = true;

    const API_URL = 'http://localhost:8000/api';

    async function loadOrders() {
        // Wymagane logowanie - tu zakładamy, że token/sesja jest wysyłana automatycznie przez cookies
        // lub (jeśli używasz tokenów Bearer) musisz dodać nagłówek Authorization
        try {
            const res = await fetch(`${API_URL}/orders`); 
            
            if (res.ok) {
                orders = await res.json();
            } else {
                // Jeśli 401 Unauthorized, przekieruj
                if (res.status === 401) window.location.href = '/login';
            }
        } catch (e) {
            notifications.add('Błąd pobierania historii.', 'error');
        } finally {
            isLoading = false;
        }
    }

    onMount(loadOrders);
</script>

<div class="container mx-auto px-4 py-8 max-w-4xl min-h-[60vh]">
    <h1 class="text-3xl font-bold mb-8 text-gray-800">Moje Zamówienia</h1>

    {#if isLoading}
        <div class="text-center py-10">Ładowanie...</div>
    {:else if orders.length === 0}
        <div class="bg-blue-50 p-8 rounded-lg text-blue-800 border border-blue-100">
            Nie złożyłeś jeszcze żadnych zamówień.
        </div>
    {:else}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100 text-gray-600 text-sm uppercase">
                    <tr>
                        <th class="p-4 border-b">Nr Zamówienia</th>
                        <th class="p-4 border-b">Data</th>
                        <th class="p-4 border-b">Status</th>
                        <th class="p-4 border-b text-right">Kwota</th>
                    </tr>
                </thead>
                <tbody>
                    {#each orders as order}
                        <tr class="hover:bg-gray-50 border-b last:border-0 transition">
                            <td class="p-4 font-bold text-gray-700">#{order.id}</td>
                            <td class="p-4 text-gray-600">
                                {new Date(order.created_at).toLocaleDateString('pl-PL')}
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold
                                    {order.status === 'completed' ? 'bg-green-100 text-green-700' : 
                                     order.status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600'}">
                                    {order.status}
                                </span>
                            </td>
                            <td class="p-4 text-right font-bold text-blue-600">
                                {order.total_price} zł
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </table>
        </div>
    {/if}
</div>
