<?php
/**
 * PRZYKŁAD konfiguracji Google Sheets API
 * 
 * Skopiuj ten plik jako config/google-sheets.php
 * i uzupełnij prawdziwymi danymi
 */

// OAuth 2.0 Credentials
// Pobierz z: https://console.cloud.google.com/apis/credentials
define('GOOGLE_CLIENT_ID', 'TWOJ_CLIENT_ID.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'TWOJ_CLIENT_SECRET');

// Redirect URI - MUSI być dodany w Google Cloud Console
// Dla lokalnego rozwoju:
define('GOOGLE_REDIRECT_URI', 'http://localhost/api/google-auth-callback.php');
// Dla produkcji:
// define('GOOGLE_REDIRECT_URI', 'https://twoja-domena.pl/api/google-auth-callback.php');

// ID arkusza Google Sheets
// Pobierz z URL arkusza: https://docs.google.com/spreadsheets/d/TUTAJ_JEST_ID/edit
define('GOOGLE_SPREADSHEET_ID', 'TWOJ_SPREADSHEET_ID');

// Plik z tokenem - nie zmieniaj tej ścieżki
define('GOOGLE_TOKEN_FILE', __DIR__ . '/google-token.json');

// Uprawnienia (scopes) - nie zmieniaj
define('GOOGLE_SCOPES', [
    'https://www.googleapis.com/auth/spreadsheets'
]);
