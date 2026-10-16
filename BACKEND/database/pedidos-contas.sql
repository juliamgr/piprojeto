-- Execute UMA VEZ no banco configurado em BACKEND/app/config.php.
-- Pedidos antigos permanecem sem dono; não há associação por e-mail.
ALTER TABLE orders
  ADD COLUMN user_id INT UNSIGNED NULL AFTER id,
  ADD INDEX idx_orders_user (user_id),
  ADD CONSTRAINT fk_orders_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE SET NULL;
