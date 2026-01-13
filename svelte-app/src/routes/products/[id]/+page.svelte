<script lang="ts">
    import { notifications } from '$lib/notificationStore';
    
    // ODBIERAMY DANE Z SERWERA (+page.server.ts)
    // To jest kluczowe! "data" zawiera obiekt { product: ..., suggested: ... }
    export let data: { product: any, suggested: any[] }; 

    // Przypisujemy do zmiennych reaktywnych
    $: product = data.product;
    $: relatedProducts = data.suggested;

    let quantity = 1;
    // Do koszyka uderzamy z przeglądarki (klienta), więc localhost jest OK
    const API_URL = 'http://localhost:8000/api'; 

    async function addToCart() {
        // Logika koszyka pozostaje po stronie klienta (Client-Side)
        const sessionId = localStorage.getItem('session_id') || 'guest';
        try {
            const res = await fetch(`${API_URL}/cart`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Session-ID': sessionId },
                body: JSON.stringify({ product_id: product.product_id, quantity: quantity })
            });

            if (res.ok) {
                notifications.add(`Dodano do koszyka (${quantity} szt.)`, 'success');
            } else {
                notifications.add('Błąd dodawania do koszyka.', 'error');
            }
        } catch (e) {
            notifications.add('Błąd połączenia.', 'error');
        }
    }
</script>

<div class="container mx-auto px-4 py-8">
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-12">
        <div class="grid grid-cols-1 md:grid-cols-2">
            
            <div class="bg-gray-50 p-8 flex items-center justify-center border-r border-gray-100">
                <img 
                    src={product.front_image || 'https://placehold.co/600x400'} 
                    alt={product.name} 
                    class="max-w-full max-h-[400px] object-contain shadow-lg rounded-lg"
                />
            </div>

            <div class="p-8 flex flex-col justify-center">
                <div class="mb-2">
                    <span class="text-sm text-blue-600 font-bold tracking-wide uppercase">
                        {product.category?.name || 'Kategoria'}
                    </span>
                </div>
                
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{product.name}</h1>
                
                <div class="text-gray-600 mb-6 leading-relaxed">
                    {product.description || 'Brak opisu dla tego produktu.'}
                </div>

                <div class="flex flex-wrap gap-2 mb-8">
                    {#if product.tags}
                        {#each product.tags as tag}
                            <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-sm">
                                #{tag.name}
                            </span>
                        {/each}
                    {/if}
                </div>

                <div class="mt-auto border-t pt-6 flex flex-col sm:flex-row items-center gap-4">
                    <div class="text-3xl font-bold text-green-700 mr-auto">
                        {product.price} zł
                    </div>

                    <div class="flex items-center border rounded-md">
                        <button class="px-3 py-2 bg-gray-50 hover:bg-gray-100" on:click={() => quantity > 1 && quantity--}>-</button>
                        <input type="number" bind:value={quantity} class="w-12 text-center outline-none" min="1">
                        <button class="px-3 py-2 bg-gray-50 hover:bg-gray-100" on:click={() => quantity++}>+</button>
                    </div>

                    <button 
                        on:click={addToCart}
                        class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-md font-bold transition shadow-md flex items-center justify-center gap-2"
                    >
                        Do koszyka
                    </button>
                </div>
            </div>
        </div>
    </div>

    {#if relatedProducts && relatedProducts.length > 0}
        <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-2">Może Cię zainteresować</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            {#each relatedProducts as related}
                <a href="/products/{related.product_id}" class="bg-white border rounded-lg p-4 hover:shadow-lg transition group">
                    <div class="h-40 overflow-hidden rounded mb-3 bg-gray-100">
                        <img 
                            src={related.front_image || 'https://placehold.co/300'} 
                            alt={related.name}
                            class="w-full h-full object-cover group-hover:scale-105 transition"
                        />
                    </div>
                    <h3 class="font-bold text-gray-800 mb-1 truncate">{related.name}</h3>
                    <p class="text-green-600 font-bold">{related.price} zł</p>
                </a>
            {/each}
        </div>
    {/if}
</div>
