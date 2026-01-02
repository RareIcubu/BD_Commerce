import { writable } from 'svelte/store';

export interface Product {
    product_id: number;
    name: string;
    price: string;
    front_image: string;
    category: { name: string };
    tags: { name: string }[];
  }


export const products = writable<Product[]>([]);
export const searchTerm = writable('');
export const loading = writable(false);
export const error = writable<string | null>(null);

products.set([]); 

export async function fetchProducts(search = '') {
    loading.set(true);
    error.set(null);
    try {
        const url = new URL('http://localhost:8000/api/products');
        if (search) url.searchParams.append('search', search);

        const res = await fetch(url.toString());
        if (!res.ok) throw new Error('Błąd pobierania danych');
        
        const data = await res.json();
        products.set(data.data); 

    } catch (err) {
        if (err instanceof Error) {
            error.set(err.message);
        } else {
            error.set("Wystąpił nieoczekiwany błąd");
        }
    } finally {
        loading.set(false);
    }
}