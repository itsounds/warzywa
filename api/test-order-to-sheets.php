<?php
/**
 * Skrypt testowy - próbuje zapisać testowe zamówienie do Google Sheets
 * Uruchom: php api/test-order-to-sheets.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/GoogleSheetsHelper.php';

echo "=== TEST ZAPISU DO GOOGLE SHEETS ===\n\n";

// Testowe dane zamówienia
$testOrder = [
    'order_id' => 999,
    'customer_name' => 'Jan Testowy',
    'customer_email' => 'test@example.com',
    'customer_phone' => '+48 123 456 789',
    'customer_street' => 'ul. Kwiatowa',
    'customer_building' => '12',
    'customer_apartment' => '3',
    'customer_postal_code' => '00-001',
    'customer_city' => 'Warszawa',
    'box_type' => 'BOX 12 KG',
    'total_weight' => 12.5,
    'base_price' => 70.00,
    'extra_price' => 5.00,
    'final_price' => 75.00,
    'products' => [
        [
            'name' => 'Marchew',
            'quantity' => 3.0,
            'unit' => 'kg',
            'unit_price' => 4.00
        ],
        [
            'name' => 'Ziemniaki',
            'quantity' => 5.0,
            'unit' => 'kg',
            'unit_price' => 3.00
        ],
        [
            'name' => 'Cebula',
            'quantity' => 2.5,
            'unit' => 'kg',
            'unit_price' => 5.00
        ]
    ]
];

try {
    echo "Tworzę połączenie z Google Sheets...\n";
    $sheets = new GoogleSheetsHelper();
    
    echo "Zapisuję testowe zamówienie...\n";
    $sheets->addOrder($testOrder);
    
    echo "\n✓ SUKCES! Testowe zamówienie zostało zapisane do Google Sheets.\n";
    echo "Sprawdź arkusz: https://docs.google.com/spreadsheets/d/1m0ibKHU3i7tTyzi_2rrsCju4n0Sc3aTBdqpvyPemptc/edit\n";
    
} catch (Exception $e) {
    echo "\n✗ BŁĄD: " . $e->getMessage() . "\n\n";
    
    // Szczegółowe informacje o błędzie
    if (strpos($e->getMessage(), 'Brak tokena') !== false) {
        echo "Musisz najpierw przeprowadzić autoryzację:\n";
        echo "Otwórz w przeglądarce: http://localhost/api/google-auth.php\n";
    } elseif (strpos($e->getMessage(), 'Token wygasł') !== false) {
        echo "Token wygasł. Uruchom ponownie autoryzację:\n";
        echo "Otwórz w przeglądarce: http://localhost/api/google-auth.php\n";
    } elseif (strpos($e->getMessage(), 'Permission') !== false) {
        echo "Brak uprawnień do edycji arkusza.\n";
        echo "Upewnij się, że arkusz jest udostępniony dla konta Google używanego w autoryzacji.\n";
    }
    
    exit(1);
}
