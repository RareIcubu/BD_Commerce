<script lang="ts">
    import { onMount } from 'svelte';
    import { user } from '../../stores';

    let orders: any[] = [];
    let loading = true;

    onMount(async () => {
        // Jeśli nie zalogowany -> przekieruj (opcjonalnie)
        if (!$user) return;

        // Pobieramy zamówienia przekazując user_id w query params
        // W prawdziwym API REST robiłoby się to przez token, tutaj prosto:
        const res = await fetch(`http://localhost:8000/api/orders?user_id=${$user.user_id}`);
        if (res.ok) {
            orders = await res.json();
        }
        loading = false;
    });
</script>

<h1 class="text-3xl font-bold mb-6">Moje Zamówienia</h1>

{#if loading}
    <p>Ładowanie...</p>
{:else if !$user}
    <p>Zaloguj się, aby zobaczyć historię.</p>
{:else if orders.length === 0}
    <p>Nie masz jeszcze żadnych zamówień.</p>
{:else}
    <div class="space-y-6">
        {#each orders as order}
            <div class="bg-white border rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center border-b pb-4 mb-4">
                    <div>
                        <span class="text-gray-500 text-sm">Zamówienie #</span>
                        <span class="font-bold text-lg">{order.order_id}</span>
                        <span class="ml-4 px-2 py-1 bg-gray-100 rounded text-sm status-{order.status}">
                            {order.status}
                        </span>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">{new Date(order.ordered_at).toLocaleDateString()}</div>
                        <div class="font-bold text-xl">{order.price_total} PLN</div>
                    </div>
                </div>

                <ul class="space-y-2">
                    {#each order.products as product}
                        <li class="flex justify-between text-sm">
                            <span>{product.name} <span class="text-gray-400">x{product.pivot.quantity}</span></span>
                            <span>{(product.pivot.price_when_purchased * product.pivot.quantity).toFixed(2)} PLN</span>
                        </li>
                    {/each}
                </ul>
            </div>
        {/each}
    </div>
{/if}

<style>
    .status-pending { color: orange; }
    .status-completed { color: green; }
    .status-cancelled { color: red; }
</style>
