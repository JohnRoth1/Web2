-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 05, 2026 lúc 11:02 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `db-web2`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `accounts`
--

CREATE TABLE `accounts` (
  `username` varchar(50) NOT NULL,
  `password` text NOT NULL,
  `role_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `email` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `accounts`
--

INSERT INTO `accounts` (`username`, `password`, `role_id`, `status`, `email`) VALUES
('3124560017', '123456', 3, 1, 'hoangngocdai5729@gmail.com'),
('admin', '123456', 1, 1, 'admin@gmail.com'),
('customer', '123456', 3, 1, 'customer@gmail.com'),
('minhne04', '123456', 3, 1, 'minhne04@gmail.com'),
('staff', '123456', 2, 1, 'staff@gmail.com'),
('triwjbu1212', '123456', 3, 1, 'echosans57@gmail.com'),
('triwjbu13', '123456', 3, 1, 'minhtriqt04@gmail.com');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `authors`
--

CREATE TABLE `authors` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `authors`
--

INSERT INTO `authors` (`id`, `name`, `email`, `status`) VALUES
(1, 'CHOU MU-TZU', 'CHOUMU-TZU@gmail.com', 1),
(2, 'LEE JIN SONG', 'LEEJINSONG@gmail.com', 1),
(3, 'PHAN KHẢI VĂN', 'PHANKHẢIVĂN@gmail.com', 1),
(4, 'STEVE HARVEY', 'STEVEHARVEY@gmail.com', 1),
(5, 'KRISTIN NEFF', 'KRISTINNEFF@gmail.com', 1),
(6, 'CHRISTOPHER GERMER', 'CHRISTOPHERGERMER@gmail.com', 1),
(7, 'CARL GUSTAV JUNG', 'CARLGUSTAVJUNG@gmail.com', 1),
(8, 'ILSE SAND', 'ILSESAND@gmail.com', 1),
(9, 'CHANDRA GHOSH IPPEN', 'CHANDRAGHOSHIPPEN@gmail.com', 1),
(10, ' SIGMUND FREUD', 'SIGMUNDFREUD@gmail.com', 1),
(11, 'ANNE ROONEY', 'ANNEROONEY@gmail.com', 1),
(12, 'DIÊU NGHIÊU', 'DIÊUNGHIÊU@gmail.com', 1),
(13, 'MATT HAIG', 'MATTHAIG@gmail.com', 1),
(14, 'CHRISTONPHE ANDRÉ', 'CHRISTONPHEANDRÉ@gmail.com', 1),
(15, 'DIANE MUSHO HAMILTON', 'DIANEMUSHOHAMILTON@gmail.com', 1),
(16, 'POMNYUN', 'POMNYUN@gmail.com', 1),
(17, 'UYÊN BÙI', 'UYÊNBÙI@gmail.com', 1),
(18, 'VALENTINE VŨ', 'VALENTINEVŨ@gmail.com', 1),
(19, 'TRẦN LANG', 'TRẦNLANG@gmail.com', 1),
(20, 'SYLVIA BROWNE', 'SYLVIABROWNE@gmail.com', 1),
(21, 'GUSTAVE DUMOUTIER', 'GUSTAVEDUMOUTIER@gmail.com', 1),
(22, 'TÂM BÙI', 'TÂMBÙI@gmail.com', 1),
(23, 'NGUYỄN QUANG LẬP', 'NGUYỄNQUANGLẬP@gmail.com', 1),
(24, 'GEORGES COULET', 'GEORGESCOULET@gmail.com', 1),
(25, 'PUAL GIRAN', 'PUALGIRAN@gmail.com', 1),
(26, 'HAYDEN CHERRY', 'HAYDENCHERRY@gmail.com', 1),
(27, 'Lạc Bạch Mai', 'LạcBạchMai@gmail.com', 1),
(28, 'Khương Chi Ngư', 'KhươngChiNgư@gmail.com', 1),
(29, 'Diệp Lạc Vô Tâm', 'DiệpLạcVôTâm@gmail.com', 1),
(30, 'Túy Hậu Ngư Ca', 'TúyHậuNgưCa@gmail.com', 1),
(31, 'Thương Thái Vi', 'ThươngTháiVi@gmail.com', 1),
(32, 'Lâu Vũ Tình', 'LâuVũTình@gmail.com', 1),
(33, 'Nghê Đa Hỉ', 'NghêĐaHỉ@gmail.com', 1),
(34, 'Cố Tây Tước', 'CốTâyTước@gmail.com', 1),
(35, 'Cố Mạn', 'CốMạn@gmail.com', 1),
(36, 'KRISTI', '@gmail.com', 1),
(37, 'NguyenVanA', 'abc@gmail.com', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `author_details`
--

CREATE TABLE `author_details` (
  `product_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `author_details`
--

INSERT INTO `author_details` (`product_id`, `author_id`) VALUES
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(5, 6),
(6, 7),
(7, 8),
(8, 9),
(10, 11),
(11, 12),
(12, 13),
(13, 14),
(14, 15),
(15, 16),
(16, 17),
(16, 18),
(17, 19),
(18, 20),
(19, 21),
(20, 22),
(21, 23),
(22, 24),
(27, 25),
(39, 27),
(40, 28),
(41, 29),
(42, 30),
(43, 31),
(44, 32),
(45, 33),
(46, 34),
(47, 35),
(48, 29),
(49, 29),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(5, 6),
(6, 7),
(7, 8),
(8, 9),
(10, 11),
(11, 12),
(12, 13),
(13, 14),
(14, 15),
(15, 16),
(16, 17),
(16, 18),
(17, 19),
(18, 20),
(19, 21),
(20, 22),
(21, 23),
(22, 24),
(27, 25),
(39, 27),
(40, 28),
(41, 29),
(42, 30),
(43, 31),
(44, 32),
(45, 33),
(46, 34),
(47, 35),
(48, 29),
(49, 29),
(1, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `delete_date` date DEFAULT NULL,
  `create_date` date NOT NULL,
  `update_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `status`, `delete_date`, `create_date`, `update_date`) VALUES
(1, ' Tâm lý học ', 1, NULL, '2026-04-01', '2025-05-09'),
(2, ' Tâm linh - tôn giáo ', 1, NULL, '2026-04-01', '2025-05-09'),
(3, ' Lịch sử Việt Nam ', 1, NULL, '2025-04-01', '2025-05-09'),
(4, '  Ngôn tình ', 1, NULL, '2026-04-01', '2025-05-09');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `category_details`
--

CREATE TABLE `category_details` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `category_details`
--

INSERT INTO `category_details` (`product_id`, `category_id`) VALUES
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 2),
(14, 2),
(15, 2),
(16, 2),
(17, 2),
(18, 2),
(19, 2),
(20, 2),
(21, 3),
(22, 3),
(23, 3),
(27, 3),
(39, 4),
(40, 4),
(41, 4),
(42, 4),
(43, 4),
(44, 4),
(45, 4),
(46, 4),
(47, 4),
(48, 4),
(49, 4),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 2),
(14, 2),
(15, 2),
(16, 2),
(17, 2),
(18, 2),
(19, 2),
(20, 2),
(21, 3),
(22, 3),
(23, 3),
(27, 3),
(39, 4),
(40, 4),
(41, 4),
(42, 4),
(43, 4),
(44, 4),
(45, 4),
(46, 4),
(47, 4),
(48, 4),
(49, 4),
(1, 2);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `delivery_infoes`
--

CREATE TABLE `delivery_infoes` (
  `user_info_id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `fullname` varchar(50) NOT NULL,
  `phone_number` varchar(11) NOT NULL,
  `address` varchar(50) NOT NULL,
  `city` varchar(255) NOT NULL,
  `district` varchar(255) NOT NULL,
  `ward` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `delivery_infoes`
--

INSERT INTO `delivery_infoes` (`user_info_id`, `user_id`, `fullname`, `phone_number`, `address`, `city`, `district`, `ward`) VALUES
(1, 'customer', 'Lữ Quang Minhaaa', '0931814480', '280 An Dương Vương', 'TP. Hồ Chí Minh', 'Quận 5', 'Phường 03'),
(2, 'customer', 'Tèo Ú aaa', '0931814480', '273 An Dương Vương', 'TP. Hồ Chí Minh', 'Quận 5', 'Phường 03'),
(3, 'customer', 'Dương Chươnggg', '0911311312', '280 An Dương Vương', 'TP. Hồ Chí Minh', 'Quận 5', 'Phường 03'),
(4, 'minhne04', 'Lữ Quang Minh', '0931814480', '528 Hưng Phú', 'TP. Hồ Chí Minh', 'Quận 8', 'Phường 09'),
(5, 'admin', 'Lữ Quang Minh', '0931814480', '528 Hưng Phú', 'TP. Hồ Chí Minh', 'Quận 8', 'Phường 09'),
(6, 'staff', 'Nguyễn Minh Trí', '0983479999', '200 Phạm Văn Đồng', 'TP. Hồ Chí Minh', 'Quận Gò Vấp', 'Phường 01'),
(7, 'customer', 'Nguyễn Thế Ngọc', '0377927824', '20 Lê Minh Xuân', 'TP. Hồ Chí Minh', 'Quận Bình Tân', 'Phường Bình Hưng Hòa'),
(8, 'minhne04', 'Lữ Quang Minh', '0931814480', '502 Hưng Phú', 'TP. Hồ Chí Minh', 'Quận 8', 'Phường 09'),
(10, 'triwjbu13', 'Nguyễn Minh Trí', '0394080644', '115 Mạc Đĩnh Chi', 'Vĩnh Phúc', 'Vĩnh Yên', 'Phường Tích Sơn'),
(11, 'triwjbu1212', 'Nguyễn Minh Trí', '0394080644', '115 Mạc Đĩnh Chi', 'Bắc Giang', 'Bắc Giang', 'Phường Thọ Xương'),
(12, '3124560017', 'Hoàng Ngọc Đại', '0333485728', 'Đường 87', 'Bắc Ninh', 'Bắc Ninh', 'Phường Vũ Ninh'),
(13, '3124560017', 'Hoàng Ngọc Đại', '0333485729', '12 An Dương', 'Tuyên Quang', 'Tuyên Quang', 'Phường Phan Thiết');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `discounts`
--

CREATE TABLE `discounts` (
  `discount_code` varchar(10) NOT NULL,
  `discount_value` int(11) NOT NULL,
  `type` varchar(2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` tinyint(1) NOT NULL,
  `delete_date` date DEFAULT NULL,
  `create_date` date DEFAULT NULL,
  `update_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `discounts`
--

INSERT INTO `discounts` (`discount_code`, `discount_value`, `type`, `start_date`, `end_date`, `status`, `delete_date`, `create_date`, `update_date`) VALUES
('SALE50K', 50000, 'AR', '2025-04-01', '2030-04-01', 1, NULL, '2025-05-09', '2025-05-09'),
('SALENUAGIA', 50, 'PR', '2025-04-01', '2031-04-01', 1, NULL, '2025-05-09', '2025-05-09');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `functions`
--

CREATE TABLE `functions` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `status` int(11) NOT NULL,
  `delete_date` date DEFAULT NULL,
  `update_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `functions`
--

INSERT INTO `functions` (`id`, `name`, `status`, `delete_date`, `update_date`) VALUES
(1, 'Thống kê và báo cáo', 1, NULL, NULL),
(2, 'Quản lý sản phẩm', 1, NULL, NULL),
(3, 'Quản lý đơn hàng', 1, NULL, NULL),
(4, 'Quản lý tài khoản', 1, NULL, NULL),
(5, 'Quản lý nhà xuất bản', 1, NULL, NULL),
(6, 'Quản lý tác giả', 1, NULL, NULL),
(7, 'Quản lý thể loại', 1, NULL, NULL),
(8, 'Quản lý nhà cung cấp', 1, NULL, NULL),
(9, 'Quản lý nhập hàng', 1, NULL, NULL),
(10, 'Quản lý phân quyền', 1, NULL, NULL),
(11, 'Quản lý khuyển mãi', 1, NULL, NULL),
(12, 'Quản lý giá bán', 1, NULL, NULL),
(13, 'Quản lý giá bán', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `function_details`
--

CREATE TABLE `function_details` (
  `function_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `action` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `function_details`
--

INSERT INTO `function_details` (`function_id`, `role_id`, `action`) VALUES
(1, 1, 1),
(2, 1, 1),
(3, 1, 1),
(4, 1, 1),
(5, 1, 1),
(6, 1, 1),
(7, 1, 1),
(8, 1, 1),
(9, 1, 1),
(10, 1, 1),
(1, 2, 0),
(2, 2, 1),
(3, 2, 1),
(4, 2, 0),
(5, 2, 1),
(6, 2, 1),
(7, 2, 1),
(8, 2, 1),
(9, 2, 1),
(10, 2, 0),
(11, 1, 1),
(11, 2, 1),
(12, 1, 1),
(12, 2, 1),
(13, 1, 1),
(13, 2, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `goodsreceipts`
--

CREATE TABLE `goodsreceipts` (
  `id` int(11) NOT NULL,
  `staff_id` varchar(50) NOT NULL,
  `total_price` double NOT NULL,
  `date_create` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `supplier_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `goodsreceipts`
--

INSERT INTO `goodsreceipts` (`id`, `staff_id`, `total_price`, `date_create`, `status`, `supplier_id`) VALUES
(6, 'admin', 37940480, '2026-04-05', 'completed', NULL),
(7, 'admin', 9977800, '2026-04-05', 'completed', NULL),
(8, 'admin', 24419500, '2026-04-05', 'completed', NULL),
(9, 'admin', 150, '2026-04-05', 'draft', 1),
(10, 'admin', 150000, '2026-04-05', 'completed', 1),
(11, 'admin', 150000, '2026-04-05', 'draft', 1),
(12, 'admin', 150000, '2026-04-05', 'draft', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `goodsreceipt_details`
--

CREATE TABLE `goodsreceipt_details` (
  `product_id` int(11) NOT NULL,
  `goodsreceipt_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `input_price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `goodsreceipt_details`
--

INSERT INTO `goodsreceipt_details` (`product_id`, `goodsreceipt_id`, `quantity`, `input_price`) VALUES
(1, 6, 10, 20),
(2, 6, 26, 118300),
(3, 6, 9, 137200),
(4, 6, 15, 75600),
(5, 6, 18, 121660),
(6, 6, 16, 315000),
(7, 6, 19, 69300),
(8, 6, 16, 52500),
(9, 6, 20, 94500),
(10, 6, 21, 97300),
(11, 6, 16, 103600),
(12, 6, 11, 80500),
(13, 6, 28, 82600),
(14, 6, 27, 96600),
(15, 6, 18, 118300),
(16, 6, 5, 151200),
(17, 6, 18, 41300),
(18, 6, 21, 132300),
(19, 6, 18, 202300),
(20, 6, 24, 69300),
(21, 7, 11, 381500),
(22, 7, 30, 104300),
(23, 7, 6, 181300),
(27, 7, 15, 104300),
(39, 8, 24, 96600),
(40, 8, 21, 167300),
(41, 8, 30, 94500),
(42, 8, 29, 181300),
(43, 8, 23, 53200),
(44, 8, 27, 60200),
(45, 8, 6, 132300),
(46, 8, 23, 83300),
(47, 8, 13, 125300),
(48, 8, 19, 75600),
(49, 8, 27, 69300),
(1, 9, 10, 15),
(1, 10, 10, 15000),
(1, 11, 10, 15000),
(1, 12, 10, 15000);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `staff_id` varchar(50) DEFAULT NULL,
  `delivery_info_id` int(11) NOT NULL,
  `date_create` date NOT NULL,
  `total_price` double NOT NULL,
  `status_id` int(11) NOT NULL,
  `discount_code` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `staff_id`, `delivery_info_id`, `date_create`, `total_price`, `status_id`, `discount_code`) VALUES
(8, 'admin', 1, '2026-03-19', 278000, 5, NULL),
(9, 'admin', 1, '2026-04-05', 96.00000023842, 5, NULL),
(10, 'admin', 3, '2026-04-05', 80, 4, NULL),
(11, 'admin', 1, '2026-04-05', 80000, 5, NULL),
(12, 'admin', 1, '2026-04-06', 369339.60047717, 5, NULL),
(13, NULL, 12, '2026-04-06', 20250.000050291, 1, NULL),
(14, NULL, 12, '2026-04-06', 40500.000100583, 1, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_details`
--

CREATE TABLE `order_details` (
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_details`
--

INSERT INTO `order_details` (`order_id`, `product_id`, `quantity`, `price`) VALUES
(8, 1, 1, 109000),
(8, 2, 1, 169000),
(9, 1, 4, 24.000000059605),
(10, 1, 4, 20000),
(11, 1, 4, 20000),
(12, 2, 1, 171771.59949583528),
(12, 3, 1, 197568.00098133343),
(13, 1, 1, 20250.00005029142),
(14, 1, 2, 20250.00005029142);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_statuses`
--

CREATE TABLE `order_statuses` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_statuses`
--

INSERT INTO `order_statuses` (`id`, `name`) VALUES
(1, 'Chờ duyệt'),
(2, 'Đã duyệt'),
(3, 'Đã huỷ'),
(4, 'Đang giao'),
(5, 'Đã giao');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `publisher_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `create_date` date NOT NULL,
  `update_date` date NOT NULL,
  `price` double NOT NULL,
  `cost_price` double NOT NULL DEFAULT 0,
  `quantity` int(11) NOT NULL,
  `alert_qty` int(11) DEFAULT 10,
  `out_of_stock_qty` int(11) NOT NULL DEFAULT 0,
  `supplier_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `profit_margin` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `name`, `publisher_id`, `image_path`, `create_date`, `update_date`, `price`, `cost_price`, `quantity`, `alert_qty`, `out_of_stock_qty`, `supplier_id`, `status`, `profit_margin`) VALUES
(1, 'THAO TÚNG CẢM XÚC: LÀM SAO THOÁT KHỎI CHIẾC BẪY VÔ', 3, 'assets/images/product/image_1.jpg', '2025-04-01', '2026-04-06', 16875, 20000, 13, 12, 3, 1, 1, 0.2),
(2, 'TỰ DO KHÔNG YÊU ĐƯƠNG', 4, 'assets/images/product/image_2.jpg', '2025-04-01', '2026-04-06', 141960.00035256, 0, 25, 12, 3, 1, 1, 0.21),
(3, 'ĐỪNG THÁCH THỨC NHÂN TÍNH', 5, 'assets/images/product/image_3.jpg', '2025-04-01', '2026-04-05', 164640.00040889, 0, 8, 12, 3, 1, 1, 0.2),
(4, 'NÓI LUÔN CHO NÓ VUÔNG', 6, 'assets/images/product/image_4.jpg', '2025-04-01', '2026-04-05', 90720.000225306, 0, 15, 12, 3, 1, 1, 0.2),
(5, 'TRẮC ẨN VỚI CHÍNH MÌNH', 7, 'assets/images/product/image_5.jpg', '2025-04-01', '2026-04-05', 145992.00036258, 0, 18, 12, 3, 1, 1, 0.2),
(6, 'CON NGƯỜI VÀ BIỂU TƯỢNG', 6, 'assets/images/product/image_6.jpg', '2025-04-01', '2026-04-05', 378000.00093877, 0, 16, 12, 3, 1, 1, 0.2),
(7, 'DÁM SỐNG HƯỚNG NỘI VÀ CỰC KỲ NHẠY CẢM', 6, 'assets/images/product/image_7.jpg', '2025-04-01', '2026-04-05', 83160.00020653, 0, 19, 12, 3, 1, 1, 0.2),
(8, 'TỚ ĐÃ TỪNG SỢ HÃI - LỜI KHUYÊN TỪ CHUYÊN GIA TÂM L', 5, 'assets/images/product/image_8.jpg', '2025-04-01', '2026-04-05', 63000.000156462, 0, 16, 12, 3, 1, 1, 0.2),
(9, 'NGHIÊN CỨU PHÂN TÂM HỌC', 6, 'assets/images/product/image_9.jpg', '2025-04-01', '2026-04-05', 113400.00028163, 0, 20, 12, 3, 1, 1, 0.2),
(10, 'TƯ DUY NHƯ NHÀ TÂM LÝ HỌC', 6, 'assets/images/product/image_10.jpg', '2025-04-01', '2026-04-05', 116760.00028998, 0, 21, 12, 3, 1, 1, 0.2),
(11, 'CON QUÁI VẬT TRONG TÂM TRÍ – NHỮNG CA BỆNH TÂM LÝ ', 3, 'assets/images/product/image_11.jpg', '2025-04-01', '2026-04-05', 124320.00030875, 0, 16, 12, 3, 1, 1, 0.2),
(12, 'LÝ DO ĐỂ SỐNG TIẾP', 8, 'assets/images/product/image_12.jpg', '2025-04-01', '2026-04-05', 96600.000239909, 0, 11, 12, 3, 1, 1, 0.2),
(13, 'THIỀN ĐỊNH MỖI NGÀY', 5, 'assets/images/product/image_13.jpg', '2025-04-01', '2026-04-05', 99120.000246167, 0, 28, 12, 3, 1, 1, 0.2),
(14, 'MỌI VIỆC ĐỀU CÓ THỂ GIẢI QUYẾT - THÁO GỠ KHÓ KHĂN ', 5, 'assets/images/product/image_14.jpg', '2025-04-01', '2026-04-05', 115920.00028789, 0, 27, 12, 3, 1, 1, 0.2),
(15, 'LÀM SAO HỌC HẾT ĐƯỢC NHÂN SINH', 5, 'assets/images/product/image_15.jpg', '2025-04-01', '2026-04-05', 141960.00035256, 0, 18, 12, 3, 1, 1, 0.2),
(16, 'CHIÊM TINH PHÙ THỦY - ÚM BA LA ... SOI RA TÍNH CÁC', 6, 'assets/images/product/image_16.jpg', '2025-04-01', '2026-04-05', 181440.00045061, 0, 5, 12, 3, 1, 1, 0.2),
(17, 'BÙA CHÚ - GIẢI THÍCH CÁC TRÒ MẸO VÀ PHÉP BÍ THUẬT ', 6, 'assets/images/product/image_17.jpg', '2025-04-01', '2026-04-05', 49560.000123084, 0, 18, 12, 3, 1, 1, 0.2),
(18, 'NGÀY TẬN THẾ - LỜI TIÊN TRI VỀ TƯƠNG LAI VÀ THẾ GI', 9, 'assets/images/product/image_18.jpg', '2025-04-01', '2026-04-05', 158760.00039428, 0, 21, 12, 3, 1, 1, 0.2),
(19, 'TANG LỄ CỦA NGƯỜI AN NAM (BÌA CỨNG)', 6, 'assets/images/product/image_19.jpg', '2025-04-01', '2026-04-05', 242760.0006029, 0, 18, 12, 3, 1, 1, 0.2),
(20, 'CÁ HỒI - HÀNH TRÌNH TỈNH THỨC', 4, 'assets/images/product/image_20.jpg', '2025-04-01', '2026-04-05', 83160.00020653, 0, 24, 12, 3, 1, 1, 0.2),
(21, 'Ba Đồn mạn thuật', 2, 'assets/images/product/image_21.jpg', '2025-04-01', '2025-04-01', 545000, 0, 11, 12, 3, 2, 1, 0.2),
(22, 'Bộ Sách Hội Kín', 2, 'assets/images/product/image_22.jpg', '2025-04-01', '2025-04-01', 149000, 0, 30, 12, 3, 2, 1, 0.2),
(23, 'Chìm nổi ở Sài Gòn – Những cảnh đời bần cùng ở một', 2, 'assets/images/product/image_23.jpg', '2025-04-01', '2025-04-01', 259000, 0, 6, 12, 3, 2, 1, 0.2),
(27, 'Tâm lý Dân Tộc An Nam', 2, 'assets/images/product/image_27.jpg', '2025-04-01', '2025-04-01', 149000, 0, 15, 12, 3, 2, 1, 0.2),
(39, 'Năm Tháng Tĩnh Lặng, Kiếp Này Bình Yên', 10, 'assets/images/product/image_39.jpg', '2025-04-01', '2026-04-05', 115920, 0, 24, 12, 3, 3, 1, 0.2),
(40, 'Eo Thon Nhỏ', 11, 'assets/images/product/image_40.jpg', '2025-04-01', '2026-04-05', 200760, 0, 21, 12, 3, 3, 1, 0.2),
(41, 'Mãi Mãi Là Bao Xa', 11, 'assets/images/product/image_41.jpg', '2025-04-01', '2026-04-05', 113400, 0, 30, 12, 3, 3, 1, 0.2),
(42, 'Chỉ Muốn Thương Anh, Chiều Anh, Nuôi Anh', 8, 'assets/images/product/image_42.jpg', '2025-04-01', '2026-04-05', 217560, 0, 29, 12, 3, 3, 1, 0.2),
(43, 'Bến Xe', 8, 'assets/images/product/image_43.jpg', '2025-04-01', '2026-04-05', 63840, 0, 23, 12, 3, 3, 1, 0.2),
(44, 'Thất Tịch Không Mưa', 4, 'assets/images/product/image_44.jpg', '2025-04-01', '2026-04-05', 72240, 0, 27, 12, 3, 3, 1, 0.2),
(45, 'Rung Động Chỉ Vì Em', 3, 'assets/images/product/image_45.jpg', '2025-04-01', '2026-04-05', 158760, 0, 6, 12, 3, 3, 1, 0.2),
(46, 'All In Love - Ngập Tràn Yêu Thương', 4, 'assets/images/product/image_46.jpg', '2025-04-01', '2026-04-05', 99960, 0, 23, 12, 3, 3, 1, 0.2),
(47, 'Yêu Em Từ Cái Nhìn Đầu Tiên', 8, 'assets/images/product/image_47.jpg', '2025-04-01', '2026-04-05', 150360, 0, 13, 12, 3, 3, 1, 0.2),
(48, 'Em Vốn Thích Cô Độc, Cho Đến Khi Có Anh', 8, 'assets/images/product/image_48.jpg', '2025-04-01', '2026-04-05', 90720, 0, 19, 12, 3, 3, 1, 0.2),
(49, 'Chờ Em Lớn Nhé Được Không?', 8, 'assets/images/product/image_49.jpg', '2025-04-01', '2026-04-05', 83160, 0, 27, 12, 3, 3, 1, 0.2);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `publishers`
--

CREATE TABLE `publishers` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `publishers`
--

INSERT INTO `publishers` (`id`, `name`, `email`, `status`) VALUES
(2, 'Nhà Xuất Bản Hội Nhà Văn', 'nxbhoinhavan@gmail.com', 1),
(3, 'Nhà Xuất Bản Hà Nội', 'nxbhanoi@gmail.com', 1),
(4, 'Nhà Xuât Bản Phụ Nữ', 'nxbphunu@gmail.com', 1),
(5, 'Nhà Xuất Bản Dân Trí', 'nxbdantri@gmail.com', 1),
(6, 'Nhà Xuất Bản Thế Giới', 'nxbthegioi@gmail.com', 1),
(7, 'Nhà Xuất Bản Tổng Hợp Thành phố Hồ Chí Minh', 'nxbtonghoptphcm@gmail.com', 1),
(8, 'Nhà Xuất Bản Văn Học', 'nxbvanhoc@gmail.com', 1),
(9, 'Nhà Xuất Bản Thông Tấn', 'nxbthongtan@gmail.com', 1),
(10, 'Nhà Xuất Bản Lao Động', 'nxblaodong@gmail.com', 1),
(11, 'Nhà Xuất Bản Thanh Niên', 'nxbthanhnien@gmail.com', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'admin'),
(2, 'staff'),
(3, 'customer');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `number_phone` varchar(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `delete_date` date DEFAULT NULL,
  `create_date` date DEFAULT NULL,
  `update_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `email`, `number_phone`, `status`, `delete_date`, `create_date`, `update_date`) VALUES
(1, ' Nhã Nam ', ' info@nhanam.com ', ' 0324253224', 1, NULL, '2025-05-09', '2025-05-09'),
(2, ' Omega Plus ', ' info@omegaplus.vn ', ' 0932329922', 1, NULL, '2025-05-09', '2025-05-09'),
(3, 'Minh Quang Books', 'info@minhquangbooks.com', '0975225265', 1, NULL, '2025-05-09', '2025-05-09');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `verify_code`
--

CREATE TABLE `verify_code` (
  `email` varchar(50) NOT NULL,
  `code` varchar(10) NOT NULL,
  `time_send` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `verify_code`
--

INSERT INTO `verify_code` (`email`, `code`, `time_send`) VALUES
('admin@gmail.com', '890678', '2025-05-03 09:21:29'),
('customer@gmail.com', '325786', '2025-05-15 04:22:03'),
('echosans57@gmail.com', '631292', '2025-05-16 09:37:03'),
('hoangngocdai5729@gmail.com', '000000', '2026-04-06 00:28:57'),
('minhne04@gmail.com', '325786', '2025-05-15 04:22:03'),
('minhtriqt04@gmail.com', '482407', '2025-05-16 09:10:10'),
('staff@gmail.com', '325786', '2025-05-15 04:22:03');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`username`),
  ADD KEY `fk_role_id` (`role_id`),
  ADD KEY `fk_email` (`email`);

--
-- Chỉ mục cho bảng `authors`
--
ALTER TABLE `authors`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `author_details`
--
ALTER TABLE `author_details`
  ADD KEY `fk_product_id_author_details` (`product_id`),
  ADD KEY `fk_author_id_author_details` (`author_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `category_details`
--
ALTER TABLE `category_details`
  ADD KEY `fk_product_id_category_details` (`product_id`),
  ADD KEY `fk_category_id_category_details` (`category_id`);

--
-- Chỉ mục cho bảng `delivery_infoes`
--
ALTER TABLE `delivery_infoes`
  ADD PRIMARY KEY (`user_info_id`),
  ADD KEY `fk_user_id_user_info` (`user_id`);

--
-- Chỉ mục cho bảng `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`discount_code`);

--
-- Chỉ mục cho bảng `functions`
--
ALTER TABLE `functions`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `function_details`
--
ALTER TABLE `function_details`
  ADD KEY `fk_function_id` (`function_id`),
  ADD KEY `fk_role_id_function` (`role_id`);

--
-- Chỉ mục cho bảng `goodsreceipts`
--
ALTER TABLE `goodsreceipts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_staff_id_goodsreceipts` (`staff_id`);

--
-- Chỉ mục cho bảng `goodsreceipt_details`
--
ALTER TABLE `goodsreceipt_details`
  ADD KEY `fk_product_id_goodsreceipt_details` (`product_id`),
  ADD KEY `fk_goodsreceipt_id_goodsreceipt_details` (`goodsreceipt_id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_status_id_order` (`status_id`),
  ADD KEY `fk_staff_id_order` (`staff_id`),
  ADD KEY `fk_discount_code_order` (`discount_code`);

--
-- Chỉ mục cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD KEY `fk_product_id_order_details` (`product_id`),
  ADD KEY `fk_order_id_order_details` (`order_id`);

--
-- Chỉ mục cho bảng `order_statuses`
--
ALTER TABLE `order_statuses`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_publisher_id_product` (`publisher_id`),
  ADD KEY `fk_supplier_id_product` (`supplier_id`);

--
-- Chỉ mục cho bảng `publishers`
--
ALTER TABLE `publishers`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `verify_code`
--
ALTER TABLE `verify_code`
  ADD PRIMARY KEY (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `authors`
--
ALTER TABLE `authors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `delivery_infoes`
--
ALTER TABLE `delivery_infoes`
  MODIFY `user_info_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `functions`
--
ALTER TABLE `functions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `goodsreceipts`
--
ALTER TABLE `goodsreceipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `order_statuses`
--
ALTER TABLE `order_statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT cho bảng `publishers`
--
ALTER TABLE `publishers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `fk_email` FOREIGN KEY (`email`) REFERENCES `verify_code` (`email`),
  ADD CONSTRAINT `fk_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Các ràng buộc cho bảng `author_details`
--
ALTER TABLE `author_details`
  ADD CONSTRAINT `fk_author_id_author_details` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`),
  ADD CONSTRAINT `fk_product_id_author_details` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Các ràng buộc cho bảng `category_details`
--
ALTER TABLE `category_details`
  ADD CONSTRAINT `fk_category_id_category_details` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `fk_product_id_category_details` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Các ràng buộc cho bảng `delivery_infoes`
--
ALTER TABLE `delivery_infoes`
  ADD CONSTRAINT `fk_user_info_delivery_infoes` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`username`);

--
-- Các ràng buộc cho bảng `function_details`
--
ALTER TABLE `function_details`
  ADD CONSTRAINT `fk_function_id` FOREIGN KEY (`function_id`) REFERENCES `functions` (`id`),
  ADD CONSTRAINT `fk_role_id_function` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Các ràng buộc cho bảng `goodsreceipts`
--
ALTER TABLE `goodsreceipts`
  ADD CONSTRAINT `fk_staff_id_goodsreceipts` FOREIGN KEY (`staff_id`) REFERENCES `accounts` (`username`);

--
-- Các ràng buộc cho bảng `goodsreceipt_details`
--
ALTER TABLE `goodsreceipt_details`
  ADD CONSTRAINT `fk_goodsreceipt_id_goodsreceipt_details` FOREIGN KEY (`goodsreceipt_id`) REFERENCES `goodsreceipts` (`id`),
  ADD CONSTRAINT `fk_product_id_goodsreceipt_details` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_discount_code_order` FOREIGN KEY (`discount_code`) REFERENCES `discounts` (`discount_code`),
  ADD CONSTRAINT `fk_staff_id_order` FOREIGN KEY (`staff_id`) REFERENCES `accounts` (`username`),
  ADD CONSTRAINT `fk_status_id_order` FOREIGN KEY (`status_id`) REFERENCES `order_statuses` (`id`);

--
-- Các ràng buộc cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `fk_order_id_order_details` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `fk_product_id_order_details` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_publisher_id_product` FOREIGN KEY (`publisher_id`) REFERENCES `publishers` (`id`),
  ADD CONSTRAINT `fk_supplier_id_product` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
