/**
 * Warzywa Sędzinko - Aplikacja Konfiguratora
 * jQuery + AJAX
 */

$(document).ready(function() {
    
    // Zmienne globalne
    let calculationData = null;
    
    // Inicjalizacja
    init();
    
    function init() {
        // Event listeners
        $('.input-quantity').on('input change', handleQuantityChange);
        $('.btn-quantity').on('click', handleButtonClick);
        $('#resetBtn').on('click', handleReset);
        $('#orderBtn').on('click', handleOrderClick);
        $('#stickyOrderBtn').on('click', handleOrderClick); // Sticky CTA
        $('#modalClose, #successModalClose').on('click', closeModals);
        $('#orderForm').on('submit', handleOrderSubmit);
        $('#successModalBtn').on('click', closeModals);
        
        // Zamknij modal przy kliknięciu poza nim
        $(window).on('click', function(e) {
            if ($(e.target).hasClass('modal')) {
                closeModals();
            }
        });
        
        // Początkowe obliczenie
        calculateOrder();
    }
    
    /**
     * Obsługa zmiany ilości przez input
     */
    function handleQuantityChange(e) {
        const $input = $(e.target);
        const unit = $input.data('unit');
        let value = parseFloat($input.val()) || 0;
        
        // Walidacja wartości
        if (value < 0) {
            value = 0;
        }
        
        const max = parseFloat($input.attr('max'));
        if (value > max) {
            value = max;
        }
        
        // Zaokrąglij do pełnych liczb
        value = Math.round(value);
        
        $input.val(value);
        
        // Przelicz
        calculateOrder();
    }
    
    /**
     * Obsługa przycisków +/-
     */
    function handleButtonClick(e) {
        e.preventDefault();
        const $button = $(e.currentTarget);
        const action = $button.data('action');
        const $input = $button.siblings('.input-quantity');
        const unit = $input.data('unit');
        let currentValue = parseFloat($input.val()) || 0;
        const step = 1; // Zawsze co 1 (kg lub sztuka)
        
        if (action === 'increase') {
            currentValue += step;
        } else if (action === 'decrease') {
            currentValue -= step;
        }
        
        // Walidacja
        const min = parseFloat($input.attr('min'));
        const max = parseFloat($input.attr('max'));
        
        if (currentValue < min) {
            currentValue = min;
        }
        if (currentValue > max) {
            currentValue = max;
        }
        
        $input.val(currentValue);
        calculateOrder();
    }
    
    /**
     * Reset konfiguratora
     */
    function handleReset() {
        $('.input-quantity').val(0);
        calculationData = null;
        calculateOrder();
    }
    
    /**
     * Główna funkcja obliczająca zamówienie
     */
    function calculateOrder() {
        // Zbierz produkty
        const products = [];
        
        $('.input-quantity').each(function() {
            const $input = $(this);
            const quantity = parseFloat($input.val()) || 0;
            
            if (quantity > 0) {
                products.push({
                    id: $input.data('product-id'),
                    quantity: quantity
                });
            }
        });
        
        // Jeśli brak produktów
        if (products.length === 0) {
            updateUI({
                total_weight: 0,
                box_type: 'none',
                box_name: '-',
                can_order: false,
                message: 'Dodaj produkty, aby rozpocząć konfigurację boxa',
                missing_to_12: 12,
                missing_to_20: 20,
                base_price: 0,
                extra_price: 0,
                final_price: 0,
                products: [],
                extras: []
            });
            return;
        }
        
        // AJAX do calculate.php
        $.ajax({
            url: 'api/calculate.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ products: products }),
            success: function(response) {
                if (response.success) {
                    calculationData = response.data;
                    updateUI(response.data);
                } else {
                    showError(response.error || 'Błąd obliczania');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Błąd połączenia z serwerem';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMsg = response.error || errorMsg;
                } catch(e) {}
                showError(errorMsg);
            }
        });
    }
    
    /**
     * Aktualizacja interfejsu na podstawie danych
     */
    function updateUI(data) {
        // Aktualizuj panel informacyjny
        $('#currentWeight').text(data.total_weight.toFixed(2) + ' kg');
        $('#boxType').text(data.box_name);
        $('#basePrice').text(data.base_price.toFixed(2) + ' zł');
        $('#extraPrice').text(data.extra_price.toFixed(2) + ' zł');
        $('#finalPrice').text(data.final_price.toFixed(2) + ' zł');
        
        // Aktualizuj STICKY BAR
        $('#stickyWeight').text(data.total_weight.toFixed(2) + ' kg');
        $('#stickyBox').text(data.box_name);
        $('#stickyPrice').text(data.final_price.toFixed(2) + ' zł');
        
        // Aktualizuj pasek postępu wagi
        const maxWeight = 24;
        const percentage = Math.min((data.total_weight / maxWeight) * 100, 100);
        $('#weightProgressBar').css('width', percentage + '%');
        
        // Aktualizuj komunikat
        const $message = $('#infoMessage');
        $message.removeClass('error success warning');
        
        if (data.can_order) {
            $message.addClass('success');
        } else if (data.total_weight > 24) {
            $message.addClass('error');
        } else if (data.total_weight < 12) {
            $message.addClass('warning');
        }
        
        $message.html('<p>' + data.message + '</p>');
        
        // Dodaj informacje o brakach
        if (data.missing_to_12 > 0 && data.total_weight < 12) {
            $message.append('<p><strong>Do BOX 12 KG brakuje: ' + data.missing_to_12.toFixed(2) + ' kg</strong></p>');
        }
        
        if (data.missing_to_20 > 0 && data.total_weight >= 12 && data.total_weight < 20) {
            $message.append('<p><strong>Do BOX 20 KG brakuje: ' + data.missing_to_20.toFixed(2) + ' kg</strong></p>');
        }
        
        // Dodaj informacje o dopłatach
        if (data.extras && data.extras.length > 0) {
            let extrasHtml = '<p style="margin-top: 1rem;"><strong>Dopłaty:</strong></p><ul style="margin-left: 1.5rem; margin-top: 0.5rem;">';
            data.extras.forEach(function(extra) {
                extrasHtml += '<li>' + extra.name + ': ' + extra.quantity + ' × ' + extra.unit_price + ' zł = ' + extra.price.toFixed(2) + ' zł</li>';
            });
            extrasHtml += '</ul>';
            $message.append(extrasHtml);
        }
        
        // Włącz/wyłącz przyciski zamówienia
        if (data.can_order) {
            $('#orderBtn').prop('disabled', false);
            $('#stickyOrderBtn').prop('disabled', false);
        } else {
            $('#orderBtn').prop('disabled', true);
            $('#stickyOrderBtn').prop('disabled', true);
        }
    }
    
    /**
     * Obsługa kliknięcia w przycisk zamówienia
     */
    function handleOrderClick() {
        if (!calculationData || !calculationData.can_order) {
            return;
        }
        
        // Przygotuj podsumowanie
        let summaryHtml = '<h3>Podsumowanie zamówienia</h3>';
        
        // Produkty
        summaryHtml += '<div style="margin-bottom: 1.5rem;">';
        summaryHtml += '<h4 style="margin-bottom: 0.75rem;">Produkty:</h4>';
        
        calculationData.products.forEach(function(product) {
            summaryHtml += '<div class="summary-item">';
            summaryHtml += '<span>' + product.name + ' (' + product.quantity + ' ' + product.unit + ')</span>';
            summaryHtml += '<span>' + (product.quantity * product.unit_price).toFixed(2) + ' zł</span>';
            summaryHtml += '</div>';
        });
        summaryHtml += '</div>';
        
        // Podsumowanie
        summaryHtml += '<div class="summary-item"><span><strong>Waga całkowita:</strong></span><span>' + calculationData.total_weight.toFixed(2) + ' kg</span></div>';
        summaryHtml += '<div class="summary-item"><span><strong>Wariant boxa:</strong></span><span>' + calculationData.box_name + '</span></div>';
        summaryHtml += '<div class="summary-item"><span><strong>Cena bazowa:</strong></span><span>' + calculationData.base_price.toFixed(2) + ' zł</span></div>';
        summaryHtml += '<div class="summary-item"><span><strong>Dopłaty:</strong></span><span>' + calculationData.extra_price.toFixed(2) + ' zł</span></div>';
        summaryHtml += '<div class="summary-item summary-total"><span><strong>RAZEM:</strong></span><span>' + calculationData.final_price.toFixed(2) + ' zł</span></div>';
        
        $('#orderSummary').html(summaryHtml);
        
        // Pokaż modal
        $('#orderModal').addClass('show');
    }
    
    /**
     * Obsługa wysłania formularza zamówienia
     */
    function handleOrderSubmit(e) {
        e.preventDefault();
        
        if (!calculationData) {
            return;
        }

        // Wymagaj akceptacji regulaminu / polityki prywatności
        const accepted = $('#acceptTerms').is(':checked');
        if (!accepted) {
            showError('Aby złożyć zamówienie, zaakceptuj Regulamin i Politykę prywatności.');
            return;
        }
        
        // Pobierz dane z formularza
        const customerName = $('#customerName').val().trim();
        const customerEmail = $('#customerEmail').val().trim();
        const customerPhone = $('#customerPhone').val().trim();
        
        // Przygotuj dane do wysłania
        const orderData = {
            total_weight: calculationData.total_weight,
            box_type: calculationData.box_type,
            base_price: calculationData.base_price,
            extra_price: calculationData.extra_price,
            final_price: calculationData.final_price,
            can_order: calculationData.can_order,
            products: calculationData.products,
            customer_name: customerName || null,
            customer_email: customerEmail || null,
            customer_phone: customerPhone || null
        };
        
        // Wyślij AJAX
        $.ajax({
            url: 'api/order.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(orderData),
            success: function(response) {
                if (response.success) {
                    // Zamknij modal zamówienia
                    $('#orderModal').removeClass('show');
                    
                    // Pokaż modal sukcesu
                    $('#orderNumber').text(response.order_id);
                    $('#successModal').addClass('show');
                    
                    // Zresetuj konfigurator po 2 sekundach
                    setTimeout(function() {
                        handleReset();
                    }, 2000);
                } else {
                    showError(response.error || 'Błąd podczas składania zamówienia');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Błąd połączenia z serwerem';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMsg = response.error || errorMsg;
                } catch(e) {}
                showError(errorMsg);
            }
        });
    }
    
    /**
     * Zamknij wszystkie modale
     */
    function closeModals() {
        $('.modal').removeClass('show');
        // Wyczyść formularz
        $('#orderForm')[0].reset();
    }
    
    /**
     * Pokaż komunikat błędu
     */
    function showError(message) {
        alert('Błąd: ' + message);
    }
    
});
