-- Recuperação sem DELETE, DROP ou TRUNCATE. Selecione seu banco existente.
-- Importar uma única vez em banco novo, pelo phpMyAdmin.
CREATE TABLE IF NOT EXISTS admins (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(90) NOT NULL,
 email VARCHAR(160) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS login_attempts (
 bucket CHAR(64) PRIMARY KEY,
 failures INT UNSIGNED NOT NULL DEFAULT 0,
 locked_until DATETIME NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS products (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 category VARCHAR(60) NOT NULL,
 producer VARCHAR(120) NOT NULL,
 location VARCHAR(100) NOT NULL,
 unit VARCHAR(40) NOT NULL,
 price_cents INT UNSIGNED NOT NULL,
 stock INT UNSIGNED NOT NULL DEFAULT 0,
 min_stock INT UNSIGNED NOT NULL DEFAULT 5,
 image VARCHAR(240) NOT NULL DEFAULT 'img/produto-placeholder.svg',
 badge VARCHAR(40) NOT NULL DEFAULT '',
 batch_code VARCHAR(60) NOT NULL DEFAULT '',
 expires_on DATE NULL,
 active TINYINT(1) NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_catalog(active, expires_on)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS orders (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(24) NOT NULL UNIQUE,
 request_key CHAR(64) NOT NULL UNIQUE,
 payload_hash CHAR(64) NOT NULL,
 customer_name VARCHAR(90) NOT NULL,
 customer_email VARCHAR(160) NOT NULL,
 customer_phone VARCHAR(30) NOT NULL,
 region VARCHAR(60) NOT NULL,
 address VARCHAR(220) NOT NULL,
 payment_method ENUM('pix','delivery') NOT NULL,
 status ENUM('recebido','confirmado','separacao','enviado','concluido','cancelado') NOT NULL DEFAULT 'recebido',
 subtotal_cents INT UNSIGNED NOT NULL,
 shipping_cents INT UNSIGNED NOT NULL,
 total_cents INT UNSIGNED NOT NULL,
 notes TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_orders(status, created_at)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS order_items (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_id INT UNSIGNED NOT NULL,
 product_id INT UNSIGNED NOT NULL,
 product_name VARCHAR(120) NOT NULL,
 unit VARCHAR(40) NOT NULL,
 quantity INT UNSIGNED NOT NULL,
 unit_price_cents INT UNSIGNED NOT NULL,
 FOREIGN KEY(order_id) REFERENCES orders(id),
 FOREIGN KEY(product_id) REFERENCES products(id),
 UNIQUE KEY unique_item(order_id,product_id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS stock_movements (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 product_id INT UNSIGNED NOT NULL,
 order_id INT UNSIGNED NULL,
 admin_id INT UNSIGNED NULL,
 type ENUM('entrada','ajuste_entrada','ajuste_saida','reserva','cancelamento','doacao','perda') NOT NULL,
 quantity INT NOT NULL,
 balance_after INT UNSIGNED NOT NULL,
 reason VARCHAR(220) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(product_id) REFERENCES products(id),
 FOREIGN KEY(order_id) REFERENCES orders(id),
 FOREIGN KEY(admin_id) REFERENCES admins(id),
 INDEX idx_movement(product_id, created_at)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS order_history (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_id INT UNSIGNED NOT NULL,
 admin_id INT UNSIGNED NULL,
 status VARCHAR(20) NOT NULL,
 note VARCHAR(220) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(order_id) REFERENCES orders(id),
 FOREIGN KEY(admin_id) REFERENCES admins(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS newsletter (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 email VARCHAR(160) NOT NULL UNIQUE,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(90) NOT NULL,
 email VARCHAR(160) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 phone VARCHAR(30) NOT NULL,
 cpf VARCHAR(11) NULL,
 birth_date DATE NULL,
 member_type VARCHAR(60) NOT NULL,
 city VARCHAR(100) NOT NULL,
 address VARCHAR(220) NOT NULL,
 neighborhood VARCHAR(100) NOT NULL DEFAULT '',
 zip_code VARCHAR(8) NOT NULL DEFAULT '',
 production TEXT NOT NULL,
 notes TEXT NOT NULL,
 privacy_accepted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS user_login_attempts (
 bucket CHAR(64) PRIMARY KEY,
 failures INT UNSIGNED NOT NULL DEFAULT 0,
 locked_until DATETIME NULL
) ENGINE=InnoDB;

-- Apenas adiciona user_id quando faltar. Não associa pedidos antigos por e-mail.
SET @agro_sql = (SELECT IF(COUNT(*)=0,
 'ALTER TABLE orders ADD COLUMN user_id INT UNSIGNED NULL, ADD INDEX idx_orders_user(user_id)',
 'SELECT 1') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='user_id');
PREPARE agro_stmt FROM @agro_sql;
EXECUTE agro_stmt;
DEALLOCATE PREPARE agro_stmt;
-- Aceita as categorias que o formulário já oferece, preservando os valores atuais.
SET @agro_sql = (SELECT IF(DATA_TYPE='enum', 'ALTER TABLE products MODIFY category VARCHAR(60) NOT NULL', 'SELECT 1')
 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='products' AND COLUMN_NAME='category');
PREPARE agro_stmt FROM @agro_sql;
EXECUTE agro_stmt;
DEALLOCATE PREPARE agro_stmt;

-- Selecione o MESMO banco do Agrolink no phpMyAdmin e importe este arquivo.
-- Pode ser executado novamente; não apaga pedidos nem usuários.
CREATE TABLE IF NOT EXISTS order_delivery (
 order_id INT UNSIGNED NOT NULL PRIMARY KEY,
 estimated_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_delivery_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
