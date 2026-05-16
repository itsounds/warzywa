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
    // Pobierz dane z POST (TPay wysyła w formacie URL-encoded)
    $notification = $_POST;
    
    // Jeśli nie ma danych w $_POST, spróbuj JSON
    if (empty($notification)) {
        $input = file_get_contents('php://input');
        $notification = json_decode($input, true);
    }
    
    // Sprawdź czy są wymagane dane
    if (empty($notification)) {
        throw new Exception('Brak danych w powiadomieniu');
    }
    
    // TPay może wysyłać w różnych formatach - obsłuż oba
    $transactionId = null;
    $status = null;
    
    // Format starego API (ma pole tr_id)
    if (isset($notification['tr_id'])) {
        $transactionId = $notification['tr_id'];
        $status = ($notification['tr_status'] === 'TRUE') ? 'paid' : 'pending';
    } 
    // Format Open API (ma pole transactionId)
    elseif (isset($notification['transactionId'])) {
        $transactionId = $notification['transactionId'];
        $status = $notification['status'] ?? 'unknown';
    }
    
    if (!$transactionId) {
        throw new Exception('Brak transaction ID w powiadomieniu');
    }
    
    // Mapowanie statusów TPay
    $statusMap = [
        'pending' => 'pending',
        'correct' => 'paid',
        'paid' => 'paid',
        'TRUE' => 'paid',
        'refund' => 'refunded',
        'chargeback' => 'chargeback',
        'error' => 'error'
    ];
    
    $paymentStatus = $statusMap[$status] ?? 'unknown';
    
    // Aktualizuj status zamówienia w bazie
    $pdo = getDBConnection();
    
    // Szukaj po tpay_transaction_id lub tpay_title (stary format używa tr_id)
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET payment_status = :payment_status 
        WHERE tpay_transaction_id = :transaction_id 
           OR tpay_title = :transaction_id
    ");
    
    $stmt->execute([
        ':payment_status' => $paymentStatus,
        ':transaction_id' => $transactionId
    ]);
    
    // Pobierz order_id dla Google Sheets
    $stmt = $pdo->prepare("
        SELECT id FROM orders 
        WHERE tpay_transaction_id = :transaction_id 
           OR tpay_title = :transaction_id
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
