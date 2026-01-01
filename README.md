# Aplikacja Sklep.io

Platforma sprzedażowa zbudowana w architekturze rozdzielonej, wykorzystująca **Laravel** jako silnik API oraz **Svelte** jako interfejs użytkownika. Całość środowiska utrzymywana jest w pełni w kontenerach **Docker**. System opiera się na relacyjnym modelu danych zaimplementowanym w MySQL. 

## Tech Stack

| Warstwa | Technologia |
| :--- | :--- |
| **Backend** | Laravel 11 (PHP 8.3) |
| **Frontend** | Svelte / SvelteKit |
| **Baza Danych** | MySQL 8.4 |
| **Konteneryzacja** | Docker & Docker Compose |
| **Komunikacja** | REST API / JSON |


## Instalacja i Konfiguracja

### Wymagania:
* Docker & Docker Desktop
* Git
* Node.js (opcjonalnie do lokalnego uruchamiania frontend)
* PHP (opcjonalnie do lokalnego uruchamiania backend)

### Kroki instalacyjne:

1.  **Klonowanie repozytorium:**
    ```bash
    git clone https://github.com/RareIcubu/BD_Commerce.git
    cd BD_Commerce
    ```

2.  **Konfiguracja środowiska:**
    Skopiuj plik `.env.example` i upewnij się, że dane bazy danych są zgodne z tymi w `docker-compose.yml`:
    ```bash
    cp .env.example .env
    ```

3.  **Uruchomienie kontenerów:**
    ```bash
    docker compose up -d
    ```

4.  **Inicjalizacja aplikacji (Migracje i Dane):**
    Załaduj zmienne środowiskowe i przygotuj bazę danych (komendy uruchamiane wewnątrz kontenera):
    ```bash
    # Eksport zmiennych (macOS/Linux)
    export $(grep -v '^#' .env | xargs)

    # Instalacja i migracja
    docker compose run --rm laravel_app composer install
    docker compose run --rm laravel_app php artisan key:generate
    docker compose run --rm laravel_app php artisan migrate --seed
    ```

## Dostęp do aplikacji

* Backend Laravel: http://localhost:8000
* Frontend Svelte: http://localhost:5173
* phpMyAdmin: http://localhost:8080 (login i hasło z .env)

## Licencja
Projekt realizowany w celach edukacyjnych w ramach przedmiotu Bazy Danych na Politechnice Wrocławskiej.
