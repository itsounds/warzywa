# 💳 Integracja z TPay - Dokumentacja

## ✅ Co zostało zaimplementowane:

### 1. Konfiguracja TPay
- ✅ Plik `config/tpay.php` z credentials Sandbox
- ✅ Środowisko: **Sandbox** (testowe)
- ✅ Merchant ID: 417622

### 2. Klasa TPayHelper (`api/TPayHelper.php`)
- ✅ Autoryzacja OAuth 2.0
- ✅ Tworzenie transakcji
- ✅ Pobieranie statusu transakcji
- ✅ Weryfikacja powiadomień (webhook)

### 3. Integracja z formularzem zamówień
- ✅ Po złożeniu zamówienia tworzona jest transakcja TPay
- ✅ Użytkownik jest przekierowywany na stronę płatności TPay
- ✅ Dane zamówienia zapisywane do bazy + Google Sheets

### 4. Strony powrotu
- ✅ `payment-success.php` - po udanej płatności
- ✅ `payment-error.php` - po anulowaniu/błędzie

### 5. Webhook
- ✅ `api/tpay-notification.php` - odbiera powiadomienia z TPay
- ✅ Aktualizuje status płatności w bazie
- ✅ Loguje wszystkie powiadomienia do `logs/tpay-notifications.log`

### 6. Aktualizacje bazy danych
- ✅ Dodano kolumny: `payment_status`, `tpay_transaction_id`, `tpay_title`

## 🧪 Testowanie

### Test połączenia
```bash
php api/test-tpay.php
```

Powinno zwrócić:
- ✓ Konfiguracja załadowana
- ✓ Połączenie z TPay działa!
- Transaction ID i Payment URL

### Test pełnego flow:

1. **Złóż zamówienie** przez formularz
2. **Zostaniesz przekierowany** do TPay Sandbox
3. **Testowe płatności Sandbox:**
   - Email: `[email protected]`
   - Kliknij "Testuj płatność" bez podawania karty (sandbox)
4. **Po płatności** wrócisz na `payment-success.php`
5. **Sprawdź bazę** - status zmieni się na `paid`

### Test webhooka lokalnie

⚠️ **WAŻNE:** Webhook nie zadziała na localhost!

TPay musi mieć dostęp do URL-a `http://localhost/api/tpay-notification.php`, co nie jest możliwe lokalnie.

**Opcje testowania webhooka:**
1. **Ngrok** - tunel do localhost
   ```bash
   ngrok http 80
   ```
   Następnie zmień `TPAY_NOTIFICATION_URL` na URL z ngrok
   
2. **Testuj na hostingu** - wdróż na serwer z publicznym URL

3. **Ręcznie** - symuluj powiadomienie:
   ```bash
   curl -X POST http://localhost/api/tpay-notification.php \
     -H "Content-Type: application/json" \
     -d '{"tr_id":"01KRR0CV4EER5CNMJ19NXS5FY5","tr_status":"paid"}'
   ```

## 🔧 Migracja bazy danych

### Przez phpMyAdmin:

```sql
ALTER TABLE orders
ADD COLUMN payment_status VARCHAR(50) DEFAULT 'pending' AFTER created_at,
ADD COLUMN tpay_transaction_id VARCHAR(255) NULL AFTER payment_status,
ADD COLUMN tpay_title VARCHAR(255) NULL AFTER tpay_transaction_id,
ADD INDEX idx_payment_status (payment_status),
ADD INDEX idx_tpay_transaction_id (tpay_transaction_id);
```

## 📊 Statusy płatności

| Status | Opis |
|--------|------|
| `pending` | Oczekuje na płatność (domyślny) |
| `paid` | Opłacone |
| `refunded` | Zwrot |
| `chargeback` | Chargeback |
| `error` | Błąd |

## 🚀 Przejście na produkcję

### 1. Wygeneruj klucze produkcyjne

1. Przejdź do: https://panel.tpay.com/
2. **Integration → API**
3. **Open API Keys → Add new key**
4. Skopiuj `Client ID` i `Client Secret`

### 2. Zaktualizuj konfigurację

W `config/tpay.php` zmień:

```php
define('TPAY_ENVIRONMENT', 'production');
```

I dodaj produkcyjne klucze:

```php
define('TPAY_CLIENT_ID', 'TWOJ_PRODUKCYJNY_CLIENT_ID');
define('TPAY_CLIENT_SECRET', 'TWOJ_PRODUKCYJNY_SECRET');
```

### 3. Zaktualizuj URL-e powrotu

Zmień w `config/tpay.php`:

```php
define('TPAY_SUCCESS_URL', 'https://twoja-domena.pl/payment-success.php');
define('TPAY_ERROR_URL', 'https://twoja-domena.pl/payment-error.php');
define('TPAY_NOTIFICATION_URL', 'https://twoja-domena.pl/api/tpay-notification.php');
```

### 4. Przetestuj na produkcji

Wykonaj testową transakcję prawdziwą kartą.

## 🔒 Bezpieczeństwo

⚠️ **NIE commituj** pliku `config/tpay.php` - jest w .gitignore!

### Webhook Security

Obecna implementacja webhooka nie weryfikuje podpisu JWS. Dla produkcji **wymagane** jest dodanie weryfikacji podpisu zgodnie z dokumentacją TPay:
https://docs-api.tpay.com/en/webhooks/

## 📝 Logi

Wszystkie powiadomienia z TPay są logowane do:
```
logs/tpay-notifications.log
```

Format:
```
2026-05-16 10:58:00 - {"tr_id":"...","tr_status":"paid"}
```

## 🎯 Przepływ płatności

```
Użytkownik składa zamówienie
         ↓
API zapisuje zamówienie do bazy
         ↓
API tworzy transakcję w TPay
         ↓
Przekierowanie na TPay
         ↓
Użytkownik płaci
         ↓
TPay wysyła webhook do naszego API
         ↓
API aktualizuje status w bazie
         ↓
Użytkownik wraca na success/error page
```

## 📚 Przydatne linki

- Panel Sandbox: https://panel.sandbox.tpay.com/
- Panel Produkcja: https://panel.tpay.com/
- Dokumentacja API: https://docs-api.tpay.com/
- Support: [email protected]

---

**Pliki utworzone:**
- config/tpay.php
- config/tpay.example.php (template)
- api/TPayHelper.php
- api/tpay-notification.php
- api/test-tpay.php
- payment-success.php
- payment-error.php
- logs/tpay-notifications.log

**Pliki zmodyfikowane:**
- api/order.php (dodano tworzenie transakcji TPay)
- assets/js/app.js (dodano przekierowanie do płatności)
- config/database.sql (dodano kolumny płatności)
- .gitignore (dodano wykluczenia)
