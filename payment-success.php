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
        }
        .btn-back:hover {
            background: var(--primary-hover);
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
                echo "Dziękujemy za zamówienie #" . $orderId . ".<br>";
            }
            ?>
            Twoje zamówienie zostało opłacone i jest w trakcie realizacji.<br>
            Wkrótce otrzymasz potwierdzenie na email.
        </p>
        <a href="index.php" class="btn-back">Wróć do strony głównej</a>
    </div>
</body>
</html>
