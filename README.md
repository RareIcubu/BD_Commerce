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

## Funkcjonalności
1. **Kluczowe funkcje dla użytkownika (User):**

* **Dynamiczny katalog produktów:** Przeglądanie pełnej listy asortymentu pobieranej w czasie rzeczywistym z bazy danych.

* **Szczegóły produktu:** Dedykowane podstrony dla każdego produktu z opisem zdjęciem, ceną, tagami, a także listą kolejnych rekomendowanych dla użytkownika produktów.
* **System kategorii i tagów:** Intuicyjne filtrowanie i grupowanie ofert, ułatwiające nawigację w sklepie i wyszukiwanie interesujących użytkownika produktów.

* **Zintegrowany Koszyk Sesyjny:** Możliwość dodawania wielu produktów do koszyka bez konieczności logowania oraz zarządzanie ilością produktów z poziomu strony produktu. Trwałość koszyka zapewniona jest dzięki synchronizacji localStorage z bazą danych Laravel.

2. **Kluczowe funkcje dla sprzedawcy (Seller):**
* **Panel sprzedawcy:** Podstrona, z poziomu której możliwe jest dla sprzedawcy dodanie nowego produktu, przegląd oraz usuwanie aktywnych ofert. Zapewnia szybki dostęp do narzędzi administracyjnych bez konieczności nawigacji po stronie konsumenckiej. 

* **Dodawanie produktów:** Formularz pozwalający na definiowanie nazw, opisów, cen oraz stanów magazynowych.

* **Wycofywanie produktów:** Funkcja usuwania produktów z oferty w przypadku zakończenia ich sprzedaży.

* **Kategoryzacja i tagowanie:** Sprzedawcy mają możliwość przypisywania produktów do globalnych kategorii oraz dodawania tagów, co bezpośrednio wpływa na widoczność ich ofert w wynikach wyszukiwania.



3. **Funkcje techniczne (Administrative & Backend):**

* **Zautomatyzowane zarządzanie danymi:** Wykorzystanie Seederów i Fabryk do szybkiego generowania bazy testowej.

* **Bezpieczna komunikacja:** Implementacja polityki CORS oraz walidacja danych wejściowych po stronie serwera.

* **Wizualizacja stanu:** Wykorzystanie narzędzia phpMyAdmin do monitorowania relacji i spójności danych.
## Dokumentacja API
Wszystkie zapytania powinny być kierowane pod adres bazowy: http://localhost:8000/api. Dane wejściowe oraz wyjściowe są przesyłane w formacie JSON.
1. **Produkty, kategorie i tagi**

Dostępne publicznie dla wszystkich użytkowników. 

| Metoda | Endpoint | Opis |
|:--|:--|:--|
|GET|	/products|	Pobiera listę wszystkich produktów (z paginacją/filtrami).|
|GET	|/products/featured	| Pobiera listę produktów wyróżnionych (np. na stronę główną).|
|GET	|/products/{id}	|Pobiera szczegółowe dane konkretnego produktu.|
|GET	|/categories	|Pobiera listę wszystkich kategorii produktów.|
|GET	|/tags	|Pobiera listę wszystkich dostępnych tagów (używane w filtrach/formularzach).|

2. **Koszyk**

Wymaga nagłówka X-Session-ID do identyfikacji koszyka użytkownika.
| Metoda | Endpoint | Opis | Parametry |
|:--|:--|:--|:--|
|GET	|/cart	|Pobiera aktualną zawartość koszyka sesji.	|Brak|
|POST	|/cart	|Dodaje produkt do koszyka.	|product_id, quantity|
|PUT	|/cart/{product_id}	|Aktualizuje ilość wybranego produktu w koszyku.	|quantity|
|DELETE	|/cart/{product_id}	|Całkowicie usuwa produkt z koszyka.|Brak|

3. **Zamówienia i Autoryzacja**

Obsługa procesów zakupowych oraz kont użytkowników.
| Metoda | Endpoint | Opis | Parametry |
|:--|:--|:--|:--|
|POST|	/checkout	|Tworzy nowe zamówienie z zawartości koszyka.	|Dane do wysyłki|
|GET	|/orders	|Pobiera historię zamówień zalogowanego użytkownika.	|Brak|
|POST	|/register	|Rejestracja nowego użytkownika.	|name, email, password|
|POST	|/login	|Logowanie do aplikacji i uzyskanie tokena.	|email, password|

4. **Panel Sprzedawcy (Seller Panel)**

Punkty końcowe dedykowane dla osób zarządzających własnym asortymentem.
| Metoda | Endpoint | Opis | Parametry |
|:--|:--|:--|:--|
|GET	|/seller/products	|Pobiera listę produktów należących do sprzedawcy.	|Brak|
|POST	|/seller/products	|Tworzy i wystawia nowy produkt do sprzedaży.	|Dane produktu + tags[]|
|PUT	|/seller/products/{id}	|Edytuje dane istniejącego produktu.	|Dane produktu|
|DELETE	|/seller/products/{id}	|Usuwa produkt z oferty sprzedawcy.	|Brak|


## Zaplecze techniczne
Aplikacja oparta jest na architekturze decoupled, w której warstwa prezentacji (frontend) jest całkowicie oddzielona od warstwy logicznej (backend).

### Komunikacja backend-frontend

* **REST API:** 
    Backend pełni rolę serwera API, który udostępnia dane w formacie JSON. Frontend komunikuje się z nim za pomocą asynchronicznych żądań fetch.
* **Nagłówki i sesja:**
    Ze względu na bezstanowość API, do identyfikacji koszyka użytkownika wykorzystywany jest niestandardowy nagłówek HTTP X-Session-ID. Identyfikator ten jest generowany po stronie klienta (Svelte), przechowywany w localStorage i przesyłany przy każdym żądaniu dotyczącym koszyka.
* **CORS (Cross-Origin Resource Sharing):**
    Komunikacja jest zabezpieczona i skonfigurowana w Laravelu tak, aby akceptować  żądania z konkretnej domeny frontendu (domyślnie localhost:5173), co zapewnia bezpieczeństwo przy jednoczesnej swobodzie wymiany danych.

### Struktura projektu 

1. **Backend (Laravel)**

    Logika serwerowa opiera się na frameworku Laravel, który zarządza danymi i dostarcza je do API.

* **Baza danych MySQL:**
    Wykorzystywana jako główne miejsce składowania danych o produktach, kategoriach i zamówieniach.

* **Migracje (Migrations):**
    Definiują strukturę bazy danych w kodzie, co pozwala na łatwe odtworzenie schematu tabel na dowolnym środowisku.

* **Fabryki (Factories) i Seedery (Seeders):**
    Służą do automatycznego wypełniania bazy danych rekordami testowymi. Pozwala to na natychmiastowe uruchomienie aplikacji z pełną listą produktów i kategorii zaraz po instalacji.

* **Modele i Relacje:**
    Wykorzystują Eloquent ORM do definiowania powiązań, takich jak relacje wiele-do-wielu między produktami a tagami.
* **Zarządzanie bazami danych:**
    Dla ułatwienia prac programistycznych i administracyjnych, do projektu dołączono kontener z narzędziem *phpMyAdmin*. Jest to webowy interfejs graficzny, który umożliwia bezpośredni wgląd w tabele bazy MySQL, podgląd rekordów oraz ręczne testowanie zapytań SQL. Narzędzie jest zintegrowane ze stossem Dockerowym i uruchamia się automatycznie wraz z całą infrastrukturą. Jest dostępne pod adresem http://localhost:8080. 

2. **Frontend (SvelteKit)**

Warstwa wizualna wykorzystuje system routingu opartego na systemie plików (file-based routing), co zapewnia przejrzystość struktury.

* **Katalog src/routes:** 
    Każdy podfolder w tym katalogu definiuje ścieżkę URL w przeglądarce.

* **+page.svelte:** 
    Plik definiujący unikalny szablon HTML danej podstrony. Na przykład src/routes/products/[id]/+page.svelte odpowiada za wyświetlanie szczegółów konkretnego produktu.

* **+layout.svelte:** 
    Globalny szablon, w którym znajdują się elementy wspólne dla wszystkich stron (np. pasek nawigacyjny, stopka, inicjalizacja sesji). Dzięki temu unikamy powtarzania kodu.

* **Komponenty .svelte:** 
    Interfejs jest budowany z reużywalnych komponentów łączących w jednym pliku logikę (JavaScript/TypeScript), strukturę (HTML) oraz style (CSS/Tailwind).

* **Svelte Stores:** 
    Wykorzystywane do reaktywnego zarządzania stanami aplikacji, takimi jak dane o produktach czy użytkownikach, dostępne globalnie w każdym komponencie.

## Dostęp do aplikacji

* Backend Laravel: http://localhost:8000
* Frontend Svelte: http://localhost:5173
* phpMyAdmin: http://localhost:8080 (login i hasło z .env)

## Licencja
Projekt realizowany w celach edukacyjnych w ramach przedmiotu Bazy Danych na Politechnice Wrocławskiej.
