-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 16, 2024 at 06:30 AM
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
(0, 'admin', 'admin'),
(0, 'admin', 'admin');

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
(1, 137, 83, '2024-06-16', 3.60, 0, '2024-06-16 04:23:15', 0, 0, NULL, 1),
(2, 137, 83, '2024-06-16', 2.80, 0, '2024-06-16 04:24:33', 0, 0, NULL, 1),
(3, 137, 83, '2024-06-16', 4.40, 0, '2024-06-16 04:26:12', 0, 0, NULL, 1);

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deposits`
--

INSERT INTO `deposits` (`id`, `user_id`, `amount`, `screenshot`, `transaction_id`, `status`, `created_at`) VALUES
(1, 137, 100.00, '1714497546738.jpg', 'gfdgfdg', 'Accepted', '2024-06-16 04:23:02');

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
  `user_10_percent_reward` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `referral_rewards`
--

INSERT INTO `referral_rewards` (`id`, `referrer_id`, `referred_user_id`, `daily_earning_amount`, `reward_percentage`, `reward_amount`, `reward_date`, `user_10_percent_reward`) VALUES
(1, 136, 137, 3.60, 10.00, 0.00, '2024-06-16 09:24:37', 0.36),
(2, 136, 137, 2.80, 10.00, 0.00, '2024-06-16 09:24:37', 0.28),
(3, 136, 137, 4.40, 10.00, 0.00, '2024-06-16 09:26:13', 0.44);

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
  `level_three_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rewards`
--

INSERT INTO `rewards` (`id`, `user_id`, `reward_points`, `referral_count`, `level_one_count`, `level_two_count`, `level_three_count`) VALUES
(1, 136, 10, 1, 1, 0, 0),
(2, 137, 0, 0, 0, 0, 0);

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
(83, 137, 800.00, '2024-06-16 04:23:15', 0.00, 'active', 0, 10.80, 0, 2400, 2388.7999999999997, NULL, 0, NULL, 2, 0.00, 0.00, 0.00, 0, 0);

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
  `rewarded` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `amount`, `screenshot`, `transaction_id`, `status`, `created_at`, `updated_at`, `referrer_rewarded`, `rewarded`) VALUES
(1, 137, 10.00, '1714497546738.jpg', 'sdfdsfsdf', 'accepted', '2024-06-16 04:22:23', '2024-06-16 04:24:24', 0, 1);

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
  `referred_bonus_received` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `random_string`, `referrer_id`, `referral_code`, `referred_bonus_received`) VALUES
(136, 'NAWAB', 'shahidjhawari@gmail.com', '$2y$10$OPZYXCnHU9oPSORQ3Dy2X.CfDwJOwqGCiIhVK1GzDnTsd4cst7XVO', 'ebcb759d0791a37af27861f318b3999733ddbd17b4d5261f01ff8b8a647c95ba95187ecdf08139e4f431862b1bc007ff1d85', NULL, 'MjJ1uVat', 0),
(137, 'NAWAB ACADEMY', 'shahidiqbaljhawari@gmail.com', '$2y$10$5U9.mV1Ig6cFvokPKjn2P.cmH9HB6VVarxlP5mQk.xWgq0/AfQgUm', '15e9add06c212e3f88c2e1ebc857c3a5d65ef425d0d521193377782e5907f6bcc6c477b069bc0705ec1174b7d1dfbc771d0b', 136, 'U3JuYR5F', 0);

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `daily_earnings`
--
ALTER TABLE `daily_earnings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `deposits`
--
ALTER TABLE `deposits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `referral_rewards`
--
ALTER TABLE `referral_rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rewards`
--
ALTER TABLE `rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reward_logs`
--
ALTER TABLE `reward_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stakings`
--
ALTER TABLE `stakings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- Constraints for dumped tables
--

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
