-- Migration: Update order status enum to align with application statuses
-- Adds 'shipped' and 'delivered' statuses (replacing legacy 'completed')

ALTER TABLE orders
    MODIFY COLUMN status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'completed')
        NOT NULL DEFAULT 'pending';
