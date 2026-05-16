# 📍 Dodano pola adresowe - Instrukcja aktualizacji

## ✅ Co zostało zaktualizowane:

### 1. Formularz zamówienia (konfigurator.php)
- ✅ Dodano pole "Ulica"
- ✅ Dodano pole "Nr budynku"
- ✅ Dodano pole "Nr lokalu"
- ✅ Dodano pole "Kod pocztowy"
- ✅ Dodano pole "Miasto"

### 2. JavaScript (assets/js/app.js)
- ✅ Aktualizacja wysyłania danych z nowymi polami

### 3. Stylowanie (assets/css/style.css)
- ✅ Dodano style dla układu formularza (form-row, form-col-*)
- ✅ Dodano style dla tytułu sekcji (form-section-title)
- ✅ Responsywność na urządzeniach mobilnych

### 4. API backend (api/order.php)
- ✅ Przyjmowanie i walidacja nowych pól
- ✅ Zapis do bazy danych
- ✅ Przekazywanie do Google Sheets

### 5. Google Sheets (api/GoogleSheetsHelper.php)
- ✅ Nowa kolumna "Adres dostawy" w arkuszu
- ✅ Formatowanie: "ul. Kwiatowa 12/3, 00-001 Warszawa"

### 6. Struktura bazy danych
- ✅ Zaktualizowano `config/database.sql`
- ⚠️ **Wymagana migracja bazy** (patrz niżej)

## 🔧 WYMAGANE: Aktualizacja bazy danych

### Opcja 1: Przez phpMyAdmin (zalecane)

1. Otwórz phpMyAdmin
2. Wybierz bazę `warzywasedzinko`
3. Przejdź do zakładki "SQL"
4. Wklej i wykonaj:

```sql
ALTER TABLE orders
ADD COLUMN customer_street VARCHAR(255) NULL AFTER customer_phone,
ADD COLUMN customer_building VARCHAR(50) NULL AFTER customer_street,
ADD COLUMN customer_apartment VARCHAR(50) NULL AFTER customer_building,
ADD COLUMN customer_postal_code VARCHAR(20) NULL AFTER customer_apartment,
ADD COLUMN customer_city VARCHAR(100) NULL AFTER customer_postal_code;
```

### Opcja 2: Przez skrypt PHP

```bash
php config/migrate_add_address_fields.php
```

(Wymaga uruchomionego MySQL/MAMP)

## 📊 Arkusz Google Sheets

Arkusz został automatycznie zaktualizowany i ma teraz kolumnę:
- **Adres dostawy** - zawiera pełny adres w jednej komórce

Przykład: `ul. Kwiatowa 12/3, 00-001 Warszawa`

## ✅ Test

Testowe zamówienie z adresem zostało już zapisane do arkusza:
https://docs.google.com/spreadsheets/d/1m0ibKHU3i7tTyzi_2rrsCju4n0Sc3aTBdqpvyPemptc/edit

## 🎯 Gotowe!

Po wykonaniu migracji bazy danych wszystko będzie działać automatycznie:
- Formularz będzie zbierał dane adresowe
- Zamówienia będą zapisywane z adresami do MySQL i Google Sheets

---

**Pliki zmodyfikowane:**
- konfigurator.php
- assets/js/app.js
- assets/css/style.css
- api/order.php
- api/GoogleSheetsHelper.php
- config/database.sql

**Pliki utworzone:**
- config/add_address_fields.sql
- config/migrate_add_address_fields.php
- ADDRESS_FIELDS_UPDATE.md (ten plik)
