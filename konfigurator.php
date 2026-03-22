<?php
// Wczytaj konfigurację produktów (bez połączenia z bazą)
$configPath = __DIR__ . '/config/products.json';
$config = json_decode(file_get_contents($configPath), true);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Świeże warzywa prosto z gospodarstwa. Skonfiguruj swój box warzywny i zamów online.">
    <title>Warzywa Sędzinko - Konfigurator Boxa Warzywnego</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Sticky Bar - Kompaktowe Podsumowanie -->
    <div class="sticky-bar" id="stickyBar">
        <div class="sticky-content">
            <div class="sticky-info">
                <span class="sticky-weight" id="stickyWeight">0 kg</span>
                <span class="sticky-box" id="stickyBox">-</span>
                <span class="sticky-price" id="stickyPrice">0 zł</span>
            </div>
            <button class="sticky-cta" id="stickyOrderBtn" disabled>Zamów</button>
        </div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <h1 class="logo">🥕 Warzywa Sędzinko</h1>
                <p class="tagline">Prosto z gospodarstwa do Twojego stołu</p>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h2>Skonfiguruj swój box warzywny</h2>
            <p>Wybierz produkty, a my dostosujemy najlepszy wariant boxa</p>
        </div>
    </section>

    <!-- Konfigurator -->
    <main class="main-content">
        <div class="container">
            
            <!-- Panel informacyjny -->
            <div class="info-panel" id="infoPanel">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Aktualna waga:</span>
                        <span class="info-value" id="currentWeight">0 kg</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Aktualny wariant:</span>
                        <span class="info-value" id="boxType">-</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Cena bazowa:</span>
                        <span class="info-value" id="basePrice">0 zł</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Dopłaty:</span>
                        <span class="info-value" id="extraPrice">0 zł</span>
                    </div>
                    <div class="info-item info-item-highlight">
                        <span class="info-label">Cena końcowa:</span>
                        <span class="info-value" id="finalPrice">0 zł</span>
                    </div>
                </div>
                
                <!-- Pasek postępu wagi -->
                <div class="weight-progress">
                    <div class="weight-progress-bar" id="weightProgressBar"></div>
                    <div class="weight-marker weight-marker-12" title="BOX 12 KG">12 kg</div>
                    <div class="weight-marker weight-marker-20" title="BOX 20 KG">20 kg</div>
                    <div class="weight-marker weight-marker-24" title="Maksimum">24 kg</div>
                </div>
                
                <!-- Komunikaty -->
                <div class="info-message" id="infoMessage">
                    <p>Dodaj produkty, aby rozpocząć konfigurację boxa</p>
                </div>
            </div>

            <!-- Sekcja produktów -->
            <div class="products-section">
                
                <!-- Produkty na wagę -->
                <div class="product-category">
                    <h3 class="category-title">🥕 Produkty na wagę (kg)</h3>
                    <div class="products-grid" id="weightProducts">
                        <?php foreach ($config['products']['weight'] as $product): ?>
                        <div class="product-card" data-product-id="<?php echo htmlspecialchars($product['id']); ?>">
                            <div class="product-header">
                                <h4 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h4>
                                <span class="product-price"><?php echo $product['price']; ?> zł/kg</span>
                            </div>
                            
                            <?php if (isset($product['free_limit'])): ?>
                            <div class="product-info">
                                <small>Do <?php echo $product['free_limit']; ?> kg w cenie, powyżej +<?php echo $product['extra_price']; ?> zł/kg</small>
                            </div>
                            <?php endif; ?>
                            
                            <div class="product-controls">
                                <button class="btn-quantity btn-minus" data-action="decrease">−</button>
                                <input 
                                    type="number" 
                                    class="input-quantity" 
                                    value="0" 
                                    min="0" 
                                    max="24" 
                                    step="1"
                                    data-product-id="<?php echo htmlspecialchars($product['id']); ?>"
                                    data-unit="kg">
                                <button class="btn-quantity btn-plus" data-action="increase">+</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Produkty na sztuki -->
                <div class="product-category">
                    <h3 class="category-title">🌿 Produkty na sztuki</h3>
                    <p class="category-note">Por + Czosnek + Natka → maksymalnie 5 sztuk łącznie w cenie boxa</p>
                    
                    <div class="products-grid" id="piecesProducts">
                        <?php foreach ($config['products']['pieces'] as $product): ?>
                        <div class="product-card" data-product-id="<?php echo htmlspecialchars($product['id']); ?>">
                            <div class="product-header">
                                <h4 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h4>
                                <span class="product-price"><?php echo $product['price']; ?> zł/<?php echo $product['unit']; ?></span>
                            </div>
                            
                            <?php if (isset($product['free_limit'])): ?>
                            <div class="product-info">
                                <small><?php echo $product['free_limit']; ?> w cenie, każdy kolejny +<?php echo $product['extra_price']; ?> zł</small>
                            </div>
                            <?php endif; ?>
                            
                            <div class="product-controls">
                                <button class="btn-quantity btn-minus" data-action="decrease">−</button>
                                <input 
                                    type="number" 
                                    class="input-quantity" 
                                    value="0" 
                                    min="0" 
                                    max="20" 
                                    step="1"
                                    data-product-id="<?php echo htmlspecialchars($product['id']); ?>"
                                    data-unit="<?php echo $product['unit']; ?>">
                                <button class="btn-quantity btn-plus" data-action="increase">+</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Przyciski akcji -->
                <div class="action-buttons">
                    <button class="btn btn-secondary" id="resetBtn">Wyczyść konfigurator</button>
                    <button class="btn btn-primary" id="orderBtn" disabled>Zamów swój box</button>
                </div>

            </div>
        </div>
    </main>

    <!-- Sekcje Landingowe -->
    
    <!-- Dlaczego warto -->
    <section class="section section-why">
        <div class="container">
            <h2 class="section-title">Dlaczego warto?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🚜</div>
                    <h3>Prosto z gospodarstwa</h3>
                    <p>Nasze warzywa pochodzą bezpośrednio z naszych pól w Sędzinku</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🌱</div>
                    <h3>Naturalne uprawy</h3>
                    <p>Bez chemii i sztucznych nawozów - tylko natura</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🤝</div>
                    <h3>Bez pośredników</h3>
                    <p>Kupujesz bezpośrednio od producenta</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">✨</div>
                    <h3>Świeże zbiory</h3>
                    <p>Zbieramy na bieżąco, warzywa trafiają do Ciebie świeże</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🇵🇱</div>
                    <h3>Polskie warzywa</h3>
                    <p>100% polskie produkty z lokalnych upraw</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Jak to działa -->
    <section class="section section-how">
        <div class="container">
            <h2 class="section-title">Jak to działa?</h2>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3>Wybierasz produkty</h3>
                    <p>Używasz konfiguratora i dobierasz produkty według swojego gustu</p>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3>System dopasowuje box</h3>
                    <p>Automatycznie dobieramy najlepszy wariant i liczymy cenę</p>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3>My pakujemy i wysyłamy</h3>
                    <p>Zbieramy, pakujemy i dostarczamy świeże warzywa pod Twoje drzwi</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Opinie -->
    <section class="section section-testimonials">
        <div class="container">
            <h2 class="section-title">Opinie klientów</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        <p>"Najlepsze warzywa jakie jadłam! Smakują jak u babci na wsi. Marchew jest słodka a ziemniaki mają prawdziwy smak."</p>
                    </div>
                    <div class="testimonial-author">
                        <strong>Anna K.</strong>
                        <span>Warszawa</span>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        <p>"Świeże i pachnące ziemią. W końcu wiem skąd mam jedzenie dla mojej rodziny. Polecam każdemu!"</p>
                    </div>
                    <div class="testimonial-author">
                        <strong>Michał P.</strong>
                        <span>Kraków</span>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        <p>"W końcu wiem skąd mam jedzenie. Kupowanie bezpośrednio od rolnika to przyszłość. Dziękuję za pyszne warzywa!"</p>
                    </div>
                    <div class="testimonial-author">
                        <strong>Katarzyna L.</strong>
                        <span>Wrocław</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="section section-cta">
        <div class="container">
            <div class="cta-content">
                <h2>Zamów swój box już dziś!</h2>
                <p>Dołącz do grona zadowolonych klientów i delektuj się świeżymi warzywami prosto z gospodarstwa</p>
                <a href="#infoPanel" class="btn btn-cta">Skonfiguruj swój box</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <p>&copy; 2026 Warzywa Sędzinko. Wszystkie prawa zastrzeżone.</p>
                <p>Prosto z gospodarstwa do Twojego stołu 🥕</p>
                <p class="footer-links">
                    <a href="regulamin.php">Regulamin</a>
                    <span class="footer-sep">•</span>
                    <a href="polityka-prywatnosci.php">Polityka prywatności</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- Modal zamówienia -->
    <div class="modal" id="orderModal">
        <div class="modal-content">
            <span class="modal-close" id="modalClose">&times;</span>
            <h2>Potwierdź zamówienie</h2>
            
            <div id="orderSummary"></div>
            
            <form id="orderForm">
                <div class="form-group">
                    <label for="customerName">Imię i nazwisko (opcjonalne)</label>
                    <input type="text" id="customerName" class="form-input" placeholder="Jan Kowalski">
                </div>
                
                <div class="form-group">
                    <label for="customerEmail">Email (opcjonalne)</label>
                    <input type="email" id="customerEmail" class="form-input" placeholder="jan@example.com">
                </div>
                
                <div class="form-group">
                    <label for="customerPhone">Telefon (opcjonalne)</label>
                    <input type="tel" id="customerPhone" class="form-input" placeholder="+48 123 456 789">
                </div>

                <div class="form-group form-consents">
                    <label class="consent">
                        <input type="checkbox" id="acceptTerms" required>
                        <span>Akceptuję <a href="regulamin.php" target="_blank" rel="noopener noreferrer">Regulamin</a> oraz <a href="polityka-prywatnosci.php" target="_blank" rel="noopener noreferrer">Politykę prywatności</a>.</span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Potwierdź zamówienie</button>
            </form>
        </div>
    </div>

    <!-- Sukces modal -->
    <div class="modal" id="successModal">
        <div class="modal-content modal-success">
            <span class="modal-close" id="successModalClose">&times;</span>
            <div class="success-icon">✓</div>
            <h2>Zamówienie złożone!</h2>
            <p>Dziękujemy za zamówienie. Wkrótce się z Tobą skontaktujemy.</p>
            <p><strong>Numer zamówienia: <span id="orderNumber"></span></strong></p>
            <button class="btn btn-primary" id="successModalBtn">OK</button>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Aplikacja -->
    <script src="assets/js/app.js"></script>
</body>
</html>

