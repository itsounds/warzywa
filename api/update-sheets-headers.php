<?php
/**
 * Skrypt do aktualizacji nagłówków w Google Sheets
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/google-sheets.php';

try {
    echo "=== AKTUALIZACJA NAGŁÓWKÓW W GOOGLE SHEETS ===\n\n";
    
    // Połącz się z Google Sheets
    $client = new Google_Client();
    $client->setAuthConfig(GOOGLE_SERVICE_ACCOUNT_FILE);
    $client->setScopes(GOOGLE_SCOPES);
    $service = new Google_Service_Sheets($client);
    
    // Sprawdź obecne nagłówki
    echo "Sprawdzam obecne nagłówki...\n";
    $range = 'Sheet1!A1:Z1';
    $response = $service->spreadsheets_values->get(GOOGLE_SPREADSHEET_ID, $range);
    $values = $response->getValues();
    
    if (!empty($values)) {
        echo "Obecne nagłówki:\n";
        foreach ($values[0] as $i => $header) {
            echo "  " . chr(65 + $i) . ": $header\n";
        }
    } else {
        echo "Brak nagłówków.\n";
    }
    
    echo "\nAktualizuję nagłówki...\n";
    
    // Nowe nagłówki z kolumną statusu płatności
    $headers = [
        'Data zamówienia',
        'ID zamówienia',
        'Imię i nazwisko',
        'Email',
        'Telefon',
        'Adres dostawy',
        'Typ boxa',
        'Waga (kg)',
        'Cena bazowa (zł)',
        'Dopłaty (zł)',
        'Cena końcowa (zł)',
        'Status płatności',
        'Produkty'
    ];
    
    $body = new Google_Service_Sheets_ValueRange([
        'values' => [$headers]
    ]);
    
    $params = [
        'valueInputOption' => 'USER_ENTERED'
    ];
    
    $service->spreadsheets_values->update(
        GOOGLE_SPREADSHEET_ID,
        'Sheet1!A1:M1',
        $body,
        $params
    );
    
    echo "✓ Nagłówki zaktualizowane!\n\n";
    echo "Nowe nagłówki:\n";
    foreach ($headers as $i => $header) {
        echo "  " . chr(65 + $i) . ": $header\n";
    }
    
} catch (Exception $e) {
    echo "✗ Błąd: " . $e->getMessage() . "\n";
    exit(1);
}
