<script lang="ts">
    import { user } from '../../stores';
    import { goto } from '$app/navigation';

    let email = '';
    let password = '';
    let error = '';

    async function handleLogin() {
        try {
            const res = await fetch('http://localhost:8000/api/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });
            
            const data = await res.json();

            if (res.ok) {
                user.set(data.user); 
                goto('/');
            } else {
                error = data.message || 'Błąd logowania';
            }
        } catch (e) {
            error = 'Błąd połączenia z serwerem';
        }
    }
</script>

<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow mt-10">
    <h1 class="text-2xl font-bold mb-6 text-center">Zaloguj się</h1>
    {#if error} <p class="text-red-500 mb-4 text-center">{error}</p> {/if}

    <form on:submit|preventDefault={handleLogin} class="space-y-4">
        <input type="email" bind:value={email} placeholder="Email" class="w-full p-2 border rounded" required>
        <input type="password" bind:value={password} placeholder="Hasło" class="w-full p-2 border rounded" required>
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded font-bold hover:bg-blue-700">Zaloguj</button>
    </form>
    <p class="mt-4 text-center text-sm">Nie masz konta? <a href="/register" class="text-blue-600">Zarejestruj się</a></p>
</div>
