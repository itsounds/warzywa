-- Dump / schema pod hosting (phpMyAdmin)
-- Importuj do ISTNIEJĄCEJ bazy: admin_warzywa
-- Nie zawiera CREATE DATABASE ani USE.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- Na wypadek ponownego importu:
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
SET FOREIGN_KEY_CHECKS = 1;

-- Tabela zamówień
CREATE TABLE `orders` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `total_weight` DECIMAL(10,2) NOT NULL,
  `box_type` VARCHAR(50) NOT NULL,
  `base_price` DECIMAL(10,2) NOT NULL,
  `extra_price` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `final_price` DECIMAL(10,2) NOT NULL,
  `customer_name` VARCHAR(255) NULL,
  `customer_email` VARCHAR(255) NULL,
  `customer_phone` VARCHAR(50) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_box_type` (`box_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela pozycji zamówienia
CREATE TABLE `order_items` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `order_id` INT NOT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `quantity` DECIMAL(10,2) NOT NULL,
  `unit` VARCHAR(10) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `total_price` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  CONSTRAINT `fk_order_items_order_id`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

