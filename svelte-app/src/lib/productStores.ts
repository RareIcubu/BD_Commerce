import { writable } from 'svelte/store';

export interface Product {
    product_id: number;
    name: string;
    price: string;
    front_image: string;
    category: { name: string };
    tags: { name: string }[];
  }

export interface Category {
    category_id: number;
    name: string;
  }


export const products = writable<Product[]>([]);
export const searchTerm = writable('');
export const loading = writable(false);
export const error = writable<string | null>(null);
export const categories = writable<Category[]>([]);

products.set([]); 

export async function fetchProducts(query = '') {
    loading.set(true);
    error.set(null);
    try {
        // Tworzymy obiekt URL, co ułatwia dodawanie parametrów
        const url = new URL('http://localhost:8000/api/products');

        if (query) {
            // SPRAWDZENIE: Czy query wygląda jak parametr techniczny (ma znak "=")?
            if (query.includes('=')) {
                // Jeśli tak (np. "category_id=1"), dzielimy to i dodajemy jako parametr
                const [key, value] = query.split('=');
                url.searchParams.append(key, value);
            } else {
                // Jeśli nie, traktujemy to jako zwykłe wyszukiwanie tekstowe
                url.searchParams.append('search', query);
            }
        }

        const res = await fetch(url.toString());
        if (!res.ok) throw new Error('Błąd pobierania danych');
        
        const data = await res.json();
        
        // Backend z paginacją zwraca dane w polu 'data'
        products.set(data.data); 
    } catch (err) {
        console.error(err); // Warto widzieć błąd w konsoli
        error.set((err as Error).message);
    } finally {
        loading.set(false);
    }
}
export async function fetchCategories() {
    try {
        const res = await fetch('http://localhost:8000/api/categories');
        if (!res.ok) throw new Error('Błąd pobierania kategorii');
        
        const result = await res.json();
        
        const categoryData = Array.isArray(result) ? result : (result.data || []);
        
        categories.set(categoryData);
        console.log("Categories in store:", categoryData);
    } catch (err) {
        console.error("Failed to load categories", err);
        categories.set([]);
    }
}
