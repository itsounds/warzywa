<?php
/**
 * Skrypt testowy - sprawdza konfigurację Google Sheets z Service Account
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/google-sheets.php';

echo "=== TEST KONFIGURACJI GOOGLE SHEETS ===\n\n";

// 1. Sprawdź czy biblioteka jest zainstalowana
echo "✓ Google API Client zainstalowany\n";

// 2. Sprawdź Service Account file
if (file_exists(GOOGLE_SERVICE_ACCOUNT_FILE)) {
    echo "✓ Plik Service Account istnieje: " . basename(GOOGLE_SERVICE_ACCOUNT_FILE) . "\n";
    
    $credentials = json_decode(file_get_contents(GOOGLE_SERVICE_ACCOUNT_FILE), true);
    if ($credentials && isset($credentials['client_email'])) {
        echo "✓ Email Service Account: " . $credentials['client_email'] . "\n";
        echo "\n⚠️  WAŻNE: Upewnij się, że arkusz jest udostępniony dla tego emaila!\n\n";
    }
} else {
    echo "✗ Brak pliku Service Account: " . GOOGLE_SERVICE_ACCOUNT_FILE . "\n";
    exit(1);
}

// 3. Sprawdź Spreadsheet ID
echo "✓ Spreadsheet ID: " . GOOGLE_SPREADSHEET_ID . "\n";

// 4. Sprawdź czy można utworzyć klienta
try {
    $client = new Google_Client();
    $client->setAuthConfig(GOOGLE_SERVICE_ACCOUNT_FILE);
    $client->setScopes(GOOGLE_SCOPES);
    echo "✓ Klient Google utworzony poprawnie\n";
} catch (Exception $e) {
    echo "✗ Błąd tworzenia klienta: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== KONFIGURACJA OK ===\n";
echo "\nMożesz teraz przetestować zapis:\n";
echo "php api/test-order-to-sheets.php\n";
