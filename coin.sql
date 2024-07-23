-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 23, 2024 at 12:28 PM
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
  `bonus_claimed_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 154, 0, 0, 0, 0, 0);

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
(154, 'NAWAB', 'shahidjhawari@gmail.com', '$2y$10$LI40l0Q5KHsFX1CdDoSQ1.PBA/M.bFgjklNFpqSPfUFjpaSAMoRz6', '4e39b891a8a24c986bf002b9c29e3d72af7811724ea043b98e84679788a06dd2cb98f493c2d5faa25e4efa86521f36795250', NULL, '34yJjLrs', 0, 'shahidiqbaljhawari');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deposits`
--
ALTER TABLE `deposits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referral_rewards`
--
ALTER TABLE `referral_rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rewards`
--
ALTER TABLE `rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reward_logs`
--
ALTER TABLE `reward_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stakings`
--
ALTER TABLE `stakings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `staking_requests`
--
ALTER TABLE `staking_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

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
