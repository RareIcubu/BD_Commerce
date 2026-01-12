import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ fetch }) => {
    try {
        const response = await fetch('http://laravel_app:8000/api/admin/users');
        
        if (!response.ok) {
            // Wypisze np. "Błąd Laravela: 401" w logach Dockera
            console.error(`Błąd Laravela: ${response.status} ${response.statusText}`);
            return { users: [], error: `Serwer zwrócił błąd: ${response.status}` };
        }

        const users = await response.json();
        return { users: Array.isArray(users) ? users : (users.data || []) };
    } catch (e) {
        console.error("Błąd sieciowy:", e);
        return { users: [], error: "Błąd połączenia z backendem" };
    }
};