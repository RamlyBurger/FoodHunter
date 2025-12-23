-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 20, 2025 at 07:04 AM
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
-- Database: `university_canteen`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `special_request` text DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`cart_id`, `user_id`, `item_id`, `quantity`, `special_request`, `added_at`, `created_at`, `updated_at`) VALUES
(3, 9, 37, 2, 'Extra wasabi', '2025-11-23 17:43:46', NULL, NULL),
(4, 9, 42, 1, 'Extra noodles', '2025-11-23 17:43:46', NULL, NULL),
(5, 10, 6, 1, 'Medium rare', '2025-11-23 17:43:46', NULL, NULL),
(6, 10, 47, 1, NULL, '2025-11-23 17:43:46', NULL, NULL),
(7, 11, 29, 1, 'Dressing on the side', '2025-11-23 17:43:46', NULL, NULL),
(8, 11, 36, 1, NULL, '2025-11-23 17:43:46', NULL, NULL),
(9, 13, 50, 1, NULL, '2025-11-23 17:43:46', NULL, NULL),
(10, 13, 51, 1, NULL, '2025-11-23 17:43:46', NULL, NULL),
(11, 13, 48, 2, 'Less sugar', '2025-11-23 17:43:46', NULL, NULL),
(27, 12, 2, 1, '', '2025-12-19 17:02:06', '2025-12-19 09:02:06', '2025-12-19 09:02:06'),
(28, 8, 61, 1, '', '2025-12-19 20:12:09', '2025-12-19 12:12:09', '2025-12-19 12:12:23');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `created_at`, `updated_at`) VALUES
(1, 'Rice Dishes', NULL, NULL),
(2, 'Noodles', NULL, NULL),
(3, 'Western Food', NULL, NULL),
(4, 'Indian Cuisine', NULL, NULL),
(5, 'Japanese Food', NULL, NULL),
(6, 'Salads & Healthy', NULL, NULL),
(7, 'Beverages', NULL, NULL),
(8, 'Desserts', NULL, NULL),
(9, 'Breakfast', NULL, NULL),
(10, 'Snacks', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_points`
--

CREATE TABLE `loyalty_points` (
  `lp_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `points` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `loyalty_points`
--

INSERT INTO `loyalty_points` (`lp_id`, `user_id`, `points`, `created_at`, `updated_at`) VALUES
(1, 1, 0, NULL, '2025-11-23 17:43:46'),
(2, 2, 0, NULL, '2025-11-23 17:43:46'),
(3, 3, 0, NULL, '2025-11-23 17:43:46'),
(4, 4, 0, NULL, '2025-11-23 17:43:46'),
(5, 5, 0, NULL, '2025-11-23 17:43:46'),
(6, 6, 0, NULL, '2025-11-23 17:43:46'),
(7, 7, 0, NULL, '2025-11-23 17:43:46'),
(8, 8, 37, NULL, '2025-11-25 18:57:53'),
(9, 9, 180, NULL, '2025-11-23 17:43:46'),
(10, 10, 420, NULL, '2025-11-23 17:43:46'),
(11, 11, 95, NULL, '2025-11-23 17:43:46'),
(12, 12, 310, NULL, '2025-11-23 17:43:46'),
(13, 13, 155, NULL, '2025-11-23 17:43:46'),
(14, 14, 275, NULL, '2025-11-23 17:43:46'),
(15, 15, 60, NULL, '2025-11-23 17:43:46'),
(16, 16, 500, NULL, '2025-11-23 17:43:46'),
(17, 17, 380, NULL, '2025-11-23 17:43:46'),
(18, 18, 290, NULL, '2025-11-23 17:43:46'),
(19, 19, 1000, NULL, '2025-11-23 17:43:46'),
(20, 20, 0, '2025-12-19 09:38:49', '2025-12-19 09:38:49');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `vendor_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`item_id`, `category_id`, `vendor_id`, `name`, `description`, `price`, `image_path`, `is_available`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Nasi Lemak Special', 'Fragrant coconut rice with fried chicken, sambal, peanuts, and anchovies', 7.50, '/images/menu/nasi-lemak.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(2, 1, 1, 'Nasi Lemak Rendang', 'Coconut rice with tender beef rendang and condiments', 9.00, '/images/menu/nasi-lemak-rendang.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(3, 1, 1, 'Nasi Lemak Ayam Berempah', 'Spicy marinated fried chicken with coconut rice', 8.50, '/images/menu/nasi-lemak-berempah.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(4, 1, 1, 'Nasi Goreng Kampung', 'Traditional village-style fried rice with anchovies', 6.50, '/images/menu/nasi-goreng.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(5, 1, 1, 'Nasi Tomato Ayam', 'Tomato rice with fried chicken', 7.00, '/images/menu/nasi-tomato.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(6, 3, 2, 'Chicken Chop', 'Grilled chicken chop with black pepper sauce, fries, and coleslaw', 12.00, '/images/menu/chicken-chop.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(7, 3, 2, 'Fish & Chips', 'Crispy battered fish fillet with chunky chips and tartar sauce', 13.50, '/images/menu/fish-chips.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(8, 3, 2, 'Spaghetti Carbonara', 'Creamy carbonara with turkey bacon and mushrooms', 11.00, '/images/menu/carbonara.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(9, 3, 2, 'Beef Burger', 'Juicy beef patty with cheese, lettuce, tomato, and fries', 10.50, '/images/menu/burger.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(10, 3, 2, 'Grilled Lamb Chop', 'Tender grilled lamb chop with mint sauce', 18.00, '/images/menu/lamb-chop.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(11, 3, 2, 'Chicken Lasagna', 'Layered pasta with chicken and cheese', 12.50, '/images/menu/lasagna.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(12, 9, 3, 'Roti Canai Kosong', 'Plain crispy roti canai with curry dhal', 2.50, '/images/menu/roti-kosong.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(13, 9, 3, 'Roti Telur', 'Roti canai with egg filling', 3.50, '/images/menu/roti-telur.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(14, 9, 3, 'Roti Planta', 'Roti with butter and sugar', 3.00, '/images/menu/roti-planta.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(15, 9, 3, 'Roti Boom', 'Fluffy layered roti with condensed milk', 4.00, '/images/menu/roti-boom.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(16, 4, 3, 'Nasi Briyani Ayam', 'Aromatic briyani rice with chicken curry', 8.50, '/images/menu/briyani.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(17, 4, 3, 'Mee Goreng Mamak', 'Spicy fried yellow noodles with prawns', 7.00, '/images/menu/mee-goreng.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(18, 4, 3, 'Maggi Goreng', 'Fried instant noodles with vegetables and egg', 6.50, '/images/menu/maggi-goreng.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(19, 7, 3, 'Teh Tarik', 'Pulled milk tea - Malaysian favorite', 2.50, '/images/menu/teh-tarik.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(20, 7, 3, 'Milo Ais', 'Iced chocolate malt drink', 3.00, '/images/menu/milo-ais.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(21, 2, 4, 'Wantan Mee', 'Egg noodles with wantan dumplings and char siew', 7.50, '/images/menu/wantan-mee.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(22, 2, 4, 'Curry Laksa', 'Spicy coconut curry noodle soup', 8.50, '/images/menu/curry-laksa.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(23, 2, 4, 'Char Kuey Teow', 'Stir-fried flat noodles with prawns and cockles', 8.00, '/images/menu/char-kuey-teow.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(24, 2, 4, 'Hokkien Mee', 'Braised noodles in dark soy sauce', 7.50, '/images/menu/hokkien-mee.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(25, 2, 4, 'Pan Mee Soup', 'Handmade noodle soup with minced pork', 7.00, '/images/menu/pan-mee.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(26, 2, 4, 'Dry Chili Pan Mee', 'Noodles with spicy chili paste and anchovies', 7.50, '/images/menu/chili-pan-mee.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(27, 1, 4, 'Chicken Rice', 'Steamed chicken with fragrant rice', 7.00, '/images/menu/chicken-rice.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(28, 1, 4, 'Roast Duck Rice', 'Sliced roast duck with rice', 9.50, '/images/menu/duck-rice.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(29, 6, 5, 'Caesar Salad', 'Romaine lettuce, parmesan, croutons, and Caesar dressing', 9.50, '/images/menu/caesar-salad.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(30, 6, 5, 'Greek Salad', 'Fresh vegetables with feta cheese and olives', 10.00, '/images/menu/greek-salad.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(31, 6, 5, 'Grilled Chicken Salad', 'Mixed greens with grilled chicken breast', 11.50, '/images/menu/chicken-salad.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(32, 6, 5, 'Tuna Nicoise Salad', 'Tuna, eggs, green beans, and potatoes', 12.00, '/images/menu/tuna-salad.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(33, 6, 5, 'Quinoa Buddha Bowl', 'Quinoa with roasted vegetables and tahini', 11.00, '/images/menu/buddha-bowl.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(34, 6, 5, 'Fruit Salad Cup', 'Fresh seasonal fruits with honey yogurt', 6.50, '/images/menu/fruit-salad.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(35, 7, 5, 'Green Smoothie', 'Spinach, banana, mango, and chia seeds', 7.50, '/images/menu/green-smoothie.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(36, 7, 5, 'Berry Blast Smoothie', 'Mixed berries with yogurt and honey', 8.00, '/images/menu/berry-smoothie.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(37, 5, 6, 'Chicken Teriyaki Bento', 'Grilled teriyaki chicken with rice and sides', 12.50, '/images/menu/teriyaki-bento.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(38, 5, 6, 'Salmon Bento', 'Grilled salmon with Japanese rice', 15.00, '/images/menu/salmon-bento.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(39, 5, 6, 'Tonkatsu Bento', 'Breaded pork cutlet with curry sauce', 13.50, '/images/menu/tonkatsu-bento.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(40, 5, 6, 'Chicken Katsu Curry', 'Crispy chicken with Japanese curry', 12.00, '/images/menu/katsu-curry.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(41, 5, 6, 'Ebi Tempura Set', 'Prawn tempura with rice and miso soup', 14.00, '/images/menu/tempura-set.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(42, 5, 6, 'Ramen Bowl', 'Japanese noodle soup with pork and egg', 11.50, '/images/menu/ramen.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(43, 5, 6, 'Sushi Platter (8pcs)', 'Assorted fresh sushi selection', 16.50, '/images/menu/sushi-platter.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(44, 5, 6, 'Unagi Don', 'Grilled eel on rice with teriyaki sauce', 17.00, '/images/menu/unagi-don.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(45, 7, 7, 'Fresh Orange Juice', 'Freshly squeezed orange juice', 5.50, '/images/menu/orange-juice.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(46, 7, 7, 'Iced Latte', 'Espresso with cold milk and ice', 6.50, '/images/menu/iced-latte.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(47, 7, 7, 'Cappuccino', 'Classic cappuccino with milk foam', 6.00, '/images/menu/cappuccino.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(48, 7, 7, 'Bubble Milk Tea', 'Milk tea with tapioca pearls', 7.00, '/images/menu/bubble-tea.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(49, 7, 7, 'Watermelon Juice', 'Fresh watermelon juice', 5.00, '/images/menu/watermelon-juice.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(50, 8, 7, 'Chocolate Lava Cake', 'Warm chocolate cake with molten center', 8.50, '/images/menu/lava-cake.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(51, 8, 7, 'Tiramisu', 'Classic Italian coffee-flavored dessert', 9.00, '/images/menu/tiramisu.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(52, 8, 7, 'Cendol', 'Traditional Malaysian shaved ice dessert', 5.50, '/images/menu/cendol.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(53, 8, 7, 'Ice Cream Sundae', 'Vanilla ice cream with toppings', 6.50, '/images/menu/sundae.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(54, 8, 7, 'Cheese Cake Slice', 'New York style cheesecake', 8.00, '/images/menu/cheesecake.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(55, 10, 1, 'Curry Puff', 'Crispy pastry with spicy potato filling', 2.50, '/images/menu/curry-puff.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(56, 10, 3, 'Vadai', 'Indian savory donuts', 1.50, '/images/menu/vadai.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(57, 10, 4, 'Spring Rolls (3pcs)', 'Crispy vegetable spring rolls', 5.00, '/images/menu/spring-rolls.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(58, 10, 6, 'Edamame', 'Steamed soybeans with sea salt', 4.50, '/images/menu/edamame.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(59, 10, 2, 'Chicken Wings (5pcs)', 'Crispy fried chicken wings', 8.50, '/images/menu/chicken-wings.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(60, 10, 7, 'Donut (2pcs)', 'Glazed donuts', 4.00, '/images/menu/donuts.jpg', 1, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(61, 1, 1, 'Burger Hack', '', 100.00, '/images/menu/1766172953_WIN_20240918_17_26_43_Pro.jpg', 1, '2025-12-19 11:35:53', '2025-12-19 11:35:53'),
(63, 1, 1, 'Pc', '', 13.00, '/images/menu/1766175041_WIN_20240918_17_26_43_Pro.jpg', 1, '2025-12-19 12:10:41', '2025-12-19 12:10:41'),
(64, 2, 1, 'Pc', '12', 13.00, '/images/menu/1766175071_e.jpeg', 1, '2025-12-19 12:11:11', '2025-12-19 12:11:11');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(15, '2025_11_23_172149_create_wishlists_table', 2),
(16, '0001_01_01_000000_create_users_table', 3),
(17, '0001_01_01_000001_create_cache_table', 3),
(18, '0001_01_01_000002_create_jobs_table', 3),
(19, '2025_01_01_000001_create_categories_table', 3),
(20, '2025_01_02_000001_create_menu_items_table', 3),
(21, '2025_01_03_000001_create_orders_table', 3),
(22, '2025_01_04_000001_create_order_items_table', 3),
(23, '2025_01_05_000001_create_cart_items_table', 3),
(24, '2025_01_06_000001_create_payments_table', 3),
(25, '2025_01_07_000001_create_rewards_table', 3),
(26, '2025_01_08_000001_create_loyalty_points_table', 3),
(27, '2025_01_09_000001_create_user_redeemed_rewards_table', 3),
(28, '2025_01_10_000001_create_pickup_table', 3),
(30, '2025_11_18_132052_create_personal_access_tokens_table', 4),
(31, '2025_11_23_172417_create_wishlists_table', 4),
(32, '2025_11_23_183239_add_columns_to_rewards_table', 5),
(34, '2025_11_23_184105_add_terms_to_rewards_table', 6),
(35, '2025_11_23_184627_add_voucher_code_to_user_redeemed_rewards_table', 7),
(36, '2025_11_26_021703_create_vendor_operating_hours_table', 8),
(37, '2025_11_26_021719_create_vendor_settings_table', 8),
(38, '2025_11_26_021759_create_vendor_notifications_table', 8),
(39, '2025_11_26_024134_create_student_notifications_table', 9);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `vendor_id` bigint(20) UNSIGNED NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','accepted','preparing','ready','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `vendor_id`, `total_price`, `status`, `created_at`, `updated_at`) VALUES
(1, 8, 1, 16.00, 'completed', '2025-11-18 17:43:46', '2025-11-18 17:43:46'),
(2, 9, 2, 25.50, 'completed', '2025-11-19 17:43:46', '2025-11-19 17:43:46'),
(3, 10, 3, 14.50, 'completed', '2025-11-20 17:43:46', '2025-11-20 17:43:46'),
(4, 11, 4, 15.00, 'completed', '2025-11-21 17:43:46', '2025-11-21 17:43:46'),
(5, 12, 5, 23.50, 'completed', '2025-11-22 17:43:46', '2025-11-22 17:43:46'),
(6, 16, 6, 28.50, 'completed', '2025-11-22 17:43:46', '2025-11-22 17:43:46'),
(7, 17, 1, 22.50, 'completed', '2025-11-22 17:43:46', '2025-11-22 17:43:46'),
(8, 13, 2, 24.00, 'preparing', '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(9, 14, 4, 16.50, 'ready', '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(10, 15, 6, 42.00, 'accepted', '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(11, 18, 3, 18.50, 'pending', '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(12, 8, 7, 17.00, 'ready', '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(13, 8, 4, 16.50, 'pending', '2025-11-23 10:07:51', '2025-11-23 10:07:51'),
(14, 8, 1, 45.00, 'completed', '2025-11-23 10:07:51', '2025-11-24 18:32:47'),
(15, 8, 1, 23.00, 'completed', '2025-11-23 10:13:16', '2025-11-24 18:24:15'),
(16, 8, 1, 7.50, 'completed', '2025-11-23 10:23:07', '2025-11-24 18:32:49'),
(17, 8, 1, 22.50, 'completed', '2025-11-23 11:00:32', '2025-11-24 18:24:10'),
(18, 8, 4, 4.50, 'pending', '2025-11-23 11:07:10', '2025-11-23 11:07:10'),
(19, 8, 5, 12.00, 'pending', '2025-11-23 11:10:07', '2025-11-23 11:10:07'),
(20, 8, 1, 5.50, 'completed', '2025-11-24 18:19:00', '2025-11-24 18:31:52'),
(21, 8, 1, 18.50, 'completed', '2025-11-24 18:38:41', '2025-11-24 18:42:34'),
(22, 8, 1, 10.50, 'preparing', '2025-11-24 19:17:24', '2025-11-25 18:51:35'),
(23, 8, 1, 18.50, 'pending', '2025-11-25 18:57:53', '2025-11-25 18:57:53');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `special_request` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `item_id`, `quantity`, `price`, `special_request`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 7.50, NULL, NULL, NULL),
(2, 1, 4, 1, 6.50, 'Extra spicy', NULL, NULL),
(3, 1, 55, 1, 2.50, NULL, NULL, NULL),
(4, 2, 6, 1, 12.00, 'Well done', NULL, NULL),
(5, 2, 9, 1, 10.50, 'No onions', NULL, NULL),
(6, 2, 45, 1, 5.50, NULL, NULL, NULL),
(7, 3, 12, 2, 5.00, NULL, NULL, NULL),
(8, 3, 13, 1, 3.50, NULL, NULL, NULL),
(9, 3, 19, 2, 5.00, 'Less sugar', NULL, NULL),
(10, 4, 21, 1, 7.50, NULL, NULL, NULL),
(11, 4, 22, 1, 8.50, 'Extra spicy', NULL, NULL),
(12, 5, 29, 1, 9.50, 'No croutons', NULL, NULL),
(13, 5, 31, 1, 11.50, NULL, NULL, NULL),
(14, 5, 35, 1, 7.50, NULL, NULL, NULL),
(15, 6, 37, 1, 12.50, NULL, NULL, NULL),
(16, 6, 42, 1, 11.50, NULL, NULL, NULL),
(17, 6, 58, 1, 4.50, NULL, NULL, NULL),
(18, 7, 1, 2, 15.00, NULL, NULL, NULL),
(19, 7, 2, 1, 9.00, NULL, NULL, NULL),
(20, 8, 6, 1, 12.00, NULL, NULL, NULL),
(21, 8, 8, 1, 11.00, NULL, NULL, NULL),
(22, 8, 46, 1, 6.50, NULL, NULL, NULL),
(23, 9, 21, 1, 7.50, NULL, NULL, NULL),
(24, 9, 23, 1, 8.00, NULL, NULL, NULL),
(25, 9, 45, 1, 5.00, NULL, NULL, NULL),
(26, 10, 37, 1, 12.50, NULL, NULL, NULL),
(27, 10, 39, 1, 13.50, NULL, NULL, NULL),
(28, 10, 43, 1, 16.50, NULL, NULL, NULL),
(29, 11, 16, 1, 8.50, NULL, NULL, NULL),
(30, 11, 17, 1, 7.00, NULL, NULL, NULL),
(31, 11, 20, 1, 3.00, NULL, NULL, NULL),
(32, 12, 50, 1, 8.50, NULL, NULL, NULL),
(33, 12, 51, 1, 9.00, NULL, NULL, NULL),
(34, 13, 27, 1, 7.00, NULL, '2025-11-23 10:07:51', '2025-11-23 10:07:51'),
(35, 13, 28, 1, 9.50, NULL, '2025-11-23 10:07:51', '2025-11-23 10:07:51'),
(36, 14, 1, 6, 7.50, '', '2025-11-23 10:07:51', '2025-11-23 10:07:51'),
(37, 15, 1, 1, 7.50, '', '2025-11-23 10:13:16', '2025-11-23 10:13:16'),
(38, 15, 2, 1, 9.00, '', '2025-11-23 10:13:16', '2025-11-23 10:13:16'),
(39, 15, 4, 1, 6.50, '', '2025-11-23 10:13:16', '2025-11-23 10:13:16'),
(40, 16, 1, 1, 7.50, '', '2025-11-23 10:23:07', '2025-11-23 10:23:07'),
(41, 17, 1, 3, 7.50, '', '2025-11-23 11:00:32', '2025-11-23 11:00:32'),
(42, 18, 26, 1, 7.50, '', '2025-11-23 11:07:10', '2025-11-23 11:07:10'),
(43, 19, 30, 1, 10.00, '', '2025-11-23 11:10:07', '2025-11-23 11:10:07'),
(44, 20, 3, 1, 8.50, '', '2025-11-24 18:19:00', '2025-11-24 18:19:00'),
(45, 21, 2, 1, 9.00, '', '2025-11-24 18:38:41', '2025-11-24 18:38:41'),
(46, 21, 1, 1, 7.50, '', '2025-11-24 18:38:41', '2025-11-24 18:38:41'),
(47, 22, 3, 1, 8.50, '', '2025-11-24 19:17:24', '2025-11-24 19:17:24'),
(48, 23, 1, 1, 7.50, '', '2025-11-25 18:57:53', '2025-11-25 18:57:53'),
(49, 23, 2, 1, 9.00, '', '2025-11-25 18:57:53', '2025-11-25 18:57:53');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` enum('online','cash','ewallet') NOT NULL,
  `status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `transaction_ref` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `amount`, `method`, `status`, `transaction_ref`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 1, 16.00, 'online', 'paid', 'TXN20250113001', '2025-11-18 17:43:46', NULL, NULL),
(2, 2, 25.50, 'ewallet', 'paid', 'TXN20250114002', '2025-11-19 17:43:46', NULL, NULL),
(3, 3, 14.50, 'cash', 'paid', NULL, '2025-11-20 17:43:46', NULL, NULL),
(4, 4, 15.00, 'online', 'paid', 'TXN20250116004', '2025-11-21 17:43:46', NULL, NULL),
(5, 5, 23.50, 'ewallet', 'paid', 'TXN20250117005', '2025-11-22 17:43:46', NULL, NULL),
(6, 6, 28.50, 'online', 'paid', 'TXN20250117006', '2025-11-22 17:43:46', NULL, NULL),
(7, 7, 22.50, 'ewallet', 'paid', 'TXN20250117007', '2025-11-22 17:43:46', NULL, NULL),
(8, 8, 24.00, 'online', 'paid', 'TXN20250118008', '2025-11-23 17:43:46', NULL, NULL),
(9, 9, 16.50, 'online', 'paid', 'TXN20250118009', '2025-11-23 17:43:46', NULL, NULL),
(10, 10, 42.00, 'ewallet', 'paid', 'TXN20250118010', '2025-11-23 17:43:46', NULL, NULL),
(11, 11, 18.50, 'online', 'pending', NULL, NULL, NULL, NULL),
(12, 12, 17.00, 'cash', 'paid', NULL, '2025-11-23 17:43:46', NULL, NULL),
(13, 13, 16.50, 'cash', 'pending', NULL, NULL, '2025-11-23 10:07:51', '2025-11-23 10:07:51'),
(14, 14, 45.00, 'cash', 'pending', NULL, NULL, '2025-11-23 10:07:51', '2025-11-23 10:07:51'),
(15, 15, 23.00, 'cash', 'pending', NULL, NULL, '2025-11-23 10:13:16', '2025-11-23 10:13:16'),
(16, 16, 7.50, 'ewallet', 'paid', 'TNG202511231857148F', '2025-11-23 10:23:07', '2025-11-23 10:23:07', '2025-11-23 10:23:07'),
(17, 17, 22.50, 'cash', 'pending', NULL, NULL, '2025-11-23 11:00:32', '2025-11-23 11:00:32'),
(18, 18, 4.50, 'cash', 'pending', NULL, NULL, '2025-11-23 11:07:10', '2025-11-23 11:07:10'),
(19, 19, 12.00, 'cash', 'pending', NULL, NULL, '2025-11-23 11:10:07', '2025-11-23 11:10:07'),
(20, 20, 5.50, 'ewallet', 'paid', 'TNG20251125C4EDEA42', '2025-11-24 18:19:00', '2025-11-24 18:19:00', '2025-11-24 18:19:00'),
(21, 21, 18.50, 'ewallet', 'paid', 'TNG2025112546B5167A', '2025-11-24 18:38:41', '2025-11-24 18:38:41', '2025-11-24 18:38:41'),
(22, 22, 10.50, 'cash', 'pending', NULL, NULL, '2025-11-24 19:17:24', '2025-11-24 19:17:24'),
(23, 23, 18.50, 'cash', 'pending', NULL, NULL, '2025-11-25 18:57:53', '2025-11-25 18:57:53');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 8, 'auth_token', '7401fbf8d52312150939df864c1488b1ee0bb8e58dca2d78a60a03fa070b33ea', '[\"*\"]', '2025-12-19 10:36:21', NULL, '2025-12-19 10:36:03', '2025-12-19 10:36:21');

-- --------------------------------------------------------

--
-- Table structure for table `pickups`
--

CREATE TABLE `pickups` (
  `pickup_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `queue_number` int(11) NOT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `status` enum('waiting','ready','picked_up') NOT NULL DEFAULT 'waiting',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pickups`
--

INSERT INTO `pickups` (`pickup_id`, `order_id`, `queue_number`, `qr_code`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 101, 'QR-ORD001-2025', 'picked_up', NULL, '2025-11-18 17:43:46'),
(2, 2, 102, 'QR-ORD002-2025', 'picked_up', NULL, '2025-11-19 17:43:46'),
(3, 3, 103, 'QR-ORD003-2025', 'picked_up', NULL, '2025-11-20 17:43:46'),
(4, 4, 104, 'QR-ORD004-2025', 'picked_up', NULL, '2025-11-21 17:43:46'),
(5, 5, 105, 'QR-ORD005-2025', 'picked_up', NULL, '2025-11-22 17:43:46'),
(6, 6, 106, 'QR-ORD006-2025', 'picked_up', NULL, '2025-11-22 17:43:46'),
(7, 7, 107, 'QR-ORD007-2025', 'picked_up', NULL, '2025-11-22 17:43:46'),
(8, 8, 108, 'QR-ORD008-2025', 'waiting', NULL, '2025-11-23 17:43:46'),
(9, 9, 109, 'QR-ORD009-2025', 'ready', NULL, '2025-11-23 17:43:46'),
(10, 10, 110, 'QR-ORD010-2025', 'waiting', NULL, '2025-11-23 17:43:46'),
(11, 11, 111, 'QR-ORD011-2025', 'waiting', NULL, '2025-11-23 17:43:46'),
(12, 12, 112, 'QR-ORD012-2025', 'ready', NULL, '2025-11-23 17:43:46'),
(13, 13, 100, 'QR-ORD000013-2025', 'waiting', '2025-11-23 10:07:51', '2025-11-23 10:07:51'),
(14, 14, 108, 'QR-ORD000014-2025', 'waiting', '2025-11-23 10:07:51', '2025-11-23 10:07:51'),
(15, 15, 109, 'QR-ORD000015-2025', 'waiting', '2025-11-23 10:13:16', '2025-11-23 10:13:16'),
(16, 16, 110, 'QR-ORD000016-2025', 'waiting', '2025-11-23 10:23:07', '2025-11-23 10:23:07'),
(17, 17, 111, 'QR-ORD000017-2025', 'waiting', '2025-11-23 11:00:32', '2025-11-23 11:00:32'),
(18, 18, 101, 'QR-ORD000018-2025', 'waiting', '2025-11-23 11:07:10', '2025-11-23 11:07:10'),
(19, 19, 106, 'QR-ORD000019-2025', 'waiting', '2025-11-23 11:10:07', '2025-11-23 11:10:07'),
(20, 20, 100, 'QR-ORD000020-2025', 'waiting', '2025-11-24 18:19:00', '2025-11-24 18:19:00'),
(21, 21, 102, 'QR-ORD000021-2025', 'waiting', '2025-11-24 18:38:41', '2025-11-24 18:42:30'),
(22, 22, 103, 'QR-ORD000022-2025', 'waiting', '2025-11-24 19:17:24', '2025-11-24 19:17:24'),
(23, 23, 100, 'QR-ORD000023-2025', 'waiting', '2025-11-25 18:57:53', '2025-11-25 18:57:53');

-- --------------------------------------------------------

--
-- Table structure for table `rewards`
--

CREATE TABLE `rewards` (
  `reward_id` bigint(20) UNSIGNED NOT NULL,
  `reward_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `terms_conditions` text DEFAULT NULL,
  `points_required` int(11) NOT NULL,
  `reward_value` decimal(10,2) NOT NULL,
  `min_spend` decimal(10,2) DEFAULT NULL,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `reward_type` varchar(50) NOT NULL,
  `stock` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rewards`
--

INSERT INTO `rewards` (`reward_id`, `reward_name`, `description`, `terms_conditions`, `points_required`, `reward_value`, `min_spend`, `max_discount`, `reward_type`, `stock`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Free Drink', 'Get any beverage for free', NULL, 50, 0.00, NULL, NULL, '', NULL, 1, '2025-11-23 17:43:46', NULL),
(2, 'RM5 Discount Voucher', 'RM5 off your next purchase', NULL, 100, 0.00, NULL, NULL, '', NULL, 1, '2025-11-23 17:43:46', NULL),
(3, 'Free Dessert', 'Choose any dessert on the house', NULL, 150, 0.00, NULL, NULL, '', NULL, 1, '2025-11-23 17:43:46', NULL),
(4, 'RM10 Discount Voucher', 'RM10 off your next purchase', NULL, 200, 0.00, NULL, NULL, '', NULL, 1, '2025-11-23 17:43:46', NULL),
(5, 'Free Appetizer', 'Get any appetizer for free', NULL, 150, 0.00, NULL, NULL, '', NULL, 1, '2025-11-23 17:43:46', NULL),
(6, 'RM20 Discount Voucher', 'RM20 off your next purchase', NULL, 400, 0.00, NULL, NULL, '', NULL, 1, '2025-11-23 17:43:46', NULL),
(7, 'Free Lunch Set', 'One free lunch set meal', NULL, 500, 0.00, NULL, NULL, '', NULL, 1, '2025-11-23 17:43:46', NULL),
(8, 'VIP Member Status', 'Exclusive VIP benefits for 1 month', NULL, 1000, 0.00, NULL, NULL, '', NULL, 1, '2025-11-23 17:43:46', NULL),
(9, 'RM 5 Voucher', 'Get RM 5 off your next order (minimum spend RM 20)', NULL, 50, 5.00, NULL, NULL, 'voucher', NULL, 1, '2025-11-23 10:33:10', '2025-11-23 10:33:10'),
(10, 'RM 10 Voucher', 'Get RM 10 off your next order (minimum spend RM 40)', NULL, 100, 10.00, NULL, NULL, 'voucher', NULL, 1, '2025-11-23 10:33:10', '2025-11-23 10:33:10'),
(11, 'RM 20 Voucher', 'Get RM 20 off your next order (minimum spend RM 80)', NULL, 200, 20.00, NULL, NULL, 'voucher', NULL, 1, '2025-11-23 10:33:10', '2025-11-23 10:33:10'),
(12, 'Free Drink', 'Get a free drink (worth up to RM 5) with any order', NULL, 30, 5.00, NULL, NULL, 'free_item', 99, 1, '2025-11-23 10:33:10', '2025-11-23 10:39:09'),
(13, 'Free Side Dish', 'Get a free side dish (worth up to RM 8) with any order', NULL, 60, 8.00, NULL, NULL, 'free_item', 50, 1, '2025-11-23 10:33:10', '2025-11-23 10:33:10'),
(14, '15% Off Coupon', 'Get 15% off your entire order (maximum discount RM 30)', NULL, 150, 15.00, NULL, NULL, 'percentage', NULL, 1, '2025-11-23 10:33:10', '2025-11-23 10:33:10'),
(15, 'RM 5 Voucher', 'Get RM 5 off your next order (minimum spend RM 20)', NULL, 50, 5.00, NULL, NULL, 'voucher', NULL, 1, '2025-11-23 10:41:55', '2025-11-23 10:41:55'),
(16, 'RM 10 Voucher', 'Get RM 10 off your next order (minimum spend RM 40)', NULL, 100, 10.00, NULL, NULL, 'voucher', NULL, 1, '2025-11-23 10:41:55', '2025-11-23 10:41:55'),
(17, 'RM 20 Voucher', 'Get RM 20 off your next order (minimum spend RM 80)', NULL, 200, 20.00, NULL, NULL, 'voucher', NULL, 1, '2025-11-23 10:41:55', '2025-11-23 10:41:55'),
(18, 'Free Drink', 'Get a free drink (worth up to RM 5) with any order', NULL, 30, 5.00, NULL, NULL, 'free_item', 99, 1, '2025-11-23 10:41:55', '2025-11-23 10:52:17'),
(19, 'Free Side Dish', 'Get a free side dish (worth up to RM 8) with any order', NULL, 60, 8.00, NULL, NULL, 'free_item', 50, 1, '2025-11-23 10:41:55', '2025-11-23 10:41:55'),
(20, '15% Off Coupon', 'Get 15% off your entire order (maximum discount RM 30)', NULL, 150, 15.00, NULL, NULL, 'percentage', NULL, 1, '2025-11-23 10:41:55', '2025-11-23 10:41:55');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('29jxStxP9jng1NgpSZHCipe5m9vukjdMxEuYl7OS', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.107.0 Chrome/142.0.7444.175 Electron/39.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZGp6SmVGWE9JZktvVVhkeGVrT0JTUE1CSmd4T3RVc3g3WlNNRTNJSiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTIxOiJodHRwOi8vbG9jYWxob3N0L2Zvb2RodW50ZXIvcHVibGljL3Rlc3QtcGF0dGVybnM/aWQ9ZTJhNTAwMWItYzlhMy00NjY5LWE2MGUtMDE4NmM2YzNlZWQ5JnZzY29kZUJyb3dzZXJSZXFJZD0xNzY2MTcyNjI4MTg5IjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1766172628),
('2wI6IKXxIHN1J1ngiT8fdi1rCxLccNoQai0zEzfA', NULL, '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.7019', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiak4yQ0JFb21HSkQwVWxwQjJnTFl0MGFWeHJBNE1Wb0k1UE5wRnlkOSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9sb2NhbGhvc3QvZm9vZGh1bnRlci9wdWJsaWMvdGVzdC1maWxlLXZhbGlkYXRpb24iO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1766173707),
('6ujwVN9NJnwAEASK6qMBM28WfadkGkEF9Iept3or', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.107.0 Chrome/142.0.7444.175 Electron/39.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRllLVVdTaE5yRkVmUmtubW1Pd1VSSDczbElVQ2hZTlppZ1NCQThrRiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTIxOiJodHRwOi8vbG9jYWxob3N0L2Zvb2RodW50ZXIvcHVibGljL3Rlc3QtcGF0dGVybnM/aWQ9ZThhMzg4YmItNWY4YS00ZGZhLWEzNjctOTFmNDk2ODhmYTZkJnZzY29kZUJyb3dzZXJSZXFJZD0xNzY2MTcyMTI2MzA3IjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1766172126),
('7LiB90Xaf62fYsB4Q34dSyLV5rAY2WwXHsybSDJf', NULL, '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.7019', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVGxnVTVKbFdwN2M0NzdMTzY5dXkxb0ExVlRzVGlVNldVNk4yYkpIMyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly9sb2NhbGhvc3QvZm9vZGh1bnRlci9wdWJsaWMvdGVzdC1wYXR0ZXJucyI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1766172476),
('8bUamgmjueDJf6BvTtrmBsDmqQOWwjLAsZRK9tch', NULL, '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.7019', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiS0dKQjQ4cjFiNGxmcFh0d21FYmk0TUxpTExVS2NScVNBblV6NkkySCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9sb2NhbGhvc3QvZm9vZGh1bnRlci9wdWJsaWMvdGVzdC1maWxlLXZhbGlkYXRpb24iO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1766173720),
('CciYtV0kjklROey8q1IgBMf3Cql49fUkMGJPL24Z', NULL, '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.7019', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUVZ4bTByWGhuUnNaSUVJUlFONjFrQlE5TlQ3a0ZsRkpkczJiTlVIRSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTY6Imh0dHA6Ly9sb2NhbGhvc3QvZm9vZGh1bnRlci9wdWJsaWMvdGVzdC1zZWN1cml0eS1sb2dnaW5nIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1766174179),
('gaeBI8887nOj2w7vXX4Fb6huTirgHDTBGquZLOhZ', NULL, '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.7019', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoid1lYaHZkNjVFSWVqZU9Gd0hnT1QyNWk1dXNPRmxUenA5alJDeUY4cyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6OTk6Imh0dHA6Ly9sb2NhbGhvc3QvZm9vZGh1bnRlci9wdWJsaWMvYXBpL2RlbW8vc3RyYXRlZ3k/c3RyYXRlZ3k9dm91Y2hlciZzdWJ0b3RhbD0xNTAmdm91Y2hlcl92YWx1ZT0yMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1766172545),
('GKKwmyzsD9XuDP55fxHxRLWfllfTAlMc3NR0Q3s0', NULL, '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.7019', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiV2VDSnBRWDBBUWU2eFNMZUhMd1UwM3lEazN6TjNZTlRTVDc3NlpUMCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly9sb2NhbGhvc3QvZm9vZGh1bnRlci9wdWJsaWMvdGVzdC1wYXR0ZXJucyI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1766172276),
('hn8RRhnkd3twQ0w2AT12t99GUREcokclhZgv5ELZ', NULL, '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.7019', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMXVDTExzcXFFT0FTMEZFSEpuS2RDNUpmOWM0NmVYSFd4NVNmSWNKRSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly9sb2NhbGhvc3QvZm9vZGh1bnRlci9wdWJsaWMvdGVzdC1wYXR0ZXJucyI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1766172029),
('IebeDOhdCC5cDv29NSSH98RrCX1Atl9UPPzdEbwe', NULL, '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.7019', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWGpSblJPTElUSGd2Q2FPMldtcmlKZm44djBieVFoV2FyRWwyd0E3YiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6OTA6Imh0dHA6Ly9sb2NhbGhvc3QvZm9vZGh1bnRlci9wdWJsaWMvYXBpL2RlbW8vc3RyYXRlZ3k/cXVhbnRpdHk9NSZzdHJhdGVneT1idWxrJnN1YnRvdGFsPTEwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1766172523),
('IowETRmxR5OK3WwGLTSvWWan8ogsfzCdZUcDT0N0', NULL, '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.7019', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoib1Q1QmpPRElDYzl4Sm1LaTQwZFcyZm1Ed1EyTUFaeWNmR2lwWG5sNyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9sb2NhbGhvc3QvZm9vZGh1bnRlci9wdWJsaWMvdGVzdC1maWxlLXZhbGlkYXRpb24iO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1766173942),
('pgSGzQ0UsSUwsiZKNMaqCCYYz6jvsURLnWfsktRo', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQkRZTFV2dGdEVWxFbDVBc3Y0YnVKcEVNOXVodWRUamI5OElsY24ycyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly9sb2NhbGhvc3QvZm9vZGh1bnRlci9wdWJsaWMvdGVzdC1wYXR0ZXJucyI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1766172418),
('rrIF6YBq7VcUhYLwBNSVec8iapjg8aexT8V0sm7R', NULL, '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.7019', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicFhGaXBqN0tQM1h4c2ZmTnlFTjBZUmlrbjV0bnduZVY5OUY0eDNSdiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6OTE6Imh0dHA6Ly9sb2NhbGhvc3QvZm9vZGh1bnRlci9wdWJsaWMvYXBpL2RlbW8vc3RyYXRlZ3k/cXVhbnRpdHk9MTAmc3RyYXRlZ3k9YnVsayZzdWJ0b3RhbD0yMDAiO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1766172534),
('T5waqo74w1GWeCWzUI1CxecYq4x8kvZ1IooH0yPq', NULL, '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.7019', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiS0JlUHU2R1FzYkNZMUNUMzB3VnBQZ1FFQUpRT0tSWWYzdVV4RWIwUCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6ODI6Imh0dHA6Ly9sb2NhbGhvc3QvZm9vZGh1bnRlci9wdWJsaWMvYXBpL2RlbW8vc3RyYXRlZ3k/c3RyYXRlZ3k9cmVndWxhciZzdWJ0b3RhbD0xMDAiO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1766172512),
('tvoMYKmWCKtWdH13QHbpGV8y7OQKRPnbhap0VrFa', NULL, '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.7019', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoia0xiSWdvb3JUckZTTGYxYWZzQ1pKdklCdVVFeXFteFZpQVJHRzg4NCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTY6Imh0dHA6Ly9sb2NhbGhvc3QvZm9vZGh1bnRlci9wdWJsaWMvdGVzdC1zZWN1cml0eS1sb2dnaW5nIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1766173950),
('uyTDAyJHMuodlCfjHHcWrC8WPcbOqFOhzuyoehr5', NULL, '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.7019', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRlhDWlE4MnRwdzE3T25ZWUpRREFWZzkyVXR4UmUzVUI0UUpGNG5UNCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTY6Imh0dHA6Ly9sb2NhbGhvc3QvZm9vZGh1bnRlci9wdWJsaWMvdGVzdC1zZWN1cml0eS1sb2dnaW5nIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1766173742),
('V146qY1AjqUefqSOYmEY72DHH1aprAeyEn1rUG34', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNWtRSWxxcGx6WkhleDVCVFprTlRJbzN2d0VlWmZ4Y1FUVjJxWlpoNSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=', 1766175166),
('VS7WZM6QQmlrBSiMBlZiJXmNSVNtZmny2qS2W1wH', NULL, '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.7019', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTTBHZlBMVkpXRXE3dVBSUFVPZVZPMHhKOHI2REs1eTN6cHR0czNORiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9sb2NhbGhvc3QvZm9vZGh1bnRlci9wdWJsaWMvdGVzdC1maWxlLXZhbGlkYXRpb24iO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1766174179),
('xjmrYCaryczZVlUw7wON4suOv3CDWXTv9uspp73j', NULL, '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.7019', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYmNLTUtOc0MwRENiQ0JNMms3TkhlcWxvRFpwUUhHVncxUGNZdUFXdiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9sb2NhbGhvc3QvZm9vZGh1bnRlci9wdWJsaWMvdGVzdC1maWxlLXZhbGlkYXRpb24iO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1766174284),
('zBespuj7wjmCTmu45owbLRmcUe9mQfiI5eVdk5Y8', NULL, '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.7019', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVDVUME5UMTJkZXBna0NXYmExT2paZzdEbVRpek1KNHFjMDVTUDdiaSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly9sb2NhbGhvc3QvZm9vZGh1bnRlci9wdWJsaWMvdGVzdC1wYXR0ZXJucyI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1766172430);

-- --------------------------------------------------------

--
-- Table structure for table `student_notifications`
--

CREATE TABLE `student_notifications` (
  `notification_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_notifications`
--

INSERT INTO `student_notifications` (`notification_id`, `user_id`, `order_id`, `type`, `title`, `message`, `is_read`, `read_at`, `created_at`, `updated_at`) VALUES
(1, 8, 22, 'order_accepted', 'Order Accepted', 'Your order #22 has been accepted by the vendor and will be prepared soon.', 1, '2025-11-25 18:51:52', '2025-11-25 18:51:01', '2025-11-25 18:51:52'),
(2, 8, 22, 'order_preparing', 'Order Being Prepared', 'Your order #22 is now being prepared. We\'ll notify you when it\'s ready!', 1, '2025-11-25 18:51:52', '2025-11-25 18:51:35', '2025-11-25 18:51:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','staff','vendor','admin') NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `email_verified_at`, `password`, `role`, `phone`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Nasi Lemak Stall', 'nasilemak@vendor.com', NULL, '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'vendor', '+60123456001', '9mKbLPKAhh5phNDl0vla2CjxVV48i6DVwAdV4re94uZJLPSBbCpfJlVpx1ri', '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(2, 'Western Food Corner', 'western@vendor.com', NULL, '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'vendor', '+60123456002', NULL, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(3, 'Mamak Roti Canai', 'mamak@vendor.com', NULL, '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'vendor', '+60123456003', NULL, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(4, 'Chinese Noodle House', 'noodles@vendor.com', NULL, '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'vendor', '+60123456004', NULL, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(5, 'Healthy Salad Bar', 'salad@vendor.com', NULL, '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'vendor', '+60123456005', NULL, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(6, 'Japanese Bento', 'bento@vendor.com', NULL, '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'vendor', '+60123456006', NULL, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(7, 'Drinks & Desserts', 'drinks@vendor.com', NULL, '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'vendor', '+60123456007', NULL, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(8, 'Ahmad bin Abdullah', 'ahmad.student@university.edu', NULL, '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'student', '+60129876001', 'At9wy7aMnz5soQpxlhuPV387khWScVRiicdClRokZpeu0N6kK9TxYN9oIPmH', '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(9, 'Siti Nurhaliza', 'siti.student@university.edu', NULL, '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'student', '+60129876002', NULL, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(10, 'Lee Wei Ming', 'lee.student@university.edu', NULL, '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'student', '+60129876003', NULL, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(11, 'Priya Devi', 'priya.student@university.edu', NULL, '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'student', '+60129876004', NULL, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(12, 'John Tan', 'john.student@university.edu', NULL, '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'student', '+60129876005', NULL, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(13, 'Nurul Aina', 'nurul.student@university.edu', NULL, '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'student', '+60129876006', NULL, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(14, 'Chen Mei Ling', 'chen.student@university.edu', NULL, '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'student', '+60129876007', NULL, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(15, 'Kumar Raj', 'kumar.student@university.edu', NULL, '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'student', '+60129876008', NULL, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(16, 'Dr. Sarah Wong', 'sarah.staff@university.edu', NULL, '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'staff', '+60125551001', NULL, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(17, 'Prof. Hassan Ibrahim', 'hassan.staff@university.edu', NULL, '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'staff', '+60125551002', NULL, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(18, 'Mrs. Lim Siew Hua', 'lim.staff@university.edu', NULL, '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'staff', '+60125551003', NULL, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(19, 'Admin User', 'admin@foodhunter.com', NULL, '$2y$12$0rBDmed4bTiNdXbMyqgPCOCdbBQ1IoL0GtoJe2oXoAJik4iMjnvki', 'admin', '+60123456789', NULL, '2025-11-23 17:43:46', '2025-11-23 17:43:46'),
(20, 'Test', 'sarah@university.edu', NULL, '$2y$12$MhWhlPmkVPXcsSLFLsI74.iqqEfkTlrd8hnrHHO35OXHwVTZs6r62', 'staff', '+60123456789', 'iH6MlZ5Fe8uwhZOF3sULebgV6l2Un9nElnoUjiprT6FYc5KzTqMLzdksjphf', '2025-12-19 09:38:49', '2025-12-19 09:38:49'),
(21, 'Demo Vendor', 'demo1766171484@vendor.com', NULL, '$2y$12$nFrg8wwB0wJZfYKuzXc0VuTdM6EiiaJPVLnMwl//EYsRm6.VTgFoa', 'vendor', '+60123456789', NULL, '2025-12-19 11:11:24', '2025-12-19 11:11:24');

-- --------------------------------------------------------

--
-- Table structure for table `user_redeemed_rewards`
--

CREATE TABLE `user_redeemed_rewards` (
  `redeemed_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `reward_id` bigint(20) UNSIGNED NOT NULL,
  `voucher_code` varchar(20) NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `used_at` timestamp NULL DEFAULT NULL,
  `redeemed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_redeemed_rewards`
--

INSERT INTO `user_redeemed_rewards` (`redeemed_id`, `user_id`, `reward_id`, `voucher_code`, `is_used`, `used_at`, `redeemed_at`, `created_at`, `updated_at`) VALUES
(1, 8, 1, 'FH-CE4DE1B6', 0, NULL, '2025-11-13 17:43:46', NULL, NULL),
(2, 9, 1, 'FH-E70CF5E7', 0, NULL, '2025-11-15 17:43:46', NULL, NULL),
(3, 10, 2, 'FH-386486B3', 0, NULL, '2025-11-16 17:43:46', NULL, NULL),
(4, 10, 3, 'FH-6DD2F36F', 0, NULL, '2025-11-18 17:43:46', NULL, NULL),
(5, 12, 2, 'FH-C4BB3E74', 0, NULL, '2025-11-17 17:43:46', NULL, NULL),
(6, 14, 1, 'FH-34C67A2B', 0, NULL, '2025-11-19 17:43:46', NULL, NULL),
(7, 16, 4, 'FH-8BBD8A2A', 0, NULL, '2025-11-20 17:43:46', NULL, NULL),
(8, 17, 2, 'FH-62F46CBF', 0, NULL, '2025-11-21 17:43:46', NULL, NULL),
(9, 17, 3, 'FH-E8669827', 0, NULL, '2025-11-22 17:43:46', NULL, NULL),
(10, 8, 12, 'FH-6E27D760', 0, NULL, '2025-11-23 18:39:09', '2025-11-23 10:39:09', '2025-11-23 10:39:09'),
(11, 8, 15, 'FH-B6D298F5', 0, NULL, '2025-11-23 18:45:25', '2025-11-23 10:45:25', '2025-11-23 10:45:25'),
(12, 8, 18, 'FH-953570FC', 0, NULL, '2025-11-23 18:52:17', '2025-11-23 10:52:17', '2025-11-23 10:52:17'),
(13, 8, 9, 'FH-29177EC1', 1, '2025-11-23 11:00:32', '2025-11-23 18:56:04', '2025-11-23 10:56:04', '2025-11-23 11:00:32'),
(14, 8, 9, 'FH-DA7653DF', 1, '2025-11-23 11:07:10', '2025-11-23 19:06:40', '2025-11-23 11:06:40', '2025-11-23 11:07:10'),
(15, 8, 9, 'FH-AE834731', 1, '2025-11-24 18:19:00', '2025-11-25 02:18:04', '2025-11-24 18:18:04', '2025-11-24 18:19:00'),
(16, 8, 9, 'FH-4E289722', 0, NULL, '2025-11-25 03:17:10', '2025-11-24 19:17:10', '2025-11-24 19:17:10');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_notifications`
--

CREATE TABLE `vendor_notifications` (
  `notification_id` bigint(20) UNSIGNED NOT NULL,
  `vendor_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vendor_notifications`
--

INSERT INTO `vendor_notifications` (`notification_id`, `vendor_id`, `type`, `title`, `message`, `order_id`, `is_read`, `read_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'new_order', 'New Order #23', 'You have a new order from Ahmad bin Abdullah totaling RM 18.50', 23, 1, '2025-11-25 18:58:26', '2025-11-25 18:57:53', '2025-11-25 18:58:26');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_operating_hours`
--

CREATE TABLE `vendor_operating_hours` (
  `hour_id` bigint(20) UNSIGNED NOT NULL,
  `vendor_id` bigint(20) UNSIGNED NOT NULL,
  `day` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
  `opening_time` time DEFAULT NULL,
  `closing_time` time DEFAULT NULL,
  `is_open` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vendor_operating_hours`
--

INSERT INTO `vendor_operating_hours` (`hour_id`, `vendor_id`, `day`, `opening_time`, `closing_time`, `is_open`, `created_at`, `updated_at`) VALUES
(1, 1, 'monday', '08:00:00', '17:00:00', 1, '2025-11-25 18:26:26', '2025-11-25 18:26:26'),
(2, 1, 'tuesday', '08:00:00', '17:00:00', 1, '2025-11-25 18:26:26', '2025-11-25 18:26:26'),
(3, 1, 'wednesday', '08:00:00', '17:00:00', 1, '2025-11-25 18:26:26', '2025-11-25 18:26:26'),
(4, 1, 'thursday', '08:00:00', '17:00:00', 1, '2025-11-25 18:26:26', '2025-11-25 18:26:26'),
(5, 1, 'friday', '08:00:00', '17:00:00', 1, '2025-11-25 18:26:26', '2025-11-25 18:26:26'),
(6, 1, 'saturday', '08:00:00', '17:00:00', 1, '2025-11-25 18:26:26', '2025-11-25 18:26:26'),
(7, 1, 'sunday', '08:00:00', '17:00:00', 0, '2025-11-25 18:26:26', '2025-11-25 18:26:26'),
(8, 21, 'monday', NULL, NULL, 1, '2025-12-19 11:11:24', '2025-12-19 11:11:24'),
(9, 21, 'tuesday', NULL, NULL, 1, '2025-12-19 11:11:24', '2025-12-19 11:11:24'),
(10, 21, 'wednesday', NULL, NULL, 1, '2025-12-19 11:11:24', '2025-12-19 11:11:24'),
(11, 21, 'thursday', NULL, NULL, 1, '2025-12-19 11:11:24', '2025-12-19 11:11:24'),
(12, 21, 'friday', NULL, NULL, 1, '2025-12-19 11:11:24', '2025-12-19 11:11:24'),
(13, 21, 'saturday', NULL, NULL, 0, '2025-12-19 11:11:24', '2025-12-19 11:11:24'),
(14, 21, 'sunday', NULL, NULL, 0, '2025-12-19 11:11:24', '2025-12-19 11:11:24');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_settings`
--

CREATE TABLE `vendor_settings` (
  `setting_id` bigint(20) UNSIGNED NOT NULL,
  `vendor_id` bigint(20) UNSIGNED NOT NULL,
  `store_name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `accepting_orders` tinyint(1) NOT NULL DEFAULT 1,
  `notify_new_orders` tinyint(1) NOT NULL DEFAULT 1,
  `notify_order_updates` tinyint(1) NOT NULL DEFAULT 1,
  `notify_email` tinyint(1) NOT NULL DEFAULT 1,
  `payment_methods` varchar(255) NOT NULL DEFAULT 'cash,online',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vendor_settings`
--

INSERT INTO `vendor_settings` (`setting_id`, `vendor_id`, `store_name`, `phone`, `description`, `logo_path`, `accepting_orders`, `notify_new_orders`, `notify_order_updates`, `notify_email`, `payment_methods`, `created_at`, `updated_at`) VALUES
(1, 1, 'Nasi Lemak Stall', NULL, 'A super good Store', 'vendor_logos/etNEM4g8JmNtTHBVuY2i0qbBQrwKksTlPKSbosV7.jpg', 1, 1, 1, 1, 'cash,online,card', '2025-11-25 18:26:26', '2025-11-25 19:02:30'),
(2, 21, 'Demo Store', NULL, 'Created using Factory Pattern', NULL, 1, 1, 1, 1, 'cash,online', '2025-12-19 11:11:24', '2025-12-19 11:11:24');

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `wishlist_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` (`wishlist_id`, `user_id`, `item_id`, `created_at`, `updated_at`) VALUES
(5, 8, 1, '2025-11-23 09:56:03', '2025-11-23 09:56:03'),
(7, 8, 4, '2025-11-23 09:56:04', '2025-11-23 09:56:04'),
(8, 8, 3, '2025-11-23 10:01:49', '2025-11-23 10:01:49'),
(9, 8, 9, '2025-11-24 18:17:45', '2025-11-24 18:17:45'),
(10, 20, 1, '2025-12-19 10:14:02', '2025-12-19 10:14:02');

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
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `cart_items_user_id_foreign` (`user_id`),
  ADD KEY `cart_items_item_id_foreign` (`item_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

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
-- Indexes for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  ADD PRIMARY KEY (`lp_id`),
  ADD KEY `loyalty_points_user_id_foreign` (`user_id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `menu_items_category_id_foreign` (`category_id`),
  ADD KEY `menu_items_vendor_id_foreign` (`vendor_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `orders_user_id_foreign` (`user_id`),
  ADD KEY `orders_vendor_id_foreign` (`vendor_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_item_id_foreign` (`item_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `payments_order_id_foreign` (`order_id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `pickups`
--
ALTER TABLE `pickups`
  ADD PRIMARY KEY (`pickup_id`),
  ADD KEY `pickups_order_id_foreign` (`order_id`);

--
-- Indexes for table `rewards`
--
ALTER TABLE `rewards`
  ADD PRIMARY KEY (`reward_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `student_notifications`
--
ALTER TABLE `student_notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `student_notifications_order_id_foreign` (`order_id`),
  ADD KEY `student_notifications_user_id_is_read_index` (`user_id`,`is_read`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_redeemed_rewards`
--
ALTER TABLE `user_redeemed_rewards`
  ADD PRIMARY KEY (`redeemed_id`),
  ADD UNIQUE KEY `user_redeemed_rewards_voucher_code_unique` (`voucher_code`),
  ADD KEY `user_redeemed_rewards_user_id_foreign` (`user_id`),
  ADD KEY `user_redeemed_rewards_reward_id_foreign` (`reward_id`);

--
-- Indexes for table `vendor_notifications`
--
ALTER TABLE `vendor_notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `vendor_notifications_order_id_foreign` (`order_id`),
  ADD KEY `vendor_notifications_vendor_id_is_read_index` (`vendor_id`,`is_read`);

--
-- Indexes for table `vendor_operating_hours`
--
ALTER TABLE `vendor_operating_hours`
  ADD PRIMARY KEY (`hour_id`),
  ADD UNIQUE KEY `vendor_operating_hours_vendor_id_day_unique` (`vendor_id`,`day`);

--
-- Indexes for table `vendor_settings`
--
ALTER TABLE `vendor_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `vendor_settings_vendor_id_unique` (`vendor_id`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD UNIQUE KEY `wishlists_user_id_item_id_unique` (`user_id`,`item_id`),
  ADD KEY `wishlists_item_id_foreign` (`item_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  MODIFY `lp_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pickups`
--
ALTER TABLE `pickups`
  MODIFY `pickup_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `rewards`
--
ALTER TABLE `rewards`
  MODIFY `reward_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `student_notifications`
--
ALTER TABLE `student_notifications`
  MODIFY `notification_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `user_redeemed_rewards`
--
ALTER TABLE `user_redeemed_rewards`
  MODIFY `redeemed_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `vendor_notifications`
--
ALTER TABLE `vendor_notifications`
  MODIFY `notification_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vendor_operating_hours`
--
ALTER TABLE `vendor_operating_hours`
  MODIFY `hour_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `vendor_settings`
--
ALTER TABLE `vendor_settings`
  MODIFY `setting_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `wishlist_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  ADD CONSTRAINT `loyalty_points_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `menu_items_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `pickups`
--
ALTER TABLE `pickups`
  ADD CONSTRAINT `pickups_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_notifications`
--
ALTER TABLE `student_notifications`
  ADD CONSTRAINT `student_notifications_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_redeemed_rewards`
--
ALTER TABLE `user_redeemed_rewards`
  ADD CONSTRAINT `user_redeemed_rewards_reward_id_foreign` FOREIGN KEY (`reward_id`) REFERENCES `rewards` (`reward_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_redeemed_rewards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_notifications`
--
ALTER TABLE `vendor_notifications`
  ADD CONSTRAINT `vendor_notifications_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_notifications_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_operating_hours`
--
ALTER TABLE `vendor_operating_hours`
  ADD CONSTRAINT `vendor_operating_hours_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_settings`
--
ALTER TABLE `vendor_settings`
  ADD CONSTRAINT `vendor_settings_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
