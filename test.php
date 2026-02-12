<?php
/**
 * Test konfiguracji - Warzywa Sędzinko
 * Ten plik sprawdza czy wszystko jest poprawnie skonfigurowane
 * USUŃ TEN PLIK NA PRODUKCJI!
 */

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Konfiguracji - Warzywa Sędzinko</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2e7d32;
            border-bottom: 3px solid #f57c00;
            padding-bottom: 10px;
        }
        h2 {
            color: #2e7d32;
            margin-top: 0;
        }
        .success {
            color: #2e7d32;
            background: #e8f5e9;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .error {
            color: #c62828;
            background: #ffebee;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .warning {
            color: #f57c00;
            background: #fff3e0;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .info {
            color: #1976d2;
            background: #e3f2fd;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        pre {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .status-icon {
            font-size: 24px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <h1>🥕 Test Konfiguracji - Warzywa Sędzinko</h1>
    
    <div class="warning">
        <strong>⚠️ WAŻNE:</strong> Ten plik służy tylko do testowania. Usuń go przed wdrożeniem na produkcję!
    </div>

    <?php
    $allTestsPassed = true;
    
    // Test 1: Wersja PHP
    echo '<div class="test-section">';
    echo '<h2>Test 1: Wersja PHP</h2>';
    $phpVersion = phpversion();
    if (version_compare($phpVersion, '8.0.0', '>=')) {
        echo '<div class="success"><span class="status-icon">✓</span>PHP ' . $phpVersion . ' - OK</div>';
    } else {
        echo '<div class="error"><span class="status-icon">✗</span>PHP ' . $phpVersion . ' - Wymagana wersja 8.0 lub nowsza</div>';
        $allTestsPassed = false;
    }
    echo '</div>';
    
    // Test 2: Rozszerzenia PHP
    echo '<div class="test-section">';
    echo '<h2>Test 2: Rozszerzenia PHP</h2>';
    $requiredExtensions = ['pdo', 'pdo_mysql', 'json'];
    foreach ($requiredExtensions as $ext) {
        if (extension_loaded($ext)) {
            echo '<div class="success"><span class="status-icon">✓</span>' . $ext . ' - zainstalowane</div>';
        } else {
            echo '<div class="error"><span class="status-icon">✗</span>' . $ext . ' - brak rozszerzenia</div>';
            $allTestsPassed = false;
        }
    }
    echo '</div>';
    
    // Test 3: Pliki konfiguracyjne
    echo '<div class="test-section">';
    echo '<h2>Test 3: Pliki konfiguracyjne</h2>';
    
    $configFile = __DIR__ . '/config/database.php';
    if (file_exists($configFile)) {
        echo '<div class="success"><span class="status-icon">✓</span>config/database.php - istnieje</div>';
    } else {
        echo '<div class="error"><span class="status-icon">✗</span>config/database.php - nie znaleziono</div>';
        echo '<div class="info">Skopiuj config/database.example.php jako config/database.php</div>';
        $allTestsPassed = false;
    }
    
    $productsFile = __DIR__ . '/config/products.json';
    if (file_exists($productsFile)) {
        echo '<div class="success"><span class="status-icon">✓</span>config/products.json - istnieje</div>';
        
        // Sprawdź czy jest poprawny JSON
        $json = file_get_contents($productsFile);
        $products = json_decode($json, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo '<div class="success"><span class="status-icon">✓</span>products.json - poprawny format JSON</div>';
            echo '<div class="info">Znaleziono ' . count($products['products']['weight']) . ' produktów na wagę i ' . count($products['products']['pieces']) . ' produktów na sztuki</div>';
        } else {
            echo '<div class="error"><span class="status-icon">✗</span>products.json - błędny format JSON: ' . json_last_error_msg() . '</div>';
            $allTestsPassed = false;
        }
    } else {
        echo '<div class="error"><span class="status-icon">✗</span>config/products.json - nie znaleziono</div>';
        $allTestsPassed = false;
    }
    echo '</div>';
    
    // Test 4: Połączenie z bazą danych
    echo '<div class="test-section">';
    echo '<h2>Test 4: Połączenie z bazą danych</h2>';
    
    if (file_exists($configFile)) {
        try {
            require_once $configFile;
            $pdo = getDBConnection();
            echo '<div class="success"><span class="status-icon">✓</span>Połączenie z bazą danych - OK</div>';
            
            // Sprawdź tabele
            $tables = ['orders', 'order_items'];
            foreach ($tables as $table) {
                $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                if ($stmt->rowCount() > 0) {
                    echo '<div class="success"><span class="status-icon">✓</span>Tabela ' . $table . ' - istnieje</div>';
                } else {
                    echo '<div class="error"><span class="status-icon">✗</span>Tabela ' . $table . ' - nie istnieje</div>';
                    echo '<div class="info">Wykonaj plik config/database.sql w swojej bazie danych</div>';
                    $allTestsPassed = false;
                }
            }
            
            // Sprawdź liczbę zamówień
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
            $result = $stmt->fetch();
            echo '<div class="info">Liczba zamówień w bazie: ' . $result['count'] . '</div>';
            
        } catch (Exception $e) {
            echo '<div class="error"><span class="status-icon">✗</span>Błąd połączenia: ' . $e->getMessage() . '</div>';
            echo '<div class="info">Sprawdź dane w config/database.php i upewnij się, że baza została utworzona</div>';
            $allTestsPassed = false;
        }
    } else {
        echo '<div class="warning">Pomiń - brak pliku konfiguracyjnego</div>';
    }
    echo '</div>';
    
    // Test 5: Struktura katalogów
    echo '<div class="test-section">';
    echo '<h2>Test 5: Struktura katalogów</h2>';
    
    $directories = ['config', 'api', 'assets', 'assets/css', 'assets/js'];
    foreach ($directories as $dir) {
        $path = __DIR__ . '/' . $dir;
        if (is_dir($path)) {
            echo '<div class="success"><span class="status-icon">✓</span>' . $dir . '/ - istnieje</div>';
        } else {
            echo '<div class="error"><span class="status-icon">✗</span>' . $dir . '/ - brak katalogu</div>';
            $allTestsPassed = false;
        }
    }
    echo '</div>';
    
    // Test 6: Pliki aplikacji
    echo '<div class="test-section">';
    echo '<h2>Test 6: Pliki aplikacji</h2>';
    
    $files = [
        'index.php' => 'Strona główna',
        'api/calculate.php' => 'API obliczania',
        'api/order.php' => 'API zamówień',
        'assets/css/style.css' => 'Style CSS',
        'assets/js/app.js' => 'Aplikacja JS',
    ];
    
    foreach ($files as $file => $desc) {
        $path = __DIR__ . '/' . $file;
        if (file_exists($path)) {
            $size = filesize($path);
            echo '<div class="success"><span class="status-icon">✓</span>' . $desc . ' (' . $file . ') - ' . number_format($size/1024, 2) . ' KB</div>';
        } else {
            echo '<div class="error"><span class="status-icon">✗</span>' . $desc . ' (' . $file . ') - brak pliku</div>';
            $allTestsPassed = false;
        }
    }
    echo '</div>';
    
    // Test 7: Uprawnienia
    echo '<div class="test-section">';
    echo '<h2>Test 7: Uprawnienia do zapisu</h2>';
    
    if (is_writable(__DIR__)) {
        echo '<div class="success"><span class="status-icon">✓</span>Katalog główny - możliwy zapis</div>';
    } else {
        echo '<div class="warning"><span class="status-icon">⚠</span>Katalog główny - brak uprawnień do zapisu (może nie być problemem)</div>';
    }
    echo '</div>';
    
    // Podsumowanie
    echo '<div class="test-section">';
    echo '<h2>📊 Podsumowanie</h2>';
    if ($allTestsPassed) {
        echo '<div class="success" style="font-size: 18px; font-weight: bold;">';
        echo '<span class="status-icon">🎉</span>Wszystkie testy przeszły pomyślnie!';
        echo '</div>';
        echo '<div class="info">Aplikacja jest gotowa do użycia. Przejdź do <a href="index.php">strony głównej</a>.</div>';
        echo '<div class="warning"><strong>Pamiętaj:</strong> Usuń plik test.php przed wdrożeniem na produkcję!</div>';
    } else {
        echo '<div class="error" style="font-size: 18px; font-weight: bold;">';
        echo '<span class="status-icon">❌</span>Niektóre testy nie przeszły';
        echo '</div>';
        echo '<div class="info">Popraw błędy wymienione powyżej i odśwież stronę.</div>';
    }
    echo '</div>';
    
    // Informacje systemowe
    echo '<div class="test-section">';
    echo '<h2>ℹ️ Informacje systemowe</h2>';
    echo '<pre>';
    echo 'PHP Version: ' . phpversion() . "\n";
    echo 'Server Software: ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
    echo 'Document Root: ' . $_SERVER['DOCUMENT_ROOT'] . "\n";
    echo 'Current Directory: ' . __DIR__ . "\n";
    echo '</pre>';
    echo '</div>';
    ?>
    
    <div class="test-section">
        <p style="text-align: center; color: #757575;">
            Warzywa Sędzinko © 2026 | <a href="README.md">Dokumentacja</a> | <a href="INSTALL.md">Instalacja</a>
        </p>
    </div>
</body>
</html>
