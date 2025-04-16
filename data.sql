-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2025 at 06:17 AM
-- Server version: 10.11.10-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `local_buzz`
--

-- --------------------------------------------------------

--
-- Table structure for table `videos_promote`
--

CREATE TABLE `videos_promote` (
  `id` int(11) NOT NULL,
  `video_id` varchar(155) NOT NULL,
  `video_name` varchar(255) NOT NULL,
  `video_link` varchar(255) NOT NULL,
  `state_id` varchar(155) DEFAULT NULL,
  `state_name` text DEFAULT NULL,
  `district_id` varchar(155) DEFAULT NULL,
  `district_name` text DEFAULT NULL,
  `schedule_time` datetime DEFAULT NULL,
  `schedule_limit` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `videos_promote`
--

INSERT INTO `videos_promote` (`id`, `video_id`, `video_name`, `video_link`, `state_id`, `state_name`, `district_id`, `district_name`, `schedule_time`, `schedule_limit`, `created_at`) VALUES
(10, 'VID_67e0f86cb89c8', 'State Wise', 'https://www.youtube.com/watch?v=AqE7VKvGH1Q', '31', 'Delhi', NULL, NULL, NULL, NULL, '2025-03-24 06:15:08'),
(12, 'VID_67e0f89dc7d17', 'Distrtic Wise', 'https://www.youtube.com/watch?v=UCJN9N955Wk', NULL, NULL, '2', 'West Delhi', NULL, NULL, '2025-03-24 06:15:57'),
(28, 'VID_67e50a5d74ef2', 'Tu Hai Khan', ' https://www.youtube.com/watch?v=AX6OrbgS8lI', NULL, NULL, NULL, NULL, '2025-03-28 13:51:00', NULL, '2025-03-27 08:20:45'),
(29, 'VID_67e531cda18c8', 'Jaipur', 'https://www.youtube.com/watch?v=1CrlNhk0Ko8', NULL, NULL, '17', 'Noida', NULL, NULL, '2025-03-27 11:09:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `videos_promote`
--
ALTER TABLE `videos_promote`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `videos_promote`
--
ALTER TABLE `videos_promote`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
