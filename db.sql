-- Dizi/Film Lokasyonları Takip Sistemi Veritabanı
-- Veritabanı oluştur
CREATE DATABASE IF NOT EXISTS movie_locations_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE movie_locations_db;

-- Users tablosu
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Locations tablosu
CREATE TABLE locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    location_name VARCHAR(200) NOT NULL,
    address TEXT,
    city VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    description TEXT,
    scene_description TEXT,
    genre ENUM('Dram', 'Komedi', 'Aksiyon', 'Romantik', 'Gerilim', 'Korku', 'Bilim Kurgu', 'Fantastik', 'Tarih', 'Belgesel', 'Diğer') DEFAULT 'Diğer',
    year YEAR,
    visited BOOLEAN DEFAULT FALSE,
    rating DECIMAL(2,1) CHECK (rating >= 1 AND rating <= 5),
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Index'ler performans için
CREATE INDEX idx_locations_user_id ON locations(user_id);
CREATE INDEX idx_locations_city ON locations(city);
CREATE INDEX idx_locations_country ON locations(country);
CREATE INDEX idx_locations_genre ON locations(genre);
CREATE INDEX idx_locations_visited ON locations(visited);

-- Örnek kullanıcı verisi (şifre: 123456)
INSERT INTO users (username, email, password_hash, full_name) VALUES 
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User');

