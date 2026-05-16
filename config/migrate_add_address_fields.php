<?php
/**
 * Skrypt migracji - dodaje pola adresowe do tabeli orders
 * Uruchom: php config/migrate_add_address_fields.php
 */

require_once __DIR__ . '/database.php';

try {
    $pdo = getDBConnection();
    
    echo "Dodaję pola adresowe do tabeli orders...\n";
    
    // Sprawdź czy kolumny już istnieją
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'customer_street'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Kolumny adresowe już istnieją.\n";
        exit(0);
    }
    
    // Dodaj kolumny
    $pdo->exec("
        ALTER TABLE orders
        ADD COLUMN customer_street VARCHAR(255) NULL AFTER customer_phone,
        ADD COLUMN customer_building VARCHAR(50) NULL AFTER customer_street,
        ADD COLUMN customer_apartment VARCHAR(50) NULL AFTER customer_building,
        ADD COLUMN customer_postal_code VARCHAR(20) NULL AFTER customer_apartment,
        ADD COLUMN customer_city VARCHAR(100) NULL AFTER customer_postal_code
    ");
    
    echo "✓ Kolumny adresowe dodane pomyślnie!\n";
    echo "\nDodane kolumny:\n";
    echo "  - customer_street (Ulica)\n";
    echo "  - customer_building (Nr budynku)\n";
    echo "  - customer_apartment (Nr lokalu)\n";
    echo "  - customer_postal_code (Kod pocztowy)\n";
    echo "  - customer_city (Miasto)\n";
    
} catch (Exception $e) {
    echo "✗ Błąd: " . $e->getMessage() . "\n";
    exit(1);
}
