<script lang="ts">
    import { notifications } from '$lib/notificationStore';
    import { fly } from 'svelte/transition';
</script>

<div class="fixed top-24 right-4 z-[9999] flex flex-col gap-2 pointer-events-none">
    {#each $notifications as note (note.id)}
        <div 
            in:fly={{ x: 200, duration: 300 }} 
            out:fly={{ x: 200, duration: 300 }}
            class="pointer-events-auto px-4 py-3 rounded shadow-lg text-white min-w-[300px] flex justify-between items-center cursor-pointer transition-opacity hover:opacity-90"
            class:bg-green-500={note.type === 'success'}
            class:bg-red-500={note.type === 'error'}
            class:bg-blue-500={note.type === 'info'}
            on:click={() => notifications.remove(note.id)}
            on:keydown={() => {}}
            role="button"
            tabindex="0"
        >
            <span class="font-medium">{note.message}</span>
            <span class="ml-2 font-bold opacity-75 hover:opacity-100">&times;</span>
        </div>
    {/each}
</div>
