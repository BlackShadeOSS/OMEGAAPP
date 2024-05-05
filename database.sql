-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Czas generowania: 05 Maj 2024, 12:53
-- Wersja serwera: 10.4.25-MariaDB
-- Wersja PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Baza danych: `omegaapp`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `community_note`
--

CREATE TABLE `community_note` (
  `id` int(11) NOT NULL,
  `cn_post_id` int(11) NOT NULL,
  `content` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Zrzut danych tabeli `community_note`
--

INSERT INTO `community_note` (`id`, `cn_post_id`, `content`) VALUES
(1, 1, 'dsadadsadasd');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `fa_manager`
--

CREATE TABLE `fa_manager` (
  `manager_id` int(11) NOT NULL,
  `firm_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `firm_account`
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
-- Struktura tabeli dla tabeli `follow`
--

CREATE TABLE `follow` (
  `follow_id` int(11) NOT NULL,
  `user_ac_id` int(11) DEFAULT NULL,
  `user_follower_id` int(11) DEFAULT NULL,
  `firm_ac_id` int(11) DEFAULT NULL,
  `firm_follower_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `like`
--

CREATE TABLE `like` (
  `follow_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_follower_id` int(11) DEFAULT NULL,
  `firm_follower_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `posts`
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
-- Zrzut danych tabeli `posts`
--

INSERT INTO `posts` (`post_id`, `author`, `author_firm`, `content`, `likes`, `shares`, `comments_number`, `file_id`, `post_id_for_comment`, `create_datetime`) VALUES
(4, 1, NULL, 'jhdfgjdfgjfd', 0, 0, 0, '66339a04c608c', NULL, '2024-05-05 12:53:17'),
(5, 1, NULL, 'dfsadsadsadsadas', 0, 0, 0, '6633a35b8458d1.00942415', NULL, '2024-05-05 12:53:17'),
(6, 2, NULL, 'dsadasdas', 0, 0, 0, '6633b48dbb40c8.29140694', NULL, '2024-05-05 12:53:17'),
(7, 2, NULL, 'dasdwfasrdffgwaef', 0, 0, 0, '6635399cedaa49.40825932', NULL, '2024-05-05 12:53:17'),
(8, 2, NULL, 'dsadasdsad', 0, 0, 0, '663618ce560ca3.32491856', NULL, '2024-05-05 12:53:17'),
(9, 2, NULL, 'ewqeqweqweqeqeqwedsada', 0, 0, 0, '663619836e9b82.19645908', 8, '2024-05-05 12:53:17'),
(10, 2, NULL, 'dsadsadasdfdas', 0, 0, 0, '663762d24f6361.17536704', 9, '2024-05-05 12:53:17');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `user_account`
--

CREATE TABLE `user_account` (
  `user_ac_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(64) NOT NULL,
  `email` varchar(50) NOT NULL,
  `followers` int(11) NOT NULL,
  `is_cn_admin` tinyint(1) NOT NULL DEFAULT 0,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `avatar_id` varchar(23) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Zrzut danych tabeli `user_account`
--

INSERT INTO `user_account` (`user_ac_id`, `username`, `password`, `email`, `followers`, `is_cn_admin`, `is_verified`, `avatar_id`) VALUES
(1, '123', '$2y$10$f/LiaveNrDzCyOHJKPgZd.JZQjQ6fWUBrFZduvAWw94.rK8zAZExW', '123@gmail.com', 0, 1, 0, NULL),
(2, 'morda1123', '$2y$10$E/ypNgrKxWG5hZK6YOnyIe6WwfZfPxqCEby8ImC5.LiLMG2JeweG6', 'diaxsio10@gmail.com', 0, 0, 0, '6633a89eaf25e4.86468926'),
(3, 'BlackShade', '$2y$10$HE0HUi1EgPpm7sTUUtBUbuCqn9tD5DVUXrxSKUjymoauI/30uTQAa', 'diaxsio10@gmail.com', 0, 0, 0, '66353963410ff1.06804830');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `community_note`
--
ALTER TABLE `community_note`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `community_note_fk1` (`cn_post_id`);

--
-- Indeksy dla tabeli `fa_manager`
--
ALTER TABLE `fa_manager`
  ADD PRIMARY KEY (`manager_id`),
  ADD UNIQUE KEY `manager_id` (`manager_id`),
  ADD KEY `fa_manager_fk1` (`firm_id`),
  ADD KEY `fa_manager_fk2` (`user_id`);

--
-- Indeksy dla tabeli `firm_account`
--
ALTER TABLE `firm_account`
  ADD PRIMARY KEY (`firm_ac_id`),
  ADD UNIQUE KEY `firm_ac_id` (`firm_ac_id`);

--
-- Indeksy dla tabeli `follow`
--
ALTER TABLE `follow`
  ADD PRIMARY KEY (`follow_id`),
  ADD UNIQUE KEY `follow_id` (`follow_id`),
  ADD KEY `follow_fk1` (`user_ac_id`),
  ADD KEY `follow_fk2` (`user_follower_id`),
  ADD KEY `follow_fk3` (`firm_ac_id`),
  ADD KEY `follow_fk4` (`firm_follower_id`);

--
-- Indeksy dla tabeli `like`
--
ALTER TABLE `like`
  ADD PRIMARY KEY (`follow_id`),
  ADD UNIQUE KEY `follow_id` (`follow_id`),
  ADD KEY `like_fk1` (`post_id`),
  ADD KEY `like_fk2` (`user_follower_id`),
  ADD KEY `like_fk3` (`firm_follower_id`);

--
-- Indeksy dla tabeli `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD UNIQUE KEY `post_id` (`post_id`),
  ADD KEY `posts_fk1` (`author`),
  ADD KEY `posts_fk2` (`author_firm`),
  ADD KEY `posts_fk8` (`post_id_for_comment`);

--
-- Indeksy dla tabeli `user_account`
--
ALTER TABLE `user_account`
  ADD PRIMARY KEY (`user_ac_id`),
  ADD UNIQUE KEY `user_ac_id` (`user_ac_id`);

--
-- AUTO_INCREMENT dla zrzuconych tabel
--

--
-- AUTO_INCREMENT dla tabeli `community_note`
--
ALTER TABLE `community_note`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT dla tabeli `fa_manager`
--
ALTER TABLE `fa_manager`
  MODIFY `manager_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `firm_account`
--
ALTER TABLE `firm_account`
  MODIFY `firm_ac_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `follow`
--
ALTER TABLE `follow`
  MODIFY `follow_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `like`
--
ALTER TABLE `like`
  MODIFY `follow_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT dla tabeli `user_account`
--
ALTER TABLE `user_account`
  MODIFY `user_ac_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ograniczenia dla zrzutów tabel
--

--
-- Ograniczenia dla tabeli `community_note`
--
ALTER TABLE `community_note`
  ADD CONSTRAINT `community_note_fk1` FOREIGN KEY (`cn_post_id`) REFERENCES `posts` (`post_id`);

--
-- Ograniczenia dla tabeli `fa_manager`
--
ALTER TABLE `fa_manager`
  ADD CONSTRAINT `fa_manager_fk1` FOREIGN KEY (`firm_id`) REFERENCES `firm_account` (`firm_ac_id`),
  ADD CONSTRAINT `fa_manager_fk2` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_ac_id`);

--
-- Ograniczenia dla tabeli `follow`
--
ALTER TABLE `follow`
  ADD CONSTRAINT `follow_fk1` FOREIGN KEY (`user_ac_id`) REFERENCES `user_account` (`user_ac_id`),
  ADD CONSTRAINT `follow_fk2` FOREIGN KEY (`user_follower_id`) REFERENCES `user_account` (`user_ac_id`),
  ADD CONSTRAINT `follow_fk3` FOREIGN KEY (`firm_ac_id`) REFERENCES `firm_account` (`firm_ac_id`),
  ADD CONSTRAINT `follow_fk4` FOREIGN KEY (`firm_follower_id`) REFERENCES `firm_account` (`firm_ac_id`);

--
-- Ograniczenia dla tabeli `like`
--
ALTER TABLE `like`
  ADD CONSTRAINT `like_fk1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`),
  ADD CONSTRAINT `like_fk2` FOREIGN KEY (`user_follower_id`) REFERENCES `user_account` (`user_ac_id`),
  ADD CONSTRAINT `like_fk3` FOREIGN KEY (`firm_follower_id`) REFERENCES `firm_account` (`firm_ac_id`);

--
-- Ograniczenia dla tabeli `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_fk1` FOREIGN KEY (`author`) REFERENCES `user_account` (`user_ac_id`),
  ADD CONSTRAINT `posts_fk2` FOREIGN KEY (`author_firm`) REFERENCES `firm_account` (`firm_ac_id`),
  ADD CONSTRAINT `posts_fk8` FOREIGN KEY (`post_id_for_comment`) REFERENCES `posts` (`post_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
