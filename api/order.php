<?php
/**
 * API endpoint do zapisywania zamówień do bazy danych
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Obsługa OPTIONS request (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Tylko POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metoda niedozwolona']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

try {
    // Pobierz dane z requestu
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Nieprawidłowy format JSON');
    }
    
    // Walidacja wymaganych pól
    if (!isset($data['total_weight']) || !isset($data['box_type']) || 
        !isset($data['base_price']) || !isset($data['final_price']) ||
        !isset($data['products']) || !is_array($data['products'])) {
        throw new Exception('Brak wymaganych danych zamówienia');
    }
    
    if (empty($data['products'])) {
        throw new Exception('Zamówienie musi zawierać przynajmniej jeden produkt');
    }
    
    // Walidacja dodatkowa - czy można zamówić
    if (!isset($data['can_order']) || $data['can_order'] !== true) {
        throw new Exception('To zamówienie nie może zostać złożone (nieprawidłowa waga)');
    }
    
    // Opcjonalne dane klienta
    $customerName = isset($data['customer_name']) ? trim($data['customer_name']) : null;
    $customerEmail = isset($data['customer_email']) ? trim($data['customer_email']) : null;
    $customerPhone = isset($data['customer_phone']) ? trim($data['customer_phone']) : null;
    
    // Walidacja email jeśli podany
    if ($customerEmail && !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Nieprawidłowy adres email');
    }
    
    // Połącz z bazą
    $pdo = getDBConnection();
    
    // Rozpocznij transakcję
    $pdo->beginTransaction();
    
    try {
        // Wstaw zamówienie
        $stmt = $pdo->prepare("
            INSERT INTO orders 
            (total_weight, box_type, base_price, extra_price, final_price, 
             customer_name, customer_email, customer_phone)
            VALUES 
            (:total_weight, :box_type, :base_price, :extra_price, :final_price,
             :customer_name, :customer_email, :customer_phone)
        ");
        
        $stmt->execute([
            ':total_weight' => floatval($data['total_weight']),
            ':box_type' => $data['box_type'],
            ':base_price' => floatval($data['base_price']),
            ':extra_price' => isset($data['extra_price']) ? floatval($data['extra_price']) : 0,
            ':final_price' => floatval($data['final_price']),
            ':customer_name' => $customerName,
            ':customer_email' => $customerEmail,
            ':customer_phone' => $customerPhone
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // Wstaw produkty
        $stmt = $pdo->prepare("
            INSERT INTO order_items 
            (order_id, product_name, quantity, unit, unit_price, total_price)
            VALUES 
            (:order_id, :product_name, :quantity, :unit, :unit_price, :total_price)
        ");
        
        foreach ($data['products'] as $product) {
            $totalPrice = floatval($product['quantity']) * floatval($product['unit_price']);
            
            $stmt->execute([
                ':order_id' => $orderId,
                ':product_name' => $product['name'],
                ':quantity' => floatval($product['quantity']),
                ':unit' => $product['unit'],
                ':unit_price' => floatval($product['unit_price']),
                ':total_price' => $totalPrice
            ]);
        }
        
        // Zatwierdź transakcję
        $pdo->commit();
        
        // Zwróć sukces
        echo json_encode([
            'success' => true,
            'message' => 'Zamówienie zostało zapisane pomyślnie',
            'order_id' => $orderId
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        // Wycofaj transakcję w przypadku błędu
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
