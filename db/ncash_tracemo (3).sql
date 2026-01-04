-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: Dec 17, 2025 at 02:14 PM
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
-- Database: `ncash_tracemo`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` enum('Accessed','Created','Edited','Archive','Deleted') NOT NULL,
  `table_name` varchar(20) NOT NULL,
  `record_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `branch_id` int(11) NOT NULL,
  `branch_name` varchar(20) NOT NULL,
  `address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`branch_id`, `branch_name`, `address`) VALUES
(1100, 'Marikina-Pasig', 'L2 C5-A Santolan Arcade Building, Marcos Highway, Pasig, Metro Manila'),
(1101, 'Quezon City', 'Ground Floor 20, Lansbergh Place, 170 Tomas Morato Ave, Diliman, Quezon City, 1103 Metro Manila'),
(1102, 'Makati', 'UG-22, Cityland Pasong Tamo Tower, 2210 Chino Roces Ave, Makati City, 1230 Metro Manila');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int(11) NOT NULL,
  `fullname` varchar(50) NOT NULL,
  `contact` bigint(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`client_id`, `fullname`, `contact`, `email`, `address`, `created_at`) VALUES
(1, 'Peter Gabriel Perez', 912345678, 'testing@gmail.com', 'Cainta, Rizal', '2025-11-04 01:25:32'),
(2, 'Kirby Renzel Cruz', 912345678, 'testing2@gmail.com', 'Antipolo, Rizal', '2025-11-04 01:35:16'),
(3, 'Szymon Laurence Mahinay', 9383876484, 'mahinayszymon@gmail.com', 'Block 7 Lot 10, Biela Street, West Groves, Maia Alta Subdivision', '2025-11-10 09:48:46'),
(4, 'Kenyon Matthew Caubalejo', 9123456789, 'KenyonEsports@gmail.com', '2A Turkey Street, Kabayani Road, Purok 6', '2025-11-10 15:26:03'),
(5, 'John Phillip Atienza', 9123456780, 'test@gmail.com', '2A Turkey Street, Kabayani Road, Purok 6', '2025-12-10 08:17:46'),
(6, 'Amelia Rodriguez Santos', 9205551234, 'amelia.santos@mail.com', '24 P. Burgos St., Brgy. Sto. Niño, Marikina City', '2025-12-10 08:37:47'),
(7, 'John Robert De Guzman', 9205557788, 'robert.jhn@gmail.com', 'Block 2 Lot 4, Gardens, Maia Alta Subdivision, Antipolo, Rizal', '2025-12-12 11:04:46');

-- --------------------------------------------------------

--
-- Table structure for table `clients_archive`
--

CREATE TABLE `clients_archive` (
  `archive_id` int(11) NOT NULL,
  `archived_by` varchar(50) NOT NULL,
  `archived_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `client_id` int(11) NOT NULL,
  `fullname` varchar(50) NOT NULL,
  `contact` bigint(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reason` text NOT NULL,
  `delete_period` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deletion_request`
--

CREATE TABLE `deletion_request` (
  `request_id` int(11) NOT NULL,
  `table_name` varchar(20) NOT NULL,
  `record_id` int(11) NOT NULL,
  `requested_by` varchar(50) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` varchar(20) NOT NULL,
  `approved_by` varchar(50) NOT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `item_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `item_name` varchar(50) NOT NULL,
  `category` varchar(20) NOT NULL,
  `agreement_num` int(11) NOT NULL,
  `principal` decimal(9,2) NOT NULL,
  `status` enum('Active','Redeemed','Overdue') NOT NULL,
  `due_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `remarks` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(255) NOT NULL,
  `updated_by` varchar(255) NOT NULL,
  `interest` decimal(9,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`item_id`, `client_id`, `branch_id`, `item_name`, `category`, `agreement_num`, `principal`, `status`, `due_date`, `remarks`, `created_at`, `updated_at`, `created_by`, `updated_by`, `interest`) VALUES
(1, 1, 1100, 'Samsung Galaxy S21 Ultra', 'Smartphone', 100, 10000.00, 'Redeemed', '2025-11-07 01:20:26', 'Testing ', '2025-11-04 01:24:06', '2025-12-10 03:07:00', 'admin', 'admin', 800.00),
(2, 1, 1100, 'PlayStation 4', 'Console', 101, 7000.00, 'Active', '2026-01-11 09:06:46', 'Testing Again', '2025-11-04 01:33:44', '2025-12-06 11:20:30', 'admin', 'admin', 560.00),
(3, 2, 1101, 'Furina Earbuds ', 'Earbuds', 102, 5500.00, 'Redeemed', '2025-11-08 01:38:30', 'Napaka Furina nga kasi', '2025-11-04 01:38:30', '2025-12-06 04:41:07', 'perer', 'perer', 440.00),
(4, 2, 1101, 'Samsung Galaxy Z-Flip', 'Smartphone', 104, 8000.00, 'Redeemed', '2025-11-02 04:42:48', 'Flip phone', '2025-11-04 04:44:23', '2025-12-06 11:20:30', 'perer', 'perer', 640.00),
(5, 3, 1100, 'Iphone 8 Plus', 'Electronic Gadgets', 105, 4000.00, 'Overdue', '2025-12-09 16:00:00', 'for testing', '2025-11-10 09:48:46', '2025-12-06 11:20:30', 'admin', 'admin', 40.00),
(6, 3, 1100, 'Rolex G10', 'Personal Accessories', 106, 4000.00, 'Overdue', '2025-11-24 16:00:00', 'test', '2025-11-10 10:19:24', '2025-12-06 11:20:30', 'admin', 'admin', 80.00),
(7, 3, 1100, 'Mitsubishi Xpander 2023 Model', 'Vehicles', 107, 300000.00, 'Active', '2026-01-05 04:38:43', 'car', '2025-11-10 10:23:09', '2025-12-06 11:20:30', 'admin', 'admin', 3000.00),
(8, 3, 1100, 'Mitsubishi Xpander 2024 Model', 'Vehicles', 108, 300000.00, 'Active', '2026-01-11 09:05:59', 'ff', '2025-11-10 10:24:55', '2025-12-06 11:20:30', 'admin', 'admin', 12000.00),
(9, 3, 1100, 'Mitsubishi Xpander 2025 Model', 'Vehicles', 109, 300000.00, 'Active', '2026-01-11 09:05:30', 'carr', '2025-11-10 10:58:53', '2025-12-06 11:20:30', 'admin', 'admin', 3000.00),
(10, 3, 1100, 'Sample', 'Personal Accessories', 110, 4000.00, 'Redeemed', '2026-01-05 04:45:30', 'dd', '2025-11-10 11:34:28', '2025-12-10 02:54:57', 'admin', 'admin', 80.00),
(11, 4, 1100, 'Sample', 'Personal Accessories', 111, 3500.00, 'Redeemed', '2026-01-05 04:37:44', 'None', '2025-11-10 15:26:03', '2025-12-10 03:07:45', 'admin', 'admin', 140.00),
(12, 3, 1100, 'Iphone 8 Plus', 'Electronic Gadgets', 112, 4000.00, 'Active', '2026-02-08 02:35:22', 'None', '2025-11-23 04:04:35', '2025-12-06 11:20:30', 'admin', 'admin', 80.00),
(13, 5, 1100, 'Suzuki Spresso 2023 Model Automatic', 'Vehicles', 113, 600000.00, 'Active', '2026-01-09 16:00:00', 'None', '2025-12-10 08:17:46', '2025-12-10 08:17:46', 'admin', 'admin', 12000.00),
(14, 6, 1100, 'Rolex Submariner (Serial XYZ)', 'Personal Accessories', 114, 50000.00, 'Redeemed', '2026-03-09 16:00:00', 'All original paperwork included. Item appraised at 75000.', '2025-12-10 08:37:47', '2025-12-17 12:19:33', 'admin', 'admin', 5000.00),
(15, 7, 1101, 'Toyota Raize 1.0 Turbo CVT', 'Vehicles', 115, 999500.00, 'Active', '2026-01-29 16:00:00', 'All original paperwork included. ', '2025-12-12 11:04:46', '2025-12-12 11:04:46', 'perer', 'perer', 79960.00),
(16, 3, 1100, 'Sample', 'Personal Accessories', 115, 4000.00, 'Overdue', '2025-12-11 16:00:00', 'None', '2025-12-12 12:03:50', '2025-12-12 12:03:50', 'admin', 'admin', 80.00),
(18, 3, 1101, 'Rolex G10', 'Personal Accessories', 116, 4000.00, 'Overdue', '2025-12-15 16:00:00', 'None', '2025-12-13 13:42:31', '2025-12-13 13:42:31', 'perer', 'perer', 80.00),
(19, 3, 1100, 'Iphone 8 Plus', 'Personal Accessories', 117, 4000.00, 'Overdue', '2025-12-15 16:00:00', 'None', '2025-12-13 14:52:56', '2025-12-13 14:52:56', 'admin', 'admin', 160.00),
(20, 3, 1101, 'Sample', 'Electronic Gadgets', 117, 4000.00, 'Active', '2025-12-18 16:00:00', 'None', '2025-12-13 14:53:30', '2025-12-13 14:53:30', 'perer', 'perer', 160.00);

-- --------------------------------------------------------

--
-- Table structure for table `items_archive`
--

CREATE TABLE `items_archive` (
  `archive_id` int(11) NOT NULL,
  `archived_by` varchar(50) NOT NULL,
  `archived_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `item_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `item_name` varchar(50) NOT NULL,
  `category` varchar(20) NOT NULL,
  `agreement_num` int(11) NOT NULL,
  `principal` decimal(9,2) NOT NULL,
  `status` enum('Active','Redeemed','Overdue') NOT NULL,
  `due_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `remarks` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(255) NOT NULL,
  `updated_by` varchar(255) NOT NULL,
  `interest` decimal(9,2) NOT NULL,
  `reason` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items_archive`
--

INSERT INTO `items_archive` (`archive_id`, `archived_by`, `archived_date`, `item_id`, `client_id`, `branch_id`, `item_name`, `category`, `agreement_num`, `principal`, `status`, `due_date`, `remarks`, `created_at`, `updated_at`, `created_by`, `updated_by`, `interest`, `reason`) VALUES
(0, 'admin', '2025-12-17 12:39:05', 17, 3, 1100, 'Sample', 'Personal Accessories', 116, 4000.00, 'Redeemed', '2025-12-12 16:00:00', 'None', '2025-12-12 12:29:01', '2025-12-12 09:06:26', 'admin', 'admin', 80.00, 'Historical Review Complete');

-- --------------------------------------------------------

--
-- Table structure for table `notifs`
--

CREATE TABLE `notifs` (
  `notif_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` enum('SMS','Email') NOT NULL,
  `status` varchar(20) NOT NULL,
  `date_sent` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifs`
--

INSERT INTO `notifs` (`notif_id`, `branch_id`, `client_id`, `message`, `type`, `status`, `date_sent`) VALUES
(1, 1100, 1, 'Your item with agreement number blah blah', 'SMS', 'Sent', '2025-11-04 05:59:08');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `report_type` enum('Weekly','Monthly') NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `version` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `agreement_num` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `amount` decimal(9,2) NOT NULL,
  `type_of_pay` enum('Principal','Interest') NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `edited_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `edited_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `method` enum('Cash','Online','Bank') NOT NULL,
  `paid_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `agreement_num`, `client_id`, `branch_id`, `item_id`, `amount`, `type_of_pay`, `created_by`, `edited_by`, `created_at`, `edited_at`, `method`, `paid_date`) VALUES
(1, 114, 6, 1100, 14, 50000.00, 'Principal', 'admin', '', '2025-12-17 12:19:33', '2025-12-17 12:19:33', 'Cash', '2026-03-09 16:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `transactions_archive`
--

CREATE TABLE `transactions_archive` (
  `archive_id` int(11) NOT NULL,
  `archived_by` varchar(50) NOT NULL,
  `archived_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `transaction_id` int(11) NOT NULL,
  `agreement_num` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `amount` decimal(9,2) NOT NULL,
  `type_of_pay` enum('Principal','Interest') NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `edited_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `edited_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `method` enum('Cash','Online','Bank') NOT NULL,
  `paid_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `reason` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(100) NOT NULL,
  `fullname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(5) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `fullname`, `email`, `role`, `branch_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$NxI5B9HuzYtVoY/71WpeAugfNgMt.sSmPwbcLCs28WkuAyW6f0rrG', 'System Developer', 'mahinayszymon@gmail.com', 'admin', 1100, 'active', '2025-12-17 12:51:10', '2025-12-17 05:51:10'),
(2, 'user', '$2y$10$Os0gNYUK3BY971HJ8zeAfOAYivjmxSMAgJVnwFsrLd1TftrOVbXGG', 'Test User', 'test@gmail.com', 'user', 1100, 'active', '2025-12-06 13:35:37', '2025-12-06 11:22:43'),
(3, 'perer', '$2y$10$dqBnzKpw/4aBc9vsc.gedewtumIeUmq8sGhi.5A7ofgzZAN3GmRzi', 'Peter Perez', 'test123@gmail.com', 'user', 1101, 'active', '2025-12-06 13:36:03', '2025-12-06 11:22:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`branch_id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`);

--
-- Indexes for table `clients_archive`
--
ALTER TABLE `clients_archive`
  ADD PRIMARY KEY (`archive_id`);
ALTER TABLE `clients_archive` ADD FULLTEXT KEY `fullname` (`fullname`);

--
-- Indexes for table `deletion_request`
--
ALTER TABLE `deletion_request`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `items_archive`
--
ALTER TABLE `items_archive`
  ADD PRIMARY KEY (`archive_id`);

--
-- Indexes for table `notifs`
--
ALTER TABLE `notifs`
  ADD PRIMARY KEY (`notif_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `transactions_archive`
--
ALTER TABLE `transactions_archive`
  ADD PRIMARY KEY (`archive_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `branch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1103;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `clients_archive`
--
ALTER TABLE `clients_archive`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deletion_request`
--
ALTER TABLE `deletion_request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `notifs`
--
ALTER TABLE `notifs`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transactions_archive`
--
ALTER TABLE `transactions_archive`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
