-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 13, 2024 at 12:32 PM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `omegaapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `community_note`
--

CREATE TABLE `community_note` (
  `id` int(11) NOT NULL,
  `cn_post_id` int(11) NOT NULL,
  `content` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `community_note`
--

INSERT INTO `community_note` (`id`, `cn_post_id`, `content`) VALUES
(1, 1, 'dsadadsadasd'),
(2, 13, 'Kłamstwa');

-- --------------------------------------------------------

--
-- Table structure for table `fa_manager`
--

CREATE TABLE `fa_manager` (
  `manager_id` int(11) NOT NULL,
  `firm_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `firm_account`
--

CREATE TABLE `firm_account` (
  `firm_ac_id` int(11) NOT NULL,
  `firm_name` varchar(50) NOT NULL,
  `followers` int(11) NOT NULL,
  `manager` int(11) NOT NULL,
  `avatar_id` varchar(23) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `follow`
--

CREATE TABLE `follow` (
  `follow_id` int(11) NOT NULL,
  `user_ac_id` int(11) DEFAULT NULL,
  `user_follower_id` int(11) DEFAULT NULL,
  `firm_ac_id` int(11) DEFAULT NULL,
  `firm_follower_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `follow`
--

INSERT INTO `follow` (`follow_id`, `user_ac_id`, `user_follower_id`, `firm_ac_id`, `firm_follower_id`) VALUES
(5, 2, 2, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `like_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `firm_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`like_id`, `post_id`, `user_id`, `firm_id`) VALUES
(1, 7, NULL, NULL),
(2, 7, NULL, NULL),
(25, 12, 4, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL,
  `author` int(11) NOT NULL,
  `author_firm` int(11) DEFAULT NULL,
  `content` varchar(512) NOT NULL,
  `likes` int(11) NOT NULL,
  `shares` int(11) NOT NULL,
  `comments_number` int(11) NOT NULL,
  `file_id` varchar(23) DEFAULT NULL,
  `post_id_for_comment` int(11) DEFAULT NULL,
  `create_datetime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`post_id`, `author`, `author_firm`, `content`, `likes`, `shares`, `comments_number`, `file_id`, `post_id_for_comment`, `create_datetime`) VALUES
(5, 1, NULL, 'dfsadsadsadsadas', 0, 0, 0, '6633a35b8458d1.00942415', NULL, '2024-05-05 12:53:17'),
(6, 2, NULL, 'dsadasdas', 0, 0, 0, '6633b48dbb40c8.29140694', NULL, '2024-05-05 12:53:17'),
(7, 2, NULL, 'dasdwfasrdffgwaef', 3, 0, 0, '6635399cedaa49.40825932', NULL, '2024-05-05 12:53:17'),
(8, 2, NULL, 'dsadasdsad', 0, 0, 0, '663618ce560ca3.32491856', NULL, '2024-05-05 12:53:17'),
(9, 2, NULL, 'ewqeqweqweqeqeqwedsada', 0, 0, 0, '663619836e9b82.19645908', 8, '2024-05-05 12:53:17'),
(10, 2, NULL, 'dsadsadasdfdas', 0, 0, 0, '663762d24f6361.17536704', 9, '2024-05-05 12:53:17'),
(11, 2, NULL, 'rfewwfewfw', 0, 0, 0, '6639170e6efd94.19247592', 7, '2024-05-06 19:44:46'),
(12, 4, NULL, 'dasdasdasdasd', 1, 0, 0, '663a42b3ea2706.58391838', NULL, '2024-05-07 17:03:16'),
(13, 4, NULL, 'sdaDASdsfagdsf', 0, 0, 0, '663a50db60f284.66740036', 12, '2024-05-07 18:03:39'),
(14, 4, NULL, 'Kozak', 0, 0, 0, NULL, 13, '2024-05-08 14:57:37'),
(15, 4, NULL, 'WUT\r\n', 0, 0, 0, NULL, 12, '2024-05-08 18:28:48');

-- --------------------------------------------------------

--
-- Table structure for table `user_account`
--

CREATE TABLE `user_account` (
  `user_ac_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(64) NOT NULL,
  `email` varchar(50) NOT NULL,
  `followers` int(11) NOT NULL,
  `is_cn_admin` tinyint(1) NOT NULL DEFAULT 0,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `avatar_id` varchar(23) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user_account`
--

INSERT INTO `user_account` (`user_ac_id`, `username`, `password`, `email`, `followers`, `is_cn_admin`, `is_verified`, `avatar_id`, `created`) VALUES
(1, '123', '$2y$10$f/LiaveNrDzCyOHJKPgZd.JZQjQ6fWUBrFZduvAWw94.rK8zAZExW', '123@gmail.com', 0, 1, 0, NULL, '2024-05-10 11:28:50'),
(2, 'morda1123', '$2y$10$E/ypNgrKxWG5hZK6YOnyIe6WwfZfPxqCEby8ImC5.LiLMG2JeweG6', 'diaxsio10@gmail.com', 0, 0, 0, '6633a89eaf25e4.86468926', '2024-05-10 11:28:50'),
(3, 'BlackShade', '$2y$10$HE0HUi1EgPpm7sTUUtBUbuCqn9tD5DVUXrxSKUjymoauI/30uTQAa', 'diaxsio10@gmail.com', 0, 0, 0, '66353963410ff1.06804830', '2024-05-10 11:28:50'),
(4, 'BlackShadeOSS', '$2y$10$vM8Rxk5y2V0VCbfdSqIcRem3aTFFbzxl8lu8SqaBpJFzW.yJdP9Vy', 'diaxsio10@gmail.com', 0, 0, 0, '663a1bed80e7a0.30707126', '2024-05-10 11:28:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `community_note`
--
ALTER TABLE `community_note`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `community_note_fk1` (`cn_post_id`);

--
-- Indexes for table `fa_manager`
--
ALTER TABLE `fa_manager`
  ADD PRIMARY KEY (`manager_id`),
  ADD UNIQUE KEY `manager_id` (`manager_id`),
  ADD KEY `fa_manager_fk1` (`firm_id`),
  ADD KEY `fa_manager_fk2` (`user_id`);

--
-- Indexes for table `firm_account`
--
ALTER TABLE `firm_account`
  ADD PRIMARY KEY (`firm_ac_id`),
  ADD UNIQUE KEY `firm_ac_id` (`firm_ac_id`);

--
-- Indexes for table `follow`
--
ALTER TABLE `follow`
  ADD PRIMARY KEY (`follow_id`),
  ADD UNIQUE KEY `follow_id` (`follow_id`),
  ADD KEY `follow_fk1` (`user_ac_id`),
  ADD KEY `follow_fk2` (`user_follower_id`),
  ADD KEY `follow_fk3` (`firm_ac_id`),
  ADD KEY `follow_fk4` (`firm_follower_id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`like_id`),
  ADD UNIQUE KEY `follow_id` (`like_id`),
  ADD KEY `like_fk1` (`post_id`),
  ADD KEY `like_fk2` (`user_id`),
  ADD KEY `like_fk3` (`firm_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD UNIQUE KEY `post_id` (`post_id`),
  ADD KEY `posts_fk1` (`author`),
  ADD KEY `posts_fk2` (`author_firm`),
  ADD KEY `posts_fk8` (`post_id_for_comment`);

--
-- Indexes for table `user_account`
--
ALTER TABLE `user_account`
  ADD PRIMARY KEY (`user_ac_id`),
  ADD UNIQUE KEY `user_ac_id` (`user_ac_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `community_note`
--
ALTER TABLE `community_note`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `fa_manager`
--
ALTER TABLE `fa_manager`
  MODIFY `manager_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `firm_account`
--
ALTER TABLE `firm_account`
  MODIFY `firm_ac_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `follow`
--
ALTER TABLE `follow`
  MODIFY `follow_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `user_account`
--
ALTER TABLE `user_account`
  MODIFY `user_ac_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `community_note`
--
ALTER TABLE `community_note`
  ADD CONSTRAINT `community_note_fk1` FOREIGN KEY (`cn_post_id`) REFERENCES `posts` (`post_id`);

--
-- Constraints for table `fa_manager`
--
ALTER TABLE `fa_manager`
  ADD CONSTRAINT `fa_manager_fk1` FOREIGN KEY (`firm_id`) REFERENCES `firm_account` (`firm_ac_id`),
  ADD CONSTRAINT `fa_manager_fk2` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_ac_id`);

--
-- Constraints for table `follow`
--
ALTER TABLE `follow`
  ADD CONSTRAINT `follow_fk1` FOREIGN KEY (`user_ac_id`) REFERENCES `user_account` (`user_ac_id`),
  ADD CONSTRAINT `follow_fk2` FOREIGN KEY (`user_follower_id`) REFERENCES `user_account` (`user_ac_id`),
  ADD CONSTRAINT `follow_fk3` FOREIGN KEY (`firm_ac_id`) REFERENCES `firm_account` (`firm_ac_id`),
  ADD CONSTRAINT `follow_fk4` FOREIGN KEY (`firm_follower_id`) REFERENCES `firm_account` (`firm_ac_id`);

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `like_fk1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`),
  ADD CONSTRAINT `like_fk2` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_ac_id`),
  ADD CONSTRAINT `like_fk3` FOREIGN KEY (`firm_id`) REFERENCES `firm_account` (`firm_ac_id`);

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_fk1` FOREIGN KEY (`author`) REFERENCES `user_account` (`user_ac_id`),
  ADD CONSTRAINT `posts_fk2` FOREIGN KEY (`author_firm`) REFERENCES `firm_account` (`firm_ac_id`),
  ADD CONSTRAINT `posts_fk8` FOREIGN KEY (`post_id_for_comment`) REFERENCES `posts` (`post_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
