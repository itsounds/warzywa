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
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/GoogleSheetsHelper.php';
require_once __DIR__ . '/TPayHelper.php';

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
    
    // Opcjonalne dane adresowe
    $customerStreet = isset($data['customer_street']) ? trim($data['customer_street']) : null;
    $customerBuilding = isset($data['customer_building']) ? trim($data['customer_building']) : null;
    $customerApartment = isset($data['customer_apartment']) ? trim($data['customer_apartment']) : null;
    $customerPostalCode = isset($data['customer_postal_code']) ? trim($data['customer_postal_code']) : null;
    $customerCity = isset($data['customer_city']) ? trim($data['customer_city']) : null;
    
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
             customer_name, customer_email, customer_phone,
             customer_street, customer_building, customer_apartment,
             customer_postal_code, customer_city)
            VALUES 
            (:total_weight, :box_type, :base_price, :extra_price, :final_price,
             :customer_name, :customer_email, :customer_phone,
             :customer_street, :customer_building, :customer_apartment,
             :customer_postal_code, :customer_city)
        ");
        
        $stmt->execute([
            ':total_weight' => floatval($data['total_weight']),
            ':box_type' => $data['box_type'],
            ':base_price' => floatval($data['base_price']),
            ':extra_price' => isset($data['extra_price']) ? floatval($data['extra_price']) : 0,
            ':final_price' => floatval($data['final_price']),
            ':customer_name' => $customerName,
            ':customer_email' => $customerEmail,
            ':customer_phone' => $customerPhone,
            ':customer_street' => $customerStreet,
            ':customer_building' => $customerBuilding,
            ':customer_apartment' => $customerApartment,
            ':customer_postal_code' => $customerPostalCode,
            ':customer_city' => $customerCity
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
        
        // Zapisz do Google Sheets
        try {
            $sheets = new GoogleSheetsHelper();
            $sheets->addOrder([
                'order_id' => $orderId,
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'customer_phone' => $customerPhone,
                'customer_street' => $customerStreet,
                'customer_building' => $customerBuilding,
                'customer_apartment' => $customerApartment,
                'customer_postal_code' => $customerPostalCode,
                'customer_city' => $customerCity,
                'box_type' => $data['box_type'],
                'total_weight' => $data['total_weight'],
                'base_price' => $data['base_price'],
                'extra_price' => isset($data['extra_price']) ? $data['extra_price'] : 0,
                'final_price' => $data['final_price'],
                'products' => $data['products']
            ]);
        } catch (Exception $sheetsError) {
            // Jeśli zapis do Sheets się nie powiedzie, loguj błąd ale nie przerywaj (zamówienie już w bazie)
            error_log('Błąd zapisu do Google Sheets: ' . $sheetsError->getMessage());
        }
        
        // Utwórz transakcję w TPay
        try {
            $tpay = new TPayHelper();
            
            // Przygotuj dane do TPay
            $tpayData = [
                'order_id' => $orderId,
                'amount' => floatval($data['final_price']),
                'description' => 'Zamówienie #' . $orderId . ' - ' . $data['box_type'],
                'email' => $customerEmail ?: 'brak@email.pl',
                'name' => $customerName,
                'phone' => $customerPhone,
                'address' => null
            ];
            
            // Dodaj adres jeśli jest
            if ($customerStreet || $customerCity) {
                $addressParts = [];
                if ($customerStreet) {
                    $addressParts[] = $customerStreet;
                    if ($customerBuilding) {
                        $addressParts[count($addressParts)-1] .= ' ' . $customerBuilding;
                        if ($customerApartment) {
                            $addressParts[count($addressParts)-1] .= '/' . $customerApartment;
                        }
                    }
                }
                if ($customerPostalCode || $customerCity) {
                    $addressParts[] = trim(($customerPostalCode ?: '') . ' ' . ($customerCity ?: ''));
                }
                $tpayData['address'] = implode(', ', $addressParts);
            }
            
            $tpayResult = $tpay->createTransaction($tpayData);
            
            // Zaktualizuj zamówienie o dane TPay
            $stmt = $pdo->prepare("
                UPDATE orders 
                SET tpay_transaction_id = :transaction_id, tpay_title = :title
                WHERE id = :order_id
            ");
            $stmt->execute([
                ':transaction_id' => $tpayResult['transaction_id'],
                ':title' => $tpayResult['title'],
                ':order_id' => $orderId
            ]);
            
            // Zwróć sukces z URL do płatności
            echo json_encode([
                'success' => true,
                'message' => 'Zamówienie zostało zapisane pomyślnie',
                'order_id' => $orderId,
                'payment_url' => $tpayResult['payment_url'],
                'transaction_id' => $tpayResult['transaction_id']
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $tpayError) {
            // Jeśli TPay się nie powiedzie, zwróć sukces ale bez URL płatności
            error_log('Błąd tworzenia transakcji TPay: ' . $tpayError->getMessage());
            
            echo json_encode([
                'success' => true,
                'message' => 'Zamówienie zostało zapisane, ale wystąpił problem z płatnością',
                'order_id' => $orderId,
                'payment_error' => $tpayError->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        
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
