<script lang="ts">
  import { onMount } from 'svelte';
  import { notifications } from '$lib/notificationStore'; // Używamy naszego systemu powiadomień

  // --- TYPY DANYCH ---
  type Product = { product_id: number; name: string; price: string; front_image: string; category?: { name: string }; tags?: { name: string }[] };
  type Category = { category_id: number; name: string };
  type Tag = { tag_id: number; name: string };

  // --- STAN APLIKACJI ---
  let products: Product[] = [];
  let categories: Category[] = [];
  let tags: Tag[] = [];
  
  let isLoading = true;

  // --- FILTRY ---
  let searchTerm = '';
  let selectedCategory = '';
  let selectedTags: number[] = [];

  const API_URL = 'http://localhost:8000/api';

  // 1. INICJALIZACJA (Pobierz słowniki i produkty)
  async function init() {
    try {
      const [catRes, tagRes] = await Promise.all([
        fetch(`${API_URL}/categories`),
        fetch(`${API_URL}/tags`)
      ]);
      
      categories = await catRes.json();
      tags = await tagRes.json();
      
      await fetchProducts(); // Pierwsze pobranie produktów
    } catch (e) {
      notifications.add('Błąd ładowania sklepu.', 'error');
    }
  }

  // 2. POBIERANIE PRODUKTÓW Z FILTRAMI
  async function fetchProducts() {
    isLoading = true;
    try {
      // Budujemy Query String (np. ?search=abc&category_id=1&tags=1,2)
      const params = new URLSearchParams();
      if (searchTerm) params.append('search', searchTerm);
      if (selectedCategory) params.append('category_id', selectedCategory);
      if (selectedTags.length > 0) params.append('tags', selectedTags.join(','));

      const res = await fetch(`${API_URL}/products?${params.toString()}`);
      if (!res.ok) throw new Error();
      
      const data = await res.json();
      // Obsługa paginacji Laravel (data.data) lub czystej tablicy
      products = data.data ? data.data : data; 

    } catch (e) {
      notifications.add('Nie udało się pobrać produktów.', 'error');
      products = [];
    } finally {
      isLoading = false;
    }
  }

  // 3. DODAWANIE DO KOSZYKA (Twoja logika + Toast)
  async function addToCart(productId: number) {
      const sessionId = localStorage.getItem('session_id') || 'guest_' + Date.now();
      // Zapisz sesję, jeśli jej nie było
      if (!localStorage.getItem('session_id')) localStorage.setItem('session_id', sessionId);

      try {
        const res = await fetch(`${API_URL}/cart`, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-Session-ID': sessionId 
            },
            body: JSON.stringify({ 
                product_id: productId, 
                quantity: 1 
            })
        });

        if (res.ok) {
            notifications.add('Dodano produkt do koszyka!', 'success');
        } else {
            const err = await res.json();
            notifications.add(err.message || 'Błąd dodawania do koszyka.', 'error');
        }
      } catch (e) {
        notifications.add('Błąd połączenia z serwerem.', 'error');
      }
  } 

  // Obsługa zmian w filtrach
  function handleSearch() {
    fetchProducts();
  }

  // Reset filtrów
  function clearFilters() {
    searchTerm = '';
    selectedCategory = '';
    selectedTags = [];
    fetchProducts();
  }

  onMount(init);
</script>

<div class="container mx-auto p-4 flex flex-col md:flex-row gap-8 min-h-screen">

  <aside class="w-full md:w-1/4 space-y-6">
    <div class="bg-white p-6 rounded-lg shadow border border-gray-100 sticky top-24">
      <h2 class="text-xl font-bold mb-4 text-gray-800">Filtrowanie</h2>
      
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-1">Szukaj</label>
        <div class="flex gap-2">
            <input 
              type="text" 
              bind:value={searchTerm} 
              on:keydown={(e) => e.key === 'Enter' && handleSearch()}
              placeholder="Np. Laptop..." 
              class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500"
            />
            <button on:click={handleSearch} class="bg-blue-600 text-white px-3 rounded hover:bg-blue-700">🔍</button>
        </div>
      </div>

      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-1">Kategoria</label>
        <select 
            bind:value={selectedCategory} 
            on:change={handleSearch}
            class="w-full border p-2 rounded bg-white"
        >
            <option value="">Wszystkie</option>
            {#each categories as cat}
                <option value={cat.category_id}>{cat.name}</option>
            {/each}
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Tagi</label>
        <div class="space-y-2 max-h-60 overflow-y-auto border p-2 rounded bg-gray-50">
            {#each tags as tag}
                <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-100 p-1 rounded">
                    <input 
                        type="checkbox" 
                        bind:group={selectedTags} 
                        value={tag.tag_id} 
                        on:change={handleSearch}
                        class="rounded text-blue-600 focus:ring-blue-500"
                    />
                    <span class="text-gray-600 text-sm">{tag.name}</span>
                </label>
            {/each}
        </div>
      </div>
      
      <button 
        on:click={clearFilters}
        class="mt-6 w-full text-sm text-gray-500 hover:text-red-500 underline"
      >
        Wyczyść filtry
      </button>
    </div>
  </aside>

  <main class="w-full md:w-3/4">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Oferta Sklepu</h1>

    {#if isLoading}
        <div class="text-center py-20 text-gray-500">Ładowanie produktów...</div>
    {:else if products.length === 0}
        <div class="bg-yellow-50 p-10 rounded text-center border border-yellow-200 text-yellow-800">
            Nie znaleziono produktów dla podanych kryteriów.
        </div>
    {:else}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        {#each products as product (product.product_id)}
            <div class="bg-white border rounded-lg shadow-sm overflow-hidden hover:shadow-md transition flex flex-col h-full group">
            
            <div class="relative h-48 overflow-hidden bg-gray-100">
                <img 
                    src={product.front_image || 'https://placehold.co/600x400'} 
                    alt={product.name} 
                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                />
                {#if product.category}
                    <span class="absolute top-2 right-2 bg-black/60 text-white text-xs px-2 py-1 rounded">
                        {product.category.name}
                    </span>
                {/if}
            </div>

            <div class="p-4 flex-grow flex flex-col">
                <h2 class="font-bold text-lg mb-1 text-gray-800 line-clamp-1">{product.name}</h2>
                
                <div class="flex flex-wrap gap-1 mb-4 h-6 overflow-hidden">
                {#if product.tags}
                    {#each product.tags as tag}
                        <span class="bg-blue-50 text-blue-600 text-[10px] px-2 py-0.5 rounded-full border border-blue-100">
                        #{tag.name}
                        </span>
                    {/each}
                {/if}
                </div>

                <div class="mt-auto flex justify-between items-center pt-3 border-t">
                    <span class="text-xl font-bold text-green-700">{product.price} zł</span>
                    
                    <div class="flex gap-2">
                        <a 
                            href="/products/{product.product_id}"
                            class="bg-gray-100 text-gray-700 px-3 py-2 rounded text-sm hover:bg-gray-200 transition"
                        >
                            Szczegóły
                        </a>

                        <button 
                            on:click={() => addToCart(product.product_id)} 
                            class="bg-blue-600 text-white px-3 py-2 rounded text-sm hover:bg-blue-700 transition shadow-sm"
                        >
                            Do koszyka
                        </button>
                    </div>
                </div>
            </div>
            </div>
        {/each}
        </div>
    {/if}
  </main>
</div>
