-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 29, 2024 at 08:00 AM
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
-- Table structure for table `assembly_print_files`
--

CREATE TABLE `assembly_print_files` (
  `assembly_print_file_id` int(11) NOT NULL,
  `request_quotation_id` int(11) NOT NULL,
  `assembly_print_file_location` varchar(110) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assembly_print_files`
--

INSERT INTO `assembly_print_files` (`assembly_print_file_id`, `request_quotation_id`, `assembly_print_file_location`) VALUES
(48, 115, 'uploads/assembly-files/1722086069_5ca9fae3ed0854201a10.pdf'),
(49, 115, 'uploads/assembly-files/1722086069_91643cfab0ce5abdba08.pdf'),
(50, 116, 'uploads/assembly-files/1722090413_4c5b3bc27018057a85ed.pdf'),
(51, 116, 'uploads/assembly-files/1722090413_9f4a2445d83c76d3f141.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `quotation_id` int(11) NOT NULL,
  `request_quotation_id` int(11) NOT NULL,
  `productname` varchar(100) NOT NULL,
  `productprice` double(16,2) NOT NULL,
  `invoicefile` varchar(110) NOT NULL,
  `address` longtext DEFAULT NULL,
  `city` varchar(110) DEFAULT NULL,
  `state` varchar(10) DEFAULT NULL,
  `zipcode` varchar(20) DEFAULT NULL,
  `phonenumber` varchar(20) DEFAULT NULL,
  `quotationdate` date NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`quotation_id`, `request_quotation_id`, `productname`, `productprice`, `invoicefile`, `address`, `city`, `state`, `zipcode`, `phonenumber`, `quotationdate`, `status`) VALUES
(57, 115, 'OFF!® Overtime', 558.00, '/uploads/PDFs/1722093357_f0a332e3028f2dbdef35.pdf', '1 Main St', 'San Jose', 'CA', '95131', NULL, '2024-07-27', 'Paid');

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

--
-- Dumping data for table `quotation_items`
--

INSERT INTO `quotation_items` (`quotation_item_id`, `request_quotation_id`, `partnumber`, `quantity`, `quotetype`, `material`, `filename`, `filetype`, `file_location`, `stl_location`, `print_location`) VALUES
(349, 115, '20-2753.STEP', 1, '3D Printing', 'PETG', '20-2753.STEP', 'STEP', 'uploads/quotation-files/61d381984662b96e.STEP', 'uploads/quotation-files/3e462fb084783a89.stl', 'uploads/print-files/1722086069_e56078805b3df0280caf.pdf'),
(350, 115, '20-2752.STEP', 1, 'CNC Machine', 'PEI (Ultem)', '20-2752.STEP', 'STEP', 'uploads/quotation-files/c19bf487cc2952fb.STEP', 'uploads/quotation-files/8834705399d3ff02.stl', 'uploads/print-files/1722086069_f59b2a209981ba0ab31b.pdf'),
(353, 116, '20-2753.STEP', 1, '3D Printing', 'Stainless Steel', '20-2753.STEP', 'STEP', 'uploads/quotation-files/0d61890fdda3c335.STEP', 'uploads/quotation-files/c61f863bee4e61fb.stl', 'uploads/print-files/1722090413_8a66afcf42f1b4be24b6.pdf'),
(354, 116, '20-2752.STEP', 3, 'CNC Machine', 'POM (Acetal/Delrin)', '20-2752.STEP', 'STEP', 'uploads/quotation-files/faf0dd237a8841e3.STEP', 'uploads/quotation-files/b566553c4aca5e74.stl', 'uploads/print-files/1722090413_943589ecd92c7ac3fe2f.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `request_quotations`
--

CREATE TABLE `request_quotations` (
  `request_quotation_id` int(11) NOT NULL,
  `reference` varchar(15) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `datesubmitted` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request_quotations`
--

INSERT INTO `request_quotations` (`request_quotation_id`, `reference`, `user_id`, `status`, `datesubmitted`) VALUES
(115, '20240727-001', 90, 'Done', '2024-07-27'),
(116, '20240727-002', 90, 'Pending', '2024-07-27');

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `shipment_id` int(11) NOT NULL,
  `quotation_id` int(11) NOT NULL,
  `shipment_address` varchar(110) NOT NULL,
  `shipment_note` longtext DEFAULT NULL,
  `shipment_link` varchar(110) NOT NULL,
  `shipment_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipments`
--

INSERT INTO `shipments` (`shipment_id`, `quotation_id`, `shipment_address`, `shipment_note`, `shipment_link`, `shipment_date`) VALUES
(3, 57, 'Macabalan Piaping-itum Cagayan de Oro City 9000', 'wefdsddsd', 'youtube.com', '2024-07-31');

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
  `phonenumber` varchar(20) NOT NULL,
  `companyname` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `encryptedpass` varchar(250) NOT NULL,
  `usertype` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `fullname`, `email`, `phonenumber`, `companyname`, `password`, `encryptedpass`, `usertype`) VALUES
(1, 'Rustom Codilan', 'rustomcodilan@gmail.com', '', '', 'mis137', '$2y$10$la4Hqt2.M8tQRBbqdiTStOn.3bJTOacYnjHbA802m.UP9oGKxMQS6', 'Administrator'),
(90, 'Michelle Rose Lacre Codilan', 'rustomlacrecodilan@gmail.com', '', '', 'mis137', '$2y$10$rQORQXrMCFj924ZaYjXE1eOBh8mAIbYzWRkXEA3dNYovDYRfGHiiC', 'Regular User');

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
-- Dumping data for table `user_quotations`
--

INSERT INTO `user_quotations` (`user_quotation_id`, `user_id`, `quotation_id`, `dateforwarded`, `readstatus`) VALUES
(45, 90, 57, '2024-07-27', 'Read');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assembly_print_files`
--
ALTER TABLE `assembly_print_files`
  ADD PRIMARY KEY (`assembly_print_file_id`),
  ADD KEY `assembly_print_files_ibfk_1` (`request_quotation_id`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`quotation_id`),
  ADD KEY `request_quotation_id` (`request_quotation_id`);

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
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`shipment_id`),
  ADD KEY `quotation_id` (`quotation_id`);

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
-- AUTO_INCREMENT for table `assembly_print_files`
--
ALTER TABLE `assembly_print_files`
  MODIFY `assembly_print_file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `quotation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `quotation_items`
--
ALTER TABLE `quotation_items`
  MODIFY `quotation_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=355;

--
-- AUTO_INCREMENT for table `request_quotations`
--
ALTER TABLE `request_quotations`
  MODIFY `request_quotation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `shipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `subscriber_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `user_quotations`
--
ALTER TABLE `user_quotations`
  MODIFY `user_quotation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assembly_print_files`
--
ALTER TABLE `assembly_print_files`
  ADD CONSTRAINT `assembly_print_files_ibfk_1` FOREIGN KEY (`request_quotation_id`) REFERENCES `request_quotations` (`request_quotation_id`);

--
-- Constraints for table `quotations`
--
ALTER TABLE `quotations`
  ADD CONSTRAINT `quotations_ibfk_1` FOREIGN KEY (`request_quotation_id`) REFERENCES `request_quotations` (`request_quotation_id`);

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
-- Constraints for table `shipments`
--
ALTER TABLE `shipments`
  ADD CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`quotation_id`);

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
