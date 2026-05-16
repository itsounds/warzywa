# 🎉 Integracja Google Sheets - Podsumowanie

## ✅ Co zostało zaimplementowane:

1. ✅ **Zainstalowano Google API PHP Client** (composer)
2. ✅ **Utworzono pliki konfiguracyjne:**
   - `config/google-sheets.php` - konfiguracja OAuth
   - `.gitignore` - zabezpieczenie przed commitowaniem tokenów

3. ✅ **Utworzono skrypty autoryzacji:**
   - `api/google-auth.php` - rozpoczyna proces OAuth
   - `api/google-auth-callback.php` - odbiera token po autoryzacji

4. ✅ **Utworzono klasę pomocniczą:**
   - `api/GoogleSheetsHelper.php` - obsługa zapisu do arkusza

5. ✅ **Zmodyfikowano endpoint zamówień:**
   - `api/order.php` - teraz zapisuje do MySQL i Google Sheets

6. ✅ **Utworzono narzędzia testowe:**
   - `api/test-google-sheets.php` - sprawdza konfigurację

## 🚀 Co musisz zrobić (jednorazowo):

### KROK 1: Dodaj Redirect URI w Google Cloud Console

⚠️ **TO JEST KLUCZOWE!** Bez tego autoryzacja się nie powiedzie.

1. Przejdź do: https://console.cloud.google.com/apis/credentials
2. Kliknij na Client ID: `593177254480-el7pbo2cejqchknkpmlc6aoqip1leup7`
3. W sekcji **Authorized redirect URIs** dodaj:
   ```
   http://localhost/api/google-auth-callback.php
   ```
   (lub twoja produkcyjna domena zamiast localhost)
4. **Kliknij SAVE**
5. **Poczekaj 5-10 minut** na propagację zmian

### KROK 2: Przeprowadź autoryzację

1. Otwórz w przeglądarce:
   ```
   http://localhost/api/google-auth.php
   ```

2. Zaloguj się kontem Google, które ma dostęp do arkusza

3. Zatwierdź uprawnienia dla aplikacji

4. Po pomyślnej autoryzacji pojawi się komunikat sukcesu

### KROK 3: Sprawdź czy działa

Uruchom test:
```bash
php api/test-google-sheets.php
```

Powinno pokazać:
```
✓ Token autoryzacyjny istnieje
✓ Token ważny
```

## 📊 Jak działa zapis zamówień:

Po prawidłowej konfiguracji, każde nowe zamówienie będzie:

1. **Zapisane do bazy MySQL** (jak dotychczas)
2. **Automatycznie dodane do Google Sheets** jako nowy wiersz

### Struktura arkusza:

```
| Data | ID | Imię | Email | Telefon | Box | Waga | Cena bazowa | Dopłaty | Cena końcowa | Produkty |
```

Pierwszy wiersz (nagłówki) zostanie utworzony automatycznie przy pierwszym zamówieniu.

## 🔧 Rozwiązywanie problemów:

### Błąd: "redirect_uri_mismatch"
- Nie dodałeś redirect URI w Google Cloud Console
- Lub czekasz za krótko (poczekaj 5-10 min po dodaniu)

### Błąd: "Brak tokena autoryzacyjnego"
- Przejdź przez proces autoryzacji (KROK 2)

### Błąd: "Permission denied" przy zapisie
- Upewnij się, że konto Google z autoryzacji ma dostęp do edycji arkusza
- Udostępnij arkusz dla tego konta

### Zamówienia zapisują się do bazy, ale nie do Sheets
- Sprawdź error_log PHP (`tail -f /path/to/error_log`)
- Błędy zapisu do Sheets NIE przerywają zapisu do bazy (by design)

## 🔒 Bezpieczeństwo:

⚠️ Następujące pliki **NIE POWINNY** być commitowane do repozytorium:
- ✅ `config/google-token.json` (już w .gitignore)
- ✅ `config/google-sheets.php` (już w .gitignore)
- ✅ `vendor/` (już w .gitignore)

## 📝 Produkcja:

Gdy wdrażasz na produkcję (np. hosting):

1. Zmień w `config/google-sheets.php`:
   ```php
   define('GOOGLE_REDIRECT_URI', 'https://twoja-domena.pl/api/google-auth-callback.php');
   ```

2. Dodaj nowy redirect URI w Google Cloud Console

3. Prześlij wszystkie pliki na hosting

4. Uruchom `composer install` na serwerze (lub prześlij folder `vendor/`)

5. Przeprowadź autoryzację ponownie na produkcji: `https://twoja-domena.pl/api/google-auth.php`

## 🎯 Gotowe!

Po wykonaniu tych kroków, integracja z Google Sheets będzie działać w pełni automatycznie.

---

**Utworzone pliki:**
- config/google-sheets.php
- api/google-auth.php
- api/google-auth-callback.php
- api/GoogleSheetsHelper.php
- api/test-google-sheets.php
- GOOGLE_SHEETS.md
- GOOGLE_SHEETS_SETUP.md (ten plik)

**Zmodyfikowane pliki:**
- api/order.php (dodano zapis do Sheets)
- .gitignore (dodano wykluczenia)
- composer.json (utworzony przez composer)
