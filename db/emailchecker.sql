-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 23, 2024 at 11:55 AM
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
('check0619', '717976', 'File', '../v1/json/check0619.json', '../v1/temp/temp0619.csv', '2024-07-23 02:48:00'),
('check3422', 'none', 'Single', '../v1/json/check3422.json', 'none', '2024-07-22 05:28:00'),
('check4758', '717714', 'File', '../v1/json/check4758.json', '../v1/temp/temp4758.csv', '2024-07-23 12:08:00'),
('check7713', 'none', 'Single', '../v1/json/check7713.json', 'none', '2024-07-22 05:28:00'),
('check7971', 'none', 'Single', '../v1/json/check7971.json', 'none', '2024-07-23 02:51:00'),
('check8896', 'none', 'Single', '../v1/json/check8896.json', 'none', '2024-07-22 05:29:00');

-- --------------------------------------------------------

--
-- Table structure for table `dailyusage`
--

CREATE TABLE `dailyusage` (
  `dailyuse` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dailyusage`
--

INSERT INTO `dailyusage` (`dailyuse`) VALUES
(13);

-- --------------------------------------------------------

--
-- Table structure for table `password`
--

CREATE TABLE `password` (
  `password` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password`
--

INSERT INTO `password` (`password`) VALUES
('$2y$10$4JdhjZwejYbM3uXG2Hk3I.LJ/znXXQ2V2.6wJeLicKyoPy0wf4D5G');

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
('717976', '2024-07-23 02:47:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `checks`
--
ALTER TABLE `checks`
  ADD PRIMARY KEY (`check_id`);

--
-- Indexes for table `dailyusage`
--
ALTER TABLE `dailyusage`
  ADD PRIMARY KEY (`dailyuse`);

--
-- Indexes for table `password`
--
ALTER TABLE `password`
  ADD PRIMARY KEY (`password`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`task_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
