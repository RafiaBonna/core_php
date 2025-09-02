-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 02, 2025 at 07:09 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project_pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(180) NOT NULL,
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`, `parent_id`) VALUES
(1, 'Electronics', NULL),
(3, 'Cosmetics', NULL),
(13, 'Clothing &  Apparel', NULL),
(14, 'Stationery & office supplies', NULL),
(15, 'Footware', NULL),
(16, 'Jewelery & Accessories', NULL),
(17, 'Food & Beverages', NULL),
(18, 'Furniture & Home Decor', NULL),
(19, 'Sports & Fitness', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `address`, `created_at`, `updated_at`) VALUES
(1, 'Helen', '9988776655', 'helen1@gmail.com', 'Dhaka ,Bangladesh', '2025-08-26 10:39:00', '2025-08-26 10:39:00'),
(2, 'Purno', '56789766', 'purno@gmail.com', 'Dhaka,Bangladesh', '2025-08-26 11:46:56', '2025-08-26 11:46:56'),
(3, 'Crystiana', '9988006658', 'tiana@gmail.com', 'Australia', '2025-08-28 12:26:59', '2025-08-28 12:26:59'),
(5, 'Saheb', '01928093578', 'saheb@gmail.com', 'Noakhali, Bangladesh', '2025-08-30 09:09:36', '2025-08-30 09:09:36'),
(6, 'Osman Goni', '7768906559', 'Osman@gmail.com', 'Barisal, Bangladesh', '2025-08-30 09:10:35', '2025-08-30 09:10:35'),
(7, 'Mohor', '3456787867', 'mohor@gmail.com', 'Dhaka', '2025-09-01 09:28:36', NULL),
(9, 'gulzar', '99774505', 'gulzar@gmail.com', 'Pakistan', '2025-09-01 23:11:23', NULL),
(10, 'katty', '09876543', 'K@gmail.com', 'Dhaka', '2025-09-02 09:02:54', NULL),
(11, 'Maddy', NULL, NULL, NULL, '2025-09-02 11:00:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `expired_products`
--

CREATE TABLE `expired_products` (
  `id` int(11) NOT NULL,
  `stock_id` int(11) NOT NULL,
  `quantity_expired` int(11) NOT NULL,
  `expiry_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expired_products`
--

INSERT INTO `expired_products` (`id`, `stock_id`, `quantity_expired`, `expiry_date`) VALUES
(1, 8, 0, '0000-00-00'),
(2, 9, 0, '2025-09-01'),
(3, 9, 0, '2025-09-01'),
(4, 9, 0, '2025-09-01'),
(5, 9, 0, '2025-09-01'),
(6, 9, 0, '2025-09-01'),
(7, 9, 0, '2025-09-01'),
(8, 11, 0, '2025-09-01');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `category_id`) VALUES
(1, 'Mobile', 1),
(3, 'Sunscreen', 3),
(9, 'Sunscreen', 3),
(10, 'Tablet', 1),
(11, 'lipsticks', 3),
(12, 'Shampoo', 3),
(20, 'Sunglasses', 16);

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `purchase_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`id`, `vendor_id`, `total_amount`, `purchase_date`) VALUES
(1, 2, 3000.00, '2025-08-30 20:25:08'),
(13, 4, 300000.00, '2025-08-30 20:32:11'),
(17, 4, 300000.00, '2025-08-30 20:40:07'),
(18, 4, 300000.00, '2025-08-30 20:40:36'),
(19, 6, 10000.00, '2025-08-30 21:02:08'),
(20, 4, 10000.00, '2025-08-30 21:03:22'),
(21, 5, 310000.00, '2025-09-01 05:41:39'),
(22, 5, 310000.00, '2025-09-01 05:42:08'),
(23, 5, 310000.00, '2025-09-01 05:42:26'),
(26, 5, 2000000.00, '2025-09-01 20:06:47'),
(27, 2, 300000.00, '2025-09-01 20:08:55'),
(28, 4, 10000.00, '2025-09-01 20:22:40'),
(29, 9, 60000.00, '2025-09-01 21:04:56'),
(30, 2, 87000.00, '2025-09-01 21:33:28'),
(31, 4, 80000.00, '2025-09-01 21:34:31'),
(32, 9, 80000.00, '2025-09-01 21:48:03'),
(34, 10, 135000.00, '2025-09-02 05:01:16'),
(35, 4, 4000000.00, '2025-09-02 06:59:49');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_items`
--

CREATE TABLE `purchase_items` (
  `id` int(11) NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `stock_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_items`
--

INSERT INTO `purchase_items` (`id`, `purchase_id`, `stock_id`, `quantity`, `unit_price`, `total_price`) VALUES
(2, 13, 7, 20, 15000.00, 0.00),
(3, 17, 7, 20, 15000.00, 0.00),
(4, 18, 7, 20, 15000.00, 0.00),
(5, 19, 8, 10, 1000.00, 0.00),
(6, 20, 8, 10, 1000.00, 0.00),
(7, 21, 9, 200, 200.00, 0.00),
(8, 21, 8, 300, 900.00, 0.00),
(9, 22, 9, 200, 200.00, 0.00),
(10, 22, 8, 300, 900.00, 0.00),
(11, 23, 9, 200, 200.00, 0.00),
(12, 23, 8, 300, 900.00, 0.00),
(13, 26, 8, 200, 10000.00, 0.00),
(14, 27, 10, 200, 1500.00, 0.00),
(15, 28, 9, 50, 200.00, 0.00),
(16, 29, 11, 300, 200.00, 0.00),
(17, 30, 11, 300, 290.00, 0.00),
(18, 31, 11, 400, 200.00, 0.00),
(19, 32, 9, 400, 200.00, 0.00),
(20, 34, 12, 150, 900.00, 0.00),
(21, 35, 7, 200, 20000.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_returns`
--

CREATE TABLE `purchase_returns` (
  `id` int(11) NOT NULL,
  `stock_id` int(11) NOT NULL,
  `returned_quantity` int(11) NOT NULL,
  `return_date` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_returns`
--

INSERT INTO `purchase_returns` (`id`, `stock_id`, `returned_quantity`, `return_date`, `created_at`, `purchase_id`, `reason`) VALUES
(1, 12, 50, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 23, 'Decreased it\'s demand in market.'),
(2, 20, 10, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 34, 'Broken');

-- --------------------------------------------------------

--
-- Table structure for table `return_items`
--

CREATE TABLE `return_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `stock_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `return_date` datetime NOT NULL,
  `reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `return_items`
--

INSERT INTO `return_items` (`id`, `sale_id`, `stock_id`, `quantity`, `unit_price`, `return_date`, `reason`) VALUES
(1, 23, 9, 20, 300.00, '2025-09-02 10:17:03', 'expired'),
(2, 27, 12, 2, 1100.00, '2025-09-02 11:02:05', 'Broken');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`) VALUES
(1, 'Admin'),
(2, 'Manager'),
(3, 'Cashier');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `sale_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `customer_id`, `total_amount`, `sale_date`) VALUES
(2, 2, 9000.00, '2025-08-27 12:21:59'),
(4, 2, 100000.00, '2025-08-27 12:26:11'),
(5, 2, 100000.00, '2025-08-27 12:26:30'),
(6, 2, 100000.00, '2025-08-27 12:27:08'),
(7, 2, 100000.00, '2025-08-27 12:27:24'),
(8, 3, 60000.00, '2025-08-28 12:33:39'),
(11, 5, 290000.00, '2025-08-30 09:52:27'),
(12, 5, 10000.00, '2025-08-30 09:53:34'),
(13, 2, 64500.00, '2025-08-30 10:15:24'),
(21, 9, 120000.00, '2025-09-01 19:11:23'),
(22, 9, 30000.00, '2025-09-02 05:06:14'),
(23, 2, 24000.00, '2025-09-02 05:53:49'),
(24, 1, 90000.00, '2025-09-02 06:29:53'),
(25, 3, 90000.00, '2025-09-02 06:30:34'),
(26, 1, 60000.00, '2025-09-02 06:32:08'),
(27, 11, 8800.00, '2025-09-02 07:00:43');

-- --------------------------------------------------------

--
-- Table structure for table `sales_return`
--

CREATE TABLE `sales_return` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `stock_id` int(11) NOT NULL,
  `quantity_returned` int(11) NOT NULL,
  `return_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_return`
--

INSERT INTO `sales_return` (`id`, `sale_id`, `stock_id`, `quantity_returned`, `return_date`) VALUES
(4, 22, 11, 10, '2025-09-02 09:07:01');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `stock_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `stock_id`, `quantity`, `unit_price`, `total_price`) VALUES
(1, 0, 1, 1, 100000.00, 100000.00),
(2, 0, 2, 12, 100000.00, 1200000.00),
(6, 2, 5, 3, 3000.00, 9000.00),
(7, 3, 4, 1, 100000.00, 100000.00),
(8, 4, 4, 1, 100000.00, 100000.00),
(9, 5, 4, 1, 100000.00, 100000.00),
(10, 6, 4, 1, 100000.00, 100000.00),
(11, 7, 4, 1, 100000.00, 100000.00),
(12, 8, 5, 20, 3000.00, 60000.00),
(13, 11, 6, 290, 1000.00, 290000.00),
(14, 12, 6, 10, 1000.00, 10000.00),
(15, 13, 5, 20, 3000.00, 60000.00),
(16, 13, 4, 10, 450.00, 4500.00),
(17, 21, 8, 100, 1200.00, 120000.00),
(18, 22, 11, 100, 300.00, 30000.00),
(19, 23, 9, 100, 300.00, 30000.00),
(20, 24, 9, 300, 300.00, 90000.00),
(21, 25, 9, 300, 300.00, 90000.00),
(22, 26, 11, 200, 300.00, 60000.00),
(23, 27, 12, 10, 1100.00, 11000.00);

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `purchase_price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) NOT NULL,
  `manufacture_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `vendor_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`id`, `product_id`, `quantity`, `purchase_price`, `sale_price`, `manufacture_date`, `expiry_date`, `vendor_id`, `created_at`, `updated_at`) VALUES
(7, 10, 260, 20000.00, 25000.00, '2025-09-01', '2027-06-08', 4, '2025-08-30 20:32:11', '2025-09-02 06:59:49'),
(8, 3, 0, 10000.00, 15000.00, '2025-09-01', NULL, 5, '2025-08-30 21:02:10', '2025-09-01 20:06:47'),
(9, 12, 320, 200.00, 300.00, '2025-08-14', '2025-09-01', 9, '2025-09-01 05:41:39', '2025-09-01 21:48:04'),
(10, 9, 200, 1500.00, 2000.00, '2025-09-01', '2025-09-02', 2, '2025-09-01 20:08:56', NULL),
(11, 11, 710, 200.00, 300.00, '2025-07-31', '2025-09-01', 4, '2025-09-01 21:04:56', '2025-09-01 21:34:31'),
(12, 20, 132, 900.00, 1100.00, '2025-09-01', '2027-06-02', 10, '2025-09-02 05:01:16', '2025-09-02 06:58:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(300) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  `updated_at` datetime(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `email`, `phone`, `role_id`, `created_at`, `updated_at`) VALUES
(3, 'admin1', '$2y$10$b5uWt.B0j6aa87olPJ42a.H0KpHJWdQa1/lxWzEObNCoyK2mLGO6G', 'Administrator', 'admin@gmail.com', '', 1, '2025-08-13 02:39:06.000000', '2025-08-13 02:39:06.000000'),
(4, 'lucky123', '$2y$10$fJqcEYASjfNKXHrvqtC8VOgaXFXsvBnbA1QrCD0Hd1nni8zLu1HeW', 'Farhana Lucky', 'farhana@gmail.com', '', 1, '2025-08-13 12:03:04.000000', '2025-08-13 12:03:04.000000'),
(8, 'hawlader', '$2y$10$/v0uOO9ItcfpVwGRpxtFkOIbISQgD4QW.KYjVaxGhlVfCCmAMmApa', 'rafia', 'rafia@gmail.com', '', 3, '2025-08-13 13:18:50.000000', '2025-08-25 10:35:56.000000'),
(11, 'mira123', '$2y$10$b/qgeVLFX1/6y/sjUmtwAuwEb8QmoJ6KGxWlaa.OGhtksOC5EFp2K', 'Azmira khatun', 'azmira@gmail.com', '', 3, '2025-08-19 11:37:01.000000', '2025-08-19 11:37:01.000000'),
(12, 'Shefa333', '$2y$10$REGMwSV4GGl0wShTotME0u43PNZqbXL/TTB1ZICXKf6YQ.7f0p2dG', 'Shefa Hawlader', 'shefa@gmail.com', '', 2, '2025-08-20 09:15:56.000000', '2025-08-20 09:15:56.000000'),
(13, 'armin', '$2y$10$8R9IFwXZ8d/4CktB.5/kJOIJgEKVfesL1rwLP5ZHJ.qjLUpCS8wPe', 'Sharmin', 'sharu@gmail.com', '', 2, '2025-08-25 10:41:47.000000', '2025-08-25 10:41:47.000000');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`id`, `name`, `contact_person`, `phone`, `email`, `address`, `created_at`, `updated_at`) VALUES
(1, 'Michale', NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', NULL),
(2, 'Rosella', NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', NULL),
(4, 'Elvish', NULL, NULL, NULL, NULL, '2025-08-25 09:22:38', NULL),
(5, 'Gretel', NULL, NULL, NULL, NULL, '2025-08-28 12:25:52', NULL),
(9, 'Kabir', NULL, NULL, NULL, NULL, '2025-09-01 12:38:57', NULL),
(10, 'Peter', NULL, NULL, NULL, NULL, '2025-09-02 08:59:38', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expired_products`
--
ALTER TABLE `expired_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_id` (`stock_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_id` (`purchase_id`),
  ADD KEY `stock_id` (`stock_id`);

--
-- Indexes for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_id` (`stock_id`),
  ADD KEY `fk_purchase_returns_purchase_id` (`purchase_id`);

--
-- Indexes for table `return_items`
--
ALTER TABLE `return_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `stock_id` (`stock_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `sales_return`
--
ALTER TABLE `sales_return`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sale_product` (`sale_id`,`stock_id`),
  ADD KEY `fk_sales_return_product` (`stock_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `stock_id` (`stock_id`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_unique_product_id` (`product_id`),
  ADD KEY `product_category_id` (`product_id`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pk_user_id` (`username`),
  ADD UNIQUE KEY `idx_email_unique` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `expired_products`
--
ALTER TABLE `expired_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `purchase_items`
--
ALTER TABLE `purchase_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `return_items`
--
ALTER TABLE `return_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `sales_return`
--
ALTER TABLE `sales_return`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `expired_products`
--
ALTER TABLE `expired_products`
  ADD CONSTRAINT `expired_products_ibfk_1` FOREIGN KEY (`stock_id`) REFERENCES `stock` (`id`);

--
-- Constraints for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD CONSTRAINT `fk_purchase_items_stock_id` FOREIGN KEY (`stock_id`) REFERENCES `stock` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  ADD CONSTRAINT `fk_purchase_returns_purchase_id` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sales_return`
--
ALTER TABLE `sales_return`
  ADD CONSTRAINT `fk_sales_return_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sales_return_stock` FOREIGN KEY (`stock_id`) REFERENCES `stock` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `fk_stock_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
