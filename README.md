# 🥕 Warzywa Sędzinko - Konfigurator Boxa Warzywnego

Mini sklep internetowy z konfiguratorem boxa warzywnego. Aplikacja pozwala użytkownikom na skonfigurowanie własnego boxa warzywnego, automatycznie dobiera wariant (12 kg, 20 kg lub własny), liczy dopłaty i zapisuje zamówienia do bazy danych.

## 🎯 Funkcjonalności

- **Konfigurator produktów** - dynamiczny wybór warzyw na wagę i na sztuki
- **Automatyczny dobór wariantu boxa** - system sam wybiera BOX 12 KG, BOX 20 KG lub BOX WŁASNY
- **Dynamiczne liczenie ceny** - cena bazowa + dopłaty w czasie rzeczywistym
- **System dopłat**:
  - Cebula czerwona powyżej 5 kg → +5 zł/kg
  - Natka pietruszki powyżej 1 pęka → +15 zł/pęk
  - Por + Czosnek powyżej 5 szt łącznie → +4 zł/szt
- **Pasek postępu wagi** - wizualizacja postępu do 12 kg, 20 kg i 24 kg
- **Walidacja** - blokada przy < 12 kg i > 24 kg
- **Zapis zamówień do bazy** - pełna historia zamówień
- **Sekcje landingowe** - dlaczego warto, jak to działa, opinie klientów
- **Design ekologiczny** - zieleń i pomarańcz, nowoczesny i minimalistyczny
- **Responsywny** - działa na mobile, tablet i desktop

## 📋 Wymagania

- PHP 8.0 lub nowszy
- MySQL 5.7 lub nowszy (lub MariaDB)
- Serwer web (Apache/Nginx)
- Rozszerzenia PHP: PDO, PDO_MySQL, JSON

## 🚀 Instalacja

### 1. Skopiuj pliki na serwer

Przenieś wszystkie pliki do katalogu głównego hostingu (np. `public_html`).

### 2. Utwórz bazę danych

Zaloguj się do phpMyAdmin i:
- Utwórz nową bazę danych (np. `warzywasedzinko`)
- Zaimportuj plik `config/database.sql` lub wykonaj polecenia SQL z tego pliku

Alternatywnie przez terminal MySQL:

```bash
mysql -u root -p < config/database.sql
```

### 3. Skonfiguruj połączenie z bazą

Edytuj plik `config/database.php` i ustaw właściwe dane:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'warzywasedzinko');
define('DB_USER', 'twoj_user');
define('DB_PASS', 'twoje_haslo');
```

### 4. Ustaw uprawnienia

Upewnij się, że katalogi mają odpowiednie uprawnienia:

```bash
chmod 755 api/
chmod 644 api/*.php
chmod 644 config/*.php
chmod 644 config/*.json
```

### 5. Gotowe!

Otwórz przeglądarkę i przejdź do swojej domeny. Aplikacja powinna działać.

## 📂 Struktura projektu

```
warzywasedzinko.pl/
├── index.php              # Strona główna z konfiguratorem
├── README.md              # Ten plik
├── .htaccess             # Konfiguracja Apache
│
├── config/
│   ├── database.php      # Konfiguracja połączenia z bazą
│   ├── database.sql      # Skrypt tworzenia bazy
│   └── products.json     # Konfiguracja produktów i cen
│
├── api/
│   ├── calculate.php     # API - obliczanie ceny i wariantu
│   └── order.php         # API - zapis zamówienia
│
└── assets/
    ├── css/
    │   └── style.css     # Style aplikacji
    └── js/
        └── app.js        # Logika jQuery
```

## 🛠️ Konfiguracja produktów

Ceny, limity i parametry produktów są w pliku `config/products.json`. 
Możesz je łatwo edytować bez modyfikowania kodu PHP.

### Przykład edycji ceny:

```json
{
  "id": "marchew",
  "name": "Marchew",
  "price": 4,  // ← zmień tutaj
  "unit": "kg"
}
```

## 📊 Logika biznesowa

### Warianty boxów:

| Waga | Wariant | Cena |
|------|---------|------|
| < 12 kg | Nie można zamówić | - |
| = 12 kg | BOX 12 KG | 70 zł |
| 12-20 kg | BOX WŁASNY | Suma produktów |
| = 20 kg | BOX 20 KG | 100 zł |
| 20-24 kg | BOX WŁASNY | Suma produktów |
| > 24 kg | Blokada | - |

### Dopłaty:

1. **Cebula czerwona**: do 5 kg w cenie, powyżej +5 zł/kg
2. **Natka pietruszki**: 1 pęk w cenie, każdy kolejny +15 zł
3. **Por + Czosnek**: razem do 5 szt w cenie, powyżej +4 zł/szt

## 🔧 Rozwój i dostosowanie

### Dodanie nowego produktu

Edytuj `config/products.json`:

```json
{
  "id": "kalafior",
  "name": "Kalafior",
  "price": 7,
  "unit": "szt"
}
```

### Zmiana kolorów

Edytuj zmienne CSS w `assets/css/style.css`:

```css
:root {
    --primary-color: #2e7d32;    /* Twoja zieleń */
    --accent-color: #f57c00;     /* Twoja pomarańcz */
}
```

### Zmiana limitów wagi

Edytuj `config/products.json` w sekcji `boxes`.

## 🐛 Rozwiązywanie problemów

### ⚡ Szybki start - jeśli masz błędy:

1. **Otwórz w przeglądarce:** `http://localhost/simple-test.php`
   - Pokaże czy PHP działa i pliki są na miejscu

2. **Jeśli nadal błąd 500:**
   - Zmień nazwę `.htaccess` → `.htaccess.backup`
   - Odśwież stronę

3. **Zobacz pełną dokumentację:** `TROUBLESHOOTING.md`

### Najczęstsze problemy:

**Błąd 500 (Internal Server Error)**
- Najczęściej: problem z `.htaccess` → zmień nazwę na `.htaccess.backup`
- Zobacz: `TROUBLESHOOTING.md` dla pełnej instrukcji

**Błąd połączenia z bazą**
- API potrzebuje bazy, ale strona główna już nie!
- Sprawdź dane w `config/database.php`
- Upewnij się, że baza została utworzona

**Błędy AJAX**
- Otwórz konsolę przeglądarki (F12) i sprawdź błędy
- Sprawdź czy API działa: `http://localhost/api/calculate.php`

**Produkty się nie wyświetlają**
- Sprawdź `config/products.json` - czy jest poprawny JSON?
- Uruchom: `php -l config/products.json`

## 📱 Responsywność

Aplikacja jest w pełni responsywna i działa na:
- Desktop (1920px+)
- Laptop (1366px+)
- Tablet (768px+)
- Mobile (320px+)

## 🔒 Bezpieczeństwo

- Walidacja danych po stronie backendu
- Prepared statements (PDO) - ochrona przed SQL Injection
- Walidacja email i numerów telefonu
- Sanityzacja danych wejściowych
- CORS headers dla API

## 📈 Dalszy rozwój

Możliwe rozszerzenia:
- Panel administracyjny do zarządzania zamówieniami
- Powiadomienia email po złożeniu zamówienia
- Integracja z płatnościami online
- System logowania dla stałych klientów
- Śledzenie statusu zamówienia
- Więcej produktów i kategorii

## 📞 Wsparcie

Projekt gotowy do wrzucenia na hosting. W przypadku pytań sprawdź:
- Logi serwera
- Konsolę przeglądarki (DevTools)
- Dokumentację PHP i MySQL

## 📝 Licencja

Projekt stworzony na potrzeby Warzywa Sędzinko.

---

**Wykonanie:** 2026  
**Technologie:** PHP 8.x, MySQL, HTML5, CSS3, jQuery  
**Design:** Ekologiczny, minimalistyczny, responsywny
