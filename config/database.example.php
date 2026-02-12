<?php
/**
 * Przykładowy plik konfiguracji bazy danych
 * Skopiuj ten plik jako database.php i uzupełnij danymi
 */

// Konfiguracja bazy danych
define('DB_HOST', 'localhost');
define('DB_NAME', 'warzywasedzinko');
define('DB_USER', 'root');
define('DB_PASS', ''); // Wpisz swoje hasło
define('DB_CHARSET', 'utf8mb4');

/**
 * Funkcja łącząca z bazą danych
 * @return PDO
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode([
                'success' => false,
                'error' => 'Błąd połączenia z bazą danych: ' . $e->getMessage()
            ]));
        }
    }
    
    return $pdo;
}

/**
 * Funkcja wczytująca konfigurację produktów
 * @return array
 */
function getProductsConfig() {
    $configPath = __DIR__ . '/products.json';
    
    if (!file_exists($configPath)) {
        throw new Exception('Brak pliku konfiguracyjnego products.json');
    }
    
    $json = file_get_contents($configPath);
    $config = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Błąd parsowania products.json: ' . json_last_error_msg());
    }
    
    return $config;
}
