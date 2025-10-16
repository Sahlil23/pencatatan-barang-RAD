-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 16, 2025 at 01:34 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `chicking_bjm`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `category_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Spices', 'Kategori bahan rempah dan bumbu', '2025-10-14 04:43:34', '2025-10-14 04:43:34'),
(2, 'Marinations', 'Kategori bahan marinasi', '2025-10-14 04:43:34', '2025-10-14 04:43:34'),
(3, 'Freezer Products', 'Produk yang disimpan dalam freezer', '2025-10-14 04:43:34', '2025-10-14 04:43:34'),
(4, 'Chiller Products', 'Produk yang disimpan dalam chiller', '2025-10-14 04:43:34', '2025-10-14 04:43:34'),
(5, 'Dry Products', 'Produk kering non-perishable', '2025-10-14 04:43:34', '2025-10-14 04:43:34'),
(6, 'Packaging', 'Kategori bahan kemasan', '2025-10-14 04:43:34', '2025-10-14 04:43:34'),
(7, 'Ops Supplies', 'Perlengkapan operasional', '2025-10-14 04:43:34', '2025-10-14 04:43:34'),
(8, 'Beverage', 'Minuman', '2025-10-14 04:43:34', '2025-10-14 04:43:34'),
(9, 'Ice Cream', 'Kategori es krim dan dessert beku', '2025-10-14 04:43:34', '2025-10-14 04:43:34');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` bigint UNSIGNED NOT NULL,
  `sku` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `supplier_id` bigint UNSIGNED DEFAULT NULL,
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_stock` decimal(10,2) NOT NULL DEFAULT '0.00',
  `low_stock_threshold` decimal(10,2) NOT NULL DEFAULT '5.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `sku`, `item_name`, `category_id`, `supplier_id`, `unit`, `current_stock`, `low_stock_threshold`, `created_at`, `updated_at`) VALUES
(18, 'CHK-1001', 'Grill Powder (1000Gr)', 1, NULL, 'Kg', '9.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(19, 'CHK-1002', 'Mix Pepper Powder (1000Gr)', 1, NULL, 'kg', '11.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(20, 'CHK-1003', 'Tandoori Sprinkler (1000Gr)', 1, NULL, 'kg', '8.24', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(21, 'CHK-1004', 'Reguler Marinade (500Gr)', 1, NULL, 'pack', '15.00', '0.00', '2025-09-30 16:00:00', '2025-10-15 07:46:25'),
(22, 'CHK-1005', 'Reguler Spices Powder (450Gr)', 1, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(23, 'CHK-1006', 'Bumbu Suntik Hot (100G)', 1, NULL, 'Pack', '438.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(24, 'CHK-1007', 'Bumbu Suntik Orig (100Gr)', 1, NULL, 'Pack', '420.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(25, 'CHK-1008', 'Bumbu Tepung Spicy (1000Gr)', 1, NULL, 'Pack', '16.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(26, 'CHK-1009', 'Bumbu Tepung Orig (1000Gr)', 1, NULL, 'Pack', '7.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(27, 'CHK-1010', 'Breading Mix Spicy (1000Gr)', 1, NULL, 'Batch', '61.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(28, 'CHK-1011', 'Breading Mix Ori (1000Gr)', 1, NULL, 'Batch', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(29, 'CHK-1012', 'Bumbu Nasi Mandhi (100Gr)', 1, NULL, 'Pack', '124.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(30, 'CHK-1013', 'Bumbu Nasi Biryani (100Gr)', 1, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(31, 'CHK-1014', 'Bumbu Nasgore Chicking (1000Gr)', 1, NULL, 'Kg', '2.70', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(32, 'CHK-1015', 'Bumbu Ayam Bakar Madu (1000Gr)', 1, NULL, 'Kg', '3.60', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(33, 'CHK-1016', 'Pewarna Safron Eqg Yellow (100Gr)', 1, NULL, 'Btl', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(34, 'CHK-1017', 'Butter Milk Powder Chicken (1000Gr)', 1, NULL, 'Kg', '2.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(35, 'CHK-1018', 'Spicy Chicken', 2, NULL, 'Pcs', '304.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(36, 'CHK-1019', 'Original Chicken', 2, NULL, 'Pcs', '248.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(37, 'CHK-1020', 'Honey Grill Chicken', 2, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(38, 'CHK-1021', 'Grilled Chicken', 2, NULL, 'Pcs', '224.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(39, 'CHK-1022', 'Whole Chicken Original', 2, NULL, 'Head', '4.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(40, 'CHK-1023', 'Whole Chicken Spicy', 2, NULL, 'Head', '13.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(41, 'CHK-1024', 'Chickpop (100Gr)', 2, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(42, 'CHK-1025', 'Crispy Skin (100Gr)', 2, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(43, 'CHK-1026', 'Potato Wedges (Mc Cain 6X5 Lb)', 3, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(44, 'CHK-1027', 'Crinkle French Fries (Gldn 12X1Kg)', 3, NULL, 'Kg', '8.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(45, 'CHK-1028', 'Tortilla Sukanda @100Pcs (12X12Pcs)', 3, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(46, 'CHK-1029', 'Chicken Nugget', 3, NULL, 'Pack', '57.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(47, 'CHK-1030', 'Croisan Mini (Bonpatis 125X15Gr)', 3, NULL, 'Cs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(48, 'CHK-1031', 'Vegetable Frozen Corn', 3, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(49, 'CHK-1032', 'Butter Unsulted (1X@25Kg /Carton)', 3, NULL, 'Cs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(50, 'CHK-1033', 'Samosa (50 Pcs)', 3, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(51, 'CHK-1034', 'Beef Patties Burger (1100G@12Pack/Box)', 3, NULL, 'Pack', '100.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(52, 'CHK-1035', 'Bun Burger 4.5 Inch Wijen (6X18Pack/Box)', 3, NULL, 'Pack', '18.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(53, 'CHK-1036', 'Fresh Boneless Leg 140-150Gr', 4, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(54, 'CHK-1037', 'Fresh Chicken Cut (1.2-1.3Kg)', 4, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(55, 'CHK-1038', 'Fresh Whole Chicken', 4, NULL, 'Kg', '40.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(56, 'CHK-1039', 'Fresh Chicken Skin', 4, NULL, 'Kg', '5300.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(57, 'CHK-1040', 'Black Pepper', 4, NULL, 'Kg', '2.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(58, 'CHK-1041', 'Cheese Hot Lava', 4, NULL, 'Kg', '9.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(59, 'CHK-1042', 'Garlic Mayo', 4, NULL, 'Kg', '6.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(60, 'CHK-1043', 'Lettuce', 4, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(61, 'CHK-1044', 'Tomat', 4, NULL, 'Kg', '4.50', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(62, 'CHK-1045', 'Bawang Putih', 4, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(63, 'CHK-1046', 'Paprika', 4, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(64, 'CHK-1047', 'Cheese Slice Bega', 4, NULL, 'Pack', '332.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(65, 'CHK-1048', 'Margarin @250Gr', 4, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(66, 'CHK-1049', 'Bawang Merah', 4, NULL, 'Kg', '2.90', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(67, 'CHK-1050', 'Bawang Bombay', 4, NULL, 'Kg', '5.45', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(68, 'CHK-1051', 'Daun Jeruk', 4, NULL, 'Kg', '0.10', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(69, 'CHK-1052', 'Daun Salam', 4, NULL, 'Tusuk', '0.25', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(70, 'CHK-1053', 'Kemiri', 4, NULL, 'Kg', '0.30', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(71, 'CHK-1054', 'Cabai Merah Besar', 4, NULL, 'Kg', '3.50', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(72, 'CHK-1055', 'Petis Udang (500G)', 4, NULL, 'Kg', '0.50', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(73, 'CHK-1056', 'Serei (500G)', 4, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(74, 'CHK-1057', 'Jahe', 4, NULL, 'Kg', '1.50', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(75, 'CHK-1058', 'Daun Mint', 4, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(76, 'CHK-1059', 'Kayu Manis', 4, NULL, 'Kg', '1.70', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(77, 'CHK-1060', 'Wijen', 4, NULL, 'Kg', '0.05', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(78, 'CHK-1061', 'Masako', 4, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(79, 'CHK-1062', 'Susu Uht @1.000Ml', 4, NULL, 'Ltr', '13.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(80, 'CHK-1063', 'Gula Sachet', 4, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(81, 'CHK-1064', 'Regular Rice Kura-kura', 5, NULL, 'Sack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(82, 'CHK-1065', 'Basmati Rice (25Kg)', 5, NULL, 'Sack', '844.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(83, 'CHK-1066', 'Tepung Segitiga Biru (25Kg)', 5, NULL, 'Sack', '89.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(84, 'CHK-1067', 'Shortening Oil Fryer (15Kg)', 5, NULL, 'Carton', '9.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(85, 'CHK-1068', 'Sambal Bawang (1Cs=10Kg)', 5, NULL, 'Kg', '1.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(86, 'CHK-1069', 'Tomato Sachet Belibis (1X480Pcs /Carton)', 5, NULL, 'Pack', '1690.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(87, 'CHK-1070', 'Chilli Sachet Belibis (1X480Pcs /Carton)', 5, NULL, 'Pack', '1181.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(88, 'CHK-1071', 'Garam Dolpin( Salt )', 5, NULL, 'Pack', '4.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(89, 'CHK-1072', 'Gula Pasir', 5, NULL, 'Kg', '0.80', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(90, 'CHK-1073', 'Cardamom', 5, NULL, 'Kg', '900.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(91, 'CHK-1074', 'Black Cloves', 5, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(92, 'CHK-1075', 'Corn Oil', 5, NULL, 'Btl', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(93, 'CHK-1076', 'Arang Briket', 5, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(94, 'CHK-1077', 'Box Aluminium 20X20', 6, NULL, 'Pcs', '56.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(95, 'CHK-1078', 'Sticker Chicking -10X10 Cm', 6, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(96, 'CHK-1079', 'French Fries Box', 6, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(97, 'CHK-1080', 'Rice Bowl 650Ml (1Cs=500Pcs)', 6, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(98, 'CHK-1081', 'Lid Rice Bowl 650Ml (1Cs=500Pcs)', 6, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(99, 'CHK-1082', 'Paper Bag', 6, NULL, 'Pcs', '319.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(100, 'CHK-1083', 'Cup / Mika Sambal 25Ml', 6, NULL, 'Pack', '687.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(101, 'CHK-1084', 'Cup Mayo 35Gr', 6, NULL, 'Pcs', '300.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(102, 'CHK-1085', 'Wrap Paper Rice', 6, NULL, 'Pcs', '1138.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(103, 'CHK-1086', 'Wrap Paperkebab', 6, NULL, 'Pcs', '894.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(104, 'CHK-1087', 'Plastik Bag Small', 6, NULL, 'Pcs', '369.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(105, 'CHK-1088', 'Plastik Bag Medium', 6, NULL, 'Pcs', '165.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(106, 'CHK-1089', 'Plastik Bag Large', 6, NULL, 'Pcs', '207.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(107, 'CHK-1090', 'Plastik Bag Xxl (Super L)', 6, NULL, 'Pcs', '266.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(108, 'CHK-1091', 'Sendok', 6, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(109, 'CHK-1092', 'Garpu', 6, NULL, 'Pcs', '1800.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(110, 'CHK-1093', 'Straw Besar Boba', 6, NULL, 'Pcs', '719.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(111, 'CHK-1094', 'Straw Kecil', 6, NULL, 'Pcs', '8295.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(112, 'CHK-1095', 'Hot Cup 8Oz', 6, NULL, 'Pcs', '163.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(113, 'CHK-1096', 'Lid Hot Cup 8Oz', 6, NULL, 'Pcs', '20.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(114, 'CHK-1097', 'Cup 16Oz Oval', 6, NULL, 'Pcs', '528.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(115, 'CHK-1098', 'Cup 22Oz Oval Logo Chicking', 6, NULL, 'Pcs', '1069.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(116, 'CHK-1099', 'Lid Cup16/ 22Oz', 6, NULL, 'Pcs', '430.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(117, 'CHK-1100', 'Snack Tray', 6, NULL, 'Pcs', '200.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(118, 'CHK-1101', 'Tusuk Gigi Sterill', 6, NULL, 'Pack', '2512.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(119, 'CHK-1102', 'Custom Box Chicking ( L )', 6, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(120, 'CHK-1103', 'Lunch Box Coklat', 6, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(121, 'CHK-1104', 'Chicken Tray', 6, NULL, 'Pcs', '520.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(122, 'CHK-1105', 'Magnesol', 7, NULL, 'Kg', '1.60', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(123, 'CHK-1106', 'Filter Paper', 7, NULL, 'Pcs', '7.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(124, 'CHK-1107', 'Floor Cleaner (1 Btl = 5Ltr) Xyz', 7, NULL, 'Jrg', '0.23', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(125, 'CHK-1108', 'Handsoap (1 Btl = 5Ltr)', 7, NULL, 'Jrg', '0.23', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(126, 'CHK-1109', 'Pembersih Mesin UNOX (1ltr)', 7, NULL, 'Jrg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(127, 'CHK-1110', 'Dishwashing Soap (1 Btl = 5Ltr) Xyz', 7, NULL, 'Jrg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(128, 'CHK-1111', 'Karbol (1 Btl = 4Ltr) Xyz', 7, NULL, 'Btl', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(129, 'CHK-1112', 'Hand Glove', 7, NULL, 'Pack', '28.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(130, 'CHK-1113', 'Tissue Wastafel', 7, NULL, 'Pack', '12.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(131, 'CHK-1114', 'Tissue Napkin', 7, NULL, 'Pack', '60.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(132, 'CHK-1115', 'Tissue Roll', 7, NULL, 'Roll', '10.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(133, 'CHK-1116', 'Printer Roll Thermal 80X80', 7, NULL, 'Roll', '9.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(134, 'CHK-1117', 'Termal Edc Bca Dan Mandiri 30X40', 7, NULL, 'Roll', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(135, 'CHK-1118', 'Kabel Ties', 7, NULL, 'Pack', '62.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(136, 'CHK-1119', 'Plastik Es Marinasi Chickpop', 7, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(137, 'CHK-1120', 'Plastik Es @ 2Kg Marinasi Ayam', 7, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(138, 'CHK-1121', 'Gas Lpg 12Kg Cylender', 7, NULL, 'Tb', '5.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(139, 'CHK-1122', 'Air Galon Local Ceria', 7, NULL, 'Galon', '4.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(140, 'CHK-1123', 'Kantong Sampah 100x100', 7, NULL, 'Pack', '75.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(141, 'CHK-1124', 'Kantong Sampah 60 X 100', 7, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(142, 'CHK-1125', 'Teh Botol Sosro 350Ml', 8, NULL, 'Cs', '161.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(143, 'CHK-1126', 'Prima 600 Ml Sosro', 8, NULL, 'Cs', '754.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(144, 'CHK-1127', 'Nestle Black Coffee120G', 8, NULL, 'Pack', '81.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(145, 'CHK-1128', 'Nestle Latte', 8, NULL, 'Pack', '21.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(146, 'CHK-1129', 'Bubuk Green Tea', 8, NULL, 'Kg', '25.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(147, 'CHK-1130', 'Teh Pocci Bubuk', 8, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(148, 'CHK-1131', 'Boba Arab Strawberry', 8, NULL, 'Btl', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(149, 'CHK-1132', 'Boba Arab Avocado', 8, NULL, 'Kg', '1040.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(150, 'CHK-1133', 'Boba Arab Bubble Gum', 8, NULL, 'Kg', '900.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(151, 'CHK-1134', 'Dubai Breeze Green Apple', 8, NULL, 'Btl', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(152, 'CHK-1135', 'Dubai Breeze Pasion Fruit', 8, NULL, 'Btl', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(153, 'CHK-1136', 'Mint Leaves', 8, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(154, 'CHK-1137', 'Condensed Milk (@350Ml)', 8, NULL, 'Klg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(155, 'CHK-1138', 'Gula Jawa', 8, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(156, 'CHK-1139', 'Ice Cube / Es Batu', 8, NULL, 'Pack', '7.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(157, 'CHK-1140', 'Vanilla Bl-8L Es Cream Vanilla (300 Cup)', 9, NULL, 'Carton', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(158, 'CHK-1141', 'Neapolitan 8L Ice Cream 3 Rasa (300 Cup)', 9, NULL, 'Carton', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(159, 'CHK-1142', 'Chocolate-8L Es Cream Chocolate (300Cup)', 9, NULL, 'Carton', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(160, 'CHK-1143', 'Sweet Corn', 9, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(161, 'CHK-1144', 'Cruncy Choco Lava', 9, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(162, 'CHK-1145', 'Crunchy Chocolate Blueberry', 9, NULL, 'Pcs', '26.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(163, 'CHK-1146', 'Mochi Chocolate', 9, NULL, 'Pcs', '27.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(164, 'CHK-1147', 'Mochi Strawberry', 9, NULL, 'Pcs', '19.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(165, 'CHK-1148', 'Mochi Pisang Ijo', 9, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(166, 'CHK-1149', 'Mango Smoothie', 9, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(167, 'CHK-1150', 'Grape Raisin', 9, NULL, 'Pcs', '7.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(168, 'CHK-1151', 'Crunchy Blueberry', 9, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(169, 'CHK-1152', 'Cool Blueberry', 9, NULL, 'Pcs', '39.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(170, 'CHK-1153', 'Choco Berry', 9, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(171, 'CHK-1154', 'Cool Orange', 9, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(172, 'CHK-1155', 'Boba Milk', 9, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(173, 'CHK-1156', 'Mochi Durian', 9, NULL, 'Pcs', '24.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(174, 'CHK-1157', 'Kambing', 4, NULL, 'Kg', '21.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(175, 'CHK-1158', 'Kol Putih', 4, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(176, 'CHK-1159', 'Barbeque Saos', 4, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(177, 'CHK-1160', 'Pizza Dough', 4, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(178, 'CHK-1161', 'Mozzarella Cheese 250gr', 4, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(179, 'CHK-1162', 'Plastik Ctik Salad 7x10', 6, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(180, 'CHK-1163', 'Box Pizza 26x26', 6, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(181, 'CHK-1178', 'Straw Kopi', 6, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(182, 'CHK-1179', 'Custom Box Chicking (M)', 6, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(183, 'CHK-1180', 'Paper Cup Side Dish', 6, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(184, 'CHK-1181', 'Food Tray Chicking', 6, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(185, 'CHK-1202', 'Lid Snack Tray', 6, NULL, 'Pcs', '680.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(186, 'CHK-1203', 'Cup Acar 100ml', 6, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(187, 'CHK-1182', 'Garam Kapal', 5, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(188, 'CHK-1183', 'Mamasuka Hot Lava 320gr', 5, NULL, 'Btl', '1.00', '0.00', '2025-09-30 16:00:00', '2025-10-14 19:31:10'),
(189, 'CHK-1188', 'Cuka', 5, NULL, 'Btl', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(190, 'CHK-1194', 'Masako 250Gr', 5, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(191, 'CHK-1200', 'Ketumbar', 5, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(192, 'CHK-1171', 'Milo Activ-go', 8, NULL, 'Pack', '6775.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(193, 'CHK-1173', 'Popping Boba', 8, NULL, 'Jar', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(194, 'CHK-1174', 'Marjan Cocopandan', 8, NULL, 'Btl', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(195, 'CHK-1172', 'Crunchy Chocolate Malt', 9, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(196, 'CHK-1191', 'Cool Watermelon Apple', 9, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(197, 'CHK-1192', 'Fruits Galaxy', 9, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(198, 'CHK-1193', 'Crunchy Vanilla Cookies', 9, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(199, 'CHK-1184', 'Dishwashing Soap 650ml', 7, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(200, 'CHK-1189', 'Detergen', 7, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(201, 'CHK-1190', 'Prostex', 7, NULL, 'Btl', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(202, 'CHK-1195', 'Cling Glass Cleaner', 7, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(203, 'CHK-1185', 'Telur Ayam', 4, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(204, 'CHK-1186', 'Wortel', 4, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(205, 'CHK-1187', 'Timun', 4, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(206, 'CHK-1198', 'Cabe Tiung', 4, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(207, 'CHK-1199', 'Cabe Rawit', 4, NULL, 'Kg', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(208, 'CHK-1201', 'Sambal bawang Uleg', 4, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(209, 'CHK-1204', 'Whole Grilled Chicken', 4, NULL, 'Head', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(210, 'CHK-1197', 'Mozzarella Cheese 1kg', 3, NULL, 'Head', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(211, 'CHK-1196', 'Selada', 4, NULL, 'kg', '2.10', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(222, 'CHK-1164', 'Mamasuka Hot Lava', 5, NULL, 'Pcs', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(223, 'CHK-1165', 'Mayonaise Maestro', 5, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(224, 'CHK-1166', 'Mc Lewis Cheese Souce', 5, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(225, 'CHK-1168', 'Susu Bubuk', 5, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(226, 'CHK-1169', 'Koepoe Orange', 5, NULL, 'Btl', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(227, 'CHK-1170', 'Koepoe Kuning', 5, NULL, 'Btl', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(228, 'CHK-1175', 'Kecap Bango', 5, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(229, 'CHK-1176', 'Lada Bubuk', 5, NULL, 'Pack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(230, 'CHK-1177', 'Brasmati Rice 5kg', 5, NULL, 'Sack', '0.00', '0.00', '2025-09-30 16:00:00', '2025-09-30 16:00:00'),
(231, 'CHK-81363943', 'ayam potong', 3, 1, 'kg', '100.00', '50.00', '2025-10-15 07:37:33', '2025-10-15 07:37:33'),
(232, 'CHK-25831794', 'garam', 2, 3, 'pack', '20.00', '10.00', '2025-10-15 07:44:56', '2025-10-15 07:44:56');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_10_14_add_fields_to_users_table', 1),
(5, '2025_10_13_020119_create_categories_table', 1),
(6, '2025_10_13_020418_create_suppliers_table', 1),
(7, '2025_10_13_020419_create_items_table', 1),
(8, '2025_10_14_001100_create_stock_transactions_table', 1),
(9, '2025_10_14_001200_create_sessions_table', 1),
(10, '2025_10_15_013222_create_recipes_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prep_time` int NOT NULL DEFAULT '0',
  `cook_time` int NOT NULL DEFAULT '0',
  `servings` int NOT NULL DEFAULT '1',
  `difficulty` enum('mudah','sedang','sulit') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mudah',
  `ingredients` json NOT NULL,
  `instructions` json NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`id`, `name`, `slug`, `description`, `image`, `prep_time`, `cook_time`, `servings`, `difficulty`, `ingredients`, `instructions`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(2, 'Caesar Salad Dressing Chicking', 'caesar-salad-dressing-chicking', 'Resep Caesar Salad Dressing khas Chicking dengan cita rasa segar dan gurih. Perpaduan sempurna antara sayuran segar dan saus caesar yang creamy.', 'recipes/1760508561_caesar-salad-dressing-chicking.jpg', 15, 0, 4, 'mudah', '[\"30 gr Mayo Salad\", \"0.5 porsi Tandoori Chickpop\", \"90 gr Lettuce (selada)\", \"30 gr Kol Merah\", \"20 gr Kol Putih\", \"20 gr Wortel\", \"20 gr Cucumber (timun)\", \"10 gr Corn (jagung)\", \"20 gr Tomato (tomat)\", \"20 gr Onion (bawang bombay)\", \"5 gr Parmesan Cheese\", \"1 pcs Paper Wrap\", \"1 pcs Box Kotak 750\", \"1 pcs Plastic Hand Glove\"]', '[\"Siapkan semua bahan dan pastikan sayuran sudah dicuci bersih\", \"Potong lettuce, kol merah, dan kol putih dengan ukuran sedang (bite size)\", \"Iris tipis wortel dan cucumber sesuai selera\", \"Potong tomat menjadi potongan sedang, buang bijinya jika perlu\", \"Iris bawang bombay tipis-tipis\", \"Dalam mangkuk besar, campurkan semua sayuran: lettuce, kol merah, kol putih, wortel, cucumber, dan jagung\", \"Tambahkan potongan tomat dan bawang bombay ke dalam campuran sayuran\", \"Siapkan Tandoori Chickpop yang sudah dimasak dan potong sesuai selera\", \"Dalam mangkuk terpisah, siapkan mayo salad sebagai dressing\", \"Tuang mayo salad ke atas campuran sayuran\", \"Aduk perlahan hingga semua sayuran terbalut dressing dengan merata\", \"Tambahkan Tandoori Chickpop di atas salad\", \"Taburkan parmesan cheese sebagai topping\", \"Kemas dalam box kotak 750ml dengan rapi\", \"Bungkus dengan paper wrap dan sediakan plastic hand glove untuk pelanggan\"]', 'Pastikan semua sayuran dalam kondisi segar dan sudah dicuci bersih. Mayo salad bisa diganti dengan caesar dressing buatan sendiri untuk rasa yang lebih otentik. Tandoori Chickpop sebaiknya masih hangat saat disajikan untuk hasil terbaik.', 'published', '2025-10-15 04:56:18', '2025-10-15 06:09:21'),
(3, 'Condiment Salad Mayo', 'condiment-salad-mayo', 'Campuran sayuran segar dengan dressing salad mayo yang lembut dan gurih. Cocok sebagai pelengkap hidangan utama atau sebagai salad ringan.', 'recipes/1760509197_condiment-salad-mayo.jpg', 10, 0, 1, 'mudah', '[\"20 gr Salad Mayo\", \"30 gr Lettuce\", \"10 gr Kol Merah\", \"10 gr Corn\", \"5 gr Onion\"]', '[\"Cuci bersih semua sayuran (lettuce, kol merah, dan jagung).\", \"Iris halus bawang bombai.\", \"Tiriskan semua bahan hingga tidak ada air tersisa.\", \"Campurkan semua bahan dalam mangkuk besar.\", \"Tambahkan salad mayo dan aduk hingga rata.\", \"Sajikan segera atau simpan di chiller sebelum disajikan.\"]', 'Gunakan sayuran segar agar rasa lebih renyah dan segar. Bisa tambahkan sedikit lada hitam untuk rasa lebih kuat.', 'published', '2025-10-15 05:00:21', '2025-10-15 06:19:57'),
(4, 'Sambel Geprek', 'sambel-geprek', 'Sambel pedas khas ayam geprek dengan aroma bawang dan cita rasa gurih dari masako. Cocok untuk pelengkap ayam goreng, tahu, atau tempe.', 'recipes/1760511270_sambel-geprek.jpeg', 10, 5, 1, 'mudah', '[\"8 gr Bawang Putih 1 pcs\", \"7 gr Bawang Merah 1 pcs\", \"7 gr Cabai rawit 5 pcs\", \"4 gr Masako rasa Ayam 1 tsp\", \"5 gr Gula pasir 1 tsp\"]', '[\"Kupas bawang putih dan bawang merah, kemudian goreng sebentar hingga harum.\", \"Masukkan bawang goreng dan cabai rawit ke dalam cobek.\", \"Tambahkan masako dan gula pasir.\", \"Ulek hingga halus dan tercampur rata.\", \"Sambel geprek siap disajikan bersama ayam goreng atau lauk lainnya.\"]', 'Jika ingin rasa lebih pedas, tambahkan jumlah cabai rawit sesuai selera. Bisa juga ditambah sedikit minyak panas dari penggorengan ayam agar lebih gurih.', 'published', '2025-10-15 05:01:43', '2025-10-15 06:54:30'),
(5, 'Kambing', 'sop-produksi-kambing', 'Sop kambing khas Chicking yang gurih dan kaya rempah, menggunakan iga kambing yang dipresto bersama bumbu halus hingga empuk dan beraroma sedap.', 'recipes/1760511335_kambing.jpg', 30, 60, 4, 'sedang', '[\"1,000 gr Iga depan ada daging / paha depan tulang (Bahan Utama)\", \"20 gr Grill Powder marinasi (Bahan Utama)\", \"2,000 ml Air (Presto Daging)\", \"5 pcs Daun Salam (Presto Daging)\", \"1 pcs Daun Jeruk (Presto Daging)\", \"1 pcs Jahe (Presto Daging)\", \"1 pcs Serai (Presto Daging)\", \"1 pcs Kayu Manis (Presto Daging)\", \"40 gr Bawang Merah 7 siung (Bumbu Daging (diblender))\", \"25 gr Bawang Putih 4 siung (Bumbu Daging (diblender))\", \"15 gr Cabai Merah Besar 3 pcs (Bumbu Daging (diblender))\", \"9 gr Merica 1/2 sendok teh (Bumbu Daging (diblender))\", \"10 gr Kemiri 5 pcs (Bumbu Daging (diblender))\", \"30 gr Jahe 1 ruas digeprek (Bumbu Tambahan)\", \"3 gr Daun Salam 3 lembar (Bumbu Tambahan)\", \"5 gr Serai 1 batang (Bumbu Tambahan)\", \"10 gr Petis 1 tspoon (Bumbu Tambahan)\", \"10 gr Masako 1 tspoon (Bumbu Tambahan)\", \"8 gr Garam 1/2 tspoon (Bumbu Tambahan)\", \"10 ml Kecap 2 tspoon (Bumbu Tambahan)\", \"300 ml Air (Bumbu Tambahan)\"]', '[\"Bersihkan iga kambing, potong sesuai kebutuhan.\", \"Lumuri dengan grill powder marinasi, diamkan selama beberapa menit.\", \"Masukkan daging ke dalam presto bersama bahan presto (air, daun salam, daun jeruk, jahe, serai, dan kayu manis). Presto hingga daging empuk.\", \"Haluskan bahan bumbu blender: bawang merah, bawang putih, cabai merah besar, merica, dan kemiri.\", \"Tumis bumbu halus hingga harum, lalu masukkan jahe geprek, daun salam, serai, petis, masako, dan garam.\", \"Tambahkan daging yang telah dipresto bersama sedikit air presto.\", \"Masukkan kecap dan air, masak hingga kuah mendidih dan bumbu meresap.\", \"Cicipi dan sesuaikan rasa, kemudian sajikan selagi hangat.\"]', 'Gunakan daging kambing muda agar lebih empuk. Jika tidak ada presto, rebus daging lebih lama hingga lunak.', 'published', '2025-10-15 05:05:02', '2025-10-15 06:55:35'),
(6, 'Spicy Chicken Marination', 'spicy-chicken-marination', 'Proses marinasi ayam dengan bumbu pedas khas Chicking untuk menghasilkan rasa gurih dan pedas yang meresap.', 'recipes/1760511385_spicy-chicken-marination.jpeg', 10, 0, 10, 'mudah', '[\"10 heads Whole Chicken\", \"450 grams Spicy Powder\", \"1 liter Cold Water\"]', '[\"Campurkan spicy powder dengan air dingin hingga larut.\", \"Masukkan ayam ke dalam larutan bumbu.\", \"Rendam selama 4 menit agar bumbu meresap sempurna.\", \"Setelah marinasi, ayam siap untuk proses selanjutnya (coating atau frying).\"]', 'Gunakan air benar-benar dingin untuk menjaga tekstur ayam tetap segar.', 'published', '2025-10-15 05:06:53', '2025-10-15 06:56:25'),
(7, 'Original Chicken Marination', 'original-chicken-marination', 'Resep marinasi ayam original khas Chicking dengan rasa gurih ringan dan tekstur lembut.', 'recipes/1760510843_original-chicken-marination.jpg', 10, 0, 10, 'mudah', '[\"10 heads Whole Chicken\", \"95 grams Regular Marinade Powder\", \"1.5 liters Cold Water\"]', '[\"Campurkan regular marinade powder dengan air dingin hingga rata.\", \"Masukkan ayam ke dalam campuran.\", \"Rendam selama 4 menit hingga bumbu terserap dengan baik.\"]', 'Bisa disesuaikan dengan tambahan 100 gram bumbu jika ingin rasa lebih kuat.', 'published', '2025-10-15 05:06:53', '2025-10-15 06:47:23'),
(8, 'Grilled Chicken Marination', 'grilled-chicken-marination', 'Marinasi ayam tanpa kulit dengan bumbu grill khas untuk menghasilkan rasa smoky yang nikmat.', 'recipes/1760509977_grilled-chicken-marination.jpeg', 10, 0, 10, 'mudah', '[\"10 heads Skinless Chicken\", \"400 grams Grill Marinade Powder\", \"1 liter Cold Water\"]', '[\"Larutkan grill marinade powder dalam air dingin.\", \"Masukkan ayam skinless ke dalam larutan bumbu.\", \"Marinasi selama 4 menit hingga rata.\", \"Siap untuk proses pemanggangan atau roasting.\"]', 'Gunakan suhu ruang sebelum grilling agar bumbu lebih meresap.', 'published', '2025-10-15 05:06:53', '2025-10-15 06:32:57'),
(9, 'Crunchy-Strips Chickpop Marination', 'crunchy-strips-chickpop-marination', 'Proses marinasi daging ayam tanpa tulang untuk menu Chickpop agar gurih, lembut, dan renyah saat digoreng.', 'recipes/1760509516_crunchy-strips-chickpop-marination.png', 10, 0, 1, 'mudah', '[\"1,000 grams Boneless Breast\", \"150 grams Spicy Powder\", \"50 ml Cold Water\"]', '[\"Campurkan spicy powder dan air dingin dalam wadah.\", \"Masukkan potongan ayam boneless ke dalam campuran.\", \"Aduk hingga semua bagian ayam terlapisi rata.\", \"Diamkan selama 3 menit sebelum dilanjutkan ke tahap coating.\"]', 'Gunakan daging ayam segar agar hasilnya lebih juicy.', 'published', '2025-10-15 05:06:53', '2025-10-15 06:25:16'),
(10, 'Whole Chicken Marination', 'whole-chicken-marination', 'Proses marinasi untuk ayam utuh dengan bumbu grill khas Chicking agar rasa meresap hingga ke dalam daging.', 'recipes/1760511737_whole-chicken-marination.jpg', 10, 0, 1, 'mudah', '[\"1 head Whole Chicken\", \"32 grams Grill Powder\", \"100 ml Cold Water\"]', '[\"Campurkan grill powder dengan air dingin.\", \"Lumuri ayam secara merata dengan campuran tersebut.\", \"Rendam atau diamkan selama 3 menit.\", \"Siap digunakan untuk proses roasting atau frying.\"]', 'Gunakan wadah tertutup agar marinasi lebih merata.', 'published', '2025-10-15 05:06:53', '2025-10-15 07:02:17'),
(11, 'Bumbu Suntik (Injection Seasoning)', 'bumbu-suntik', 'Larutan bumbu suntik untuk ayam utuh atau potongan, digunakan agar rasa bumbu masuk hingga ke dalam serat daging.', 'recipes/1760508439_bumbu-suntik-injection-seasoning.jpg', 5, 0, 9, 'mudah', '[\"1 pack Bumbu Suntik Ori / Hot\", \"2 liters Cold Water\"]', '[\"Blender bumbu suntik dan air dingin selama 5 menit hingga larut sempurna.\", \"Gunakan alat suntik makanan untuk menyuntikkan larutan ke bagian ayam.\", \"Suntik PA dan DD masing-masing 3x, PB dan SY 3x, dan Whole Chicken 9x.\", \"Simpan ayam dalam suhu dingin setelah proses suntik selesai.\"]', 'Gunakan air dingin (1.5 liter hingga 2 liter) untuk menjaga stabilitas rasa. Pastikan alat suntik higienis.', 'published', '2025-10-15 05:06:53', '2025-10-15 06:07:19'),
(12, 'Mayo Salad', 'mayo-salad', 'Campuran mayo segar dengan dressing dan sedikit air untuk menghasilkan tekstur lembut dan rasa gurih.', 'recipes/1760510248_mayo-salad.jpg', 5, 0, 10, 'mudah', '[\"100 gr Mayonnaise - Regular\", \"100 gr Kewpie Salad Dressing\", \"10 gr Hot Lava\", \"100 ml Water\"]', '[\"Campurkan semua bahan ke dalam wadah bersih.\", \"Aduk hingga rata dan tekstur menjadi lembut.\", \"Simpan dalam chiller sebelum disajikan.\"]', 'Gunakan air dingin agar tekstur tidak terpisah.', 'published', '2025-10-15 05:10:37', '2025-10-15 06:37:28'),
(13, 'Royal Wrap Sauce Mix', 'royal-wrap-sauce-mix', 'Saus khas untuk Royal Wrap, kombinasi mayones, bumbu reguler, dan Maggi cube.', 'recipes/1760511186_royal-wrap-sauce-mix.jpeg', 5, 0, 10, 'mudah', '[\"3,500 gr Mayonnaise - Regular\", \"80 gr Regular Spices Powder\", \"3 sachet Maggi Cube\"]', '[\"Campurkan seluruh bahan dalam wadah besar.\", \"Aduk rata hingga homogen.\", \"Simpan dalam suhu dingin sebelum digunakan.\"]', 'Gunakan mixer untuk hasil yang lebih lembut.', 'published', '2025-10-15 05:10:52', '2025-10-15 06:53:06'),
(14, 'Tandoori Sauce Mix', 'tandoori-sauce-mix', 'Saus pedas dengan cita rasa khas tandoori India, cocok untuk ayam panggang atau goreng.', 'recipes/1760511622_tandoori-sauce-mix.jpg', 5, 0, 10, 'mudah', '[\"100 gr Mayonnaise - Regular\", \"10 gr Chilli Sauce\", \"10 gr Tandoori Powder\"]', '[\"Masukkan semua bahan ke dalam wadah kecil.\", \"Aduk rata hingga tercampur sempurna.\", \"Siap digunakan sebagai saus pelengkap.\"]', 'Gunakan cabai bubuk ekstra untuk rasa lebih kuat.', 'published', '2025-10-15 05:11:12', '2025-10-15 07:00:22'),
(15, 'Regular Breading Powder', 'regular-breading-powder', 'Campuran tepung dan bumbu untuk melapisi ayam goreng khas Chicking.', 'recipes/1760510908_regular-breading-powder.jpeg', 10, 0, 20, 'sedang', '[\"10,000 gr Wheat Flour\", \"900 gr Regular Spices Powder\", \"100 gr Milk Powder\", \"1,000 gr Salt\"]', '[\"Campurkan semua bahan kering dalam wadah besar.\", \"Aduk rata hingga homogen.\", \"Simpan dalam wadah tertutup agar tetap kering.\"]', 'Pastikan wadah penyimpanan kedap udara agar tepung tidak lembab.', 'published', '2025-10-15 05:11:26', '2025-10-15 06:48:28'),
(16, 'BBQ Sauce Mix', 'bbq-sauce-mix', 'Saus BBQ khas dengan rasa manis pedas dan aroma smoky.', 'recipes/1760508192_bbq-sauce-mix.jpg', 5, 0, 10, 'mudah', '[\"150 gr Barbeque Sauce\", \"200 gr Chilli Sauce\", \"150 ml Water\"]', '[\"Campurkan seluruh bahan ke dalam panci kecil.\", \"Masak dengan api kecil hingga mengental.\", \"Diamkan hingga suhu ruang sebelum disajikan.\"]', 'Dapat disimpan dalam chiller maksimal 3 hari.', 'published', '2025-10-15 05:11:42', '2025-10-15 06:03:12'),
(17, 'Tandoori Burger', 'tandoori-burger', 'Burger dengan patty crunchy dan saus tandoori khas, memberikan rasa pedas gurih dengan sentuhan rempah India.', 'recipes/1760511439_tandoori-burger.jpg', 10, 5, 1, 'mudah', '[\"1 pcs Crown Bun (4.5\\\")\", \"14 g Tandoori Sauce\", \"14 g Lettuce\", \"1 pcs Crunchy Patty (Tandoori Sprinkled)\", \"1 pcs Heel Bun (4.5\\\")\"]', '[\"Letakkan heel bun sebagai dasar.\", \"Tambahkan crunchy patty di atasnya.\", \"Tuangkan tandoori sauce di atas patty.\", \"Tambahkan lettuce segar.\", \"Tutup dengan crown bun dan siap disajikan.\"]', 'Gunakan saus tandoori dalam jumlah pas agar tidak terlalu pedas.', 'published', '2025-10-15 05:14:05', '2025-10-15 06:57:19'),
(18, 'Royal Crunchy/Fillet Burger', 'royal-crunchy-fillet-burger', 'Burger ayam renyah khas Chicking dengan mayones lembut dan sayuran segar.', 'recipes/1760510967_royal-crunchyfillet-burger.jpeg', 10, 5, 1, 'mudah', '[\"1 pcs Crown Bun (4.5\\\")\", \"14 g Mayonnaise\", \"14 g Lettuce\", \"1 pcs Crunchy / Fillet Patty\", \"1 pcs Heel Bun (4.5\\\")\"]', '[\"Letakkan heel bun sebagai dasar.\", \"Tambahkan crunchy atau fillet patty di atasnya.\", \"Oleskan mayonnaise secara merata.\", \"Tambahkan lettuce segar di atasnya.\", \"Tutup dengan crown bun.\"]', 'Gunakan patty panas agar mayones sedikit meleleh dan terasa creamy.', 'published', '2025-10-15 05:15:00', '2025-10-15 06:49:27'),
(19, 'Beef Burger', 'beef-burger', 'Burger daging sapi premium dengan saus black pepper mayonnaise dan topping segar.', 'recipes/1760508319_beef-burger.jpg', 10, 5, 1, 'mudah', '[\"1 pcs Crown Bun (4.5\\\")\", \"14 g Black Pepper Mayonnaise\", \"14 g Lettuce\", \"5 slices Cucumber Pickle\", \"3 slices Onion\", \"2 slices Tomato\", \"1 slice Cheese\", \"100 g Big Beef Patty\", \"1 pcs Heel Bun (4.5\\\")\"]', '[\"Letakkan heel bun sebagai dasar.\", \"Tambahkan big beef patty yang sudah matang.\", \"Tambahkan selembar keju di atas patty.\", \"Letakkan irisan tomat, bawang, dan pickle secara berlapis.\", \"Tambahkan lettuce dan black pepper mayonnaise.\", \"Tutup dengan crown bun.\"]', 'Gunakan daging sapi kualitas baik untuk rasa yang lebih juicy.', 'published', '2025-10-15 05:15:12', '2025-10-15 06:05:19'),
(20, 'Double Royal Crunchy', 'double-royal-crunchy', 'Burger ayam ganda dengan lapisan keju, saus mayonnaise, dan lettuce segar, menghasilkan rasa creamy dan gurih.', 'recipes/1760509907_double-royal-crunchy.jpg', 12, 6, 1, 'mudah', '[\"1 pcs Crown Bun (4.5\\\")\", \"14 g Mayonnaise\", \"14 g Lettuce\", \"1 pcs Crunchy/Fillet Patty\", \"1 slice Cheese\", \"1 pcs Crunchy/Fillet Patty\", \"1 pcs Heel Bun (4.5\\\")\"]', '[\"Letakkan heel bun sebagai dasar.\", \"Tambahkan crunchy/fillet patty pertama.\", \"Letakkan selembar keju di atas patty.\", \"Tambahkan patty kedua di atas keju.\", \"Tambahkan lettuce dan oleskan mayonnaise.\", \"Tutup dengan crown bun.\"]', 'Gunakan patty ayam panas agar keju meleleh sempurna.', 'published', '2025-10-15 05:17:55', '2025-10-15 06:31:47'),
(21, 'Double Beef Burger', 'double-beef-burger', 'Burger daging sapi ganda dengan dua lapisan keju, sayuran segar, dan saus black pepper mayonnaise yang khas.', 'recipes/1760509745_double-beef-burger.jpg', 12, 6, 1, 'mudah', '[\"1 pcs Crown Bun (4.5\\\")\", \"14 g Black Pepper Mayonnaise\", \"14 g Lettuce\", \"5 slices Cucumber Pickles\", \"3 slices Onion\", \"2 slices Tomato\", \"1 slice Cheese\", \"100 g Big Beef Patty\", \"1 slice Cheese\", \"100 g Big Beef Patty\", \"1 pcs Heel Bun (4.5\\\")\"]', '[\"Letakkan heel bun sebagai dasar.\", \"Tambahkan big beef patty pertama dan letakkan selembar keju di atasnya.\", \"Tambahkan patty kedua di atas keju.\", \"Letakkan keju lagi di atas patty kedua.\", \"Tambahkan irisan tomat, bawang, dan pickle.\", \"Tambahkan lettuce dan oleskan black pepper mayonnaise.\", \"Tutup dengan crown bun.\"]', 'Pastikan kedua patty matang sempurna agar cita rasa dagingnya maksimal.', 'published', '2025-10-15 05:18:12', '2025-10-15 06:29:05'),
(22, 'Crunchy Burger (6\")', 'crunchy-burger-6', 'Burger ayam dengan patty renyah, keju, lettuce segar, dan mayonnaise lembut dalam roti panjang 6 inci.', 'recipes/1760509314_crunchy-burger-6.jpg', 10, 5, 1, 'mudah', '[\"1 pcs Crown Bun (6\\\")\", \"14 g Mayonnaise\", \"14 g Lettuce\", \"1 pcs Crunchy/Fillet Patty\", \"14 g Cheese\", \"1 pcs Heel Bun (6\\\")\"]', '[\"Letakkan heel bun di bawah.\", \"Tambahkan cheese slice di atasnya.\", \"Letakkan crunchy/fillet patty.\", \"Tambahkan lettuce dan mayonnaise.\", \"Tutup dengan crown bun.\"]', 'Gunakan patty ayam yang baru digoreng agar tetap renyah.', 'published', '2025-10-15 05:19:59', '2025-10-15 06:21:54'),
(23, 'Mexi-Crunchy Burger (6\")', 'mexi-crunchy-burger-6', 'Burger ayam renyah dengan saus khas Meksiko, keju, dan acar timun yang memberikan rasa segar dan pedas gurih.', 'recipes/1760510370_mexi-crunchy-burger-6.jpeg', 10, 5, 1, 'mudah', '[\"1 pcs Crown Bun (6\\\")\", \"14 g Mexican Sauce\", \"14 g Lettuce\", \"5 slices Cucumber Pickles\", \"1 pcs Crunchy Patty\", \"14 g Cheese\", \"1 pcs Heel Bun (6\\\")\"]', '[\"Letakkan heel bun di bawah.\", \"Tambahkan keju di atas bun.\", \"Letakkan crunchy patty.\", \"Tambahkan cucumber pickles, lettuce, dan oleskan Mexican sauce.\", \"Tutup dengan crown bun.\"]', 'Gunakan Mexican sauce dingin agar rasa pedas-manisnya lebih seimbang.', 'published', '2025-10-15 05:20:11', '2025-10-15 06:39:30'),
(24, 'Mexita Burger (6\")', 'mexita-burger-6', 'Burger daging khas dengan saus chili mayonnaise, tomat segar, dan acar yang memberikan sensasi pedas gurih.', 'recipes/1760510427_mexita-burger-6.png', 10, 5, 1, 'mudah', '[\"1 pcs Crown Bun (6\\\")\", \"10 g Chilli Mayonnaise\", \"14 g Lettuce\", \"3 slices Tomato\", \"5 slices Cucumber Pickles\", \"1 pcs Mexita Patty\", \"10 g Chilli Mayonnaise\", \"1 pcs Heel Bun (6\\\")\"]', '[\"Letakkan heel bun di bawah dan olesi dengan sedikit chilli mayonnaise.\", \"Tambahkan Mexita patty.\", \"Tambahkan irisan tomato dan cucumber pickles.\", \"Letakkan lettuce di atasnya dan tambahkan lagi chilli mayonnaise.\", \"Tutup dengan crown bun.\"]', 'Gunakan tomat segar agar menyeimbangkan rasa pedas dari chilli mayonnaise.', 'published', '2025-10-15 05:20:21', '2025-10-15 06:40:27'),
(25, 'Nasi Mandhi', 'nasi-mandhi', 'Nasi aromatik khas Timur Tengah yang dimasak dengan bumbu Mandhi dan nasi basmati.', 'recipes/1760510704_nasi-mandhi.jpeg', 10, 20, 4, 'sedang', '[\"1,000 gr Basmati Rice\", \"1,800 ml Air Dingin\", \"100 gr Bumbu Nasi Mandhi\", \"25 gr Bawang Putih\", \"35 gr Bawang Bombay\", \"40 gr Butter Unsalted\", \"50 gr Minyak Goreng\", \"1 pcs Arang Briket\"]', '[\"Cuci bersih beras basmati dan tiriskan.\", \"Panaskan minyak dan butter, tumis bawang bombay serta bawang putih hingga harum.\", \"Masukkan bumbu nasi Mandhi dan aduk rata.\", \"Tambahkan air dingin dan beras, lalu masak selama 15 menit.\", \"Setelah matang, lakukan proses steam selama 5 menit untuk hasil lebih pulen.\", \"Tambahkan arang briket di atas nasi untuk aroma khas Mandhi.\"]', 'Gunakan beras basmati berkualitas agar hasilnya pulen dan harum.', 'published', '2025-10-15 05:23:17', '2025-10-15 06:45:04'),
(26, 'Nasi Biryani', 'nasi-biryani', 'Nasi berbumbu khas India dengan perpaduan rempah dan aroma gurih yang menggugah selera.', 'recipes/1760510558_nasi-biryani.jpg', 10, 15, 4, 'sedang', '[\"1,000 gr Basmati Rice\", \"1,800 ml Air Dingin\", \"80 gr Bumbu Nasi Biryani\", \"25 gr Bawang Putih\", \"35 gr Cabai Hijau Besar\", \"25 gr Paprika Hijau\", \"40 gr Butter Unsalted\", \"50 gr Minyak Goreng\"]', '[\"Cuci bersih beras basmati dan tiriskan.\", \"Tumis bawang putih, cabai hijau, dan paprika dengan butter serta minyak hingga harum.\", \"Tambahkan bumbu biryani, aduk rata hingga tercampur sempurna.\", \"Masukkan beras dan air dingin, lalu masak selama 15 menit hingga matang.\", \"Lakukan proses steam selama 5 menit untuk menghasilkan tekstur nasi yang pulen.\"]', 'Tambahkan potongan daging kambing atau ayam agar cita rasa lebih khas.', 'published', '2025-10-15 05:23:30', '2025-10-15 06:42:38'),
(27, 'Nasi Goreng Biryani', 'nasi-goreng-biryani', 'Perpaduan nasi biryani dan cita rasa nasi goreng dengan tambahan ayam atau kambing suwir.', 'recipes/1760510619_nasi-goreng-biryani.jpg', 10, 15, 2, 'mudah', '[\"200 gr Nasi Biryani\", \"20 gr Bumbu NasGore Chicking\", \"10 gr Sambal Bawang\", \"10 gr Corn Frozen\", \"1 pcs Telur Orak Arik\", \"50 gr Kambing/Ayam Suwir\"]', '[\"Panaskan sedikit minyak, lalu tumis sambal bawang dan bumbu nasgore chicking hingga harum.\", \"Tambahkan telur orak arik dan suwiran ayam atau kambing, aduk hingga rata.\", \"Masukkan nasi biryani dan corn frozen, lalu masak selama 15 menit.\", \"Setelah matang, steam selama 5 menit sebelum disajikan.\"]', 'Gunakan nasi biryani yang sudah dingin agar tidak lembek saat digoreng.', 'published', '2025-10-15 05:23:43', '2025-10-15 06:43:39'),
(28, 'Onion Rings', 'onion-rings', 'Camilan bawang bombay goreng berbalut tepung pedas yang renyah dan gurih.', 'recipes/1760510766_onion-rings.jpeg', 10, 15, 2, 'mudah', '[\"300 gr Bawang Bombay\", \"250 gr Bumbu Tepung Spicy (Sasa Chicking)\", \"35 gr Tepung Segitiga Biru\", \"3 tetes Merah Cabe Kupu-Kupu\"]', '[\"Iris bawang bombay menjadi cincin-cincin tebal.\", \"Campur bumbu tepung spicy, tepung segitiga biru, dan merah cabe kupu-kupu.\", \"Balurkan cincin bawang ke adonan hingga terlapisi rata.\", \"Goreng hingga keemasan selama 15 menit.\", \"Setelah digoreng, steam selama 5 menit agar teksturnya lebih lembut di dalam.\"]', '1 porsi berisi 6–7 pcs tergantung ukuran cincin bawang.', 'published', '2025-10-15 05:24:48', '2025-10-15 06:46:06');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('mARFMPko4jaelTYGk8Nowa9oV6YFaI89NF3KcodS', 1, '192.168.1.14', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiN2RJaDU0Vm9wRFk1ZnBnUnBCVTFVSE1aTVV3anVMeGJXYWluZEpBWSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzI6Imh0dHA6Ly8xOTIuMTY4LjEuMTQ6ODAwMC9yZWNpcGVzIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1760577803);

-- --------------------------------------------------------

--
-- Table structure for table `stock_transactions`
--

CREATE TABLE `stock_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `transaction_type` enum('IN','OUT','ADJUSTMENT') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_transactions`
--

INSERT INTO `stock_transactions` (`id`, `item_id`, `user_id`, `transaction_type`, `quantity`, `notes`, `transaction_date`, `created_at`, `updated_at`) VALUES
(8, 21, 1, 'OUT', '10.00', 'Salah Pencatatan', '2025-10-13 22:53:50', '2025-10-13 22:53:50', '2025-10-13 22:53:50'),
(9, 188, 2, 'IN', '1.00', 'testing', '2025-10-14 19:30:00', '2025-10-14 19:31:10', '2025-10-14 19:31:10'),
(10, 21, 2, 'IN', '5.00', 'restock', '2025-10-15 07:45:00', '2025-10-15 07:46:25', '2025-10-15 07:46:25');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint UNSIGNED NOT NULL,
  `supplier_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_person` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `supplier_name`, `contact_person`, `phone`, `address`, `created_at`, `updated_at`) VALUES
(1, 'Chicken Go', NULL, '0852-5028-8919', NULL, '2025-10-13 21:45:29', '2025-10-13 21:45:29'),
(2, 'Yami Hidroponik', NULL, '0877-0873-1939', NULL, '2025-10-13 21:45:49', '2025-10-13 21:45:49'),
(3, 'PT. Sumber Sehat Makmur', NULL, '0813-4835-1136', NULL, '2025-10-13 21:46:06', '2025-10-13 21:46:06'),
(4, 'PT. Mulia Anugerah Distribusindo', NULL, '0821-1199-0700', NULL, '2025-10-13 21:46:25', '2025-10-13 21:46:25'),
(5, 'Wahana Inti Sejati', NULL, '0822-5501-6500', NULL, '2025-10-13 21:46:44', '2025-10-13 21:46:44'),
(6, 'PT. Ciomas Adisatwa', NULL, '0812-6772-8379', NULL, '2025-10-13 21:47:00', '2025-10-13 21:47:00'),
(7, 'PT. Sukanda Djaya', NULL, '0813-4534-2659', NULL, '2025-10-13 21:47:20', '2025-10-13 21:47:20'),
(8, 'Fajar Lestari Abadi', NULL, '08124272468', NULL, '2025-10-13 21:47:35', '2025-10-13 21:47:35'),
(9, 'PT. Joyday Segar Borneo', NULL, '0819-3824-8888', NULL, '2025-10-13 21:47:52', '2025-10-13 21:47:52'),
(10, 'Jaya Loka Banjarmasin', NULL, '0813-5771-1588', NULL, '2025-10-13 21:48:09', '2025-10-13 21:48:09'),
(11, 'Sahara', NULL, '0812-5472-7500', NULL, '2025-10-13 21:48:36', '2025-10-13 21:48:36'),
(12, 'CV. Anugerah Agung', NULL, '08115132002', NULL, '2025-10-13 21:48:53', '2025-10-13 21:48:53'),
(13, 'PT. Sarimekar Cahaya Persada', NULL, '0896-9289-8472', NULL, '2025-10-13 21:49:12', '2025-10-13 21:49:12'),
(14, 'PT. RAJA AYAM DUBAI', NULL, '08176777683', NULL, '2025-10-13 21:49:30', '2025-10-13 21:49:30'),
(15, 'House Of Culinare', NULL, '0813-2300-0918', NULL, '2025-10-13 21:49:44', '2025-10-13 21:49:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Staf',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `phone`, `password`, `full_name`, `role`, `status`, `last_login_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'admin', 'exampel@gmail.com', '0800880', '$2y$12$RGwq25ovlNOD.kXdY.Aae.4HUgsPO7HMDgmZuOxGisTrYLrVPIzH2', 'super admin', 'admin', 'active', '2025-10-16 01:20:06', '2025-10-15 02:55:31', '2025-10-16 01:20:06', NULL),
(2, 'sahlil', 'sahlil@gmail.com', '08579999999', '$2y$12$oOstUdrnMKICpD7olnvFAusx2QP0G8yiVh.M.2AolzQVfPLztrnpO', 'sahlil', 'staff', 'active', '2025-10-15 04:07:21', '2025-10-14 19:15:24', '2025-10-15 04:07:21', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_category_name_unique` (`category_name`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `items_sku_unique` (`sku`),
  ADD KEY `items_category_id_foreign` (`category_id`),
  ADD KEY `items_supplier_id_foreign` (`supplier_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `recipes_slug_unique` (`slug`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_transactions_item_id_foreign` (`item_id`),
  ADD KEY `stock_transactions_user_id_foreign` (`user_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=233;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `items_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  ADD CONSTRAINT `stock_transactions_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
