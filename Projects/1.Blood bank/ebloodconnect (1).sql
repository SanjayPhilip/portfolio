-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 03, 2025 at 04:30 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ebloodconnect`
--

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `check_tables_exist`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `check_tables_exist` ()   BEGIN
    DECLARE table_count INT DEFAULT 0;
    
    -- Count how many of our expected tables exist
    SELECT COUNT(*) INTO table_count
    FROM information_schema.tables 
    WHERE table_schema = DATABASE() 
    AND table_name IN ('users', 'blood_bank', 'blood_requests', 'blood_donations', 'blood_bank_requests');
    
    -- If we don't have all 5 tables, we'll return 0 (not setup)
    IF table_count < 5 THEN
        SELECT 0 AS is_setup;
    ELSE
        SELECT 1 AS is_setup;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `full_name`, `email`, `phone`, `password`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'Aneena', 'Aneena@gmail.com', '09567854323', '$2y$10$ofkGTPnfwTxeLirN6ORhN.xc0XQmVA6e7tKi6DeqE2GAaYv8qSeJG', '2025-04-03 12:41:34', '2025-04-02 18:40:41', '2025-04-03 07:11:34');

-- --------------------------------------------------------

--
-- Table structure for table `blood_bank`
--

DROP TABLE IF EXISTS `blood_bank`;
CREATE TABLE IF NOT EXISTS `blood_bank` (
  `id` int NOT NULL AUTO_INCREMENT,
  `blood_type` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `units` int NOT NULL DEFAULT '0',
  `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blood_type` (`blood_type`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `blood_bank`
--

INSERT INTO `blood_bank` (`id`, `blood_type`, `units`, `last_updated`) VALUES
(1, 'A+', 100, '2025-04-03 07:11:44'),
(2, 'A-', 25, '2025-04-02 18:50:09'),
(3, 'B+', 50, '2025-04-03 03:57:40'),
(4, 'B-', 20, '2025-04-02 17:40:33'),
(5, 'AB+', 16, '2025-04-03 03:58:46'),
(6, 'AB-', 50, '2025-04-02 20:30:53'),
(7, 'O+', 60, '2025-04-02 17:40:33'),
(8, 'O-', 30, '2025-04-02 17:40:33');

-- --------------------------------------------------------

--
-- Table structure for table `blood_bank_donations`
--

DROP TABLE IF EXISTS `blood_bank_donations`;
CREATE TABLE IF NOT EXISTS `blood_bank_donations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `donor_id` int NOT NULL,
  `blood_type` varchar(10) NOT NULL,
  `units` int NOT NULL DEFAULT '1',
  `donation_center` varchar(255) DEFAULT NULL,
  `donation_date` date NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `donor_id` (`donor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `blood_bank_donations`
--

INSERT INTO `blood_bank_donations` (`id`, `donor_id`, `blood_type`, `units`, `donation_center`, `donation_date`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(12, 9, 'B+', 5, NULL, '2025-04-03', 'approved', NULL, '2025-04-03 03:57:04', '2025-04-03 03:57:40');

-- --------------------------------------------------------

--
-- Table structure for table `blood_bank_requests`
--

DROP TABLE IF EXISTS `blood_bank_requests`;
CREATE TABLE IF NOT EXISTS `blood_bank_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `patient_name` varchar(100) NOT NULL,
  `blood_type` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `units` int NOT NULL,
  `hospital` varchar(100) NOT NULL,
  `required_date` date NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `blood_bank_requests`
--

INSERT INTO `blood_bank_requests` (`id`, `user_id`, `patient_name`, `blood_type`, `units`, `hospital`, `required_date`, `reason`, `status`, `created_at`, `updated_at`) VALUES
(9, 9, 'Abel k varughese', 'AB+', 1, 'medical college', '2025-04-22', 'adfasgfasgas', 'completed', '2025-04-03 03:58:34', '2025-04-03 03:59:17');

-- --------------------------------------------------------

--
-- Table structure for table `blood_donations`
--

DROP TABLE IF EXISTS `blood_donations`;
CREATE TABLE IF NOT EXISTS `blood_donations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `donor_id` int NOT NULL,
  `request_id` int DEFAULT NULL,
  `blood_type` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `units` int NOT NULL,
  `donation_date` date NOT NULL,
  `donation_type` enum('direct','bloodbank') NOT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `donor_id` (`donor_id`),
  KEY `request_id` (`request_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `blood_donations`
--

INSERT INTO `blood_donations` (`id`, `donor_id`, `request_id`, `blood_type`, `units`, `donation_date`, `donation_type`, `status`, `created_at`, `updated_at`) VALUES
(7, 8, 8, 'AB+', 1, '2025-04-08', 'direct', 'pending', '2025-04-03 03:39:44', '2025-04-03 03:39:44'),
(8, 8, 10, 'AB+', 1, '2025-04-04', 'direct', 'pending', '2025-04-03 04:10:11', '2025-04-03 04:10:11');

-- --------------------------------------------------------

--
-- Table structure for table `blood_requests`
--

DROP TABLE IF EXISTS `blood_requests`;
CREATE TABLE IF NOT EXISTS `blood_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `patient_name` varchar(100) NOT NULL,
  `blood_type` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `units` int NOT NULL,
  `hospital` varchar(100) NOT NULL,
  `hospital_address` text NOT NULL,
  `urgency` enum('critical','urgent','standard') NOT NULL,
  `required_date` date NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','in_process','completed','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `blood_requests`
--

INSERT INTO `blood_requests` (`id`, `user_id`, `patient_name`, `blood_type`, `units`, `hospital`, `hospital_address`, `urgency`, `required_date`, `reason`, `status`, `created_at`, `updated_at`) VALUES
(8, 7, 'Abel k varughese', 'AB+', 1, 'medical college', 'alappuzha', 'urgent', '2025-04-11', 'dsgdsgdsgdsgdsgds', 'in_process', '2025-04-03 03:38:38', '2025-04-03 03:39:44'),
(9, 7, 'Abel k varughese', 'B-', 1, 'medical college', 'afasfasfasf', 'urgent', '2025-04-11', 'asfawsfasfasfasf', 'pending', '2025-04-03 03:46:05', '2025-04-03 03:46:05'),
(10, 7, 'deepa', 'AB+', 1, 'medical college', 'asnklfklasnf', 'standard', '2025-04-17', 'asdasdasdasd', 'in_process', '2025-04-03 04:09:21', '2025-04-03 04:10:11'),
(11, 7, 'deepa', 'A-', 1, 'medical college', 'ljkh', 'critical', '2025-04-16', 'jgk', 'pending', '2025-04-03 07:30:54', '2025-04-03 07:30:54');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` enum('donation_response','donation_confirmed','request_completed','system') NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `link` varchar(255) DEFAULT NULL,
  `related_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `blood_type` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `dob` date NOT NULL,
  `address` text NOT NULL,
  `medical_conditions` text,
  `is_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `blood_type`, `gender`, `dob`, `address`, `medical_conditions`, `is_admin`, `created_at`, `updated_at`) VALUES
(7, 'Aneena', 'Aneena@gmail.com', '$2y$10$ObGfWCs0V8S0NliCwVF2EOtD2qv0FLZQPQacCkSbwzEbmE7a6jJPm', '09567854323', 'AB+', 'Male', '2002-02-12', 'P. H Ward, Alappuzha', '', 0, '2025-04-02 19:05:53', '2025-04-02 19:05:53'),
(8, 'Riyas', 'riyassajeed233@gmail.com', '$2y$10$lGSq/TfoinGy79o6RL6V9uFii4SRrlWGculUvmvNoBgTNsr2PLaAi', '09567854323', 'AB+', 'Male', '2004-02-15', 'P. H Ward, Alappuzha', '', 0, '2025-04-02 19:12:23', '2025-04-02 19:12:23'),
(9, 'Deepasree Pradeep', 'deepasree@gmail.com', '$2y$10$neOy9rUsDOxtT/7WJroPCe3YKSvLgZSnCz3osATzr2Vid3hmJ5oiG', '09846607940', 'B+', 'Male', '2002-06-10', 'Kandamthottukara house rspo thiruvalla', '', 0, '2025-04-03 03:54:05', '2025-04-03 04:00:15');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
