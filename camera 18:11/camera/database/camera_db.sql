-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 15, 2025 at 09:37 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `logo` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `slug`, `description`, `logo`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'Canon', 'canon', 'Canon Inc. - Nhà sản xuất máy ảnh và thiết bị hình ảnh hàng đầu', NULL, 1, 1, '2025-11-10 07:47:44', '2025-11-10 07:47:44'),
(2, 'Fujifilm', 'fujifilm', 'Fujifilm - Chuyên gia trong công nghệ hình ảnh và photography', NULL, 1, 2, '2025-11-10 07:47:44', '2025-11-10 07:47:44'),
(3, 'Nikon', 'nikon', 'Nikon Corporation - Hãng sản xuất camera chất lượng cao', NULL, 1, 3, '2025-11-10 07:47:44', '2025-11-10 07:47:44'),
(4, 'Sony', 'sony', 'Sony - Công ty điện tử hàng đầu sản xuất camera và thiết bị quay phim', NULL, 1, 4, '2025-11-10 07:47:44', '2025-11-10 07:47:44');



--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 2, '2025-11-13 11:27:21', '2025-11-13 11:27:21');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `parent_id`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Camera', 'camera', 'Máy ảnh chuyên nghiệp', NULL, NULL, 0, 1, '2025-11-08 11:59:41', '2025-11-08 11:59:41'),
(2, 'Ống Kính', 'ong-kinh', 'Ống kính chất lượng cao', NULL, NULL, 0, 1, '2025-11-08 11:59:41', '2025-11-08 11:59:41'),
(3, 'Phụ Kiện', 'phu-kien', 'Phụ kiện camera đa dạng', NULL, NULL, 0, 1, '2025-11-08 11:59:41', '2025-11-08 11:59:41');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `service` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('new','processing','completed') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `phone`, `service`, `message`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Cao Minh Tú', 'noomad1004@gmail.com', '0877891422', 'Bảo hành', 'ádawsdawd', 'new', '2025-11-10 10:10:59', '2025-11-10 10:10:59'),
(3, 'Đỗ Tuấn Anh', 'tuananh11092803@gmail.com', '0326205688', 'Tư vấn', 'Tôi muốn tư vấn thêm về các loại máy ảnh', 'new', '2025-11-11 09:56:11', '2025-11-11 09:56:11');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `shipping_phone` varchar(20) DEFAULT NULL,
  `shipping_name` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(12,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `product_price`, `quantity`, `subtotal`, `created_at`) VALUES
(3, 9, 1, 'Camera Fujifilm XT-30', 25000033.00, 5, 125000165.00, '2025-11-15 08:34:56');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `method` varchar(20) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` varchar(20) DEFAULT 'pending' COMMENT 'pending, completed, failed, cancelled',
  `shipping_phone` varchar(20) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `vnpay_transaction_no` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `user_id`, `method`, `amount`, `status`, `shipping_phone`, `shipping_address`, `vnpay_transaction_no`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'ORD-TEST-1763033222', 2, 'vnpay', 500000.00, 'pending', NULL, NULL, NULL, NULL, '2025-11-13 11:27:02', '2025-11-13 11:27:02'),
(2, 'ORD-20251113122755-6915c0bb45734', 2, 'vnpay', 50030066.00, 'pending', NULL, NULL, NULL, NULL, '2025-11-13 11:27:55', '2025-11-13 11:27:55'),
(3, 'ORD-20251113122857-6915c0f94c495', 1, 'momo', 30000000.00, 'pending', NULL, NULL, NULL, NULL, '2025-11-13 11:28:57', '2025-11-13 12:01:37'),
(4, 'ORD-20251113125439-6915c6fff15b4', 1, 'cod', 63900033.00, 'cancelled', NULL, NULL, NULL, NULL, '2025-11-13 11:54:40', '2025-11-13 12:40:21'),
(5, 'ORD-20251113131407-6915cb8fc9b73', 1, 'momo', 30000000.00, 'cancelled', '0877891422', 'Ở đâu còn lâu mới nói', NULL, NULL, '2025-11-13 12:14:07', '2025-11-15 08:35:21'),
(6, 'ORD-20251115092357-6918389d670c6', 1, 'cod', 120000000.00, 'cancelled', '0877891422', 'Ở đâu còn lâu mới nói', NULL, NULL, '2025-11-15 08:23:57', '2025-11-15 08:35:18'),
(9, 'ORD-20251115093456-69183b309e390', 1, 'cod', 125000165.00, 'pending', '0877891422', 'Ở đâu còn lâu mới nói', NULL, NULL, '2025-11-15 08:34:56', '2025-11-15 08:34:56');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `image` varchar(500) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `badge` varchar(50) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `stock` int(11) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `description`, `price`, `image`, `category`, `brand_id`, `badge`, `is_featured`, `stock`, `views`, `created_at`, `updated_at`) VALUES
(1, 'Camera Fujifilm XT-30', 'camera-fujifilm-xt30', 'Máy ảnh kỹ thuật số nhỏ gọn được thiết kế cho thế hệ sáng tạo mới với khả năng chụp ảnh và quay video chất lượng cao. Độ phân giải 26MP, tự động lấy nét nhanh, và dáng vẻ cổ điển.', 25000033.00, 'https://www.shopmoment.com/_next/image?url=https%3A%2F%2Fcdn.sanity.io%2Fimages%2Fsoj3d0g3%2Fproduction%2F596014d2e8bd20d21c596bded057aa60573607ac-3280x3280.jpg%3Fw%3D1440%26h%3D1440%26fit%3Dcrop%26auto%3Dformat%26dpr%3D2&w=3840&q=75', 'Camera', 2, 'Hot', 1, 10, 0, '2025-11-08 11:58:54', '2025-11-11 10:11:13'),
(2, 'ZV-E10 II Vlog Camera', 'zv-e10-ii-vlog-camera', 'Camera quay phim đặc biệt thiết kế cho những người làm video và vlog. Có mic tích hợp chất lượng cao, màn hình xoay, và khả năng quay 4K. Hoàn hảo cho nội dung sáng tạo.', 8900000.00, 'https://www.shopmoment.com/_next/image?url=https%3A%2F%2Fcdn.sanity.io%2Fimages%2Fsoj3d0g3%2Fproduction%2F0eaf1c78505d4316421a7eb40d23db2bb328e2ed-3280x3280.jpg%3Fw%3D1440%26h%3D1440%26fit%3Dcrop%26auto%3Dformat%26dpr%3D2&w=3840&q=75', 'Camera', 4, '#1 Bán Chạy', 1, 15, 0, '2025-11-08 11:58:54', '2025-11-10 07:47:44'),
(7, 'X half Premium Compact Camera', 'https://www.shopmoment.com/_next/image?url=https%3A%2F%2Fcdn.sanity.io%2Fimages%2Fsoj3d0g3%2Fproduction%2F5e447e6bfe6bb5cd640f564b7da9e4a44ce2b03b-5000x5000.jpg%3Fw%3D1440%26h%3D1440%26fit%3Dcrop%26auto%3Dformat%26dpr%3D2&w=3840&q=75', 'The Fujifilm X half premium compact camera is a one-of-a-kind digital shooter that makes shooting creative and sharing them super easy.', 30000000.00, 'https://www.shopmoment.com/_next/image?url=https%3A%2F%2Fcdn.sanity.io%2Fimages%2Fsoj3d0g3%2Fproduction%2F5e447e6bfe6bb5cd640f564b7da9e4a44ce2b03b-5000x5000.jpg%3Fw%3D1440%26h%3D1440%26fit%3Dcrop%26auto%3Dformat%26dpr%3D2&w=3840&q=75', 'Camera', 2, 'Hot', 1, 30, 0, '2025-11-11 09:35:41', '2025-11-11 09:35:41');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `warranty` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_bookings`
--

CREATE TABLE `service_bookings` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `device_info` text DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `status` enum('new','processing','completed','cancelled') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_bookings`
--

INSERT INTO `service_bookings` (`id`, `service_id`, `user_id`, `full_name`, `email`, `phone`, `description`, `notes`, `device_info`, `appointment_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 3, 'noomad', 'noomad1004@gmail.com', '0877891422', NULL, '', NULL, '0000-00-00', 'new', '2025-11-10 10:00:49', '2025-11-10 10:00:49'),
(2, 2, 3, 'noomad', 'noomad1004@gmail.com', '0877891422', NULL, '', NULL, '0000-00-00', 'new', '2025-11-10 10:06:52', '2025-11-10 10:06:52'),
(4, 1, 4, 'Đỗ Tuấn Anh', 'tuananh11092803@gmail.com', '0326205688', NULL, '', NULL, '2025-11-11', 'new', '2025-11-11 10:00:33', '2025-11-11 10:00:33');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_info`
--

CREATE TABLE `shipping_info` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `province` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shipping_info`
--

INSERT INTO `shipping_info` (`id`, `user_id`, `phone`, `province`, `address`, `created_at`, `updated_at`) VALUES
(1, 1, '0877891422', 'Hà Nội', 'Ở đâu còn lâu mới nói', '2025-11-13 10:34:39', '2025-11-13 12:08:18'),
(2, 2, '0912345678', 'HÓ N?i', '123 ðu?ng ABC', '2025-11-13 11:27:39', '2025-11-13 11:27:39');

-- --------------------------------------------------------

--
-- Table structure for table `transfer_payments`
--

CREATE TABLE `transfer_payments` (
  `id` int(11) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `method` varchar(20) NOT NULL COMMENT 'vnpay, momo',
  `amount` decimal(15,2) NOT NULL,
  `status` varchar(20) DEFAULT 'pending' COMMENT 'pending, confirmed, failed',
  `bank_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`bank_info`)),
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transfer_payments`
--

INSERT INTO `transfer_payments` (`id`, `order_id`, `user_id`, `method`, `amount`, `status`, `bank_info`, `confirmed_at`, `created_at`, `updated_at`) VALUES
(1, 'ORD-20251113122755-6915c0bb45734', 2, 'vnpay', 50030066.00, 'pending', '{\"bank\":\"VNPay\",\"bankCode\":\"VNPAY\",\"accountNumber\":\"0123456789\",\"accountName\":\"XEDIC CAMERA\",\"accountShort\":\"0123456789\",\"template\":\"DH{ORDER_ID}\"}', NULL, '2025-11-13 11:28:06', '2025-11-13 11:28:06'),
(2, 'ORD-20251113122857-6915c0f94c495', 1, 'momo', 30000000.00, 'confirmed', '{\"bank\":\"Momo\",\"bankCode\":\"MOMO\",\"accountNumber\":\"0392123456\",\"accountName\":\"XEDIC CAMERA\",\"accountShort\":\"0392123456\",\"template\":\"DH{ORDER_ID}\"}', '2025-11-13 11:29:01', '2025-11-13 11:28:57', '2025-11-13 11:29:01'),
(3, 'ORD-20251113131407-6915cb8fc9b73', 1, 'momo', 30000000.00, 'confirmed', '{\"bank\":\"Momo\",\"bankCode\":\"MOMO\",\"accountNumber\":\"0392123456\",\"accountName\":\"XEDIC CAMERA\",\"accountShort\":\"0392123456\",\"template\":\"DH{ORDER_ID}\"}', '2025-11-13 12:14:12', '2025-11-13 12:14:07', '2025-11-13 12:14:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `address`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@xedic.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', NULL, NULL, 'admin', 1, '2025-11-08 11:59:41', '2025-11-08 11:59:41'),
(2, 'noobtank1109', '1@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyen Van A', NULL, NULL, 'customer', 1, '2025-11-08 11:59:41', '2025-11-08 11:59:41'),
(3, 'noomad', 'tuminhcao123@gmail.com', '$2y$10$44bKBLryfc9bccMwJKpFN..xcVxhABz3NpeTvMWvDltpzUBBlCji.', 'Cao Minh Tú', NULL, NULL, 'customer', 1, '2025-11-08 12:00:22', '2025-11-08 12:00:22'),
(4, 'tank1', 'tuananh11092803@gmail.com', '$2y$10$qcnxrTWFjNyyJxc9KmRYeeVdONHoSAn7e.hhKGW./IqdRMKaukEpO', 'Đỗ Tuấn Anh', NULL, NULL, 'customer', 1, '2025-11-11 08:16:25', '2025-11-11 08:16:25'),
(5, 'trang1109', 'trang@gmail.com', '$2y$10$3v3syaIdkHfiNQxVl8KazOI5hHQGdXy83J8WT1T2w.iH.pSFnp0wq', 'Thùy Trang', NULL, NULL, 'admin', 1, '2025-11-11 09:14:41', '2025-11-11 09:14:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_parent` (`parent_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_method` (`method`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_brand` (`brand_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_approved` (`is_approved`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `service_bookings`
--
ALTER TABLE `service_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service` (`service_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `shipping_info`
--
ALTER TABLE `shipping_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `transfer_payments`
--
ALTER TABLE `transfer_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_method` (`method`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `service_bookings`
--
ALTER TABLE `service_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `shipping_info`
--
ALTER TABLE `shipping_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transfer_payments`
--
ALTER TABLE `transfer_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shipping_info`
--
ALTER TABLE `shipping_info`
  ADD CONSTRAINT `shipping_info_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transfer_payments`
--
ALTER TABLE `transfer_payments`
  ADD CONSTRAINT `transfer_payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transfer_payments_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `payments` (`order_id`) ON DELETE CASCADE;
COMMIT;


--Google
ALTER TABLE `users` ADD COLUMN `google_uid` VARCHAR(255) DEFAULT NULL UNIQUE;

--Facebook
ALTER TABLE `users` ADD COLUMN `facebook_uid` VARCHAR(255) DEFAULT NULL UNIQUE;


ALTER TABLE `payments` ADD COLUMN `delivery_status` VARCHAR(50) DEFAULT 'pending' COMMENT 'pending, shipped, delivered';

-- Cập nhật lại dữ liệu cũ: những đơn đã confirmed sẽ set thành shipped
UPDATE `payments` SET `delivery_status` = 'shipped' WHERE `status` IN ('paid', 'confirmed');

-- Kiểm tra kết quả
SELECT id, order_id, status, delivery_status, amount FROM payments ORDER BY created_at DESC;
