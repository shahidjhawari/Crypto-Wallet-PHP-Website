-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 29, 2024 at 11:57 AM
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
-- Database: `coin`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_messages`
--

CREATE TABLE `admin_messages` (
  `id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`) VALUES
(0, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `message` text NOT NULL,
  `announcement_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bonus_rewards`
--

CREATE TABLE `bonus_rewards` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `bonus_amount` decimal(10,2) NOT NULL,
  `claimed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daily_earnings`
--

CREATE TABLE `daily_earnings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `staking_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `rewarded` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed` tinyint(1) DEFAULT 0,
  `is_rewarded` tinyint(1) DEFAULT 0,
  `last_checked` datetime DEFAULT NULL,
  `reward_processed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daily_earnings`
--

INSERT INTO `daily_earnings` (`id`, `user_id`, `staking_id`, `date`, `amount`, `rewarded`, `created_at`, `processed`, `is_rewarded`, `last_checked`, `reward_processed`) VALUES
(1, 175, 120, '2024-07-29', 0.18, 0, '2024-07-29 05:42:19', 0, 0, NULL, 1),
(2, 176, 121, '2024-07-29', 0.18, 0, '2024-07-29 05:42:19', 0, 0, NULL, 1),
(3, 177, 122, '2024-07-29', 0.18, 0, '2024-07-29 05:42:19', 0, 0, NULL, 1),
(4, 175, 120, '2024-07-29', 0.18, 0, '2024-07-29 05:51:01', 0, 0, NULL, 1),
(5, 176, 121, '2024-07-29', 0.18, 0, '2024-07-29 05:51:01', 0, 0, NULL, 1),
(6, 177, 122, '2024-07-29', 0.18, 0, '2024-07-29 05:51:01', 0, 0, NULL, 1),
(7, 175, 120, '2024-07-29', 0.27, 0, '2024-07-29 05:51:21', 0, 0, NULL, 1),
(8, 176, 121, '2024-07-29', 0.27, 0, '2024-07-29 05:51:21', 0, 0, NULL, 1),
(9, 177, 122, '2024-07-29', 0.27, 0, '2024-07-29 05:51:21', 0, 0, NULL, 1),
(10, 175, 120, '2024-07-29', 0.22, 0, '2024-07-29 05:57:50', 0, 0, NULL, 1),
(11, 176, 121, '2024-07-29', 0.22, 0, '2024-07-29 05:57:50', 0, 0, NULL, 1),
(12, 177, 122, '2024-07-29', 0.22, 0, '2024-07-29 05:57:51', 0, 0, NULL, 1),
(13, 175, 120, '2024-07-29', 0.18, 0, '2024-07-29 06:02:50', 0, 0, NULL, 1),
(14, 176, 121, '2024-07-29', 0.18, 0, '2024-07-29 06:02:50', 0, 0, NULL, 1),
(15, 177, 122, '2024-07-29', 0.18, 0, '2024-07-29 06:02:50', 0, 0, NULL, 1),
(16, 175, 120, '2024-07-29', 0.18, 0, '2024-07-29 09:50:24', 0, 0, NULL, 1),
(17, 176, 121, '2024-07-29', 0.18, 0, '2024-07-29 09:50:24', 0, 0, NULL, 1),
(18, 177, 122, '2024-07-29', 0.18, 0, '2024-07-29 09:50:24', 0, 0, NULL, 1),
(19, 174, 123, '2024-07-29', 0.18, 0, '2024-07-29 09:50:24', 0, 0, NULL, 0),
(20, 175, 120, '2024-07-29', 0.18, 0, '2024-07-29 09:50:57', 0, 0, NULL, 1),
(21, 176, 121, '2024-07-29', 0.18, 0, '2024-07-29 09:50:57', 0, 0, NULL, 1),
(22, 177, 122, '2024-07-29', 0.18, 0, '2024-07-29 09:50:57', 0, 0, NULL, 1),
(23, 174, 123, '2024-07-29', 0.18, 0, '2024-07-29 09:50:57', 0, 0, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `deposits`
--

CREATE TABLE `deposits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `screenshot` varchar(255) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `status` enum('Pending','Accepted','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` enum('Easy Paisa','Valid Cash','Simple PA','USDT') NOT NULL,
  `bonus_claimed_amount` decimal(10,2) DEFAULT 0.00,
  `referral_daily_reward` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deposits`
--

INSERT INTO `deposits` (`id`, `user_id`, `amount`, `screenshot`, `transaction_id`, `status`, `created_at`, `payment_method`, `bonus_claimed_amount`, `referral_daily_reward`) VALUES
(1, 174, 0.00, 'images.jpg', 'bvc', 'Accepted', '2024-07-29 05:33:37', 'Valid Cash', 0.00, 0.00),
(2, 175, 0.00, 'images.jpg', 'bvcxzX', 'Accepted', '2024-07-29 05:37:17', 'Valid Cash', 0.00, 0.00),
(3, 176, 0.00, 'images.jpg', 'treuiyiuy', 'Accepted', '2024-07-29 05:39:28', 'Valid Cash', 0.00, 0.00),
(4, 177, 0.00, 'images.jpg', 'fdgbcvcvb', 'Accepted', '2024-07-29 05:41:28', 'Valid Cash', 0.00, 0.00),
(5, 174, 5.00, '', '', 'Accepted', '2024-07-29 05:43:26', 'Easy Paisa', 0.00, 0.00),
(6, 174, 0.02, '', '', 'Accepted', '2024-07-29 05:46:56', 'Easy Paisa', 0.00, 0.00),
(7, 174, 0.02, '', '', 'Accepted', '2024-07-29 05:47:00', 'Easy Paisa', 0.00, 0.00),
(8, 174, 0.02, '', '', 'Accepted', '2024-07-29 05:47:01', 'Easy Paisa', 0.00, 0.00),
(9, 174, 0.03, '', '', 'Accepted', '2024-07-29 05:57:08', 'Easy Paisa', 0.00, 0.00),
(10, 174, 0.03, '', '', 'Accepted', '2024-07-29 05:57:10', 'Easy Paisa', 0.00, 0.00),
(11, 174, 0.03, '', '', 'Accepted', '2024-07-29 05:57:12', 'Easy Paisa', 0.00, 0.00),
(12, 174, 0.02, '', '', 'Accepted', '2024-07-29 05:57:13', 'Easy Paisa', 0.00, 0.00),
(13, 174, 0.02, '', '', 'Accepted', '2024-07-29 05:57:14', 'Easy Paisa', 0.00, 0.00),
(14, 174, 0.02, '', '', 'Accepted', '2024-07-29 05:57:15', 'Easy Paisa', 0.00, 0.00),
(15, 174, 0.02, '', '', 'Accepted', '2024-07-29 05:57:55', 'Easy Paisa', 0.00, 0.00),
(16, 174, 0.02, '', '', 'Accepted', '2024-07-29 06:01:27', 'Easy Paisa', 0.00, 0.00),
(17, 174, 0.02, '', '', 'Accepted', '2024-07-29 06:01:27', 'Easy Paisa', 0.00, 0.00),
(18, 174, 0.02, '', '', 'Accepted', '2024-07-29 06:03:04', 'Easy Paisa', 0.00, 0.00),
(19, 174, 0.02, '', '', 'Accepted', '2024-07-29 06:03:04', 'Easy Paisa', 0.00, 0.00),
(20, 174, 0.02, '', '', 'Accepted', '2024-07-29 06:03:05', 'Easy Paisa', 0.00, 0.00),
(21, 174, 0.02, '', '', 'Accepted', '2024-07-29 09:50:45', 'Easy Paisa', 0.00, 0.02),
(22, 174, 0.02, '', '', 'Accepted', '2024-07-29 09:50:45', 'Easy Paisa', 0.00, 0.02),
(23, 174, 0.02, '', '', 'Accepted', '2024-07-29 09:50:45', 'Easy Paisa', 0.00, 0.02),
(24, 174, 0.02, '', '', 'Accepted', '2024-07-29 09:51:17', 'Easy Paisa', 0.00, 0.02),
(25, 174, 0.02, '', '', 'Accepted', '2024-07-29 09:51:17', 'Easy Paisa', 0.00, 0.02),
(26, 174, 0.02, '', '', 'Accepted', '2024-07-29 09:51:17', 'Easy Paisa', 0.00, 0.02);

-- --------------------------------------------------------

--
-- Table structure for table `referral_rewards`
--

CREATE TABLE `referral_rewards` (
  `id` int(11) NOT NULL,
  `referrer_id` int(11) NOT NULL,
  `referred_user_id` int(11) NOT NULL,
  `daily_earning_amount` decimal(10,2) NOT NULL,
  `reward_percentage` decimal(5,2) NOT NULL,
  `reward_amount` decimal(10,2) NOT NULL,
  `reward_date` datetime NOT NULL DEFAULT current_timestamp(),
  `user_10_percent_reward` decimal(10,2) DEFAULT 0.00,
  `is_claimed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `referral_rewards`
--

INSERT INTO `referral_rewards` (`id`, `referrer_id`, `referred_user_id`, `daily_earning_amount`, `reward_percentage`, `reward_amount`, `reward_date`, `user_10_percent_reward`, `is_claimed`) VALUES
(1, 174, 175, 0.18, 10.00, 0.00, '2024-07-29 10:42:23', 0.00, 0),
(2, 175, 176, 0.18, 10.00, 0.00, '2024-07-29 10:42:23', 0.02, 0),
(3, 174, 176, 0.18, 5.00, 0.00, '2024-07-29 10:42:23', 0.00, 0),
(4, 176, 177, 0.18, 10.00, 0.00, '2024-07-29 10:42:23', 0.02, 0),
(5, 175, 177, 0.18, 5.00, 0.00, '2024-07-29 10:42:23', 0.02, 0),
(6, 174, 177, 0.18, 2.00, 0.00, '2024-07-29 10:42:23', 0.00, 0),
(7, 174, 175, 0.18, 10.00, 0.00, '2024-07-29 10:51:05', 0.00, 0),
(8, 175, 176, 0.18, 10.00, 0.00, '2024-07-29 10:51:05', 0.02, 0),
(9, 174, 176, 0.18, 5.00, 0.00, '2024-07-29 10:51:05', 0.00, 0),
(10, 176, 177, 0.18, 10.00, 0.00, '2024-07-29 10:51:05', 0.02, 0),
(11, 175, 177, 0.18, 5.00, 0.00, '2024-07-29 10:51:05', 0.02, 0),
(12, 174, 177, 0.18, 2.00, 0.00, '2024-07-29 10:51:05', 0.00, 0),
(13, 174, 175, 0.27, 10.00, 0.00, '2024-07-29 10:51:23', 0.00, 0),
(14, 175, 176, 0.27, 10.00, 0.00, '2024-07-29 10:51:23', 0.03, 0),
(15, 174, 176, 0.27, 5.00, 0.00, '2024-07-29 10:51:23', 0.00, 0),
(16, 176, 177, 0.27, 10.00, 0.00, '2024-07-29 10:51:23', 0.03, 0),
(17, 175, 177, 0.27, 5.00, 0.00, '2024-07-29 10:51:23', 0.03, 0),
(18, 174, 177, 0.27, 2.00, 0.00, '2024-07-29 10:51:23', 0.00, 0),
(19, 174, 175, 0.22, 10.00, 0.00, '2024-07-29 10:57:51', 0.00, 0),
(20, 175, 176, 0.22, 10.00, 0.00, '2024-07-29 10:57:51', 0.02, 0),
(21, 174, 176, 0.22, 5.00, 0.00, '2024-07-29 10:57:51', 0.00, 0),
(22, 176, 177, 0.22, 10.00, 0.00, '2024-07-29 10:57:51', 0.02, 0),
(23, 175, 177, 0.22, 5.00, 0.00, '2024-07-29 10:57:52', 0.02, 0),
(24, 174, 177, 0.22, 2.00, 0.00, '2024-07-29 10:57:52', 0.00, 0),
(25, 174, 175, 0.18, 10.00, 0.00, '2024-07-29 11:02:54', 0.00, 0),
(26, 175, 176, 0.18, 10.00, 0.00, '2024-07-29 11:02:54', 0.02, 0),
(27, 174, 176, 0.18, 5.00, 0.00, '2024-07-29 11:02:54', 0.00, 0),
(28, 176, 177, 0.18, 10.00, 0.00, '2024-07-29 11:02:54', 0.02, 0),
(29, 175, 177, 0.18, 5.00, 0.00, '2024-07-29 11:02:54', 0.02, 0),
(30, 174, 177, 0.18, 2.00, 0.00, '2024-07-29 11:02:54', 0.00, 0),
(31, 174, 175, 0.18, 10.00, 0.00, '2024-07-29 14:50:25', 0.00, 0),
(32, 175, 176, 0.18, 10.00, 0.00, '2024-07-29 14:50:25', 0.02, 0),
(33, 174, 176, 0.18, 5.00, 0.00, '2024-07-29 14:50:25', 0.00, 0),
(34, 176, 177, 0.18, 10.00, 0.00, '2024-07-29 14:50:25', 0.02, 0),
(35, 175, 177, 0.18, 5.00, 0.00, '2024-07-29 14:50:25', 0.02, 0),
(36, 174, 177, 0.18, 2.00, 0.00, '2024-07-29 14:50:25', 0.00, 0),
(37, 174, 175, 0.18, 10.00, 0.00, '2024-07-29 14:50:58', 0.00, 0),
(38, 175, 176, 0.18, 10.00, 0.00, '2024-07-29 14:50:58', 0.02, 0),
(39, 174, 176, 0.18, 5.00, 0.00, '2024-07-29 14:50:58', 0.00, 0),
(40, 176, 177, 0.18, 10.00, 0.00, '2024-07-29 14:50:58', 0.02, 0),
(41, 175, 177, 0.18, 5.00, 0.00, '2024-07-29 14:50:58', 0.02, 0),
(42, 174, 177, 0.18, 2.00, 0.00, '2024-07-29 14:50:58', 0.00, 0);

-- --------------------------------------------------------

--
-- Table structure for table `rewards`
--

CREATE TABLE `rewards` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reward_points` int(11) DEFAULT 0,
  `referral_count` int(11) DEFAULT 0,
  `level_one_count` int(11) DEFAULT 0,
  `level_two_count` int(11) DEFAULT 0,
  `level_three_count` int(11) DEFAULT 0,
  `level_two_unlocked` tinyint(1) DEFAULT 0,
  `level_three_unlocked` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rewards`
--

INSERT INTO `rewards` (`id`, `user_id`, `reward_points`, `referral_count`, `level_one_count`, `level_two_count`, `level_three_count`, `level_two_unlocked`, `level_three_unlocked`) VALUES
(1, 174, 0, 1, 1, 1, 1, 1, 1),
(2, 175, 5, 1, 1, 1, 0, 0, 0),
(3, 176, 5, 1, 1, 0, 0, 0, 0),
(4, 177, 0, 0, 0, 0, 0, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `reward_logs`
--

CREATE TABLE `reward_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reward_amount` decimal(10,2) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stakings`
--

CREATE TABLE `stakings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_staking` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','deactivated') NOT NULL DEFAULT 'active',
  `withdrawn` tinyint(1) NOT NULL DEFAULT 0,
  `total_earning` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_tripled` tinyint(1) NOT NULL DEFAULT 0,
  `estimated_earning` double DEFAULT 0,
  `remaining_earning` double DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `last_calculation_date` date DEFAULT NULL,
  `daily_calculation_count` int(11) DEFAULT 0,
  `first_run_earning` decimal(10,2) DEFAULT 0.00,
  `second_run_earning` decimal(10,2) DEFAULT 0.00,
  `third_run_earning` decimal(10,2) DEFAULT 0.00,
  `rewarded` tinyint(1) DEFAULT 0,
  `commission_processed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stakings`
--

INSERT INTO `stakings` (`id`, `user_id`, `amount`, `created_at`, `total_staking`, `status`, `withdrawn`, `total_earning`, `is_tripled`, `estimated_earning`, `remaining_earning`, `start_date`, `is_verified`, `last_calculation_date`, `daily_calculation_count`, `first_run_earning`, `second_run_earning`, `third_run_earning`, `rewarded`, `commission_processed`) VALUES
(120, 175, 50.00, '2024-07-29 05:37:53', 0.00, 'active', 0, 1.41, 0, 150, 148.62499999999994, NULL, 0, NULL, 7, 0.00, 0.00, 0.00, 0, 0),
(121, 176, 50.00, '2024-07-29 05:39:50', 0.00, 'active', 0, 1.41, 0, 150, 148.62499999999994, NULL, 0, NULL, 7, 0.00, 0.00, 0.00, 0, 0),
(122, 177, 50.00, '2024-07-29 05:42:08', 0.00, 'active', 0, 1.41, 0, 150, 148.62499999999994, NULL, 0, NULL, 7, 0.00, 0.00, 0.00, 0, 0),
(123, 174, 50.00, '2024-07-29 09:49:07', 0.00, 'active', 0, 0.36, 0, 150, 149.64999999999998, NULL, 0, NULL, 2, 0.00, 0.00, 0.00, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `staking_requests`
--

CREATE TABLE `staking_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stake_amount` decimal(10,2) NOT NULL,
  `random_string` varchar(255) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staking_requests`
--

INSERT INTO `staking_requests` (`id`, `user_id`, `stake_amount`, `random_string`, `status`, `request_date`) VALUES
(1, 175, 50.00, '3fc4e87b88c8e490b31635bc7de4acc2d9e58e0400bf18d35ce981bf6fc9c867f4082428042cbc3385d4889bb5f881b07a6a', 'accepted', '2024-07-29 05:37:43'),
(2, 176, 50.00, 'ad9ff5c921d28b2de383da996f742a52814094b6a958693e41fde85011139f2567053d73aedb50c6e77b4ea17f033ccd1c47', 'accepted', '2024-07-29 05:39:43'),
(3, 177, 50.00, '5859fb30248bd2e46b554b539f6e3af4147e0f213033e042c0d444bc829287132d38d80916c66c828babaeb024063f7e5dee', 'accepted', '2024-07-29 05:42:04'),
(4, 174, 50.00, '1e633736acd682dac9cef45686c327e16988db5708e008acc85248dcfd96b49cab28a8bdc82c2eeba778d98f7a11b1d9d3d5', 'accepted', '2024-07-29 09:49:04');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `screenshot` varchar(255) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `referrer_rewarded` tinyint(1) DEFAULT 0,
  `rewarded` tinyint(1) DEFAULT 0,
  `payment_method` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `amount`, `screenshot`, `transaction_id`, `status`, `created_at`, `updated_at`, `referrer_rewarded`, `rewarded`, `payment_method`) VALUES
(1, 174, 10.00, 'images.jpg', 'bvbccv', 'accepted', '2024-07-29 05:33:20', '2024-07-29 05:33:25', 0, 0, 'Valid Cash'),
(2, 175, 10.00, 'images.jpg', '7657', 'accepted', '2024-07-29 05:36:45', '2024-07-29 05:42:21', 0, 1, 'Easy Paisa'),
(3, 176, 10.00, 'images.jpg', 'hgfnbvnbv', 'accepted', '2024-07-29 05:39:07', '2024-07-29 05:42:21', 0, 1, 'Valid Cash'),
(4, 177, 10.00, 'images.jpg', 'gfdtrewew', 'accepted', '2024-07-29 05:41:14', '2024-07-29 05:42:21', 0, 1, 'Valid Cash');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `random_string` varchar(255) NOT NULL,
  `referrer_id` int(11) DEFAULT NULL,
  `referral_code` varchar(50) DEFAULT NULL,
  `referred_bonus_received` tinyint(1) NOT NULL DEFAULT 0,
  `username` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `random_string`, `referrer_id`, `referral_code`, `referred_bonus_received`, `username`) VALUES
(174, 'NAWAB', 'shahidjhawari@gmail.com', '$2y$10$rPO8W3S7XmgDQUJQjdYgSe8sLhkl3FFIINLWp9DocC01vkEdQy8ka', '1e633736acd682dac9cef45686c327e16988db5708e008acc85248dcfd96b49cab28a8bdc82c2eeba778d98f7a11b1d9d3d5', NULL, '8wJydCIF', 0, 'shahidjhawari'),
(175, 'Shahid Iqbal', 'shahidiqbaljhawari@gmail.com', '$2y$10$GY0X2UzEhftYIA54dG9y/OeuaHwPDfKqrN9CTrkbMikgjEnuLD0Ia', '3fc4e87b88c8e490b31635bc7de4acc2d9e58e0400bf18d35ce981bf6fc9c867f4082428042cbc3385d4889bb5f881b07a6a', 174, 'P1ofhwXK', 0, 'shahidiqbaljhawari'),
(176, 'NEW ACADEMY', 'new@gmail.com', '$2y$10$XZR1CPpMtrc88Rt0KYKtPeF/DEIhBx4UigAGloZMuttFAUAzDHM5e', 'ad9ff5c921d28b2de383da996f742a52814094b6a958693e41fde85011139f2567053d73aedb50c6e77b4ea17f033ccd1c47', 175, 'R3G8oZ51', 0, 'nawabacademy'),
(177, '123 NAWAB', '123@gmail.com', '$2y$10$R1tW0T5o3FtTvN1l1Kk/oO8QskC/iHG.HfVqpSHrzC2LSAjvPMvN.', '5859fb30248bd2e46b554b539f6e3af4147e0f213033e042c0d444bc829287132d38d80916c66c828babaeb024063f7e5dee', 176, 'LWDm5MgF', 0, 'nawabacademy123');

-- --------------------------------------------------------

--
-- Table structure for table `user_payments`
--

CREATE TABLE `user_payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `payment_method` enum('Easy Paisa','Jazz Cash','Simple Pay','Dollar') NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('Pending','Accepted','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_totals`
--

CREATE TABLE `user_totals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_daily_earning` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_rewards_points` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_messages`
--
ALTER TABLE `admin_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bonus_rewards`
--
ALTER TABLE `bonus_rewards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `daily_earnings`
--
ALTER TABLE `daily_earnings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `staking_id` (`staking_id`);

--
-- Indexes for table `deposits`
--
ALTER TABLE `deposits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `referral_rewards`
--
ALTER TABLE `referral_rewards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `referrer_id` (`referrer_id`),
  ADD KEY `referred_user_id` (`referred_user_id`);

--
-- Indexes for table `rewards`
--
ALTER TABLE `rewards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reward_logs`
--
ALTER TABLE `reward_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `stakings`
--
ALTER TABLE `stakings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staking_requests`
--
ALTER TABLE `staking_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `referral_code` (`referral_code`),
  ADD KEY `referrer_id` (`referrer_id`);

--
-- Indexes for table `user_payments`
--
ALTER TABLE `user_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_totals`
--
ALTER TABLE `user_totals`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_messages`
--
ALTER TABLE `admin_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bonus_rewards`
--
ALTER TABLE `bonus_rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `daily_earnings`
--
ALTER TABLE `daily_earnings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `deposits`
--
ALTER TABLE `deposits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `referral_rewards`
--
ALTER TABLE `referral_rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `rewards`
--
ALTER TABLE `rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reward_logs`
--
ALTER TABLE `reward_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stakings`
--
ALTER TABLE `stakings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `staking_requests`
--
ALTER TABLE `staking_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=178;

--
-- AUTO_INCREMENT for table `user_payments`
--
ALTER TABLE `user_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_totals`
--
ALTER TABLE `user_totals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bonus_rewards`
--
ALTER TABLE `bonus_rewards`
  ADD CONSTRAINT `bonus_rewards_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `daily_earnings`
--
ALTER TABLE `daily_earnings`
  ADD CONSTRAINT `daily_earnings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `daily_earnings_ibfk_2` FOREIGN KEY (`staking_id`) REFERENCES `stakings` (`id`);

--
-- Constraints for table `referral_rewards`
--
ALTER TABLE `referral_rewards`
  ADD CONSTRAINT `referral_rewards_ibfk_1` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `referral_rewards_ibfk_2` FOREIGN KEY (`referred_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `rewards`
--
ALTER TABLE `rewards`
  ADD CONSTRAINT `rewards_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `reward_logs`
--
ALTER TABLE `reward_logs`
  ADD CONSTRAINT `reward_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `staking_requests`
--
ALTER TABLE `staking_requests`
  ADD CONSTRAINT `staking_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
