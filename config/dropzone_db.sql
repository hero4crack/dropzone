-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-11-2025 a las 04:35:53
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `dropzone_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('admin','super_admin') DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `admins`
--

INSERT INTO `admins` (`id`, `user_id`, `role`, `created_at`) VALUES
(1, 1, 'super_admin', '2025-11-23 02:14:32'),
(3, 5, 'admin', '2025-11-23 03:03:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `icon`, `created_at`) VALUES
(1, 'Juegos Móviles', 'Recargas para juegos móviles', 'fas fa-mobile-alt', '2025-11-20 20:52:00'),
(2, 'PC Gaming', 'Juegos para computadora', 'fas fa-desktop', '2025-11-20 20:52:00'),
(3, 'Consolas', 'Recargas para consolas', 'fas fa-gamepad', '2025-11-20 20:52:00'),
(4, 'Battle Royale', 'Juegos Battle Royale', 'fas fa-crosshairs', '2025-11-20 20:52:00'),
(5, 'Juegos Móviles', 'Recargas para juegos móviles', 'fas fa-mobile-alt', '2025-11-20 20:52:13'),
(6, 'PC Gaming', 'Juegos para computadora', 'fas fa-desktop', '2025-11-20 20:52:13'),
(7, 'Consolas', 'Recargas para consolas', 'fas fa-gamepad', '2025-11-20 20:52:13'),
(8, 'Battle Royale', 'Juegos Battle Royale', 'fas fa-crosshairs', '2025-11-20 20:52:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `games`
--

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `background_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `games`
--

INSERT INTO `games` (`id`, `category_id`, `name`, `description`, `image_url`, `background_image`, `is_active`, `featured`, `created_at`, `updated_at`) VALUES
(3, 4, 'Call Of Duty: Mobile', 'es un juego de disparos en primera persona gratuito para móviles que ofrece acción multijugador y Battle Royale con los mapas y modos icónicos de la saga', 'https://cdn1.codashop.com/S/content/common/images/mno/CODM-WEBSTORE-NEW-1600%E2%80%8Ax542.jpg', 'https://wallpapers.com/images/high/4k-call-of-duty-mobile-poster-5c50tuqihdfcliuz.webp', 1, 1, '2025-11-23 03:09:54', '2025-11-23 03:27:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `currency_amount` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'Bs.',
  `is_available` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`id`, `game_id`, `name`, `description`, `currency_amount`, `price`, `currency`, `is_available`, `sort_order`, `created_at`) VALUES
(3, 3, '80cp', '80cp para su cuenta de codm', '80cp', 66.60, 'USD', 1, 0, '2025-11-23 03:23:32'),
(4, 3, '5000cp', 'Paquete de cp grande', '5000cp', 666.00, 'USD', 1, 0, '2025-11-23 03:34:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `session_token`, `expires_at`, `created_at`) VALUES
(17, 1, '6c3cf9dc2bdb99e9f0aa2c02dbfde62c1e85cdfc6a56c9e3c572a2eba10c0ed0', '2025-12-21 02:35:31', '2025-11-20 21:35:31'),
(18, 1, '43102f11ab1f10901cc5b12b678468ffc165a13aa6acf2f5bd72ef3fe85fba7c', '2025-12-21 03:01:44', '2025-11-20 22:01:44'),
(25, 1, '6a782def6df3fe58e3d8f0c651458a4d27459fff6cab94b8f3528be6322b73bb', '2025-12-23 07:56:38', '2025-11-23 02:56:38'),
(26, 5, 'bcc1700c76e56cf86a9492a5a467209458af32aaeb1e514ed9db7d4be4ab99c7', '2025-12-23 08:03:18', '2025-11-23 03:03:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `discord_id` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `access_token` text DEFAULT NULL,
  `refresh_token` text DEFAULT NULL,
  `is_discord_user` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `discord_id`, `username`, `email`, `password_hash`, `avatar`, `access_token`, `refresh_token`, `is_discord_user`, `created_at`, `updated_at`) VALUES
(1, '923314818692317214', 'hero4crack', 'hectorlamaquina14@gmail.com', NULL, '8533e7856fb7e9ff4a361ffe39a2e08f', 'MTQ0MTA5OTMyOTA2OTc3NzA5OA.h3gyeLUGywpzfjKY9IEkODrZdOJo5G', 'gqmx4EGqYFmUaK5o8EKViJhSxCNxn6', 1, '2025-11-20 21:35:31', '2025-11-23 02:56:38'),
(2, '', 'Prueba', 'prueba@gmail.com', '$2y$10$bc62SYsCA6qDA8Jokoix0uCKl3AhCBDz/bbX06Fwig6.y7QjyQg7C', NULL, NULL, NULL, 0, '2025-11-23 02:33:51', '2025-11-23 02:33:51'),
(5, '1029795145111044187', 'hoverde1605.', 'josedavidgimeneztovar@gmail.com', NULL, 'c82b3fa769ed6e6ffdea579381ed5f5c', 'MTQ0MTA5OTMyOTA2OTc3NzA5OA.s4Z4JVAAhIyOyMkBf76XJNqdTeofhl', 'zEO2RkMy09D0cetHacfjGPbIkYJumQ', 1, '2025-11-23 03:03:18', '2025-11-23 03:03:18');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`);

--
-- Indices de la tabla `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `discord_id` (`discord_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `games`
--
ALTER TABLE `games`
  ADD CONSTRAINT `games_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
