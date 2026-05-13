CREATE DATABASE IF NOT EXISTS cmpe272;

USE cmpe272;

CREATE TABLE IF NOT EXISTS users (
    user_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    is_admin BOOL NOT NULL DEFAULT false,

    user_name VARCHAR(64) NOT NULL,
    password_hash VARCHAR(64),

    first_name VARCHAR(64) NOT NULL,
    last_name VARCHAR(64) NOT NULL,
    email VARCHAR(64) NOT NULL,
    home_address VARCHAR(64) NOT NULL,
    home_phone VARCHAR(64) NOT NULL,
    cell_phone VARCHAR(64) NOT NULL
);

CREATE TABLE IF NOT EXISTS product_tracking (
    product_tracking_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,

    company_name VARCHAR(64) NOT NULL,
    product_name VARCHAR(64) NOT NULL,
    product_link VARCHAR(2048) NOT NULL,
    click_count INT NOT NULL DEFAULT 1,
    clicked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_product_tracking_user
    FOREIGN KEY (user_id)
    REFERENCES users (user_id)
    ON DELETE SET NULL,

    UNIQUE KEY uk_product_tracking (user_id, company_name, product_name)
);
