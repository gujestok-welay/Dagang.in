-- Update schema untuk menambahkan user_id ke customers table
-- Jalankan script ini untuk update database yang sudah ada

-- Step 1: Tambahkan kolom user_id (nullable dulu untuk data yang sudah ada)
ALTER TABLE customers 
ADD COLUMN user_id INT NULL AFTER id;

-- Step 2: Update existing customers dengan user_id dari orders terkait
UPDATE customers c
SET c.user_id = (
    SELECT o.user_id 
    FROM orders o 
    WHERE o.customer_id = c.id 
    LIMIT 1
);

-- Step 3: Set default user_id untuk customers yang tidak punya order (jika ada)
UPDATE customers 
SET user_id = 1 
WHERE user_id IS NULL;

-- Step 4: Ubah kolom jadi NOT NULL setelah semua data terisi
ALTER TABLE customers 
MODIFY COLUMN user_id INT NOT NULL;

-- Step 5: Tambahkan foreign key constraint
ALTER TABLE customers
ADD CONSTRAINT fk_customers_user
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Step 6: Tambahkan index untuk performa
CREATE INDEX idx_customers_user_id ON customers(user_id);
