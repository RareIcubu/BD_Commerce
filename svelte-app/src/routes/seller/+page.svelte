<script lang="ts">
    let name = '';
    let description = '';
    let price = '';
    let category_id = '1';
    let message = '';

    async function addProduct() {
        try {
            const res = await fetch('http://localhost:8000/api/seller/products', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name,
                    description,
                    price: parseFloat(price),
                    category_id: parseInt(category_id),
                    seller_id: 2, // Hardcoded ID sprzedawcy (symulacja)
                    front_image: 'https://placehold.co/600x400'
                })
            });

            if (res.ok) {
                message = 'Produkt dodany pomyślnie!';
                name = ''; price = ''; description = ''; // Reset formularza
            } else {
                message = 'Błąd podczas dodawania produktu.';
            }
        } catch (e) {
            message = 'Błąd połączenia.';
        }
    }
</script>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Panel Sprzedawcy: Dodaj Produkt</h1>

    {#if message}
        <div class="mb-4 p-3 bg-blue-100 text-blue-800 rounded">{message}</div>
    {/if}

    <form on:submit|preventDefault={addProduct} class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nazwa produktu</label>
            <input type="text" bind:value={name} class="w-full border p-2 rounded" required />
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Opis</label>
            <textarea bind:value={description} class="w-full border p-2 rounded h-24" required></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cena (PLN)</label>
                <input type="number" step="0.01" bind:value={price} class="w-full border p-2 rounded" required />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ID Kategorii</label>
                <input type="number" bind:value={category_id} class="w-full border p-2 rounded" required />
            </div>
        </div>

        <button type="submit" class="w-full bg-green-600 text-white py-3 rounded hover:bg-green-700 font-bold">
            Dodaj Produkt
        </button>
    </form>
</div>
