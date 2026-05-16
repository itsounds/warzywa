<?php
/**
 * PRZYKŁAD konfiguracji TPay
 * 
 * Skopiuj ten plik jako config/tpay.php
 * i uzupełnij prawdziwymi danymi
 */

// Merchant ID
define('TPAY_MERCHANT_ID', 'TWOJ_MERCHANT_ID');

// Open API Credentials
define('TPAY_CLIENT_ID', 'TWOJ_CLIENT_ID');
define('TPAY_CLIENT_SECRET', 'TWOJ_CLIENT_SECRET');

// Środowisko (sandbox / production)
define('TPAY_ENVIRONMENT', 'sandbox'); // zmień na 'production' w produkcji

// URL-e API (nie zmieniaj)
define('TPAY_API_URL', TPAY_ENVIRONMENT === 'sandbox' 
    ? 'https://openapi.sandbox.tpay.com'
    : 'https://openapi.tpay.com'
);

// URL-e powrotu po płatności (dostosuj do swojej domeny)
define('TPAY_SUCCESS_URL', 'http://localhost/payment-success.php'); // lub https://twoja-domena.pl/payment-success.php
define('TPAY_ERROR_URL', 'http://localhost/payment-error.php'); // lub https://twoja-domena.pl/payment-error.php
define('TPAY_NOTIFICATION_URL', 'http://localhost/api/tpay-notification.php'); // lub https://twoja-domena.pl/api/tpay-notification.php
