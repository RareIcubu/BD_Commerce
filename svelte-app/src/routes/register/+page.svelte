<script lang="ts">
    import { goto } from '$app/navigation';

    let name = '';
    let surname = '';
    let email = '';
    let password = '';
    let message = '';

   async function handleRegister() {
        console.log("Wysyłanie rejestracji..."); // Log startu
        try {
            const res = await fetch('http://localhost:8000/api/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, surname, email, password })
            });
            
            console.log("Status:", res.status); // Log statusu

            if (res.ok) {
                alert('Konto utworzone! Możesz się zalogować.');
                goto('/login');
            } else {
                // To jest kluczowe - obsługa błędu gdy odpowiedź nie jest JSONem
                const text = await res.text();
                try {
                    const data = JSON.parse(text);
                    message = JSON.stringify(data.errors || data.message);
                } catch {
                    console.error("Błąd serwera (HTML):", text);
                    message = "Wystąpił błąd serwera (sprawdź konsolę przeglądarki)";
                }
            }
        } catch (e) {
            console.error(e);
            message = 'Błąd połączenia.';
        }
    }
</script>

<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow mt-10">
    <h1 class="text-2xl font-bold mb-6 text-center">Załóż konto</h1>
    {#if message} <p class="text-red-500 mb-4 text-sm">{message}</p> {/if}

    <form on:submit|preventDefault={handleRegister} class="space-y-4">
        <div class="grid grid-cols-2 gap-2">
            <input type="text" bind:value={name} placeholder="Imię" class="w-full p-2 border rounded" required>
            <input type="text" bind:value={surname} placeholder="Nazwisko" class="w-full p-2 border rounded" required>
        </div>
        <input type="email" bind:value={email} placeholder="Email" class="w-full p-2 border rounded" required>
        <input type="password" bind:value={password} placeholder="Hasło (min. 6 znaków)" class="w-full p-2 border rounded" required>
        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded font-bold hover:bg-green-700">Zarejestruj się</button>
    </form>
</div>
