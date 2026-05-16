<?php
/**
 * Klasa pomocnicza do obsługi Google Sheets z Service Account
 */

class GoogleSheetsHelper
{
    private $client;
    private $service;
    private $spreadsheetId;

    public function __construct()
    {
        require_once __DIR__ . '/../config/google-sheets.php';
        
        $this->spreadsheetId = GOOGLE_SPREADSHEET_ID;
        
        // Sprawdź czy plik Service Account istnieje
        if (!file_exists(GOOGLE_SERVICE_ACCOUNT_FILE)) {
            throw new Exception('Brak pliku Service Account: ' . GOOGLE_SERVICE_ACCOUNT_FILE);
        }
        
        // Utwórz klienta Google z Service Account
        $this->client = new Google_Client();
        $this->client->setApplicationName('Warzywa Sędzinko - Zamówienia');
        $this->client->setScopes(GOOGLE_SCOPES);
        $this->client->setAuthConfig(GOOGLE_SERVICE_ACCOUNT_FILE);

        // Utwórz serwis Google Sheets
        $this->service = new Google_Service_Sheets($this->client);
    }

    /**
     * Dodaj zamówienie do arkusza
     */
    public function addOrder($orderData)
    {
        // Przygotuj nagłówki (jeśli to pierwsze zamówienie)
        $this->ensureHeaders();

        // Sformatuj adres
        $address = $this->formatAddress($orderData);

        // Przygotuj wiersz z danymi zamówienia
        $row = [
            date('Y-m-d H:i:s'), // Data
            $orderData['order_id'] ?? '',
            $orderData['customer_name'] ?? '',
            $orderData['customer_email'] ?? '',
            $orderData['customer_phone'] ?? '',
            $address, // Pełny adres
            $orderData['box_type'] ?? '',
            number_format($orderData['total_weight'], 2, '.', ''),
            number_format($orderData['base_price'], 2, '.', ''),
            number_format($orderData['extra_price'] ?? 0, 2, '.', ''),
            number_format($orderData['final_price'], 2, '.', ''),
            $this->getPaymentStatusLabel($orderData['payment_status'] ?? 'pending'), // Status płatności
            $this->formatProducts($orderData['products'] ?? []) // Produkty na końcu
        ];

        // Dodaj wiersz do arkusza
        $range = 'Sheet1!A:M'; // Kolumny A-M
        $body = new Google_Service_Sheets_ValueRange([
            'values' => [$row]
        ]);
        $params = [
            'valueInputOption' => 'USER_ENTERED'
        ];

        $this->service->spreadsheets_values->append(
            $this->spreadsheetId,
            $range,
            $body,
            $params
        );

        return true;
    }

    /**
     * Upewnij się, że arkusz ma nagłówki
     */
    private function ensureHeaders()
    {
        // Sprawdź czy pierwszy wiersz ma nagłówki
        $range = 'Sheet1!A1:M1';
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
        $values = $response->getValues();

        // Jeśli brak nagłówków, dodaj je
        if (empty($values)) {
            $headers = [
                'Data zamówienia',
                'ID zamówienia',
                'Imię i nazwisko',
                'Email',
                'Telefon',
                'Adres dostawy',
                'Typ boxa',
                'Waga (kg)',
                'Cena bazowa (zł)',
                'Dopłaty (zł)',
                'Cena końcowa (zł)',
                'Status płatności',
                'Produkty'
            ];

            $body = new Google_Service_Sheets_ValueRange([
                'values' => [$headers]
            ]);
            $params = [
                'valueInputOption' => 'USER_ENTERED'
            ];

            $this->service->spreadsheets_values->update(
                $this->spreadsheetId,
                $range,
                $body,
                $params
            );
        }
    }

    /**
     * Sformatuj adres do jednej komórki
     */
    private function formatAddress($orderData)
    {
        $parts = [];
        
        // Ulica i numery
        if (!empty($orderData['customer_street'])) {
            $street = $orderData['customer_street'];
            if (!empty($orderData['customer_building'])) {
                $street .= ' ' . $orderData['customer_building'];
                if (!empty($orderData['customer_apartment'])) {
                    $street .= '/' . $orderData['customer_apartment'];
                }
            }
            $parts[] = $street;
        }
        
        // Kod pocztowy i miasto
        if (!empty($orderData['customer_postal_code']) || !empty($orderData['customer_city'])) {
            $location = '';
            if (!empty($orderData['customer_postal_code'])) {
                $location = $orderData['customer_postal_code'];
            }
            if (!empty($orderData['customer_city'])) {
                $location .= ($location ? ' ' : '') . $orderData['customer_city'];
            }
            $parts[] = $location;
        }
        
        return !empty($parts) ? implode(', ', $parts) : '';
    }

    /**
     * Sformatuj listę produktów do jednej komórki
     */
    private function formatProducts($products)
    {
        $formatted = [];
        foreach ($products as $product) {
            $formatted[] = sprintf(
                '%s: %.2f %s (%.2f zł)',
                $product['name'],
                $product['quantity'],
                $product['unit'],
                $product['unit_price']
            );
        }
        return implode('; ', $formatted);
    }

    /**
     * Zwróć czytelną etykietę statusu płatności
     */
    private function getPaymentStatusLabel($status)
    {
        $labels = [
            'pending' => '⏳ Oczekuje na płatność',
            'paid' => '✅ Opłacone',
            'refunded' => '↩️ Zwrot',
            'error' => '❌ Błąd',
            'chargeback' => '⚠️ Chargeback'
        ];
        return $labels[$status] ?? $status;
    }

    /**
     * Aktualizuj status płatności w arkuszu
     */
    public function updatePaymentStatus($orderId, $paymentStatus)
    {
        // Znajdź wiersz z tym order_id (kolumna B)
        $range = 'Sheet1!B:B';
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
        $values = $response->getValues();

        if (empty($values)) {
            return false;
        }

        // Szukaj wiersza z tym ID zamówienia
        $rowNumber = null;
        foreach ($values as $index => $row) {
            if (isset($row[0]) && $row[0] == $orderId) {
                $rowNumber = $index + 1; // +1 bo indeksy w Sheets zaczynają się od 1
                break;
            }
        }

        if (!$rowNumber) {
            return false; // Nie znaleziono zamówienia
        }

        // Zaktualizuj status w kolumnie L
        $updateRange = 'Sheet1!L' . $rowNumber;
        $body = new Google_Service_Sheets_ValueRange([
            'values' => [[$this->getPaymentStatusLabel($paymentStatus)]]
        ]);
        $params = [
            'valueInputOption' => 'USER_ENTERED'
        ];

        $this->service->spreadsheets_values->update(
            $this->spreadsheetId,
            $updateRange,
            $body,
            $params
        );

        return true;
    }
}
