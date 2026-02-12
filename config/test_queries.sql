-- Zapytania testowe dla bazy danych Warzywa Sędzinko
-- Użyj tych zapytań do sprawdzenia czy baza działa poprawnie

-- Sprawdź wszystkie zamówienia
SELECT * FROM orders ORDER BY created_at DESC;

-- Sprawdź zamówienia z produktami (JOIN)
SELECT 
    o.id,
    o.created_at,
    o.box_type,
    o.total_weight,
    o.final_price,
    GROUP_CONCAT(
        CONCAT(oi.product_name, ' (', oi.quantity, ' ', oi.unit, ')')
        SEPARATOR ', '
    ) as products
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id
ORDER BY o.created_at DESC;

-- Sprawdź szczegóły konkretnego zamówienia (zmień 1 na ID zamówienia)
SELECT 
    o.*,
    oi.product_name,
    oi.quantity,
    oi.unit,
    oi.unit_price,
    oi.total_price
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
WHERE o.id = 1;

-- Statystyki zamówień
SELECT 
    box_type,
    COUNT(*) as count,
    AVG(total_weight) as avg_weight,
    AVG(final_price) as avg_price,
    SUM(final_price) as total_revenue
FROM orders
GROUP BY box_type;

-- Najpopularniejsze produkty
SELECT 
    product_name,
    COUNT(*) as order_count,
    SUM(quantity) as total_quantity,
    unit
FROM order_items
GROUP BY product_name, unit
ORDER BY order_count DESC;

-- Zamówienia z ostatniego tygodnia
SELECT * FROM orders 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY created_at DESC;

-- Zamówienia z najwyższą ceną
SELECT * FROM orders 
ORDER BY final_price DESC 
LIMIT 10;

-- Średnia wartość zamówienia
SELECT 
    AVG(final_price) as avg_order_value,
    MIN(final_price) as min_order_value,
    MAX(final_price) as max_order_value
FROM orders;

-- Usuń testowe zamówienia (UWAGA: usuwa wszystkie dane!)
-- DELETE FROM order_items;
-- DELETE FROM orders;
-- ALTER TABLE orders AUTO_INCREMENT = 1;
