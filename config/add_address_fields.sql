-- Dodanie pól adresowych do tabeli orders
-- Wykonaj to polecenie w bazie danych

USE warzywasedzinko;

ALTER TABLE orders
ADD COLUMN customer_street VARCHAR(255) NULL AFTER customer_phone,
ADD COLUMN customer_building VARCHAR(50) NULL AFTER customer_street,
ADD COLUMN customer_apartment VARCHAR(50) NULL AFTER customer_building,
ADD COLUMN customer_postal_code VARCHAR(20) NULL AFTER customer_apartment,
ADD COLUMN customer_city VARCHAR(100) NULL AFTER customer_postal_code;
