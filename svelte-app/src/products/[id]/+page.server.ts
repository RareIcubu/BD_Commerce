import { error } from '@sveltejs/kit';
import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ params, fetch }) => {
    console.log("Ładowanie produktu o ID:", params.id); // Zobaczysz to w terminalu Dockera

    try {
        const res = await fetch(`http://laravel_app:8000/api/products/${params.id}`);
        
        if (!res.ok) {
            console.error("Błąd API:", res.status);
            throw error(res.status, 'Nie znaleziono produktu w API');
        }

        const data = await res.json();
        return {
            product: data.product,
            suggested: data.suggested || []
        };
    } catch (e) {
        console.error("Błąd połączenia fetch:", e);
        throw error(500, 'Problem z komunikacją między kontenerami');
    }
};