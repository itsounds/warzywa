<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Płatność zakończona - Warzywa Sędzinko</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .payment-result {
            max-width: 600px;
            margin: 100px auto;
            text-align: center;
            padding: 2rem;
        }
        .payment-result h1 {
            color: var(--primary-color);
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .payment-result .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .payment-result p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }
        .btn-back {
            display: inline-block;
            padding: 0.8rem 2rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            background: #236b26;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
        }
    </style>
</head>
<body>
    <div class="payment-result">
        <div class="icon">✅</div>
        <h1>Płatność zakończona pomyślnie!</h1>
        <p>
            <?php
            $orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;
            if ($orderId) {
                echo "Dziękujemy za zamówienie #" . $orderId . ".<br><br>";
            }
            ?>
            <strong>Twoje zamówienie zostało opłacone!</strong><br><br>
            Gdy box zostanie nadany, zostaniesz powiadomiony na adres email<br>
            wraz z numerem przesyłki do śledzenia.
        </p>
        <a href="index.php" class="btn-back">Wróć do strony głównej</a>
    </div>
</body>
</html>
