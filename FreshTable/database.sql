-- =========================================================
-- FreshTable — database schema
-- Import this in phpMyAdmin (XAMPP) or run:
--   mysql -u root -p < database.sql
-- Creates the database, tables, one admin login, and a
-- starter menu so the app is usable immediately after import.
-- =========================================================

CREATE DATABASE IF NOT EXISTS freshtable CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE freshtable;

-- ---------------------------------------------------------
-- Admins (restaurant staff who can log into /admin)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default login: username "admin", password "admin123"
-- Change this password after your first login in a real deployment.
INSERT INTO admins (username, password_hash) VALUES
('admin', '$2y$10$kxsqV8UYKLyQz9ieS0qYKenHOxt.c7N6.b2tV/yFg/pVEk6ptpfXu')
ON DUPLICATE KEY UPDATE username = username;

-- ---------------------------------------------------------
-- Menu items (also doubles as the inventory table — stock
-- lives directly on the item so there's one source of truth)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS menu_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(255) DEFAULT '',
  category VARCHAR(50) NOT NULL,
  price DECIMAL(8,2) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO menu_items (name, description, category, price, stock, is_available) VALUES
('Paneer Tikka', 'Char-grilled cottage cheese, mint chutney', 'Starters', 180.00, 14, 1),
('Veg Spring Rolls', 'Crisp rolls, sweet chilli dip', 'Starters', 140.00, 20, 1),
('Corn & Pepper Soup', 'Light broth, cracked pepper', 'Starters', 110.00, 0, 1),
('Butter Chicken', 'Tomato-cashew gravy, served with rice', 'Main Course', 260.00, 10, 1),
('Dal Makhani', 'Slow-cooked black lentils, finished with cream', 'Main Course', 210.00, 15, 1),
('Veg Biryani', 'Basmati rice, garden vegetables, saffron', 'Main Course', 230.00, 3, 1),
('Margherita Pizza', 'Wood-fired base, mozzarella, basil', 'Main Course', 240.00, 8, 1),
('Masala Chai', 'Spiced milk tea', 'Beverages', 50.00, 40, 1),
('Fresh Lime Soda', 'Sweet or salted, chilled', 'Beverages', 60.00, 25, 1),
('Cold Coffee', 'Blended with vanilla ice cream', 'Beverages', 90.00, 18, 1),
('Gulab Jamun', 'Two pieces, warm sugar syrup', 'Desserts', 80.00, 2, 1),
('Chocolate Brownie', 'Served warm with vanilla scoop', 'Desserts', 120.00, 12, 1)
ON DUPLICATE KEY UPDATE name = name;

-- ---------------------------------------------------------
-- Orders placed by customers
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_number VARCHAR(20) NOT NULL UNIQUE,
  customer_name VARCHAR(100) NOT NULL,
  table_number VARCHAR(10) DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  status ENUM('pending','preparing','ready','completed','cancelled') NOT NULL DEFAULT 'pending',
  total DECIMAL(10,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------
-- Line items for each order. Item name/price are copied in
-- at order time so historical orders stay accurate even if
-- the menu item is later edited, deleted, or repriced.
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  menu_item_id INT DEFAULT NULL,
  item_name VARCHAR(100) NOT NULL,
  unit_price DECIMAL(8,2) NOT NULL,
  quantity INT NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE SET NULL
);
