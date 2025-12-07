-- Database: stratos_db

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- 1. Drop old tables if they exist (Clean Slate)
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS redemptions;
DROP TABLE IF EXISTS rewards;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

-- 2. Create Users Table
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `points` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Create Products Table
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT 'https://placehold.co/300x300?text=Product',
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for products
INSERT INTO `products` (`name`, `category`, `price`, `image`, `description`) VALUES
('Stratos 2T Sprint', '2T', 15.00, 'https://placehold.co/300x300?text=2T+Sprint', 'High performance 2-stroke engine oil for daily commute.'),
('Stratos 4T Super', '4T', 28.50, 'https://placehold.co/300x300?text=4T+Super', 'Premium semi-synthetic 4-stroke oil for smooth riding.'),
('Stratos 4T Ultimate', '4T', 45.00, 'https://placehold.co/300x300?text=4T+Ultimate', 'Fully synthetic racing grade oil.'),
('Stratos T-Shirt Black', 'Merchandise', 35.00, 'https://placehold.co/300x300?text=T-Shirt', 'Limited edition Stratos cotton t-shirt.'),
('Stratos Cap', 'Merchandise', 20.00, 'https://placehold.co/300x300?text=Cap', 'Adjustable cap with embroidered logo.');

-- 4. Create Orders Table
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `type` varchar(20) DEFAULT 'Shop',
  `delivery_method` varchar(50) DEFAULT 'Delivery',
  `delivery_address` text,
  `phone` varchar(20),
  `payment_method` varchar(50),
  `notes` text,
  `points_earned` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Create Rewards Table
CREATE TABLE `rewards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `points_cost` int(11) NOT NULL,
  `image` varchar(255) DEFAULT 'https://placehold.co/300x300?text=Reward',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `rewards` (`name`, `points_cost`, `image`) VALUES
('RM20 Fuel Voucher', 450, 'https://placehold.co/300x300?text=RM20+Voucher'),
('RM45 Fuel Voucher', 1200, 'https://placehold.co/300x300?text=RM45+Voucher');

-- 6. Create Redemptions Table
CREATE TABLE `redemptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `reward_name` varchar(100) NOT NULL,
  `points_spent` int(11) NOT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Create Messages Table
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;