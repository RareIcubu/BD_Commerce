<script lang="ts">
    interface Product {
    product_id: number;
    name: string;
    price: string;
    front_image: string;
    category: { name: string };
    tags: { name: string }[];
  }

    export let data;
    let products = data.products;

    async function deleteProduct(product_id: number) {
        if (!confirm('Are you sure?')) return;
        const res = await fetch(`/api/admin/products/${product_id}`, { method: 'DELETE' });
        if (res.ok) products = products.filter((p: Product) => p.product_id !== product_id);
    }
</script>

<div class="grid">
    {#each products as product}
        <div class="card">
            <h4>{product.name}</h4>
            <p>Price: {product.price}</p>
            <button on:click={() => deleteProduct(product.product_id)} class="btn-danger">
                Delete Product
            </button>
        </div>
    {/each}
</div>