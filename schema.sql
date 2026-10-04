-- Database Schema for Booking System
CREATE DATABASE IF NOT EXISTS `booking_system_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `booking_system_db`;

-- Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) NULL,
    `role` ENUM('admin', 'customer') DEFAULT 'customer',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Services Table
CREATE TABLE IF NOT EXISTS `services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `duration_minutes` INT NOT NULL DEFAULT 30,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Bookings Table
CREATE TABLE IF NOT EXISTS `bookings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `service_id` INT NOT NULL,
    `booking_date` DATE NOT NULL,
    `booking_time` TIME NOT NULL,
    `status` ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Default Admin User (Password: admin123)
INSERT INTO `users` (`name`, `email`, `password`, `phone`, `role`) VALUES
('المدير العام', 'admin@booking.com', '$2y$10$wzVdKjZzC8mD7y7j7/tLueq1yKk6wDq5zL0Z6L9Nl0gY2r7ZgV9Qe', '01000000000', 'admin')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Sample Services
INSERT INTO `services` (`title`, `description`, `duration_minutes`, `price`) VALUES
('استشارة طبية / كشف عام', 'كشف ومتابعة شاملة مع الطبيب المختص', 30, 200.00),
('جلسة عناية وتجميل', 'جلسة متكاملة للعناية بالبشرة والمظهر', 60, 350.00),
('دورة تدريبية فردية (1 on 1)', 'جلسة تدريب وتطوير شخصي مخصصة', 45, 500.00)
ON DUPLICATE KEY UPDATE `id`=`id`;
