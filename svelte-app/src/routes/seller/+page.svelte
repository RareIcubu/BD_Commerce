<script lang="ts">
    import { notifications } from '$lib/notificationStore';
    import { onMount } from 'svelte';

    // --- TYPY DANYCH ---
    type Category = { category_id: number; name: string; };
    type Tag = { tag_id: number; name: string; };
    type Product = {
        product_id: number;
        name: string;
        price: string;
        description: string;
        front_image: string | null;
        is_active: boolean;
        category?: { name: string }; // Do wyświetlania nazwy w tabeli
        tags?: Tag[];
    };

    // --- ZMIENNE STANU ---
    let products: Product[] = [];
    let categories: Category[] = []; // Lista dostępnych kategorii
    let availableTags: Tag[] = [];   // Lista dostępnych tagów
    
    // --- ZMIENNE FORMULARZA ---
    let name = '';
    let description = '';
    let price = '';
    let selectedCategoryId: number | null = null; // Teraz to liczba z Selecta
    let selectedTagIds: number[] = [];            // Tablica wybranych tagów

    let isLoading = true;
    const API_URL = 'http://localhost:8000/api';

    let isEditing = false;
    let editingProductId: number | null = null;

    // --- 1. POBIERANIE DANYCH (Produkty + Kategorie + Tagi) ---
    async function loadData() {
        isLoading = true;
        try {
            // Używamy Promise.all, żeby pobrać wszystko równolegle (szybciej!)
            const [prodRes, catRes, tagRes] = await Promise.all([
                fetch(`${API_URL}/seller/products`),
                fetch(`${API_URL}/categories`),
                fetch(`${API_URL}/tags`)
            ]);

            if (!prodRes.ok || !catRes.ok || !tagRes.ok) 
                throw new Error('Błąd pobierania danych słownikowych');

            products = await prodRes.json();
            categories = await catRes.json();
            availableTags = await tagRes.json();

            // Ustaw domyślnie pierwszą kategorię, jeśli dostępna
            if (categories.length > 0 && !selectedCategoryId) {
                selectedCategoryId = categories[0].category_id;
            }

        } catch (e) {
            console.error(e);
            notifications.add('Błąd inicjalizacji panelu.', 'error');
        } finally {
            isLoading = false;
        }
    }

    // --- 2. DODAWANIE PRODUKTU ---
    async function addProduct() {
        const method = 'POST';
        
        const url = isEditing && editingProductId 
            ? `${API_URL}/seller/products/${editingProductId}` 
            : `${API_URL}/seller/products`; 

        if (!selectedCategoryId) {
            notifications.add('Wybierz kategorię!', 'error');
            return;
        }

        const payload: any = {
            name,
            description,
            price: parseFloat(price),
            category_id: selectedCategoryId,
            tags: selectedTagIds,
            seller_id: 2, 
            front_image: 'https://placehold.co/600x400',
            is_active: true
        };

        if (isEditing) {
            payload['_method'] = 'PUT';
        }

        try {
            const res = await fetch(url, {
                method: method,
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                notifications.add(isEditing ? 'Produkt zaktualizowany!' : 'Produkt dodany!', 'success');
                resetForm(); 
                const prodRes = await fetch(`${API_URL}/seller/products`);
                products = await prodRes.json();
            } else {
                const data = await res.json();
                notifications.add(data.message || 'Błąd zapisu.', 'error');
            }
        } catch (e) {
            notifications.add('Błąd połączenia.', 'error');
            console.error("Szczegóły błędu:", e);
        }
    }

    // --- 3. USUWANIE ---
    async function deleteProduct(id: number) {
        if (!confirm('Usunąć produkt?')) return;
        try {
            const res = await fetch(`${API_URL}/seller/products/${id}`, { method: 'DELETE' });
            if (!res.ok) throw new Error();
            products = products.filter(p => p.product_id !== id);
            notifications.add('Usunięto.', 'success');
        } catch (e) {
            notifications.add('Błąd usuwania.', 'error');
        }
    }

    // --- 4. RESET FORMULARZA PRODUKTU ---
    function resetForm() {
        isEditing = false;
        editingProductId = null;
        name = '';
        price = '';
        description = '';
        selectedTagIds = [];
    }

    // --- 5. STAN POCZATKOWY EDYCJI PRODUKTU ---
    function startEdit(product: Product) {
        isEditing = true;
        editingProductId = product.product_id;
        
        name = product.name;
        price = product.price.toString();
        description = product.description;
        selectedCategoryId = categories.find(c => c.name === product.category?.name)?.category_id || null;

        if (product.tags && Array.isArray(product.tags)) {
            selectedTagIds = product.tags.map((t: Tag) => t.tag_id);
        } else {
            selectedTagIds = [];
        }
        
    }

    onMount(() => {
        loadData();
    });
</script>

<div class="container mx-auto p-6 max-w-6xl space-y-12">
    
    <div class="bg-white p-8 rounded-lg shadow-md border border-gray-100">
        <h1 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-2">
            {isEditing ? 'Edytuj Produkt' : 'Dodaj Nowy Produkt'}
        </h1>
        
        <form on:submit|preventDefault={addProduct} class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nazwa produktu</label>
                    <input type="text" bind:value={name} class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500" required />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cena (PLN)</label>
                    <input type="number" step="0.01" bind:value={price} class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500" required />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                     <label class="block text-sm font-medium text-gray-700 mb-1">Kategoria</label>
                     <select bind:value={selectedCategoryId} class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500 bg-white">
                        {#each categories as cat}
                            <option value={cat.category_id}>{cat.name}</option>
                        {/each}
                     </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tagi</label>
                    <div class="flex flex-wrap gap-3 p-3 border rounded bg-gray-50 max-h-32 overflow-y-auto">
                        {#each availableTags as tag}
                            <label class="inline-flex items-center bg-white border px-2 py-1 rounded cursor-pointer hover:bg-blue-50">
                                <input 
                                    type="checkbox" 
                                    bind:group={selectedTagIds} 
                                    value={tag.tag_id} 
                                    class="form-checkbox h-4 w-4 text-blue-600 rounded mr-2"
                                >
                                <span class="text-sm text-gray-700">{tag.name}</span>
                            </label>
                        {/each}
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Wybrano: {selectedTagIds.length}</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Opis</label>
                <textarea bind:value={description} class="w-full border p-2 rounded h-20 focus:ring-2 focus:ring-blue-500" required></textarea>
            </div>

            <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded hover:bg-blue-700 font-bold transition shadow-lg">
                {isEditing ? 'Zapisz Zmiany' : '+ Opublikuj Produkt'}
            </button>
            {#if isEditing}
                <button type="button" on:click={resetForm} class="bg-gray-500 text-white px-6 py-3 rounded hover:bg-gray-600 font-bold transition">
                    Anuluj
                </button>
            {/if}
        </form>
    </div>

    <div>
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Twoje Aktywne Produkty</h2>
        {#if isLoading}
            <div class="text-center py-10">Ładowanie danych...</div>
        {:else if products.length === 0}
            <div class="text-center text-gray-500 py-10 bg-gray-50 rounded">Brak produktów.</div>
        {:else}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-600 text-sm uppercase">
                        <tr>
                            <th class="p-4">Nazwa</th>
                            <th class="p-4">Kategoria</th> <th class="p-4">Cena</th>
                            <th class="p-4 text-center">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        {#each products as product (product.product_id)}
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-4 font-medium">{product.name}</td>
                                <td class="p-4 text-gray-500 text-sm">
                                    {product.category?.name || '---'} 
                                </td>
                                <td class="p-4 text-green-600 font-bold">{product.price} zł</td>
                                <td class="p-4 text-center space-x-6">
                                    <button 
                                        on:click={() => startEdit(product)}
                                        class="text-blue-500 hover:text-blue-700 font-medium"
                                    >Edytuj</button>
                                    <button 
                                        on:click={() => deleteProduct(product.product_id)}
                                        class="text-red-500 hover:text-red-700 font-medium"
                                    >Usuń</button>
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        {/if}
    </div>
</div>
