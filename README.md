# BD Commerce

Projekt e-commerce z backendem w Laravel i frontendem w Svelte, uruchamiany w Dockerze.

## Struktura projektu

- `laravel/` – backend Laravel
- `svelte-app/` – frontend Svelte (Vite)
- `docker-compose.yml` – konfiguracja Docker Compose
- `.env.example` – przykład konfiguracji środowiskowej

## Wymagania

- Docker i Docker Compose
- Node.js (opcjonalnie do lokalnego uruchamiania frontend)
- PHP (opcjonalnie do lokalnego uruchamiania backend)

## Uruchamianie projektu

1. Skopiuj przykładowy `.env`:
   ```bash
    cp .env.example .env

2. Uzupełnij wartości (bazy danych, hasła itd.).

    Zbuduj i uruchom kontenery:
    ```bash
    docker compose up --build

3. Dostęp do aplikacji:

- Backend Laravel: http://localhost:8000
- Frontend Svelte: http://localhost:5173
- phpMyAdmin: http://localhost:8080 (login i hasło z .env)
## Tworzenie migracji i seedów Laravel

```bash
    docker compose exec laravle_app php artisan migrate
    docker compose exec laravel_app php artisam db:seed
