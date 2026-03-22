<?php
// Strona główna: wybór boxa (konfigurator jest w konfigurator.php)
$configPath = __DIR__ . '/config/products.json';
$config = json_decode(file_get_contents($configPath), true);

$presets = [
    'small' => [
        'title' => 'BOX MAŁY (12 kg)',
        'subtitle' => 'Gotowy skład – idealny na start',
        'base_price' => 70.00,
        'shipping' => 30.00,
        'items' => [
            ['id' => 'marchew', 'qty' => 3, 'unit' => 'kg'],
            ['id' => 'ziemniaki', 'qty' => 2, 'unit' => 'kg'],
            ['id' => 'cebula', 'qty' => 2, 'unit' => 'kg'],
            ['id' => 'burak', 'qty' => 2, 'unit' => 'kg'],
            ['id' => 'cebula_czerwona', 'qty' => 1, 'unit' => 'kg'],
            ['id' => 'pietruszka', 'qty' => 1, 'unit' => 'kg'],
            ['id' => 'seler', 'qty' => 1, 'unit' => 'kg'],
            ['id' => 'por', 'qty' => 2, 'unit' => 'szt'],
            ['id' => 'czosnek', 'qty' => 2, 'unit' => 'szt'],
        ],
    ],
    'big' => [
        'title' => 'BOX DUŻY (20 kg)',
        'subtitle' => 'Więcej warzyw, więcej gotowania',
        'base_price' => 100.00,
        'shipping' => 35.00,
        'items' => [
            ['id' => 'marchew', 'qty' => 4, 'unit' => 'kg'],
            ['id' => 'ziemniaki', 'qty' => 4, 'unit' => 'kg'],
            ['id' => 'cebula', 'qty' => 4, 'unit' => 'kg'],
            ['id' => 'burak', 'qty' => 4, 'unit' => 'kg'],
            ['id' => 'cebula_czerwona', 'qty' => 2, 'unit' => 'kg'],
            ['id' => 'pietruszka', 'qty' => 1, 'unit' => 'kg'],
            ['id' => 'seler', 'qty' => 1, 'unit' => 'kg'],
            ['id' => 'por', 'qty' => 2, 'unit' => 'szt'],
            ['id' => 'czosnek', 'qty' => 2, 'unit' => 'szt'],
        ],
    ],
];

function findProductNameLocal(array $config, string $id): string {
    foreach ($config['products']['weight'] as $p) {
        if (($p['id'] ?? '') === $id) return (string)($p['name'] ?? $id);
    }
    foreach ($config['products']['pieces'] as $p) {
        if (($p['id'] ?? '') === $id) return (string)($p['name'] ?? $id);
    }
    return $id;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Wybierz box warzywny: mały 12 kg, duży 20 kg albo własny 12–24 kg.">
    <title>Warzywa Sędzinko - Wybierz box</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
</head>
<body class="home">
    <header class="header">
        <div class="container">
            <div class="header-content">
                <h1 class="logo">🥕 Warzywa Sędzinko</h1>
                <p class="tagline">Wybierz box - a my ustawimy konfigurator</p>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <section class="box-picker">
                <h2 class="picker-title">Wybierz swój box</h2>
                <p class="picker-subtitle">Box mały i duży mają gotowy skład. Box własny to wybór od zera (12–24 kg).</p>

                <div class="box-grid">
                    <?php foreach ($presets as $key => $preset): ?>
                        <article class="box-card">
                            <div class="box-card-header">
                                <h3 class="box-card-title"><?php echo htmlspecialchars($preset['title']); ?></h3>
                                <p class="box-card-sub"><?php echo htmlspecialchars($preset['subtitle']); ?></p>
                            </div>

                            <div class="box-card-prices">
                                <div class="price-row">
                                    <span>Cena bazowa</span>
                                    <strong><?php echo number_format($preset['base_price'], 2, ',', ' '); ?> PLN</strong>
                                </div>
                                <div class="price-row">
                                    <span>Koszt wysyłki</span>
                                    <strong><?php echo number_format($preset['shipping'], 2, ',', ' '); ?> PLN</strong>
                                </div>
                            </div>

                            <div class="box-card-items">
                                <h4>Skład</h4>
                                <ul>
                                    <?php foreach ($preset['items'] as $it): ?>
                                        <li>
                                            <span><?php echo htmlspecialchars(findProductNameLocal($config, $it['id'])); ?></span>
                                            <strong><?php echo htmlspecialchars((string)$it['qty']); ?> <?php echo htmlspecialchars($it['unit']); ?></strong>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <div class="box-card-actions">
                                <a class="btn btn-primary" href="konfigurator.php?preset=<?php echo urlencode($key); ?>">Wybieram ten box</a>
                            </div>
                        </article>
                    <?php endforeach; ?>

                    <article class="box-card box-card-custom">
                        <div class="box-card-header">
                            <h3 class="box-card-title">BOX WŁASNY (12–24 kg)</h3>
                            <p class="box-card-sub">Sam wybierasz skład od zera</p>
                        </div>

                        <div class="box-card-prices">
                            <div class="price-row">
                                <span>Cena</span>
                                <strong>Dynamiczna</strong>
                            </div>
                            <div class="price-row">
                                <span>Waga</span>
                                <strong>12–24 kg</strong>
                            </div>
                        </div>

                        <div class="box-card-items">
                            <h4>Jak działa?</h4>
                            <ul>
                                <li><span>Dodajesz produkty</span><strong>od zera</strong></li>
                                <li><span>System liczy wagę</span><strong>na bieżąco</strong></li>
                                <li><span>Limit</span><strong>max 24 kg</strong></li>
                            </ul>
                        </div>

                        <div class="box-card-actions">
                            <a class="btn btn-primary" href="konfigurator.php?preset=custom">Konfiguruję własny</a>
                        </div>
                    </article>
                </div>
            </section>

            <footer class="footer footer-home">
                <div class="container">
                    <div class="footer-content">
                        <p>&copy; 2026 Warzywa Sędzinko. Wszystkie prawa zastrzeżone.</p>
                        <p class="footer-links">
                            <a href="regulamin.php">Regulamin</a>
                            <span class="footer-sep">•</span>
                            <a href="polityka-prywatnosci.php">Polityka prywatności</a>
                        </p>
                    </div>
                </div>
            </footer>
        </div>
    </main>
</body>
</html>
