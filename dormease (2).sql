-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 23, 2024 at 04:07 AM
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
-- Database: `dormease`
--

-- --------------------------------------------------------

--
-- Table structure for table `dormitory`
--

CREATE TABLE `dormitory` (
  `d_ID` varchar(10) NOT NULL,
  `d_Name` varchar(30) NOT NULL,
  `d_Street` varchar(35) NOT NULL,
  `d_City` varchar(25) NOT NULL,
  `d_ZIPCode` int(4) NOT NULL,
  `d_Province` varchar(20) NOT NULL,
  `d_Region` varchar(15) NOT NULL,
  `d_Availability` varchar(20) NOT NULL,
  `d_Description` varchar(60) NOT NULL,
  `d_Owner` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ledger`
--

CREATE TABLE `ledger` (
  `l_ID` int(3) NOT NULL,
  `l_Biller` varchar(60) NOT NULL,
  `l_Recipient` varchar(60) NOT NULL,
  `l_Date` date NOT NULL,
  `l_Desctription` varchar(50) NOT NULL,
  `l_Amount` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `occupancy`
--

CREATE TABLE `occupancy` (
  `o_Room` varchar(15) NOT NULL,
  `o_Occupant` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `picture`
--

CREATE TABLE `picture` (
  `p_ID` int(3) NOT NULL,
  `p_Name` int(60) NOT NULL,
  `p_Directory` varchar(100) NOT NULL,
  `p_Type` int(1) NOT NULL,
  `p_Uploader` varchar(60) NOT NULL,
  `p_UploadedFor` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `room`
--

CREATE TABLE `room` (
  `r_Name` varchar(11) NOT NULL,
  `r_Description` varchar(50) NOT NULL,
  `r_Availability` varchar(20) NOT NULL,
  `r_Dormitory` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `u_ID` varchar(10) NOT NULL,
  `u_FName` varchar(30) NOT NULL,
  `u_MName` varchar(30) NOT NULL,
  `u_LName` varchar(30) NOT NULL,
  `u_Street` varchar(35) NOT NULL,
  `u_City` varchar(25) NOT NULL,
  `u_ZIPCode` int(4) NOT NULL,
  `u_Province` varchar(15) NOT NULL,
  `u_Region` varchar(20) NOT NULL,
  `u_Gender` tinyint(1) NOT NULL,
  `u_ContactNumber` varchar(11) NOT NULL,
  `u_Email` varchar(40) NOT NULL,
  `u_Password` text NOT NULL,
  `u_Balance` decimal(5,2) NOT NULL,
  `u_Status` tinyint(1) NOT NULL,
  `u_Account_Type` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dormitory`
--
ALTER TABLE `dormitory`
  ADD PRIMARY KEY (`d_ID`),
  ADD KEY `d_Owner` (`d_Owner`);

--
-- Indexes for table `ledger`
--
ALTER TABLE `ledger`
  ADD PRIMARY KEY (`l_ID`),
  ADD KEY `l_Biller` (`l_Biller`,`l_Recipient`),
  ADD KEY `l_Recipient` (`l_Recipient`);

--
-- Indexes for table `occupancy`
--
ALTER TABLE `occupancy`
  ADD KEY `o_Room` (`o_Room`,`o_Occupant`),
  ADD KEY `o_Occupant` (`o_Occupant`);

--
-- Indexes for table `picture`
--
ALTER TABLE `picture`
  ADD PRIMARY KEY (`p_ID`),
  ADD KEY `p_Uploader` (`p_Uploader`,`p_UploadedFor`),
  ADD KEY `fk_picture_dormitory` (`p_UploadedFor`);

--
-- Indexes for table `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`r_Name`),
  ADD KEY `r_Dormitory` (`r_Dormitory`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`u_ID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dormitory`
--
ALTER TABLE `dormitory`
  ADD CONSTRAINT `dormitory_ibfk_1` FOREIGN KEY (`d_Owner`) REFERENCES `user` (`u_ID`);

--
-- Constraints for table `ledger`
--
ALTER TABLE `ledger`
  ADD CONSTRAINT `ledger_ibfk_1` FOREIGN KEY (`l_Biller`) REFERENCES `user` (`u_ID`),
  ADD CONSTRAINT `ledger_ibfk_2` FOREIGN KEY (`l_Recipient`) REFERENCES `user` (`u_ID`);

--
-- Constraints for table `occupancy`
--
ALTER TABLE `occupancy`
  ADD CONSTRAINT `occupancy_ibfk_1` FOREIGN KEY (`o_Occupant`) REFERENCES `user` (`u_ID`),
  ADD CONSTRAINT `occupancy_ibfk_2` FOREIGN KEY (`o_Room`) REFERENCES `room` (`r_Name`);

--
-- Constraints for table `picture`
--
ALTER TABLE `picture`
  ADD CONSTRAINT `fk_picture_dormitory` FOREIGN KEY (`p_UploadedFor`) REFERENCES `dormitory` (`d_ID`),
  ADD CONSTRAINT `fk_picture_room` FOREIGN KEY (`p_UploadedFor`) REFERENCES `room` (`r_Name`),
  ADD CONSTRAINT `picture_ibfk_1` FOREIGN KEY (`p_UploadedFor`) REFERENCES `user` (`u_ID`),
  ADD CONSTRAINT `picture_ibfk_2` FOREIGN KEY (`p_Uploader`) REFERENCES `user` (`u_ID`);

--
-- Constraints for table `room`
--
ALTER TABLE `room`
  ADD CONSTRAINT `room_ibfk_1` FOREIGN KEY (`r_Dormitory`) REFERENCES `dormitory` (`d_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
