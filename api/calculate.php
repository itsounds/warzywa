<?php
/**
 * API endpoint do obliczania ceny, wagi i wariantu boxa
 * Obsługuje całą logikę biznesową konfiguratora
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
    
    if (!isset($data['products']) || !is_array($data['products'])) {
        throw new Exception('Brak danych produktów');
    }
    
    // Wczytaj konfigurację
    $config = getProductsConfig();
    
    // Inicjalizacja zmiennych
    $totalWeight = 0;
    $basePrice = 0;
    $extraPrice = 0;
    $products = [];
    
    // Liczniki dla specjalnych produktów
    $cebulaCzerwonaKg = 0;
    $natkaCount = 0;
    $porCount = 0;
    $czosnekCount = 0;
    
    // Przejdź przez produkty
    foreach ($data['products'] as $item) {
        if (!isset($item['id']) || !isset($item['quantity'])) {
            continue;
        }
        
        $productId = $item['id'];
        $quantity = floatval($item['quantity']);
        
        if ($quantity <= 0) {
            continue;
        }
        
        // Znajdź produkt w konfiguracji
        $product = findProductById($config, $productId);
        
        if (!$product) {
            continue;
        }
        
        // Dodaj do listy
        $products[] = [
            'id' => $productId,
            'name' => $product['name'],
            'quantity' => $quantity,
            'unit' => $product['unit'],
            'unit_price' => $product['price']
        ];
        
        // Zlicz wagę i specjalne produkty
        if ($product['unit'] === 'kg') {
            $totalWeight += $quantity;
            
            if ($productId === 'cebula_czerwona') {
                $cebulaCzerwonaKg += $quantity;
            }
        } else {
            // Produkty na sztuki
            if ($productId === 'natka') {
                $natkaCount += $quantity;
            } elseif ($productId === 'por') {
                $porCount += $quantity;
            } elseif ($productId === 'czosnek') {
                $czosnekCount += $quantity;
            }
        }
    }
    
    // Zaokrąglij wagę do 2 miejsc po przecinku
    $totalWeight = round($totalWeight, 2);
    
    // Określ wariant boxa
    $boxType = determineBoxType($totalWeight, $config);
    
    // Oblicz cenę bazową
    if ($boxType['type'] === 'box_12') {
        $basePrice = $config['boxes']['box_12']['price'];
    } elseif ($boxType['type'] === 'box_20') {
        $basePrice = $config['boxes']['box_20']['price'];
    } elseif ($boxType['type'] === 'box_custom') {
        // BOX WŁASNY - suma cen produktów wagowych
        foreach ($products as $product) {
            if ($product['unit'] === 'kg') {
                $basePrice += $product['quantity'] * $product['unit_price'];
            }
        }
    }
    
    // Oblicz dopłaty
    $extras = [];
    
    // 1. Cebula czerwona powyżej 5 kg
    if ($cebulaCzerwonaKg > 5) {
        $overLimit = $cebulaCzerwonaKg - 5;
        $cebulaCzerwonaExtra = $overLimit * $config['products']['weight'][6]['extra_price'];
        $extraPrice += $cebulaCzerwonaExtra;
        $extras[] = [
            'name' => 'Cebula czerwona (powyżej 5 kg)',
            'quantity' => round($overLimit, 2),
            'unit_price' => $config['products']['weight'][6]['extra_price'],
            'price' => round($cebulaCzerwonaExtra, 2)
        ];
    }
    
    // 2. Natka powyżej 1 pęka
    if ($natkaCount > 1) {
        $natkaPieces = findProductById($config, 'natka');
        $overLimit = $natkaCount - 1;
        $natkaExtra = $overLimit * $natkaPieces['extra_price'];
        $extraPrice += $natkaExtra;
        $extras[] = [
            'name' => 'Natka pietruszki (powyżej 1 pęka)',
            'quantity' => $overLimit,
            'unit_price' => $natkaPieces['extra_price'],
            'price' => round($natkaExtra, 2)
        ];
    }
    
    // 3. Por + Czosnek powyżej 5 łącznie
    $totalPorCzosnek = $porCount + $czosnekCount;
    if ($totalPorCzosnek > 5) {
        $overLimit = $totalPorCzosnek - 5;
        $porCzosnekExtra = $overLimit * $config['rules']['pieces_extra_price'];
        $extraPrice += $porCzosnekExtra;
        $extras[] = [
            'name' => 'Por/Czosnek (powyżej 5 szt łącznie)',
            'quantity' => $overLimit,
            'unit_price' => $config['rules']['pieces_extra_price'],
            'price' => round($porCzosnekExtra, 2)
        ];
    }
    
    // Zaokrąglij ceny
    $basePrice = round($basePrice, 2);
    $extraPrice = round($extraPrice, 2);
    $finalPrice = round($basePrice + $extraPrice, 2);
    
    // Przygotuj odpowiedź
    $response = [
        'success' => true,
        'data' => [
            'total_weight' => $totalWeight,
            'box_type' => $boxType['type'],
            'box_name' => $boxType['name'],
            'can_order' => $boxType['can_order'],
            'message' => $boxType['message'],
            'missing_to_12' => $boxType['missing_to_12'],
            'missing_to_20' => $boxType['missing_to_20'],
            'base_price' => $basePrice,
            'extra_price' => $extraPrice,
            'final_price' => $finalPrice,
            'products' => $products,
            'extras' => $extras
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Znajdź produkt po ID
 */
function findProductById($config, $id) {
    // Szukaj w produktach wagowych
    foreach ($config['products']['weight'] as $product) {
        if ($product['id'] === $id) {
            return $product;
        }
    }
    
    // Szukaj w produktach sztukowych
    foreach ($config['products']['pieces'] as $product) {
        if ($product['id'] === $id) {
            return $product;
        }
    }
    
    return null;
}

/**
 * Określ wariant boxa na podstawie wagi
 */
function determineBoxType($weight, $config) {
    $result = [
        'type' => null,
        'name' => '',
        'can_order' => false,
        'message' => '',
        'missing_to_12' => 0,
        'missing_to_20' => 0
    ];
    
    // Oblicz braki
    if ($weight < 12) {
        $result['missing_to_12'] = round(12 - $weight, 2);
    }
    
    if ($weight < 20) {
        $result['missing_to_20'] = round(20 - $weight, 2);
    }
    
    // Logika wyboru boxa
    if ($weight < 12) {
        $result['type'] = 'none';
        $result['name'] = 'Za mała waga (brakuje ' . $result['missing_to_12'] . ' kg)';
        $result['can_order'] = false;
        $result['message'] = 'Minimalna waga to 12 kg. Dodaj jeszcze ' . $result['missing_to_12'] . ' kg produktów.';
    } elseif ($weight == 12) {
        $result['type'] = 'box_12';
        $result['name'] = $config['boxes']['box_12']['name'];
        $result['can_order'] = true;
        $result['message'] = 'Idealnie! Twój box waży dokładnie 12 kg.';
    } elseif ($weight > 12 && $weight < 20) {
        $result['type'] = 'box_custom';
        $result['name'] = $config['boxes']['box_custom']['name'];
        $result['can_order'] = true;
        $result['message'] = 'Do BOX 20 KG brakuje: ' . $result['missing_to_20'] . ' kg';
    } elseif ($weight == 20) {
        $result['type'] = 'box_20';
        $result['name'] = $config['boxes']['box_20']['name'];
        $result['can_order'] = true;
        $result['message'] = 'Doskonale! Twój box waży dokładnie 20 kg.';
    } elseif ($weight > 20 && $weight <= 24) {
        $result['type'] = 'box_custom';
        $result['name'] = $config['boxes']['box_custom']['name'];
        $result['can_order'] = true;
        $result['message'] = 'Można zamówić. Maksymalna waga to 24 kg.';
    } else {
        // Powyżej 24 kg
        $result['type'] = 'blocked';
        $result['name'] = 'Przekroczono limit';
        $result['can_order'] = false;
        $result['message'] = 'Maksymalna waga to 24 kg. Zmniejsz ilość produktów o ' . round($weight - 24, 2) . ' kg.';
    }
    
    return $result;
}
