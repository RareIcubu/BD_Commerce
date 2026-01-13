<script lang="ts">
  import { onMount } from 'svelte';
  import { notifications } from '$lib/notificationStore';

  // Typy
  type Product = { product_id: number; name: string; price: string; front_image: string; category?: { name: string }; tags?: { name: string }[] };
  type Category = { category_id: number; name: string };
  type Tag = { tag_id: number; name: string };

  let products: Product[] = [];
  let featuredProducts: Product[] = [];
  let categories: Category[] = [];
  let tags: Tag[] = [];
  
  let isLoading = true;

  // Filtry
  let searchTerm = '';
  let selectedCategory = '';
  let selectedTags: number[] = [];
  let sortBy = 'newest';
  
  // NOWOŚĆ: Paginacja
  let currentPage = 1;
  let lastPage = 1;

  const API_URL = 'http://localhost:8000/api';

  async function init() {
    try {
      const [catRes, tagRes, featRes] = await Promise.all([
        fetch(`${API_URL}/categories`),
        fetch(`${API_URL}/tags`),
        fetch(`${API_URL}/products/featured`)
      ]);
      
      categories = await catRes.json();
      tags = await tagRes.json();
      featuredProducts = await featRes.json();
      
      await fetchProducts(1); // Startujemy od 1. strony
    } catch (e) {
      notifications.add('Błąd ładowania sklepu.', 'error');
    }
  }

  // ZMIANA: Parametr page
  async function fetchProducts(page = 1) {
    isLoading = true;
    try {
      const params = new URLSearchParams();
      if (searchTerm) params.append('search', searchTerm);
      if (selectedCategory) params.append('category_id', selectedCategory);
      if (selectedTags.length > 0) params.append('tags', selectedTags.join(','));
      params.append('sort_by', sortBy);
      
      // Dodajemy numer strony do zapytania
      params.append('page', page.toString());

      const res = await fetch(`${API_URL}/products?${params.toString()}`);
      if (!res.ok) throw new Error();
      
      const data = await res.json();
      
      // Laravel zwraca obiekt: { data: [...], current_page: 1, last_page: 5, ... }
      products = data.data;
      currentPage = data.current_page;
      lastPage = data.last_page;

      // Przewiń do góry listy, jeśli to nie pierwsza strona
      if (page > 1) {
         const main = document.querySelector('main');
         if(main) main.scrollIntoView({ behavior: 'smooth' });
      }

    } catch (e) {
      notifications.add('Nie udało się pobrać produktów.', 'error');
      products = [];
    } finally {
      isLoading = false;
    }
  }

  function handleFilterChange() {
    fetchProducts(1); // Przy zmianie filtrów resetujemy do strony 1
  }

  function changePage(newPage: number) {
    if (newPage >= 1 && newPage <= lastPage) {
        fetchProducts(newPage);
    }
  }

  async function addToCart(productId: number) {
      const sessionId = localStorage.getItem('session_id') || 'guest_' + Date.now();
      if (!localStorage.getItem('session_id')) localStorage.setItem('session_id', sessionId);

      try {
        await fetch(`${API_URL}/cart`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Session-ID': sessionId },
            body: JSON.stringify({ product_id: productId, quantity: 1 })
        });
        notifications.add('Dodano produkt do koszyka!', 'success');
      } catch (e) {
        notifications.add('Błąd serwera.', 'error');
      }
  } 

  function clearFilters() {
    searchTerm = '';
    selectedCategory = '';
    selectedTags = [];
    sortBy = 'newest';
    fetchProducts(1);
  }

  onMount(init);
</script>

<div class="container mx-auto p-4 flex flex-col gap-8 min-h-screen">

  {#if featuredProducts.length > 0 && !searchTerm && !selectedCategory && currentPage === 1}
  <section class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-6 md:p-10 shadow-xl text-white">
      <h2 class="text-2xl md:text-3xl font-bold mb-6 flex items-center gap-2">
          🔥 Bestsellery Tygodnia
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          {#each featuredProducts as product}
              <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl p-4 flex items-center gap-4 hover:bg-white/20 transition cursor-pointer group">
                  <img 
                      src={product.front_image || 'https://placehold.co/150'} 
                      alt={product.name}
                      class="w-20 h-20 object-cover rounded-lg shadow-md group-hover:scale-110 transition"
                  />
                  <div>
                      <h3 class="font-bold text-lg leading-tight mb-1">{product.name}</h3>
                      <p class="text-green-300 font-bold text-xl">{product.price} zł</p>
                      <a href="/products/{product.product_id}" class="text-xs uppercase tracking-wide opacity-80 hover:opacity-100 hover:underline mt-1 block">Zobacz produkt →</a>
                  </div>
              </div>
          {/each}
      </div>
  </section>
  {/if}

  <div class="flex flex-col md:flex-row gap-8">
    <aside class="w-full md:w-1/4 space-y-6">
      <div class="bg-white p-6 rounded-lg shadow border border-gray-100 sticky top-24">
        <h2 class="text-xl font-bold mb-4 text-gray-800">Filtrowanie</h2>
        
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-1">Szukaj</label>
          <div class="relative">
              <input 
                type="text" 
                bind:value={searchTerm} 
                on:keydown={(e) => e.key === 'Enter' && handleFilterChange()}
                placeholder="Np. Laptop..." 
                class="w-full border p-2 pl-8 rounded focus:ring-2 focus:ring-blue-500"
              />
              <span class="absolute left-2.5 top-2.5 text-gray-400">🔍</span>
          </div>
        </div>

        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-1">Kategoria</label>
          <select 
              bind:value={selectedCategory} 
              on:change={handleFilterChange}
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
          <div class="space-y-2 max-h-60 overflow-y-auto border p-2 rounded bg-gray-50 custom-scrollbar">
              {#each tags as tag}
                  <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-100 p-1 rounded transition">
                      <input 
                          type="checkbox" 
                          bind:group={selectedTags} 
                          value={tag.tag_id} 
                          on:change={handleFilterChange}
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
      
      <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
          <h1 class="text-3xl font-bold text-gray-800">
              {#if searchTerm}Wyniki wyszukiwania{:else}Pełna Oferta{/if}
          </h1>

          <div class="flex items-center gap-2">
              <span class="text-sm text-gray-500">Sortuj:</span>
              <select 
                  bind:value={sortBy} 
                  on:change={handleFilterChange}
                  class="border border-gray-300 rounded-md py-1 px-3 text-sm focus:ring-blue-500 focus:border-blue-500 bg-white shadow-sm"
              >
                  <option value="newest">Najnowsze</option>
                  <option value="price_asc">Cena: rosnąco</option>
                  <option value="price_desc">Cena: malejąco</option>
                  <option value="name_asc">Nazwa: A-Z</option>
              </select>
          </div>
      </div>

      {#if isLoading}
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
             {#each Array(6) as _}
                <div class="h-80 bg-gray-100 rounded-lg animate-pulse"></div>
             {/each}
          </div>
      {:else if products.length === 0}
          <div class="bg-yellow-50 p-10 rounded text-center border border-yellow-200 text-yellow-800">
              Nie znaleziono produktów dla podanych kryteriów.
          </div>
      {:else}
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {#each products as product (product.product_id)}
              <div class="bg-white border rounded-lg shadow-sm overflow-hidden hover:shadow-xl transition-shadow duration-300 flex flex-col h-full group">
              
              <div class="relative h-48 overflow-hidden bg-gray-100">
                  <img 
                      src={product.front_image || 'https://placehold.co/600x400'} 
                      alt={product.name} 
                      class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                  />
                  {#if product.category}
                      <span class="absolute top-2 right-2 bg-black/60 text-white text-[10px] uppercase font-bold px-2 py-1 rounded backdrop-blur-sm">
                          {product.category.name}
                      </span>
                  {/if}
              </div>

              <div class="p-4 flex-grow flex flex-col">
                  <h2 class="font-bold text-lg mb-1 text-gray-800 line-clamp-1" title={product.name}>{product.name}</h2>
                  
                  <div class="flex flex-wrap gap-1 mb-4 h-6 overflow-hidden">
                  {#if product.tags}
                      {#each product.tags as tag}
                          <span class="bg-gray-100 text-gray-600 text-[10px] px-2 py-0.5 rounded-full">
                          #{tag.name}
                          </span>
                      {/each}
                  {/if}
                  </div>

                  <div class="mt-auto flex justify-between items-center pt-3 border-t">
                      <span class="text-xl font-bold text-gray-900">{product.price} <span class="text-sm font-normal text-gray-500">zł</span></span>
                      
                      <div class="flex gap-2">
                          <a 
                              href="/products/{product.product_id}"
                              class="text-gray-500 hover:text-blue-600 p-2 rounded transition"
                          >
                              ℹ️
                          </a>
                          <button 
                              on:click={() => addToCart(product.product_id)} 
                              class="bg-blue-600 text-white px-3 py-2 rounded-md text-sm hover:bg-blue-700 transition shadow hover:shadow-md"
                          >
                              Do koszyka
                          </button>
                      </div>
                  </div>
              </div>
              </div>
          {/each}
          </div>

          {#if lastPage > 1}
          <div class="mt-12 flex justify-center items-center gap-2">
              <button 
                  on:click={() => changePage(currentPage - 1)}
                  disabled={currentPage === 1}
                  class="px-4 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                  Poprzednia
              </button>
              
              <span class="text-gray-600 font-medium px-4">
                  Strona {currentPage} z {lastPage}
              </span>

              <button 
                  on:click={() => changePage(currentPage + 1)}
                  disabled={currentPage === lastPage}
                  class="px-4 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                  Następna
              </button>
          </div>
          {/if}
      {/if}
    </main>
  </div>
</div>
