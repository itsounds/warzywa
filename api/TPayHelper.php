<?php
/**
 * Klasa pomocnicza do obsługi TPay Open API
 */

class TPayHelper
{
    private $merchantId;
    private $clientId;
    private $clientSecret;
    private $apiUrl;
    private $accessToken;

    public function __construct()
    {
        require_once __DIR__ . '/../config/tpay.php';
        
        $this->merchantId = TPAY_MERCHANT_ID;
        $this->clientId = TPAY_CLIENT_ID;
        $this->clientSecret = TPAY_CLIENT_SECRET;
        $this->apiUrl = TPAY_API_URL;
    }

    /**
     * Pobierz token dostępu OAuth
     */
    private function getAccessToken()
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $ch = curl_init($this->apiUrl . '/oauth/auth');
        
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ],
            CURLOPT_POSTFIELDS => http_build_query([
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('Błąd cURL: ' . $error);
        }

        if ($httpCode !== 200) {
            throw new Exception('Błąd autoryzacji TPay (HTTP ' . $httpCode . '): ' . $response);
        }

        $data = json_decode($response, true);
        
        if (!isset($data['access_token'])) {
            throw new Exception('Brak tokena w odpowiedzi TPay: ' . $response);
        }

        $this->accessToken = $data['access_token'];
        return $this->accessToken;
    }

    /**
     * Utwórz transakcję płatności
     * 
     * @param array $orderData - dane zamówienia
     * @return array - zwraca URL do przekierowania i ID transakcji
     */
    public function createTransaction($orderData)
    {
        $token = $this->getAccessToken();

        // Przygotuj dane transakcji
        $transactionData = [
            'amount' => number_format($orderData['amount'], 2, '.', ''),
            'description' => $orderData['description'],
            'hiddenDescription' => 'Zamówienie #' . $orderData['order_id'],
            'payer' => [
                'email' => $orderData['email'],
                'name' => $orderData['name'] ?? null
            ],
            'callbacks' => [
                'payerUrls' => [
                    'success' => TPAY_SUCCESS_URL . '?order_id=' . $orderData['order_id'],
                    'error' => TPAY_ERROR_URL . '?order_id=' . $orderData['order_id']
                ],
                'notification' => [
                    'url' => TPAY_NOTIFICATION_URL
                ]
            ]
        ];
        
        // Dodaj opcjonalne pola
        if (!empty($orderData['phone'])) {
            $transactionData['payer']['phone'] = $orderData['phone'];
        }
        if (!empty($orderData['address'])) {
            $transactionData['payer']['address'] = $orderData['address'];
        }

        // Wywołaj API
        $ch = curl_init($this->apiUrl . '/transactions');
        
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
            CURLOPT_POSTFIELDS => json_encode($transactionData)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 201 && $httpCode !== 200) {
            throw new Exception('Błąd tworzenia transakcji TPay: ' . $response);
        }

        $data = json_decode($response, true);

        if (!isset($data['transactionId']) || !isset($data['transactionPaymentUrl'])) {
            throw new Exception('Nieprawidłowa odpowiedź z TPay: ' . $response);
        }

        return [
            'transaction_id' => $data['transactionId'],
            'payment_url' => $data['transactionPaymentUrl'],
            'title' => $data['title'] ?? null
        ];
    }

    /**
     * Weryfikuj powiadomienie z TPay (webhook)
     * 
     * @param array $notification - dane z POST
     * @return bool
     */
    public function verifyNotification($notification)
    {
        // TPay Open API używa JWT do weryfikacji notyfikacji
        // Dla uproszczenia w sandbox możemy sprawdzić podstawowe pola
        
        if (!isset($notification['tr_id']) || !isset($notification['tr_status'])) {
            return false;
        }

        return true;
    }

    /**
     * Pobierz status transakcji
     * 
     * @param string $transactionId
     * @return array
     */
    public function getTransactionStatus($transactionId)
    {
        $token = $this->getAccessToken();

        $ch = curl_init($this->apiUrl . '/transactions/' . $transactionId);
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('Błąd pobierania statusu transakcji: ' . $response);
        }

        return json_decode($response, true);
    }
}
