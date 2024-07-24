-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 24, 2024 at 12:06 PM
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
-- Database: `emailchecker`
--

-- --------------------------------------------------------

--
-- Table structure for table `checks`
--

CREATE TABLE `checks` (
  `check_id` varchar(100) NOT NULL,
  `task_id` text NOT NULL,
  `method` text NOT NULL,
  `url` text NOT NULL,
  `temp` text NOT NULL,
  `time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `checks`
--

INSERT INTO `checks` (`check_id`, `task_id`, `method`, `url`, `temp`, `time`) VALUES
('check0038', '718098', 'Multiple', '../v1/json/check0038.json', 'none', '2024-07-23 16:24:46'),
('check0619', '717976', 'File', '../v1/json/check0619.json', '../v1/temp/temp0619.csv', '2024-07-23 14:48:00'),
('check3422', 'none', 'Single', '../v1/json/check3422.json', 'none', '2024-07-22 17:28:00'),
('check4353', 'none', 'Single', '../v1/json/check4353.json', 'none', '2024-07-23 15:15:00'),
('check4758', '717714', 'File', '../v1/json/check4758.json', '../v1/temp/temp4758.csv', '2024-07-23 12:08:00'),
('check6969', 'none', 'File', '../v1/json/check6969.json', '../v1/temp/temp6969.csv', '2024-07-24 09:34:11'),
('check7713', 'none', 'Single', '../v1/json/check7713.json', 'none', '2024-07-22 17:28:00'),
('check7971', 'none', 'Single', '../v1/json/check7971.json', 'none', '2024-07-23 14:51:00'),
('check8896', 'none', 'Single', '../v1/json/check8896.json', 'none', '2024-07-22 17:29:00');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `task_id` varchar(100) NOT NULL,
  `task_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`task_id`, `task_time`) VALUES
('717703', '2024-07-23 12:00:00'),
('717706', '2024-07-23 12:01:00'),
('717708', '2024-07-23 12:03:00'),
('717714', '2024-07-23 12:05:00'),
('717976', '2024-07-23 02:47:00'),
('718098', '2024-07-23 04:18:00');

-- --------------------------------------------------------

--
-- Table structure for table `tuple`
--

CREATE TABLE `tuple` (
  `id` int(11) NOT NULL,
  `dailyuse` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `temp_key` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tuple`
--

INSERT INTO `tuple` (`id`, `dailyuse`, `password`, `temp_key`) VALUES
(1, 16, '$2y$10$aYrILxGfsOzaOEil4t9MfeykivmQ0SgPajgt6rQItFvrHPicOMrLa', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `checks`
--
ALTER TABLE `checks`
  ADD PRIMARY KEY (`check_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`task_id`);

--
-- Indexes for table `tuple`
--
ALTER TABLE `tuple`
  ADD PRIMARY KEY (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
