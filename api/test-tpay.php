<?php
/**
 * Skrypt testowy - sprawdza połączenie z TPay Sandbox
 */

require_once __DIR__ . '/TPayHelper.php';

echo "=== TEST POŁĄCZENIA Z TPAY SANDBOX ===\n\n";

try {
    $tpay = new TPayHelper();
    
    echo "✓ Konfiguracja załadowana\n";
    echo "  Merchant ID: " . TPAY_MERCHANT_ID . "\n";
    echo "  Environment: " . TPAY_ENVIRONMENT . "\n";
    echo "  API URL: " . TPAY_API_URL . "\n\n";
    
    echo "Testuję autoryzację...\n";
    
    // Próba utworzenia testowej transakcji
    $testData = [
        'order_id' => 99999,
        'amount' => 1.00,
        'description' => 'Test transakcji - Zamówienie #99999',
        'email' => 'test@example.com',
        'name' => 'Test Testowy',
        'phone' => '+48 123 456 789',
        'address' => 'ul. Testowa 1, 00-001 Warszawa'
    ];
    
    $result = $tpay->createTransaction($testData);
    
    echo "✓ Połączenie z TPay działa!\n\n";
    echo "Testowa transakcja:\n";
    echo "  Transaction ID: " . $result['transaction_id'] . "\n";
    echo "  Payment URL: " . $result['payment_url'] . "\n";
    echo "  Title: " . ($result['title'] ?? 'brak') . "\n\n";
    
    echo "=== TEST ZAKOŃCZONY SUKCESEM ===\n";
    echo "\nMożesz otworzyć URL płatności w przeglądarce, aby przetestować formularz TPay.\n";
    
} catch (Exception $e) {
    echo "✗ BŁĄD: " . $e->getMessage() . "\n";
    exit(1);
}
