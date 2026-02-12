# 🚀 SZYBKI START

## Problem z błędem 500? To normalne! Oto rozwiązanie:

### KROK 1: Wyłącz .htaccess (tymczasowo)

```bash
cd /Users/maciejkostecki/Documents/WORKSPACE/warzywasedzinko.pl
mv .htaccess .htaccess.backup
```

Lub po prostu zmień nazwę pliku `.htaccess` na `.htaccess.backup` w Finderze.

### KROK 2: Sprawdź czy działa

Otwórz w przeglądarce:
```
http://localhost/warzywasedzinko.pl/simple-test.php
```

Powinieneś zobaczyć zielone checkmarki ✓

### KROK 3: Otwórz stronę główną

```
http://localhost/warzywasedzinko.pl/index.php
```

## ✅ Działa? Świetnie!

Teraz możesz:
1. Dodawać produkty
2. Zobaczyć jak zmienia się waga i cena
3. **Ale NIE możesz jeszcze składać zamówień** (potrzebna baza danych)

## 📦 Chcesz składać zamówienia? Ustaw bazę danych:

### 1. Utwórz bazę w phpMyAdmin lub terminalu:

**phpMyAdmin:**
1. Otwórz http://localhost:8888/phpMyAdmin (MAMP) lub http://localhost/phpMyAdmin (XAMPP)
2. Kliknij "Nowa baza danych"
3. Nazwa: `warzywasedzinko`
4. Kodowanie: `utf8mb4_unicode_ci`
5. Kliknij "Utwórz"
6. Wybierz bazę, zakładka "SQL"
7. Skopiuj zawartość z `config/database.sql` i wykonaj

**Terminal:**
```bash
# Zaloguj się
mysql -u root -p

# W konsoli MySQL:
CREATE DATABASE warzywasedzinko CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;

# Zaimportuj tabele
mysql -u root -p warzywasedzinko < config/database.sql
```

### 2. Skonfiguruj połączenie:

Edytuj `config/database.php`:

```php
define('DB_HOST', 'localhost');        // lub 'localhost:8889' dla MAMP
define('DB_NAME', 'warzywasedzinko');
define('DB_USER', 'root');
define('DB_PASS', 'root');            // lub twoje hasło
```

### 3. Sprawdź połączenie:

```
http://localhost/warzywasedzinko.pl/test.php
```

Wszystkie testy powinny być zielone!

## 🎉 GOTOWE!

Teraz możesz:
- ✅ Konfigurować boxy
- ✅ Składać zamówienia
- ✅ Zapisywać do bazy danych

## ⚙️ Co zostało naprawione?

1. **Index.php** - już nie wymaga bazy danych przy starcie
2. **.htaccess** - potencjalnie problematyczne dyrektywy zakomentowane
3. **Dodane pliki testowe:**
   - `simple-test.php` - szybki test
   - `test.php` - pełny test konfiguracji
   - `info.php` - phpinfo()

## 📚 Więcej pomocy:

- **Problemy?** → Zobacz `TROUBLESHOOTING.md`
- **Instalacja?** → Zobacz `INSTALL.md`
- **Dokumentacja?** → Zobacz `README.md`

---

**Pytanie:** Nadal błąd 500?  
**Odpowiedź:** Otwórz `TROUBLESHOOTING.md` i postępuj według instrukcji.
