-- Baza danych dla konfiguratora boxa warzywnego
-- warzywasedzinko.pl

CREATE DATABASE IF NOT EXISTS warzywasedzinko CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE warzywasedzinko;

-- Tabela zamówień
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    total_weight DECIMAL(10, 2) NOT NULL,
    box_type VARCHAR(50) NOT NULL,
    base_price DECIMAL(10, 2) NOT NULL,
    extra_price DECIMAL(10, 2) DEFAULT 0,
    final_price DECIMAL(10, 2) NOT NULL,
    customer_name VARCHAR(255) NULL,
    customer_email VARCHAR(255) NULL,
    customer_phone VARCHAR(50) NULL,
    customer_street VARCHAR(255) NULL,
    customer_building VARCHAR(50) NULL,
    customer_apartment VARCHAR(50) NULL,
    customer_postal_code VARCHAR(20) NULL,
    customer_city VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_status VARCHAR(50) DEFAULT 'pending',
    tpay_transaction_id VARCHAR(255) NULL,
    tpay_title VARCHAR(255) NULL,
    INDEX idx_created_at (created_at),
    INDEX idx_box_type (box_type),
    INDEX idx_payment_status (payment_status),
    INDEX idx_tpay_transaction_id (tpay_transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela pozycji zamówienia
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity DECIMAL(10, 2) NOT NULL,
    unit VARCHAR(10) NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
