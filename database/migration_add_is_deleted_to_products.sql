-- Migration: Add is_deleted column to products table for soft delete functionality
-- Date: 2025-11-28

ALTER TABLE products
ADD COLUMN `is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 = not deleted, 1 = deleted' AFTER `image`,
ADD INDEX `idx_is_deleted` (`is_deleted`);