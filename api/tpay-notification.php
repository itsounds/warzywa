<?php
/**
 * Webhook do obsługi powiadomień z TPay
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/TPayHelper.php';

// Loguj wszystkie przychodzące powiadomienia (do debugowania)
$logFile = __DIR__ . '/../logs/tpay-notifications.log';
$logData = date('Y-m-d H:i:s') . ' - ' . file_get_contents('php://input') . "\n";
@file_put_contents($logFile, $logData, FILE_APPEND);

try {
    // Pobierz dane z POST
    $input = file_get_contents('php://input');
    $notification = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Może być format x-www-form-urlencoded
        $notification = $_POST;
    }
    
    // Sprawdź czy są wymagane dane
    if (empty($notification)) {
        throw new Exception('Brak danych w powiadomieniu');
    }
    
    // Weryfikacja powiadomienia
    $tpay = new TPayHelper();
    if (!$tpay->verifyNotification($notification)) {
        throw new Exception('Nieprawidłowe powiadomienie');
    }
    
    // Pobierz dane z powiadomienia
    $transactionId = $notification['tr_id'] ?? null;
    $status = $notification['tr_status'] ?? null;
    
    if (!$transactionId) {
        throw new Exception('Brak transaction ID');
    }
    
    // Mapowanie statusów TPay
    $statusMap = [
        'pending' => 'pending',
        'correct' => 'paid',
        'paid' => 'paid',
        'refund' => 'refunded',
        'chargeback' => 'chargeback',
        'error' => 'error'
    ];
    
    $paymentStatus = $statusMap[$status] ?? 'unknown';
    
    // Aktualizuj status zamówienia w bazie
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET payment_status = :payment_status 
        WHERE tpay_transaction_id = :transaction_id
    ");
    
    $stmt->execute([
        ':payment_status' => $paymentStatus,
        ':transaction_id' => $transactionId
    ]);
    
    // Pobierz order_id dla Google Sheets
    $stmt = $pdo->prepare("
        SELECT id FROM orders WHERE tpay_transaction_id = :transaction_id
    ");
    $stmt->execute([':transaction_id' => $transactionId]);
    $order = $stmt->fetch();
    
    // Zaktualizuj status w Google Sheets
    if ($order) {
        try {
            require_once __DIR__ . '/../vendor/autoload.php';
            require_once __DIR__ . '/GoogleSheetsHelper.php';
            
            $sheets = new GoogleSheetsHelper();
            $sheets->updatePaymentStatus($order['id'], $paymentStatus);
        } catch (Exception $sheetsError) {
            error_log('Błąd aktualizacji Google Sheets: ' . $sheetsError->getMessage());
        }
    }
    
    // Zwróć TRUE zgodnie z wymaganiami TPay
    echo json_encode(['result' => true]);
    http_response_code(200);
    
} catch (Exception $e) {
    // Loguj błąd
    $errorLog = date('Y-m-d H:i:s') . ' ERROR: ' . $e->getMessage() . "\n";
    @file_put_contents($logFile, $errorLog, FILE_APPEND);
    
    // Zwróć FALSE zgodnie z wymaganiami TPay
    echo json_encode(['result' => false, 'error' => $e->getMessage()]);
    http_response_code(400);
}
