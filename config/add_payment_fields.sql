-- Dodanie pól płatności do tabeli orders

USE warzywasedzinko;

ALTER TABLE orders
ADD COLUMN payment_status VARCHAR(50) DEFAULT 'pending' AFTER created_at,
ADD COLUMN tpay_transaction_id VARCHAR(255) NULL AFTER payment_status,
ADD COLUMN tpay_title VARCHAR(255) NULL AFTER tpay_transaction_id,
ADD INDEX idx_payment_status (payment_status),
ADD INDEX idx_tpay_transaction_id (tpay_transaction_id);
