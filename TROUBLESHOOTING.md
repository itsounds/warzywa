# 🔧 Rozwiązywanie Problemów

## Błąd 500 - Internal Server Error

### Przyczyna 1: Błędna konfiguracja .htaccess

Niektóre serwery nie obsługują dyrektyw `php_value` i `php_flag` w .htaccess.

**Rozwiązanie:**
1. Otwórz plik `.htaccess`
2. Zakomentuj (dodaj `#` na początku) wszystkie linie z `php_value` i `php_flag`
3. Lub po prostu usuń plik `.htaccess` na początek

```bash
# Zmień nazwę aby wyłączyć
mv .htaccess .htaccess.backup
```

### Przyczyna 2: Brak połączenia z bazą danych

Index.php próbuje połączyć się z bazą przy starcie.

**Rozwiązanie:**
✅ JUŻ NAPRAWIONE - index.php teraz używa tylko pliku JSON

### Przyczyna 3: Błędne uprawnienia do plików

**Rozwiązanie:**
```bash
# Ustaw odpowiednie uprawnienia
chmod 755 .
chmod 755 api/ config/ assets/ assets/css/ assets/js/
chmod 644 *.php api/*.php config/*.php assets/css/*.css assets/js/*.js
```

### Przyczyna 4: Brak rozszerzeń PHP

Sprawdź czy są zainstalowane wymagane rozszerzenia.

**Rozwiązanie:**
Otwórz `info.php` w przeglądarce i sprawdź czy masz:
- PDO
- pdo_mysql
- json

## Jak debugować?

### Krok 1: Sprawdź podstawy
Otwórz w przeglądarce:
```
http://localhost/simple-test.php
```

Powinno pokazać zielone checkmarki.

### Krok 2: Sprawdź phpinfo
```
http://localhost/info.php
```

### Krok 3: Włącz wyświetlanie błędów

Dodaj na początku `index.php`:
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// ... reszta kodu
```

### Krok 4: Sprawdź logi serwera

**MAMP:**
```
/Applications/MAMP/logs/php_error.log
```

**XAMPP (Mac):**
```
/Applications/XAMPP/logs/php_error_log
```

**Linux:**
```
/var/log/apache2/error.log
```

## Błąd: "Call to undefined function getDBConnection()"

**Przyczyna:** Próba użycia API bez bazy danych.

**Rozwiązanie:**
1. Utwórz bazę danych (zobacz INSTALL.md)
2. Skonfiguruj `config/database.php`

## Błąd: CORS lub brak odpowiedzi z API

**Rozwiązanie:**
Sprawdź czy API endpoints są dostępne:
```
http://localhost/api/calculate.php
```

Powinno zwrócić JSON z błędem (to normalne bez danych POST).

## Produkty się nie wyświetlają

**Przyczyna:** Błąd w pętli PHP generującej produkty.

**Rozwiązanie:**
Sprawdź czy `config/products.json` jest poprawny:
```bash
php -r "json_decode(file_get_contents('config/products.json')); echo json_last_error() === 0 ? 'OK' : 'BŁĄD';"
```

## JavaScript nie działa

### Sprawdź konsolę przeglądarki
1. Otwórz DevTools (F12)
2. Przejdź do zakładki "Console"
3. Szukaj błędów

### Najczęstsze problemy:
- Nie załadował się jQuery → sprawdź połączenie z internetem
- Błędy AJAX → sprawdź czy API działa
- Błędy składni → sprawdź `assets/js/app.js`

## Nie można zapisać zamówienia

**Przyczyna 1:** Baza danych nie jest skonfigurowana

**Rozwiązanie:**
```bash
mysql -u root -p < config/database.sql
```

**Przyczyna 2:** Złe dane w config/database.php

**Rozwiązanie:**
```php
// Sprawdź te wartości:
define('DB_HOST', 'localhost');  // lub 127.0.0.1
define('DB_NAME', 'warzywasedzinko');
define('DB_USER', 'root');
define('DB_PASS', 'twoje_haslo');
```

## Hosting współdzielony - dodatkowe wskazówki

### Nazwa bazy danych
Na hostingu współdzielonym często musisz użyć prefiksu:
```php
define('DB_NAME', 'twojlogin_warzywasedzinko');
```

### Host bazy danych
Może być inny niż localhost:
```php
define('DB_HOST', 'mysql.twojhost.pl');
```

### Uprawnienia
Sprawdź czy masz uprawnienia do:
- CREATE TABLE
- INSERT, SELECT, UPDATE
- FOREIGN KEY

## Problemy z MAMP

### Port MySQL
MAMP używa portu 8889, nie 3306:
```php
define('DB_HOST', 'localhost:8889');
```

### Hasło root w MAMP
Domyślnie to `root`:
```php
define('DB_PASS', 'root');
```

## Szybkie sprawdzenia

### ✅ Checklist przed uruchomieniem:

- [ ] PHP 8.0 lub nowszy
- [ ] MySQL działa
- [ ] Baza danych utworzona
- [ ] config/database.php skonfigurowany
- [ ] config/products.json istnieje
- [ ] Wszystkie katalogi na miejscu (api, assets, config)
- [ ] simple-test.php pokazuje zielone checkmarki

## Nadal nie działa?

1. Skopiuj całą treść błędu
2. Sprawdź logi serwera
3. Otwórz konsolę przeglądarki (F12)
4. Sprawdź zakładkę Network w DevTools
5. Upewnij się, że wszystkie pliki są na miejscu

## Szybkie resetowanie

Jeśli coś poszło nie tak:

```bash
# 1. Przywróć bazę danych
mysql -u root -p warzywasedzinko < config/database.sql

# 2. Wyczyść cache przeglądarki (Ctrl+Shift+R)

# 3. Sprawdź test
open http://localhost/simple-test.php
```

---

**Nie znalazłeś rozwiązania?** Sprawdź dokładnie treść błędu w logach PHP.
