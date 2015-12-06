-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `db_name`
--

-- --------------------------------------------------------

--
-- Table structure for table `Answers`
--

CREATE TABLE IF NOT EXISTS `Answers` (
  `Position` int(11) NOT NULL,
  `Answer` text COLLATE utf8_bin NOT NULL,
  `Tid` int(11) NOT NULL,
  `PID` int(11) NOT NULL AUTO_INCREMENT,
  UNIQUE KEY `PID` (`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Mail`
--

CREATE TABLE IF NOT EXISTS `Mail` (
  `Name` text COLLATE utf8_bin NOT NULL,
  `Mail` text COLLATE utf8_bin NOT NULL,
  `PID` int(11) NOT NULL AUTO_INCREMENT,
  UNIQUE KEY `PID` (`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Messages`
--

CREATE TABLE IF NOT EXISTS `Messages` (
  `Addressee` text COLLATE utf8_bin NOT NULL,
  `Sender` text COLLATE utf8_bin NOT NULL,
  `Message` text COLLATE utf8_bin NOT NULL,
  `PID` int(11) NOT NULL AUTO_INCREMENT,
  `Deleted` int(1) NOT NULL DEFAULT '0',
  `Date` text COLLATE utf8_bin NOT NULL,
  `Viewed` int(1) NOT NULL,
  UNIQUE KEY `PID` (`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Tasks`
--

CREATE TABLE IF NOT EXISTS `Tasks` (
  `Question` text COLLATE utf8_bin NOT NULL,
  `Anscol` int(11) NOT NULL,
  `Ans1` text COLLATE utf8_bin NOT NULL,
  `Ans2` text COLLATE utf8_bin NOT NULL,
  `Ans3` text COLLATE utf8_bin NOT NULL,
  `Ans4` text COLLATE utf8_bin NOT NULL,
  `Answer` int(11) NOT NULL,
  `Tpid` int(11) NOT NULL,
  `PID` int(11) NOT NULL AUTO_INCREMENT,
  `Position` int(11) NOT NULL,
  `Picture` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Tests`
--

CREATE TABLE IF NOT EXISTS `Tests` (
  `Year` int(11) NOT NULL,
  `Halfyear` int(11) NOT NULL,
  `Subject` text COLLATE utf8_bin NOT NULL,
  `Grade` int(11) NOT NULL,
  `Booklet` varchar(20) COLLATE utf8_bin NOT NULL,
  `PID` int(11) NOT NULL AUTO_INCREMENT,
  `Position` int(11) NOT NULL,
  `Paper` varchar(20) COLLATE utf8_bin NOT NULL,
  `Deleted` int(11) NOT NULL DEFAULT '0',
  `Time` int(11) NOT NULL,
  `Taskcount` int(11) NOT NULL,
  UNIQUE KEY `PID` (`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE IF NOT EXISTS `Users` (
  `PID` int(11) NOT NULL AUTO_INCREMENT,
  `Privilegies` text COLLATE utf8_bin NOT NULL,
  `Login` text COLLATE utf8_bin NOT NULL,
  `Pass` text COLLATE utf8_bin NOT NULL,
  `Mail` text COLLATE utf8_bin NOT NULL,
  `Language` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
