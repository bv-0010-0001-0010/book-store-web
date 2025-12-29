-- TiDB compatible schema for shop_db

CREATE DATABASE IF NOT EXISTS `shop_db`;
USE `shop_db`;

SET time_zone = "+00:00";

DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `message`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `cart` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `price` INT NOT NULL,
  `quantity` INT NOT NULL,
  `image` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `message` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `number` VARCHAR(12) NOT NULL,
  `message` VARCHAR(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `orders` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `number` VARCHAR(12) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `method` VARCHAR(50) NOT NULL,
  `address` VARCHAR(500) NOT NULL,
  `total_products` VARCHAR(1000) NOT NULL,
  `total_price` INT NOT NULL,
  `placed_on` VARCHAR(50) NOT NULL,
  `payment_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `products` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `price` INT NOT NULL,
  `image` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(100) NOT NULL,
  `user_type` VARCHAR(20) NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
