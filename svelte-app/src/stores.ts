import { writable } from 'svelte/store';

// Typ użytkownika
export interface User {
    user_id: number;
    name: string;
    surname: string;
    email: string;
    role_id: number;
}

// Pobieramy usera z localStorage przy starcie (żeby nie wylogowywało po odświeżeniu)
const storedUser = typeof localStorage !== 'undefined' ? localStorage.getItem('user') : null;
const initialUser = storedUser ? JSON.parse(storedUser) : null;

export const user = writable<User | null>(initialUser);

// Subskrybuj zmiany i aktualizuj localStorage
user.subscribe((value) => {
    if (typeof localStorage !== 'undefined') {
        if (value) {
            localStorage.setItem('user', JSON.stringify(value));
        } else {
            localStorage.removeItem('user');
        }
    }
});
