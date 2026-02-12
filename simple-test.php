<?php
/**
 * Prosty test - sprawdza czy PHP działa
 */
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Test PHP</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f5f5f5; }
        .box { background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; }
        .success { color: #2e7d32; font-size: 24px; }
        .info { margin: 20px 0; padding: 15px; background: #e8f5e9; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="box">
        <h1 class="success">✓ PHP działa!</h1>
        
        <div class="info">
            <strong>Wersja PHP:</strong> <?php echo phpversion(); ?><br>
            <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?><br>
            <strong>Katalog:</strong> <?php echo __DIR__; ?>
        </div>
        
        <h2>Test wczytywania products.json:</h2>
        <?php
        $configPath = __DIR__ . '/config/products.json';
        
        if (file_exists($configPath)) {
            echo '<p style="color: green;">✓ Plik products.json istnieje</p>';
            
            $json = file_get_contents($configPath);
            $config = json_decode($json, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                echo '<p style="color: green;">✓ JSON jest poprawny</p>';
                echo '<p>Produktów na wagę: ' . count($config['products']['weight']) . '</p>';
                echo '<p>Produktów na sztuki: ' . count($config['products']['pieces']) . '</p>';
            } else {
                echo '<p style="color: red;">✗ Błąd JSON: ' . json_last_error_msg() . '</p>';
            }
        } else {
            echo '<p style="color: red;">✗ Brak pliku products.json</p>';
        }
        ?>
        
        <h2>Test struktury katalogów:</h2>
        <?php
        $dirs = ['config', 'api', 'assets', 'assets/css', 'assets/js'];
        foreach ($dirs as $dir) {
            $path = __DIR__ . '/' . $dir;
            if (is_dir($path)) {
                echo '<p style="color: green;">✓ ' . $dir . '/</p>';
            } else {
                echo '<p style="color: red;">✗ Brak: ' . $dir . '/</p>';
            }
        }
        ?>
        
        <hr style="margin: 30px 0;">
        
        <p><strong>Jeśli wszystko jest zielone, przejdź do:</strong></p>
        <p>
            <a href="index.php" style="display: inline-block; padding: 12px 24px; background: #2e7d32; color: white; text-decoration: none; border-radius: 6px; margin: 5px;">
                Strona główna
            </a>
            <a href="test.php" style="display: inline-block; padding: 12px 24px; background: #f57c00; color: white; text-decoration: none; border-radius: 6px; margin: 5px;">
                Pełny test
            </a>
        </p>
    </div>
</body>
</html>
