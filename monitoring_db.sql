-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 17, 2023 at 07:06 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `monitoring_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `os` varchar(50) DEFAULT NULL,
  `cpu` varchar(50) DEFAULT NULL,
  `i_gpu` varchar(50) DEFAULT NULL,
  `e_gpu` varchar(50) DEFAULT NULL,
  `ram` float DEFAULT 0,
  `mem` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients_app`
--

CREATE TABLE `clients_app` (
  `app_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `app` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients_network`
--

CREATE TABLE `clients_network` (
  `network_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `ip` varchar(40) DEFAULT NULL,
  `mac` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients_status`
--

CREATE TABLE `clients_status` (
  `status_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `cpu_usage` int(11) DEFAULT 0,
  `ram_usage` float DEFAULT 0,
  `mem_usage` float DEFAULT 0,
  `last_bootup` datetime DEFAULT NULL,
  `connection_status` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`);

--
-- Indexes for table `clients_app`
--
ALTER TABLE `clients_app`
  ADD PRIMARY KEY (`app_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `clients_network`
--
ALTER TABLE `clients_network`
  ADD PRIMARY KEY (`network_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `clients_status`
--
ALTER TABLE `clients_status`
  ADD PRIMARY KEY (`status_id`),
  ADD KEY `client_id` (`client_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clients_app`
--
ALTER TABLE `clients_app`
  MODIFY `app_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clients_network`
--
ALTER TABLE `clients_network`
  MODIFY `network_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clients_status`
--
ALTER TABLE `clients_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `clients_app`
--
ALTER TABLE `clients_app`
  ADD CONSTRAINT `clients_app_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`);

--
-- Constraints for table `clients_network`
--
ALTER TABLE `clients_network`
  ADD CONSTRAINT `clients_network_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`);

--
-- Constraints for table `clients_status`
--
ALTER TABLE `clients_status`
  ADD CONSTRAINT `clients_status_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
