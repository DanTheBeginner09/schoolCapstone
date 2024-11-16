-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 06, 2024 at 04:44 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `schooldb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_user`
--

CREATE TABLE `admin_user` (
  `no` int(11) NOT NULL,
  `id_number` varchar(60) NOT NULL,
  `password` varchar(255) NOT NULL,
  `position` varchar(50) NOT NULL,
  `fname` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_user`
--

INSERT INTO `admin_user` (`no`, `id_number`, `password`, `position`, `fname`) VALUES
(1, 'jayvon11', '$2y$10$WWgdtRBb1qZ0tHFb.J9fR.ScnoM.jyUVBUeLzlIRjMf7WIfRCdhoi', '', 'Jayvon Dela Rosa'),
(2, 'zennia11', '$2y$10$AiwM0ylxBDDYnBk.asVbp.h3LPkhHTE9QGCck7R0qtpPlsD1eRCsi', '', 'Zennia Angelica Reyes');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `paymentID` int(11) NOT NULL,
  `accountID` int(11) DEFAULT NULL,
  `status_1st_quarter` varchar(20) DEFAULT NULL,
  `status_2nd_quarter` varchar(20) DEFAULT NULL,
  `status_3rd_quarter` varchar(20) DEFAULT NULL,
  `status_4th_quarter` varchar(20) DEFAULT NULL,
  `registration` decimal(10,2) DEFAULT NULL,
  `miscellaneous` decimal(10,2) DEFAULT NULL,
  `tuition` decimal(10,2) DEFAULT NULL,
  `quarter` decimal(10,2) DEFAULT NULL,
  `downpayment` decimal(10,2) DEFAULT NULL,
  `lab_rle` decimal(10,2) DEFAULT NULL,
  `per_exam` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`paymentID`, `accountID`, `status_1st_quarter`, `status_2nd_quarter`, `status_3rd_quarter`, `status_4th_quarter`, `registration`, `miscellaneous`, `tuition`, `quarter`, `downpayment`, `lab_rle`, `per_exam`, `total`, `created_at`) VALUES
(3, NULL, 'paid', 'unpaid', 'paid', 'unpaid', 500.00, 150.00, 1000.00, 250.00, 200.00, 100.00, 50.00, 2250.00, '2024-11-06 15:01:18'),
(4, NULL, 'paid', 'unpaid', 'paid', 'unpaid', 500.00, 150.00, 1000.00, 250.00, 200.00, 100.00, 50.00, 2250.00, '2024-11-06 15:01:56'),
(6, 50, 'paid', 'unpaid', 'paid', 'unpaid', 500.00, 150.00, 1000.00, 250.00, 200.00, 100.00, 50.00, 2250.00, '2024-11-06 15:07:18');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `accountID` int(50) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `gender` varchar(50) NOT NULL,
  `age` int(50) NOT NULL,
  `studentID` varchar(9) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL,
  `school_year` varchar(50) NOT NULL,
  `grade` varchar(100) NOT NULL,
  `remainingbalance` int(100) NOT NULL,
  `paymentAmount` int(200) NOT NULL,
  `totalTuition` int(100) NOT NULL,
  `firstQuarter` int(50) NOT NULL,
  `secondQuarter` int(50) NOT NULL,
  `thirdQuarter` int(50) NOT NULL,
  `fourthQuarter` int(50) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`accountID`, `fname`, `lname`, `gender`, `age`, `studentID`, `email`, `status`, `school_year`, `grade`, `remainingbalance`, `paymentAmount`, `totalTuition`, `firstQuarter`, `secondQuarter`, `thirdQuarter`, `fourthQuarter`, `password`) VALUES
(50, 'Daniel', 'Villanueva', 'M', 29, '2024-2030', 'villanuevadan546@gmail.com', '0', 'SY 2024-2025', '9', 4000, 0, 0, 0, 0, 0, 0, ''),
(195, 'Jayvon ', 'Dela Rosa', 'Male', 12, '2024-2031', 'delarosa@gmail.com', 'Active', 'SY 2024-2025', '9', 12000, 0, 12000, 0, 0, 0, 0, ''),
(196, 'Eddelito', 'Namoc', 'Female', 15, '2024-2032', 'namoc@gmail.com', 'Active', 'SY 2024-2025', '10', 12000, 0, 12000, 0, 0, 0, 0, ''),
(197, 'Bernadeth ', 'Lomoljo', 'Female', 12, '2024-2033', 'bernadeth@gmail.com', 'Active', 'SY 2024-2025', '9', 12000, 0, 12000, 0, 0, 0, 0, '');

--
-- Triggers `students`
--
DELIMITER $$
CREATE TRIGGER `before_student_insert` BEFORE INSERT ON `students` FOR EACH ROW BEGIN
    DECLARE current_max INT;
    DECLARE new_numeric_part INT;

    
    SET @currentYear = YEAR(CURDATE());

    
    SELECT CAST(SUBSTRING(MAX(studentID), 6) AS UNSIGNED) INTO current_max
    FROM students
    WHERE studentID LIKE CONCAT(@currentYear, '-%');

    
    IF current_max IS NULL THEN
        SET new_numeric_part = 1;
    ELSE
        SET new_numeric_part = current_max + 1;
    END IF;

    
    SET NEW.studentID = CONCAT(@currentYear, '-', LPAD(new_numeric_part, 4, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `no` int(11) NOT NULL,
  `id_number` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`no`, `id_number`, `password`) VALUES
(17, '2024-2109', '$2y$10$vpQbm6wfHfJ4pUWuxUmQaObS9dWzWLoSadqJZtk44yydcAVUwtbK6'),
(18, '2024-2078', '$2y$10$UmFtbFgmcgNcmOjtXdIvEuBhxu4Ip9bb.gAGglNJxUN3ig025va/G'),
(23, '2024-2105', '$2y$10$svdA7hrvkfzMEyLSTv5lluS6XkM1FTokfum8/hp.1XxgssGRj/akm'),
(24, '2024-2115', '$2y$10$gIpyJXSxgwj.W2sZfmDvKetH11OR2T9o2l9UEHNRHYuAM6yTCZPtu');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_user`
--
ALTER TABLE `admin_user`
  ADD PRIMARY KEY (`no`),
  ADD UNIQUE KEY `unique_id_number` (`id_number`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`paymentID`),
  ADD KEY `accountID` (`accountID`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`accountID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_user`
--
ALTER TABLE `admin_user`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `paymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `accountID` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=198;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `students` (`accountID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
