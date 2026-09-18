CREATE DATABASE IF NOT EXISTS travel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE travel;

CREATE TABLE IF NOT EXISTS consultas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(50) NOT NULL,
  destination VARCHAR(150) NOT NULL,
  start_date DATE NOT NULL,
  return_date DATE NOT NULL,
  budget VARCHAR(100) NOT NULL,
  travelers JSON NULL,
  addons JSON NULL,
  activities JSON NULL,
  source VARCHAR(50) NOT NULL DEFAULT 'guided-planner',
  status ENUM('nueva', 'contactada', 'cotizada', 'cerrada', 'descartada') NOT NULL DEFAULT 'nueva',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cotizaciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  consulta_id INT UNSIGNED NULL,
  client_name VARCHAR(150) NOT NULL,
  client_email VARCHAR(150) NOT NULL,
  destination VARCHAR(150) NOT NULL,
  cover_photo_url VARCHAR(500) NULL,
  nights VARCHAR(20) NOT NULL,
  regimen VARCHAR(100) NOT NULL,
  start_date DATE NOT NULL,
  return_date DATE NOT NULL,
  passengers VARCHAR(100) NOT NULL,
  includes JSON NOT NULL,
  excludes JSON NOT NULL,
  options JSON NOT NULL,
  payment_terms TEXT NOT NULL,
  valid_until DATE NOT NULL,
  sent_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (consulta_id) REFERENCES consultas(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  consulta_id INT UNSIGNED NOT NULL,
  author VARCHAR(100) NOT NULL,
  message TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (consulta_id) REFERENCES consultas(id) ON DELETE CASCADE
) ENGINE=InnoDB;
