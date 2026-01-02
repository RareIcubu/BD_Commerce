<script lang="ts">
  import { onMount } from 'svelte';
  import { products, loading, error, fetchProducts, type Product} from '../lib/productStores'

  
  //let products: Product[] = [];
  //let loading = true;
  //let error: string | null = null;
  let searchTerm = '';

  
async function addToCart(productId: number) {
      const sessionId = localStorage.getItem('session_id') || '';
      
      const res = await fetch('http://localhost:8000/api/cart', {
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
          alert('Dodano produkt do koszyka!');
      } else {
          alert('Błąd dodawania do koszyka.');
      }
  } 
  onMount(() => {
    fetchProducts();
  });

  function handleSearch() {
    const cleanTerm = searchTerm.startsWith('#') 
        ? searchTerm.slice(1) 
        : searchTerm;
    fetchProducts(cleanTerm);
  }
</script>

<div class="container mx-auto p-4">
  <h1 class="text-3xl font-bold mb-6">Nasz Sklep</h1>

  <div class="mb-6 flex gap-2">
    <input 
      type="text" 
      bind:value={searchTerm} 
      placeholder="Szukaj produktu..." 
      class="border p-2 rounded w-full max-w-md"
    />
    <button 
      on:click={handleSearch}
      class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
    >
      Szukaj
    </button>
  </div>

  {#if $loading}
    <p>Ładowanie produktów...</p>
  {:else if $products.length === 0}
    <p>Nie znaleziono żadnych produktów.</p>
  {:else if $error}
    <p class="text-red-500">Błąd: {error}</p>
  {:else}
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
      {#each $products as product}
        <div class="border rounded-lg shadow-sm overflow-hidden hover:shadow-md transition">
          <img 
            src={product.front_image || 'https://placehold.co/600x400'} 
            alt={product.name} 
            class="w-full h-48 object-cover"
          />
          <div class="p-4">
            <div class="text-xs text-gray-500 mb-1">
              {product.category?.name || 'Bez kategorii'}
            </div>
            <h2 class="font-bold text-lg mb-2">{product.name}</h2>
            
            <div class="flex flex-wrap gap-1 mb-3">
              {#each product.tags as tag}
                <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded">
                  #{tag.name}
                </span>
              {/each}
            </div>

            <div class="flex justify-between items-center mt-4">
              <span class="text-xl font-bold">{product.price} PLN</span>
              <button on:click={() => addToCart(product.product_id)} class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                Do koszyka
              </button>
            </div>
          </div>
        </div>
      {/each}
    </div>
  {/if}
</div>
