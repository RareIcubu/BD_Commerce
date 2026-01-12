<script lang="ts">
    import type { PageData } from './$types';

    export let data: PageData;
    
    // Używamy reaktywności Svelte ($:), aby lista odświeżała się, 
    // gdy data.users się zmieni
    $: users = data.users || [];

    async function changeRole(user_id: number, new_role_id: string) {
        const res = await fetch(`/api/admin/users/${user_id}/role`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ role_id: new_role_id})
        });
        if (res.ok) alert('Role updated!');
    }
</script>

<style>
    table {
        width: 100%;
        border-collapse: collapse; /* Kluczowe: łączy krawędzie, ale separuje komórki */
        margin-top: 1rem;
        background: white;
    }

    th {
        background-color: #f4f4f4;
        text-align: left;
        padding: 12px;
        border-bottom: 2px solid #ddd;
    }

    td {
        padding: 12px;
        border-bottom: 1px solid #eee;
    }

    tr:hover {
        background-color: #fafafa;
    }

    select {
        padding: 5px;
        border-radius: 4px;
        border: 1px solid #ccc;
    }

    a {
        margin-left: 10px;
        color: #007bff;
        text-decoration: none;
    }
</style>

<pre>{JSON.stringify(data, null, 2)}</pre>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Current Role</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        {#each data.users.users as user}
            <tr>
                <td>{user.name} {user.surname}</td>
                <td>{user.email}</td>
                <td>{user.role.name}</td>
                <td>
                    <select on:change={(e) => {
                        const target = e.target as HTMLSelectElement;
                        if (target) {
                            changeRole(user.user_id, target.value);
                        }
                    }}>
                        <option value="1">Admin</option>
                        <option value="2">Seller</option>
                        <option value="3">Client</option>
                    </select>
                    <a href="/admin/sellers/{user.user_id}/products">View Products</a>
                </td>
            </tr>
        {/each}
    </tbody>
</table>