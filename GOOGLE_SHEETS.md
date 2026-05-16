# 📊 Integracja z Google Sheets

Zamówienia z konfiguratora są automatycznie zapisywane do Google Sheets.

## 🚀 Konfiguracja (jednorazowa)

### Krok 1: Skonfiguruj redirect URI w Google Cloud Console

1. Przejdź do [Google Cloud Console](https://console.cloud.google.com)
2. Wybierz projekt z OAuth credentials
3. Przejdź do **APIs & Services** → **Credentials**
4. Kliknij na Client ID (593177254480-el7pbo2cejqchknkpmlc6aoqip1leup7)
5. W sekcji **Authorized redirect URIs** dodaj:
   - `http://localhost/api/google-auth-callback.php` (dla lokalnego rozwoju)
   - `https://twoja-domena.pl/api/google-auth-callback.php` (dla produkcji)
6. Zapisz zmiany

### Krok 2: Autoryzuj aplikację

1. Otwórz w przeglądarce: `http://localhost/api/google-auth.php`
2. Zaloguj się na konto Google
3. Zatwierdź uprawnienia dla aplikacji
4. Zostaniesz przekierowany z potwierdzeniem

Po tym kroku utworzy się plik `config/google-token.json` z tokenem autoryzacyjnym.

### Krok 3: Udostępnij arkusz

Otwórz arkusz Google Sheets:
https://docs.google.com/spreadsheets/d/1m0ibKHU3i7tTyzi_2rrsCju4n0Sc3aTBdqpvyPemptc/edit

I upewnij się, że jest on udostępniony dla konta Google, którego użyłeś w Kroku 2.

## ✅ Gotowe!

Od teraz wszystkie zamówienia będą automatycznie zapisywane do:
- ✅ Bazy danych MySQL
- ✅ Google Sheets

## 📋 Struktura arkusza

Arkusz będzie zawierał następujące kolumny:

| A | B | C | D | E | F | G | H | I | J | K |
|---|---|---|---|---|---|---|---|---|---|---|
| Data zamówienia | ID zamówienia | Imię i nazwisko | Email | Telefon | Typ boxa | Waga (kg) | Cena bazowa (zł) | Dopłaty (zł) | Cena końcowa (zł) | Produkty |

## 🔧 Rozwiązywanie problemów

### Błąd: "Brak tokena autoryzacyjnego"

Uruchom ponownie `api/google-auth.php` aby autoryzować aplikację.

### Błąd: "Token wygasł i brak refresh tokena"

Token refresh powinien być zapisany automatycznie. Jeśli nie, uruchom ponownie autoryzację:
1. Usuń plik `config/google-token.json`
2. Uruchom ponownie `api/google-auth.php`

### Błąd: "Permission denied"

Upewnij się, że arkusz jest udostępniony dla konta Google, którego używasz.

### Błąd: "redirect_uri_mismatch"

Dodaj prawidłowy redirect URI w Google Cloud Console (patrz Krok 1).

## 🔒 Bezpieczeństwo

- ⚠️ Plik `config/google-token.json` zawiera poufne dane - NIE commituj go do repozytorium!
- ⚠️ Plik `config/google-sheets.php` zawiera Client Secret - NIE commituj go!
- ✅ Oba pliki są dodane do `.gitignore`

## 📝 Uwagi

- Jeśli zapis do Google Sheets się nie powiedzie, zamówienie nadal zostanie zapisane w bazie danych
- Błędy zapisu do Sheets są logowane w error_log
- Token automatycznie się odświeża gdy wygasa
