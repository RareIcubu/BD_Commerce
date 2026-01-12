<script lang="ts">
    export let data;
    let quantity = 1;
    $: product = data.product;
    $: suggested = data.suggested;

    function addToCart() {
        console.log(`Dodano ${quantity} sztuk produktu ${product.name} do koszyka`);
        // Tu później dodamy logikę Store lub API koszyka
    }
</script>

<style>
    .main-info { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
    
    .suggested-list {
        display: flex;
        overflow-x: auto;
        gap: 1rem;
        padding-bottom: 1rem;
        scrollbar-width: thin; /* Dla Firefox */
    }

    .suggested-item {
        min-width: 200px; /* Kluczowe dla przewijania */
        border: 1px solid #eee;
        padding: 10px;
        text-align: center;
        text-decoration: none;
        color: inherit;
    }

    .suggested-item img { width: 100%; height: 150px; object-fit: cover; }
    
    .tag { background: #eee; padding: 2px 8px; border-radius: 10px; margin-right: 5px; font-size: 0.8rem; }
</style>

<div class="product-container">
    <div class="main-info">
        <img src={product.front_image} alt={product.name} />
        <div class="details">
            <h1>{product.name}</h1>
            <p class="category">Kategoria: {product.category.name}</p>
            <div class="tags">
                {#each product.tags as tag}
                    <span class="tag">{tag.name}</span>
                {/each}
            </div>
            <p class="description">{product.description}</p>
            <p class="price">{product.price} PLN</p>
            
            <div class="cart-actions">
                <input type="number" bind:value={quantity} min="1" />
                <button on:click={addToCart}>Dodaj do koszyka</button>
            </div>
        </div>
    </div>

    <h2>Może Cię zainteresować</h2>
    <div class="suggested-list">
        {#each suggested as item}
            <a href="/products/{item.id}" class="suggested-item">
                <img src={item.front_image} alt={item.name} />
                <p>{item.name}</p>
                <span>{item.price} PLN</span>
            </a>
        {/each}
    </div>
</div>