# 🚀 Szybka Instalacja - Warzywa Sędzinko

## Krok 1: Przygotowanie bazy danych

### Opcja A: phpMyAdmin
1. Zaloguj się do phpMyAdmin
2. Kliknij "Nowa baza danych"
3. Nazwa: `warzywasedzinko`
4. Kodowanie: `utf8mb4_unicode_ci`
5. Kliknij "Utwórz"
6. Wybierz bazę i przejdź do zakładki "SQL"
7. Skopiuj i wykonaj zawartość pliku `config/database.sql`

### Opcja B: Terminal MySQL
```bash
mysql -u root -p
```

Następnie w konsoli MySQL:
```sql
CREATE DATABASE warzywasedzinko CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE warzywasedzinko;
SOURCE /ścieżka/do/projektu/config/database.sql;
EXIT;
```

## Krok 2: Konfiguracja połączenia

Skopiuj przykładowy plik konfiguracyjny:
```bash
cp config/database.example.php config/database.php
```

Edytuj `config/database.php` i ustaw swoje dane:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'warzywasedzinko');
define('DB_USER', 'twoj_user');
define('DB_PASS', 'twoje_haslo');
```

## Krok 3: Test połączenia

Otwórz w przeglądarce:
```
http://localhost/warzywasedzinko.pl/
```

lub

```
http://twoja-domena.pl/
```

## Krok 4: Testowanie funkcjonalności

1. Dodaj kilka produktów (np. 5 kg marchewki, 7 kg ziemniaków)
2. Obserwuj jak zmienia się:
   - Pasek postępu wagi
   - Wariant boxa
   - Cena końcowa
3. Spróbuj dodać dokładnie 12 kg → powinien pojawić się BOX 12 KG za 70 zł
4. Spróbuj dodać dokładnie 20 kg → powinien pojawić się BOX 20 KG za 100 zł
5. Przetestuj dopłaty:
   - Dodaj więcej niż 5 kg cebuli czerwonej
   - Dodaj więcej niż 1 pęk natki
   - Dodaj więcej niż 5 sztuk pora/czosneku łącznie

## Rozwiązywanie problemów

### Błąd połączenia z bazą danych
- Sprawdź dane logowania w `config/database.php`
- Upewnij się, że MySQL jest uruchomiony
- Sprawdź czy użytkownik ma uprawnienia do bazy

### Nie działają API endpoints
- Sprawdź czy `mod_rewrite` jest włączony w Apache
- Sprawdź uprawnienia do plików (755 dla katalogów, 644 dla plików)
- Otwórz konsolę przeglądarki (F12) i sprawdź błędy

### Produkty się nie wyświetlają
- Sprawdź czy plik `config/products.json` istnieje
- Sprawdź składnię JSON (można użyć jsonlint.com)
- Sprawdź uprawnienia do odczytu

## Gotowe! 🎉

Aplikacja jest gotowa do użycia. Możesz teraz:
- Konfigurować boxy warzywne
- Składać zamówienia
- Dostosować ceny w `config/products.json`
- Zmienić kolory w `assets/css/style.css`

## Dalsze kroki (opcjonalne)

1. **SSL/HTTPS**: Zainstaluj certyfikat SSL i odkomentuj przekierowanie w `.htaccess`
2. **Email**: Dodaj powiadomienia email po złożeniu zamówienia
3. **Panel admin**: Stwórz panel do zarządzania zamówieniami
4. **Płatności**: Zintegruj bramkę płatności (Przelewy24, PayU, itp.)
5. **SEO**: Dodaj meta tagi, sitemap.xml, robots.txt

---

Miłego korzystania! 🥕
