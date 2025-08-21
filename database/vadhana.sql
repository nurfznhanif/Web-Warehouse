-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 21, 2025 at 07:41 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vadhana`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'FURNITURE', '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(2, 'CLEANING SUPPLIES', '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(3, 'SAFETY EQUIPMENT', '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(4, 'ELECTRICAL SUPPLIES', '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(5, 'MEDICAL SUPPLIES', '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(6, 'FOOD & BEVERAGES', '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(7, 'AUTOMOTIVE PARTS', '2025-08-21 17:39:26', '2025-08-21 17:39:26');

-- --------------------------------------------------------

--
-- Table structure for table `incoming_items`
--

CREATE TABLE `incoming_items` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `purchase_id` int(11) DEFAULT NULL,
  `date` datetime NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `incoming_items`
--
DELIMITER $$
CREATE TRIGGER `after_incoming_delete` AFTER DELETE ON `incoming_items` FOR EACH ROW BEGIN
    UPDATE products 
    SET stock = stock - OLD.quantity 
    WHERE id = OLD.product_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_incoming_insert` AFTER INSERT ON `incoming_items` FOR EACH ROW BEGIN
    UPDATE products 
    SET stock = stock + NEW.quantity 
    WHERE id = NEW.product_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_incoming_update` AFTER UPDATE ON `incoming_items` FOR EACH ROW BEGIN
    UPDATE products 
    SET stock = stock - OLD.quantity + NEW.quantity 
    WHERE id = NEW.product_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `outgoing_items`
--

CREATE TABLE `outgoing_items` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `recipient` varchar(100) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `outgoing_items`
--
DELIMITER $$
CREATE TRIGGER `after_outgoing_delete` AFTER DELETE ON `outgoing_items` FOR EACH ROW BEGIN
    UPDATE products 
    SET stock = stock + OLD.quantity 
    WHERE id = OLD.product_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_outgoing_insert` AFTER INSERT ON `outgoing_items` FOR EACH ROW BEGIN
    UPDATE products 
    SET stock = stock - NEW.quantity 
    WHERE id = NEW.product_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_outgoing_update` AFTER UPDATE ON `outgoing_items` FOR EACH ROW BEGIN
    UPDATE products 
    SET stock = stock + OLD.quantity - NEW.quantity 
    WHERE id = NEW.product_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `code` varchar(50) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `stock` decimal(10,2) DEFAULT 0.00,
  `min_stock` decimal(10,2) DEFAULT 5.00,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `code`, `unit`, `stock`, `min_stock`, `created_at`, `updated_at`) VALUES
(1, 1, 'Laptop Dell Inspiron 15', 'DELL-INS15-001', 'unit', 15.00, 3.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(2, 1, 'Monitor Samsung 24 inch', 'SAM-MON24-002', 'unit', 8.00, 2.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(3, 1, 'Keyboard Logitech Wireless', 'LOG-KEY-003', 'unit', 25.00, 5.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(4, 1, 'Mouse Optical USB', 'MOU-OPT-004', 'unit', 30.00, 8.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(5, 1, 'Printer Canon Pixma', 'CAN-PIX-005', 'unit', 5.00, 1.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(6, 2, 'Meja Kantor Kayu', 'DESK-WOD-006', 'unit', 12.00, 2.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(7, 2, 'Kursi Kantor Ergonomis', 'CHAIR-ERG-007', 'unit', 18.00, 3.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(8, 2, 'Lemari Arsip 4 Laci', 'ARCH-CAB-008', 'unit', 6.00, 1.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(9, 2, 'Papan Tulis Whiteboard', 'WHT-BRD-009', 'unit', 4.00, 1.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(10, 2, 'AC Split 1 PK', 'AC-SPL-010', 'unit', 3.00, 1.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(11, 3, 'Filter Udara Mobil', 'FLT-AIR-011', 'pcs', 50.00, 10.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(12, 3, 'Oli Mesin 5W-30', 'OIL-5W30-012', 'liter', 100.00, 20.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(13, 3, 'Ban Mobil 185/65R15', 'TIRE-185-013', 'pcs', 20.00, 4.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(14, 3, 'Kampas Rem Depan', 'BRK-PAD-014', 'set', 15.00, 3.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(15, 4, 'Forklift Toyota 2.5T', 'FRK-TOY-015', 'unit', 2.00, 1.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(16, 4, 'Hand Pallet Truck', 'HPT-2T-016', 'unit', 5.00, 1.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(17, 4, 'Trolley Barang', 'TRL-BGS-017', 'unit', 10.00, 2.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(18, 1, 'Meja Rapat Oval', 'TBL-OVL-018', 'unit', 3.00, 1.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(19, 2, 'Deterjen Cair 5L', 'DTG-LQ5-019', 'botol', 40.00, 8.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(20, 3, 'Helm Safety Proyek', 'HLM-SFT-020', 'pcs', 35.00, 10.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(21, 4, 'Kabel Listrik 2.5mm', 'CBL-25MM-021', 'meter', 500.00, 100.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(22, 5, 'Masker N95', 'MSK-N95-022', 'box', 25.00, 5.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(23, 6, 'Air Mineral 600ml', 'H2O-600-023', 'dus', 20.00, 5.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(24, 7, 'Aki Mobil 12V 65Ah', 'BAT-12V-024', 'unit', 8.00, 2.00, '2025-08-21 17:39:26', '2025-08-21 17:39:26');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `purchase_date` date NOT NULL,
  `buyer_name` varchar(100) NOT NULL,
  `total_amount` decimal(15,2) DEFAULT 0.00,
  `status` enum('pending','received','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_details`
--

CREATE TABLE `purchase_details` (
  `id` int(11) NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` decimal(10,3) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `total` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','operator') DEFAULT 'operator',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', 'active', '2025-08-22 00:40:32', '2025-08-21 04:59:15', '2025-08-22 00:40:32');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`id`, `name`, `address`, `phone`, `email`, `created_at`, `updated_at`) VALUES
(1, 'PT Maju Jaya Technology', 'Jl. Sudirman No. 123, Jakarta Pusat', '021-5555-1234', 'sales@majujaya.com', '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(2, 'CV Berkah Elektronik', 'Jl. Gajah Mada No. 45, Surabaya', '031-7777-5678', 'info@berkahelektronik.co.id', '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(3, 'Toko Sumber Rejeki', 'Jl. Malioboro No. 67, Yogyakarta', '0274-888-9999', 'sumberrejeki@gmail.com', '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(4, 'PT Global Furniture', 'Jl. Raya Bogor KM 25, Depok', '021-9999-1111', 'contact@globalfurniture.id', '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(5, 'UD Sinar Bahagia', 'Jl. Ahmad Yani No. 89, Bandung', '022-3333-4444', 'sinarbahagia@yahoo.com', '2025-08-21 17:39:26', '2025-08-21 17:39:26'),
(6, 'CV Mandiri Sejahtera', 'Jl. Diponegoro No. 234, Semarang', '024-5555-6666', 'mandiri.sejahtera@outlook.com', '2025-08-21 17:39:26', '2025-08-21 17:39:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `incoming_items`
--
ALTER TABLE `incoming_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_id` (`purchase_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_incoming_items_product` (`product_id`),
  ADD KEY `idx_incoming_items_date` (`date`);

--
-- Indexes for table `outgoing_items`
--
ALTER TABLE `outgoing_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_outgoing_items_product` (`product_id`),
  ADD KEY `idx_outgoing_items_date` (`date`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_code` (`code`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `purchase_details`
--
ALTER TABLE `purchase_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_id` (`purchase_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `incoming_items`
--
ALTER TABLE `incoming_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `outgoing_items`
--
ALTER TABLE `outgoing_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_details`
--
ALTER TABLE `purchase_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `incoming_items`
--
ALTER TABLE `incoming_items`
  ADD CONSTRAINT `incoming_items_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `incoming_items_ibfk_2` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `incoming_items_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `outgoing_items`
--
ALTER TABLE `outgoing_items`
  ADD CONSTRAINT `outgoing_items_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `outgoing_items_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`);

--
-- Constraints for table `purchase_details`
--
ALTER TABLE `purchase_details`
  ADD CONSTRAINT `purchase_details_ibfk_1` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
