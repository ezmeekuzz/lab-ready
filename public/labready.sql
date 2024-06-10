-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 10, 2024 at 09:57 AM
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
-- Database: `labready`
--

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `quotation_id` int(11) NOT NULL,
  `productname` varchar(100) NOT NULL,
  `productprice` double(16,2) NOT NULL,
  `invoicefile` varchar(110) NOT NULL,
  `quotationdate` date NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotation_items`
--

CREATE TABLE `quotation_items` (
  `quotation_item_id` int(11) NOT NULL,
  `request_quotation_id` int(11) NOT NULL,
  `partnumber` varchar(20) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `quotetype` varchar(100) DEFAULT NULL,
  `material` varchar(100) DEFAULT NULL,
  `filename` varchar(100) NOT NULL,
  `filetype` varchar(20) NOT NULL,
  `file_location` varchar(110) DEFAULT NULL,
  `stl_location` varchar(110) DEFAULT NULL,
  `print_location` varchar(110) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `request_quotations`
--

CREATE TABLE `request_quotations` (
  `request_quotation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `datesubmitted` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request_quotations`
--

INSERT INTO `request_quotations` (`request_quotation_id`, `user_id`, `status`, `datesubmitted`) VALUES
(40, 87, 'Done', '2024-06-06'),
(41, 87, 'Done', '2024-06-07'),
(42, 87, 'Done', '2024-06-07'),
(43, 87, 'Pending', '2024-06-08'),
(44, 87, 'Pending', '2024-06-08'),
(45, 87, 'Pending', '2024-06-08'),
(46, 87, 'Done', '2024-06-08'),
(47, 87, 'Ongoing', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `subscriber_id` int(11) NOT NULL,
  `emailaddress` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscribers`
--

INSERT INTO `subscribers` (`subscriber_id`, `emailaddress`) VALUES
(4, 'rustomcodilan@gmail.com'),
(5, 'rustomlacrecodilan@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `email` varchar(60) NOT NULL,
  `password` varchar(100) NOT NULL,
  `encryptedpass` varchar(250) NOT NULL,
  `usertype` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `fullname`, `email`, `password`, `encryptedpass`, `usertype`) VALUES
(1, 'Rustom Codilan', 'rustomcodilan@gmail.com', 'mis137', '$2y$10$15fWi5F2Qm9mxde1Fm49ee7ahybJjgtOQMr1I4xcVk0NXHXRAC5mi', 'Administrator'),
(87, 'Ralph Patrick Abrio', 'rustomlacrecodilan@gmail.com', 'mis137', '$2y$10$mCQO1vlomiSUSTU.l/W..uFvR2wwuujcEKX6ZXMVDoAg/X9m/.uGi', 'Regular User');

-- --------------------------------------------------------

--
-- Table structure for table `user_quotations`
--

CREATE TABLE `user_quotations` (
  `user_quotation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quotation_id` int(11) NOT NULL,
  `dateforwarded` date NOT NULL,
  `readstatus` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`quotation_id`);

--
-- Indexes for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD PRIMARY KEY (`quotation_item_id`),
  ADD KEY `request_quotation_id` (`request_quotation_id`);

--
-- Indexes for table `request_quotations`
--
ALTER TABLE `request_quotations`
  ADD PRIMARY KEY (`request_quotation_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`subscriber_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_quotations`
--
ALTER TABLE `user_quotations`
  ADD PRIMARY KEY (`user_quotation_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `quotation_id` (`quotation_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `quotation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `quotation_items`
--
ALTER TABLE `quotation_items`
  MODIFY `quotation_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=159;

--
-- AUTO_INCREMENT for table `request_quotations`
--
ALTER TABLE `request_quotations`
  MODIFY `request_quotation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `subscriber_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `user_quotations`
--
ALTER TABLE `user_quotations`
  MODIFY `user_quotation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD CONSTRAINT `quotation_items_ibfk_1` FOREIGN KEY (`request_quotation_id`) REFERENCES `request_quotations` (`request_quotation_id`);

--
-- Constraints for table `request_quotations`
--
ALTER TABLE `request_quotations`
  ADD CONSTRAINT `request_quotations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `user_quotations`
--
ALTER TABLE `user_quotations`
  ADD CONSTRAINT `user_quotations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `user_quotations_ibfk_2` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`quotation_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
