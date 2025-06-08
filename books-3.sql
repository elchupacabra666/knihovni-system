-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Ned 08. čen 2025, 19:00
-- Verze serveru: 10.4.32-MariaDB
-- Verze PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `novp45`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `description` text CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `category_id` int(10) DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8 COLLATE utf8_czech_ci DEFAULT NULL,
  `author` varchar(100) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `year` int(4) NOT NULL,
  `country_id` int(10) DEFAULT NULL,
  `available` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `books`
--

INSERT INTO `books` (`book_id`, `title`, `description`, `category_id`, `image`, `author`, `year`, `country_id`, `available`) VALUES
(1, 'The Accidental Billionaires', 'The story behind the creation of Facebook.', 1, '1.jpg', 'Ben Mezrich', 2009, 1, 1),
(2, 'Pride and Prejudice', 'A classic novel about love and society in 19th-century England.', 2, 'covers/1.jpg', 'Jane Austen', 1813, 1, 1),
(3, 'Dune', 'Kniha z duny', 1, 'covers/1.jpg', 'Frank Herbert', 1965, 1, 1),
(4, 'The Girl with the Dragon Tattoo', 'A gripping thriller involving a journalist and a hacker.', 4, 'covers/1.jpg', 'Stieg Larsson', 2005, 1, 1),
(5, 'Bridget Jones\'s Diary', 'A humorous look at the life of a single woman in London.', 1, '684449557b894_b951a5e409.png', 'Helen Fielding', 1996, 1, 1),
(6, 'The Notebook', 'A poignant love story spanning decades.', 2, 'covers/1.jpg', 'Nicholas Sparks', 1996, 1, 1),
(7, 'Foundation', 'A seminal science fiction series about the fall and rise of a galactic empire.', 3, 'covers/1.jpg', 'Isaac Asimov', 1951, 1, 1),
(8, 'Gone Girl', 'A psychological thriller with unexpected twists.', 4, 'covers/1.jpg', 'Gillian Flynn', 2012, 1, 1),
(9, 'Malý Princ', 'To je Pepova oblíbená kníška.', 3, 'covers/1.jpg', 'Antonie De Saint-Exupéry', 1943, 1, 1),
(10, '1984', 'Je to fajn kniha', 2, '6844497d82115_d58acf7641.jpg', 'Španěl', 1984, 1, 0),
(11, '1999', '1999', 1, '684449924ed31_af3062c08e.png', '1999 a', 1999, 1, 1);

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `books_ibfk_2` (`country_id`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `books_ibfk_2` FOREIGN KEY (`country_id`) REFERENCES `countries` (`country_id`) ON DELETE SET NULL ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
