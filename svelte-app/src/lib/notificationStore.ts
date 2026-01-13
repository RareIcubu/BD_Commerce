import { writable } from 'svelte/store';

export type NotificationType = 'success' | 'error' | 'info';

export interface Notification {
    id: number;
    message: string;
    type: NotificationType;
}

function createNotificationStore() {
    const { subscribe, update } = writable<Notification[]>([]);

    return {
        subscribe,
        add: (message: string, type: NotificationType = 'info') => {
            const id = Date.now();
            update((n) => [...n, { id, message, type }]);
            
            // Auto usuwanie po 5s
            setTimeout(() => {
                update((n) => n.filter((t) => t.id !== id));
            }, 5000);
        },
        remove: (id: number) => {
            update((n) => n.filter((t) => t.id !== id));
        }
    };
}

// WAŻNE: Musi być "export const notifications"
export const notifications = createNotificationStore();
