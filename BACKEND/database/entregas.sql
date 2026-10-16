-- Selecione o MESMO banco do Agrolink no phpMyAdmin e importe este arquivo.
-- Pode ser executado novamente; não apaga pedidos nem usuários.
CREATE TABLE IF NOT EXISTS order_delivery (
 order_id INT UNSIGNED NOT NULL PRIMARY KEY,
 estimated_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_delivery_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
