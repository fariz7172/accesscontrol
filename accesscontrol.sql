/*
SQLyog Professional v12.5.1 (64 bit)
MySQL - 8.0.12 : Database - accesscontrol_baru
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`accesscontrol_baru` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */;

USE `accesscontrol`;

/*Table structure for table `api` */

DROP TABLE IF EXISTS `api`;

CREATE TABLE `api` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `desc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `api` */

insert  into `api`(`id`,`name`,`desc`) values 
(1,'http://localhost:8000/api/soyal/book/v1/1/adduser','URL ADD USER SOYAL MECHINE'),
(2,'http://localhost:787/sztimmy/registerface','ADD USER RESGISTER TASOFT'),
(3,'http://localhost:787/sztimmy/useraccess','User Access Tasoft'),
(4,'http://localhost:787/sztimmy/getuserlist','Get User List Tasoft'),
(5,'http://127.0.0.1:8000/api/soyal/book/v1/1/deleteuser','Delete User Soyal Mechine'),
(6,'http://localhost:787/sztimmy/deleteface','Delete User Tasoft'),
(7,'http://localhost:8080/api/members','Sync Member Dreampos'),
(8,'http://localhost:8080/api/view-members','Get Data Member Dreampos'),
(9,'http://localhost:787/sztimmy/opengate','Opengate'),
(10,'http://localhost:787/sztimmy/cleanlogs','CleanLog'),
(11,'http://localhost:787/sztimmy/gettime','Gettime'),
(12,'http://localhost:787/sztimmy/settime','SetTime'),
(13,'http://localhost:787/sztimmy/reboot','reboot'),
(14,'http://localhost:787/sztimmy/dayzone','dayzone'),
(15,'http://localhost:787/sztimmy/weekzone','weekzone'),
(16,'http://localhost:787/sztimmy/userWeekzone','userweekzone');

/*Table structure for table `attend` */

DROP TABLE IF EXISTS `attend`;

CREATE TABLE `attend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `EmployeeID` int(11) NOT NULL DEFAULT '0',
  `AttDate` date NOT NULL,
  `PatternID` int(11) DEFAULT '0' COMMENT 'link ke shiftpattern id',
  `ShiftCode` int(11) DEFAULT '0' COMMENT 'link ke shift.id',
  `DayType` int(11) DEFAULT '0' COMMENT '(1 kerja, 2 hari libur)',
  `Time_In` datetime DEFAULT NULL,
  `Time_Break` datetime DEFAULT NULL,
  `Time_Resume` datetime DEFAULT NULL,
  `Time_Out` datetime DEFAULT NULL,
  `Time_InShort` int(11) DEFAULT '0',
  `Time_BreakShort` int(11) DEFAULT '0',
  `Time_ResumeShort` int(11) DEFAULT '0',
  `Time_OutShort` int(11) DEFAULT '0',
  `WorkTime` int(11) DEFAULT '0',
  `EarlyWork` int(11) DEFAULT '0',
  `TotalWorkHour` int(11) DEFAULT '0',
  `TotalOT` int(11) DEFAULT '0',
  `Remark` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'catatan',
  `DutyProcessID` int(11) DEFAULT '0' COMMENT '(link ke leave type id)',
  `Present` int(11) DEFAULT '0' COMMENT '(1 hadir, 0 ga hadir)',
  PRIMARY KEY (`id`,`EmployeeID`,`AttDate`),
  KEY `EmployeeID` (`EmployeeID`),
  KEY `PatternID` (`PatternID`),
  KEY `ShiftCode` (`ShiftCode`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=183 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `attend` */

insert  into `attend`(`id`,`EmployeeID`,`AttDate`,`PatternID`,`ShiftCode`,`DayType`,`Time_In`,`Time_Break`,`Time_Resume`,`Time_Out`,`Time_InShort`,`Time_BreakShort`,`Time_ResumeShort`,`Time_OutShort`,`WorkTime`,`EarlyWork`,`TotalWorkHour`,`TotalOT`,`Remark`,`DutyProcessID`,`Present`) values 
(1,2,'2025-11-01',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(2,4,'2025-11-01',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(3,2,'2025-11-02',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(4,4,'2025-11-02',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(5,2,'2025-11-03',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(6,4,'2025-11-03',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(7,2,'2025-11-04',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(8,4,'2025-11-04',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(9,2,'2025-11-05',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(10,4,'2025-11-05',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(11,2,'2025-11-06',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(12,4,'2025-11-06',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(13,2,'2025-11-07',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(14,4,'2025-11-07',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(15,2,'2025-11-08',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(16,4,'2025-11-08',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(17,2,'2025-11-09',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(18,4,'2025-11-09',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(19,2,'2025-11-10',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(20,4,'2025-11-10',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(21,2,'2025-11-11',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(22,4,'2025-11-11',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(23,2,'2025-11-12',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(24,4,'2025-11-12',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(25,2,'2025-11-13',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(26,4,'2025-11-13',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(27,2,'2025-11-14',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(28,4,'2025-11-14',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(29,2,'2025-11-15',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(30,4,'2025-11-15',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(31,2,'2025-11-16',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(32,4,'2025-11-16',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(33,2,'2025-11-17',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(34,4,'2025-11-17',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(35,2,'2025-11-18',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(36,4,'2025-11-18',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(37,2,'2025-11-19',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(38,4,'2025-11-19',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(39,2,'2025-11-20',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(40,4,'2025-11-20',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(41,2,'2025-11-21',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(42,4,'2025-11-21',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(43,2,'2025-11-22',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(44,4,'2025-11-22',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(45,2,'2025-11-23',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(46,4,'2025-11-23',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(47,2,'2025-11-24',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(48,4,'2025-11-24',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(49,2,'2025-11-25',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(50,4,'2025-11-25',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(51,2,'2025-11-26',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(52,4,'2025-11-26',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(53,2,'2025-11-27',1,1,1,'2025-11-27 09:39:00',NULL,NULL,NULL,99,0,0,0,450,NULL,0,0,NULL,NULL,1),
(54,4,'2025-11-27',1,1,1,NULL,NULL,NULL,'2025-11-27 14:05:00',0,0,0,145,450,NULL,0,0,NULL,NULL,1),
(55,2,'2025-11-28',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(56,4,'2025-11-28',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(57,2,'2025-11-29',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(58,4,'2025-11-29',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(59,2,'2025-11-30',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(60,4,'2025-11-30',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(61,7,'2025-11-01',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(62,8,'2025-11-01',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(63,7,'2025-11-02',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(64,8,'2025-11-02',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(65,7,'2025-11-03',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(66,8,'2025-11-03',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(67,7,'2025-11-04',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(68,8,'2025-11-04',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(69,7,'2025-11-05',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(70,8,'2025-11-05',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(71,7,'2025-11-06',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(72,8,'2025-11-06',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(73,7,'2025-11-07',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(74,8,'2025-11-07',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(75,7,'2025-11-08',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(76,8,'2025-11-08',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(77,7,'2025-11-09',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(78,8,'2025-11-09',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(79,7,'2025-11-10',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(80,8,'2025-11-10',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(81,7,'2025-11-11',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(82,8,'2025-11-11',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(83,7,'2025-11-12',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(84,8,'2025-11-12',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(85,7,'2025-11-13',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(86,8,'2025-11-13',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(87,7,'2025-11-14',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(88,8,'2025-11-14',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(89,7,'2025-11-15',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(90,8,'2025-11-15',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(91,7,'2025-11-16',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(92,8,'2025-11-16',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(93,7,'2025-11-17',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(94,8,'2025-11-17',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(95,7,'2025-11-18',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(96,8,'2025-11-18',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(97,7,'2025-11-19',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(98,8,'2025-11-19',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(99,7,'2025-11-20',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(100,8,'2025-11-20',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(101,7,'2025-11-21',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(102,8,'2025-11-21',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(103,7,'2025-11-22',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(104,8,'2025-11-22',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(105,7,'2025-11-23',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(106,8,'2025-11-23',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(107,7,'2025-11-24',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(108,8,'2025-11-24',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(109,7,'2025-11-25',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(110,8,'2025-11-25',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(111,7,'2025-11-26',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(112,8,'2025-11-26',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(113,7,'2025-11-27',1,1,1,NULL,NULL,NULL,'2025-11-27 14:04:00',0,0,0,146,450,NULL,0,0,NULL,NULL,1),
(114,8,'2025-11-27',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(115,7,'2025-11-28',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(116,8,'2025-11-28',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(117,7,'2025-11-29',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(118,8,'2025-11-29',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(119,7,'2025-11-30',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(120,8,'2025-11-30',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(121,2,'2025-12-01',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(122,7,'2025-12-01',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(123,2,'2025-12-02',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(124,7,'2025-12-02',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(125,2,'2025-12-03',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(126,7,'2025-12-03',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(127,2,'2025-12-04',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(128,7,'2025-12-04',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(129,2,'2025-12-05',1,1,1,NULL,NULL,NULL,'2025-12-05 16:59:00',0,0,0,0,450,NULL,0,29,NULL,NULL,1),
(130,7,'2025-12-05',1,1,1,NULL,NULL,NULL,'2025-12-05 14:32:00',0,0,0,118,450,NULL,0,0,NULL,NULL,1),
(131,2,'2025-12-06',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(132,7,'2025-12-06',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(133,2,'2025-12-07',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(134,7,'2025-12-07',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(135,2,'2025-12-08',1,1,1,'2025-12-08 09:07:00',NULL,NULL,NULL,67,0,0,0,450,NULL,0,0,NULL,NULL,1),
(136,7,'2025-12-08',1,1,1,'2025-12-08 09:05:00',NULL,NULL,NULL,65,0,0,0,450,NULL,0,0,NULL,NULL,1),
(137,2,'2025-12-09',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(138,7,'2025-12-09',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(139,2,'2025-12-10',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(140,7,'2025-12-10',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(141,2,'2025-12-11',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(142,7,'2025-12-11',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(143,2,'2025-12-12',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(144,7,'2025-12-12',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(145,2,'2025-12-13',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(146,7,'2025-12-13',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(147,2,'2025-12-14',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(148,7,'2025-12-14',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(149,2,'2025-12-15',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(150,7,'2025-12-15',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(151,2,'2025-12-16',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(152,7,'2025-12-16',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(153,2,'2025-12-17',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(154,7,'2025-12-17',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(155,2,'2025-12-18',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(156,7,'2025-12-18',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(157,2,'2025-12-19',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(158,7,'2025-12-19',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(159,2,'2025-12-20',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(160,7,'2025-12-20',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(161,2,'2025-12-21',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(162,7,'2025-12-21',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(163,2,'2025-12-22',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(164,7,'2025-12-22',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(165,2,'2025-12-23',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(166,7,'2025-12-23',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(167,2,'2025-12-24',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(168,7,'2025-12-24',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(169,2,'2025-12-25',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(170,7,'2025-12-25',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(171,2,'2025-12-26',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(172,7,'2025-12-26',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(173,2,'2025-12-27',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(174,7,'2025-12-27',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(175,2,'2025-12-28',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(176,7,'2025-12-28',1,5,2,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,0,0,NULL,NULL,0),
(177,2,'2025-12-29',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(178,7,'2025-12-29',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(179,2,'2025-12-30',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(180,7,'2025-12-30',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(181,2,'2025-12-31',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0),
(182,7,'2025-12-31',1,1,1,NULL,NULL,NULL,NULL,0,0,0,0,450,NULL,0,0,NULL,NULL,0);

/*Table structure for table `attendsummary` */

DROP TABLE IF EXISTS `attendsummary`;

CREATE TABLE `attendsummary` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Period` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `StartPeriod` date NOT NULL,
  `EndPeriod` date NOT NULL,
  `EmployeeID` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `WorkingDays` int(11) DEFAULT NULL,
  `Present` int(11) DEFAULT NULL,
  `Absent` int(11) DEFAULT NULL,
  `LateIn` int(11) DEFAULT NULL,
  `EarlyOut` int(11) DEFAULT NULL,
  `LateInMinute` int(11) DEFAULT NULL,
  `EarlyOutMinute` int(11) DEFAULT NULL,
  `TotalWorktime` int(11) DEFAULT NULL,
  `TotalWorkHour` int(11) DEFAULT NULL,
  `OT` int(11) DEFAULT NULL,
  `OTMinute` int(11) DEFAULT NULL,
  `EarlyWork` int(11) DEFAULT NULL,
  `EarlyWorkMinute` int(11) DEFAULT NULL,
  `Ncheckin` int(11) DEFAULT NULL,
  `Ncheckout` int(11) DEFAULT NULL,
  `LeaveTaken` int(11) DEFAULT NULL,
  `D1` int(11) DEFAULT NULL,
  `D2` int(11) DEFAULT NULL,
  `D3` int(11) DEFAULT NULL,
  `D4` int(11) DEFAULT NULL,
  `D5` int(11) DEFAULT NULL,
  `D6` int(11) DEFAULT NULL,
  `D7` int(11) DEFAULT NULL,
  `D8` int(11) DEFAULT NULL,
  `D9` int(11) DEFAULT NULL,
  `D10` int(11) DEFAULT NULL,
  `D11` int(11) DEFAULT NULL,
  `D12` int(11) DEFAULT NULL,
  `D13` int(11) DEFAULT NULL,
  `D14` int(11) DEFAULT NULL,
  `D15` int(11) DEFAULT NULL,
  `D16` int(11) DEFAULT NULL,
  `D17` int(11) DEFAULT NULL,
  `D18` int(11) DEFAULT NULL,
  `D19` int(11) DEFAULT NULL,
  `D20` int(11) DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `attendsummary` */

insert  into `attendsummary`(`Id`,`Period`,`StartPeriod`,`EndPeriod`,`EmployeeID`,`WorkingDays`,`Present`,`Absent`,`LateIn`,`EarlyOut`,`LateInMinute`,`EarlyOutMinute`,`TotalWorktime`,`TotalWorkHour`,`OT`,`OTMinute`,`EarlyWork`,`EarlyWorkMinute`,`Ncheckin`,`Ncheckout`,`LeaveTaken`,`D1`,`D2`,`D3`,`D4`,`D5`,`D6`,`D7`,`D8`,`D9`,`D10`,`D11`,`D12`,`D13`,`D14`,`D15`,`D16`,`D17`,`D18`,`D19`,`D20`) values 
(1,'2025-12-01','2025-12-01','2025-12-31','2',23,2,21,1,0,67,0,10350,0,1,29,0,0,22,22,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
(2,'2025-12-01','2025-12-01','2025-12-31','7',23,2,21,1,1,65,118,10350,0,0,0,0,0,22,22,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

/*Table structure for table `branch` */

DROP TABLE IF EXISTS `branch`;

CREATE TABLE `branch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` int(11) DEFAULT NULL,
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `branchCode` (`number`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `branch` */

insert  into `branch`(`id`,`number`,`name`,`description`) values 
(12,1,'Branch A','A'),
(13,2,'Branch B','B'),
(16,3,'Branch C','C'),
(17,4,'Bacang Utara','Caba PIK II');

/*Table structure for table `dayzone` */

DROP TABLE IF EXISTS `dayzone`;

CREATE TABLE `dayzone` (
  `ID` int(11) NOT NULL,
  `Name` varchar(15) DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `dayzone` */

insert  into `dayzone`(`ID`,`Name`) values 
(1,'Dayzone1'),
(2,'Dayzone2'),
(3,'Dayzone3'),
(4,'Dayzone4'),
(5,'Dayzone5'),
(6,'Dayzone6'),
(7,'Dayzone7'),
(8,'Dayzone8');

/*Table structure for table `dayzonedetail` */

DROP TABLE IF EXISTS `dayzonedetail`;

CREATE TABLE `dayzonedetail` (
  `ID` int(11) NOT NULL,
  `dz` int(11) DEFAULT NULL COMMENT 'relasi ke dayzoneid',
  `Stz1` time DEFAULT '00:00:00',
  `Etz1` time DEFAULT '00:00:00',
  `Stz2` time DEFAULT '00:00:00',
  `Etz2` time DEFAULT '00:00:00',
  `Stz3` time DEFAULT '00:00:00',
  `Etz3` time DEFAULT '00:00:00',
  `Stz4` time DEFAULT '00:00:00',
  `Etz4` time DEFAULT '00:00:00',
  `Stz5` time DEFAULT '00:00:00',
  `Etz5` time DEFAULT '00:00:00',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `dayzonedetail` */

insert  into `dayzonedetail`(`ID`,`dz`,`Stz1`,`Etz1`,`Stz2`,`Etz2`,`Stz3`,`Etz3`,`Stz4`,`Etz4`,`Stz5`,`Etz5`) values 
(1,1,'09:00:00','12:00:00','12:30:00','14:00:00','18:00:00','22:00:00','22:00:00','00:00:00','00:00:00','00:00:00'),
(2,2,'00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00'),
(3,3,'00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00'),
(4,4,'00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00'),
(5,5,'00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00'),
(6,6,'00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00'),
(7,7,'00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00'),
(8,8,'00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00');

/*Table structure for table `departemen` */

DROP TABLE IF EXISTS `departemen`;

CREATE TABLE `departemen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `description` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `DeptCode` (`number`),
  KEY `DeptName` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `departemen` */

insert  into `departemen`(`id`,`number`,`name`,`description`) values 
(1,'1','IT','IT'),
(2,'2','Marketing','Marketing'),
(17,'3','Sales','sales'),
(18,'4','Penagihan','Penagihan');

/*Table structure for table `devicegate` */

DROP TABLE IF EXISTS `devicegate`;

CREATE TABLE `devicegate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number` int(11) DEFAULT '0',
  `flagstatus` int(4) DEFAULT '0',
  `type` int(11) DEFAULT NULL COMMENT 'type 0 Finger, 50 face, 1 soyal',
  `sn` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nodeid` int(5) DEFAULT '0' COMMENT 'nodeid soyal',
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'untuk keterangan',
  `groupid` int(11) DEFAULT '0',
  `stat` tinyint(4) DEFAULT '0' COMMENT '0=IN, 1=Out',
  `Lift1` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT 'Setting Lift 1- 16',
  `Lift2` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT 'Setting Lift 17- 32',
  `Lift3` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT 'Setting Lift 33- 48',
  `Lift4` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT 'Setting Lift 49- 64',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `devicegate` */

insert  into `devicegate`(`id`,`name`,`number`,`flagstatus`,`type`,`sn`,`ip`,`nodeid`,`description`,`groupid`,`stat`,`Lift1`,`Lift2`,`Lift3`,`Lift4`) values 
(1,'GATE1',8,1,0,'ZYSL20032920','192.168.100.128',8,'LOBBY UTAMA',4,0,0,0,0,0),
(2,'GATE2',1,1,50,'ZXRL23103798','192.168.100.224',1,'Mesin Face',0,1,0,0,0,0),
(4,'DeviceKecil',4,1,0,'ZYSL20032921','192.168.100.129',5,'devicekecil',9,0,0,0,0,0),
(9,'Soyal Mesin',3,1,1,'SOYAL antai 3','192.168.100.127',3,'Mesin Soyal',1,1,7,0,0,0),
(19,'mantap',19,1,1,'AYSG0122221','192.168.100.213',19,'3131',9,0,1,0,0,0),
(20,'admin',14,1,1,'AYSG0122214','192.168.100.114',14,'212',0,0,0,0,0,0);

/*Table structure for table `devicegroup` */

DROP TABLE IF EXISTS `devicegroup`;

CREATE TABLE `devicegroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `devicegroup` */

insert  into `devicegroup`(`id`,`number`,`name`,`description`) values 
(3,'2','IT Room 5','IT Room Lantai 5'),
(4,'1','Lobby Gate Pintu Barat','Lobby Gate Pintu Barat'),
(7,'3','Kitchen Room','Kitchen Room lantai 5'),
(8,'4','Soyal Mesin','Lantai Soyal'),
(9,'5','Door Group','Lantai 5');

/*Table structure for table `devicelog` */

DROP TABLE IF EXISTS `devicelog`;

CREATE TABLE `devicelog` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `log_date` datetime DEFAULT NULL,
  `modul` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Daftaruser - DeleteUser',
  `desc` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'data Json yang dikrim',
  `status` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'berhasil atau gagal',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=594 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `devicelog` */

insert  into `devicelog`(`id`,`log_date`,`modul`,`desc`,`status`) values 
(1,'2025-11-25 16:51:27','{\"user_id\":\"4\",\"device_ip\":\"192.168.100.213\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user Suti (ID: 4) ke device 192.168.100.213: timeout','Failed'),
(2,'2025-11-25 16:52:16','{\"user_id\":\"4\",\"device_ip\":\"192.168.100.213\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user Suti (ID: 4) ke device 192.168.100.213: timeout','Failed'),
(3,'2025-11-25 16:55:34','{\"user_id\":\"7\",\"user_name\":\"Yono\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":1,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Yono (ID: 7) ke device 192.168.100.127','Success'),
(4,'2025-11-25 16:57:13','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":32,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(5,'2025-11-25 16:59:00','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":32,\"lift2\":136,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(6,'2025-11-26 09:05:13','{\"user_id\":\"4\",\"card_number\":\"00107:24131\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-19-2024\",\"expire_date\":\"12-31-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"14:35\",\"pin\":\"1\",\"lift1\":\"255\",\"lift2\":\"255\",\"lift3\":\"255\",\"lift4\":\"255\",\"user_name\":\"Suti\",\"type\":\"1\"}','Success Insert User Suti','Successfully'),
(7,'2025-11-26 09:27:58','Soyal_Register','User ID 4 (Suti) berhasil diregister ke mesin 192.168.100.127','Success'),
(8,'2025-11-26 09:58:05','{\"user_id\":4,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(9,'2025-11-26 09:58:14','{\"user_id\":4,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(10,'2025-11-26 09:58:25','{\"user_id\":4,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(11,'2025-11-26 09:58:41','{\"user_id\":\"4\",\"card_number\":\"00107:24131\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-19-2024\",\"expire_date\":\"12-31-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"14:35\",\"pin\":\"1\",\"lift1\":\"255\",\"lift2\":\"255\",\"lift3\":\"255\",\"lift4\":\"255\",\"user_name\":\"Suti\",\"type\":\"1\"}','Success Insert User Suti','Successfully'),
(12,'2025-11-26 10:24:55','{\"user_id\":\"4\",\"device_ip\":\"192.168.100.127\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user Suti (ID: 4) ke device 192.168.100.127: timeout','Failed'),
(13,'2025-11-26 10:25:15','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":2,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(14,'2025-11-26 10:49:16','{\"user_id\":\"4\",\"device_ip\":\"192.168.100.127\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user Suti (ID: 4) ke device 192.168.100.127: timeout','Failed'),
(15,'2025-11-26 10:49:56','{\"user_id\":\"4\",\"device_ip\":\"192.168.100.127\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user Suti (ID: 4) ke device 192.168.100.127: timeout','Failed'),
(16,'2025-11-26 10:50:48','{\"user_id\":\"4\",\"device_ip\":\"192.168.100.127\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user Suti (ID: 4) ke device 192.168.100.127: timeout','Failed'),
(17,'2025-11-26 10:52:41','{\"user_id\":\"4\",\"device_ip\":\"192.168.100.127\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user Suti (ID: 4) ke device 192.168.100.127: timeout','Failed'),
(18,'2025-11-26 10:53:32','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":0,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(19,'2025-11-26 10:54:39','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":8,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(20,'2025-11-26 10:55:03','{\"user_id\":\"7\",\"user_name\":\"Yono\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":64,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Yono (ID: 7) ke device 192.168.100.127','Success'),
(21,'2025-11-26 10:56:01','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":4,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(22,'2025-11-26 10:56:34','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":32,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(23,'2025-11-26 10:58:02','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":32,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(24,'2025-11-26 11:23:01','{\"user_id\":\"4\",\"device_ip\":\"192.168.100.127\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user Suti ke 192.168.100.127: timeout','Failed'),
(25,'2025-11-26 11:24:34','{\"user_id\":\"4\",\"device_ip\":\"192.168.100.127\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user Suti ke 192.168.100.127: timeout','Failed'),
(26,'2025-11-26 12:21:58','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":14,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(27,'2025-11-26 12:33:24','{\"user_id\":\"4\",\"device_ip\":\"192.168.100.127\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user Suti (ID: 4) ke device 192.168.100.127: The lift2 field must be between 0 and 255.','Failed'),
(28,'2025-11-26 12:36:40','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":30,\"lift2\":255,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(29,'2025-11-26 12:44:53','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":30,\"lift2\":255,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(30,'2025-11-26 12:53:14','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":16,\"lift2\":255,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(31,'2025-11-26 13:01:01','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":30,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(32,'2025-11-26 13:06:14','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":30,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(33,'2025-11-26 13:09:45','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":16,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(34,'2025-11-26 13:10:06','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":30,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(35,'2025-11-26 13:13:08','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":18,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(36,'2025-11-26 13:35:13','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":15,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(37,'2025-11-26 13:36:07','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":15,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(38,'2025-11-26 13:37:00','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":26,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(39,'2025-11-26 13:37:30','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":15,\"lift2\":8,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(40,'2025-11-26 14:06:21','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":15,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(41,'2025-11-26 14:08:33','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":18,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(42,'2025-11-26 14:08:48','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":15,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(43,'2025-11-26 14:09:40','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":15,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(44,'2025-11-26 14:12:25','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":15,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(45,'2025-11-26 14:12:44','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":15,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(46,'2025-11-26 14:20:14','{\"user_id\":\"4\",\"device_ip\":\"192.168.100.127\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user Suti (ID: 4) ke device 192.168.100.127: The lift2 field must be between 0 and 255.','Failed'),
(47,'2025-11-26 14:20:25','{\"user_id\":\"4\",\"device_ip\":\"192.168.100.127\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user Suti (ID: 4) ke device 192.168.100.127: The lift2 field must be between 0 and 255.','Failed'),
(48,'2025-11-26 14:20:39','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":15,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(49,'2025-11-26 14:21:05','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":15,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(50,'2025-11-26 14:21:55','{\"user_id\":\"4\",\"device_ip\":\"192.168.100.127\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user Suti (ID: 4) ke device 192.168.100.127: The lift2 field must be between 0 and 255.','Failed'),
(51,'2025-11-26 14:24:18','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":30,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(52,'2025-11-26 14:24:49','{\"user_id\":\"4\",\"device_ip\":\"192.168.100.127\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user Suti (ID: 4) ke device 192.168.100.127: The lift2 field must be between 0 and 255.','Failed'),
(53,'2025-11-26 14:36:07','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":18,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Suti (ID: 4) ke device 192.168.100.127','Success'),
(54,'2025-11-26 14:48:05','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":15,\"lift2\":128,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(55,'2025-11-26 14:48:36','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":1,\"lift2\":128,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(56,'2025-11-26 14:48:58','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":15,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(57,'2025-11-26 14:49:19','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":0,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(58,'2025-11-26 14:49:48','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":0,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(59,'2025-11-26 14:50:10','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":15,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(60,'2025-11-26 14:52:18','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":0,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(61,'2025-11-26 14:54:13','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":0,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(62,'2025-11-26 14:54:56','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":0,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(63,'2025-11-26 14:55:43','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":0,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(64,'2025-11-26 14:56:35','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":15,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(65,'2025-11-26 14:57:26','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":9,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(66,'2025-11-26 14:57:53','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":15,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(67,'2025-11-26 14:58:17','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":15,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(68,'2025-11-26 15:16:33','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":15,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(69,'2025-11-26 15:41:59','{\"user_id\":\"2\",\"user_name\":\"Andre\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":15,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Andre ke 192.168.100.127','Success'),
(70,'2025-11-26 15:42:00','{\"user_id\":\"7\",\"user_name\":\"Yono\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":15,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Yono ke 192.168.100.127','Success'),
(71,'2025-11-26 16:00:35','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":3,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(72,'2025-11-26 16:01:01','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":0,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(73,'2025-11-26 16:08:14','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":0,\"lift2\":128,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(74,'2025-11-26 17:02:32','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":15,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(75,'2025-11-27 10:04:08','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":1,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(76,'2025-11-27 10:05:19','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":7,\"lift2\":240,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(77,'2025-11-27 14:12:08','{\"user_id\":\"1\",\"user_name\":\"ayam\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":4,\"lift2\":144,\"lift3\":0,\"lift4\":0}','Success kirim user ayam ke 192.168.100.127','Success'),
(78,'2025-11-27 14:12:09','{\"user_id\":\"2\",\"user_name\":\"Andre\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":4,\"lift2\":144,\"lift3\":0,\"lift4\":0}','Success kirim user Andre ke 192.168.100.127','Success'),
(79,'2025-11-27 14:12:10','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":4,\"lift2\":144,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(80,'2025-11-27 14:12:10','{\"user_id\":\"7\",\"user_name\":\"Yono\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":4,\"lift2\":144,\"lift3\":0,\"lift4\":0}','Success kirim user Yono ke 192.168.100.127','Success'),
(81,'2025-11-27 14:12:11','{\"user_id\":\"8\",\"user_name\":\"YUZA\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":4,\"lift2\":144,\"lift3\":0,\"lift4\":0}','Success kirim user YUZA ke 192.168.100.127','Success'),
(82,'2025-11-27 14:12:11','{\"user_id\":\"9\",\"user_name\":\"bela\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":4,\"lift2\":144,\"lift3\":0,\"lift4\":0}','Success kirim user bela ke 192.168.100.127','Success'),
(83,'2025-11-27 14:12:33','{\"user_id\":\"10\",\"device_ip\":\"192.168.100.127\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user Tatang: timeout','Failed'),
(84,'2025-11-27 14:12:33','{\"user_id\":\"11\",\"device_ip\":\"192.168.100.127\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user Tatang: timeout','Failed'),
(85,'2025-11-27 14:12:33','{\"user_id\":\"12\",\"device_ip\":\"192.168.100.127\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user untung: timeout','Failed'),
(86,'2025-11-27 14:12:33','{\"user_id\":\"13\",\"device_ip\":\"192.168.100.127\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user ucup: timeout','Failed'),
(87,'2025-11-27 14:12:33','{\"user_id\":\"14\",\"device_ip\":\"192.168.100.127\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user ujang karamou: timeout','Failed'),
(88,'2025-11-27 14:12:33','{\"user_id\":\"15\",\"device_ip\":\"192.168.100.127\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user Tatang: timeout','Failed'),
(89,'2025-11-27 14:12:33','{\"user_id\":\"16\",\"device_ip\":\"192.168.100.127\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user jamet: timeout','Failed'),
(90,'2025-11-27 14:12:33','{\"user_id\":\"17\",\"device_ip\":\"192.168.100.127\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user kda: timeout','Failed'),
(91,'2025-11-27 14:12:59','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":4,\"lift2\":144,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(92,'2025-11-27 14:13:33','{\"user_id\":\"7\",\"user_name\":\"Yono\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":5,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Yono ke 192.168.100.127','Success'),
(93,'2025-11-27 14:15:04','{\"user_id\":\"7\",\"user_name\":\"Yono\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":255,\"lift2\":255,\"lift3\":0,\"lift4\":0}','Success kirim user Yono ke 192.168.100.127','Success'),
(94,'2025-11-27 14:17:17','{\"user_id\":\"7\",\"device_ip\":\"192.168.100.213\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user Yono: timeout','Failed'),
(95,'2025-11-27 14:17:17','{\"user_id\":\"7\",\"device_ip\":\"192.168.100.127\",\"action\":\"ADD_VIA_BULK_LIFT\"}','Gagal kirim user Yono: timeout','Failed'),
(96,'2025-11-27 16:35:35','{\"user_id\":\"4\",\"user_name\":\"Suti\",\"device_ip\":\"192.168.100.127\",\"device_node\":3,\"action\":\"ADD_VIA_BULK_LIFT\",\"lift1\":7,\"lift2\":0,\"lift3\":0,\"lift4\":0}','Success kirim user Suti ke 192.168.100.127','Success'),
(97,'2025-12-01 13:49:52','{\"user_id\":8,\"sn\":\"ZYSJ28009690\",\"ip\":\"192.168.100.221\"}','Failed: Machine ZYSJ28009690 (192.168.100.221) OFFLINE','Failed'),
(98,'2025-12-01 13:51:21','{\"sn\":\"ZYSJ28009690\",\"userid\":8,\"username\":\"YUZA\",\"type\":0,\"admin\":\"0\",\"cardnumber\":8711447,\"fdata\":\"\"}','Failed Insert User YUZA: Register face failed','Failed'),
(99,'2025-12-01 13:52:05','{\"sn\":\"ZYSJ28009690\",\"userid\":8,\"username\":\"YUZA\",\"type\":0,\"admin\":\"0\",\"cardnumber\":8711447,\"fdata\":\"0\"}','Failed Insert User YUZA: Register face failed','Failed'),
(100,'2025-12-01 13:53:52','{\"sn\":\"ZYSJ28009690\",\"userid\":\"8\"}','Failed Delete User YUZA from ZYSJ28009690','Failed'),
(101,'2025-12-01 13:54:02','{\"sn\":\"ZYSJ28009690\",\"userid\":\"8\"}','Failed Delete User YUZA from ZYSJ28009690','Failed'),
(102,'2025-12-01 13:54:26','{\"sn\":\"ZYSJ28009690\",\"userid\":\"8\"}','Failed Delete User YUZA from ZYSJ28009690','Failed'),
(103,'2025-12-01 13:56:25','{\"sn\":\"ZYSJ20032920\",\"userid\":8,\"username\":\"YUZA\",\"type\":0,\"admin\":\"0\",\"cardnumber\":8711447,\"fdata\":\"\"}','Failed Insert User YUZA: Register face failed','Failed'),
(104,'2025-12-01 13:56:37','{\"sn\":\"ZYSJ20032920\",\"userid\":\"8\"}','Failed Delete User YUZA from ZYSJ20032920','Failed'),
(105,'2025-12-01 13:57:58','{\"userId\":8,\"deviceId\":\"4\",\"weekzone\":0}','Weekzone synced: User YUZA → ZYSL20032920 (WZ: 0)','Successfully'),
(106,'2025-12-01 13:57:58','{\"user_id\":\"8\",\"sn\":\"ZYSL20032920\",\"username\":\"YUZA\",\"card_number\":\"8711447\",\"ip_address\":\"192.168.100.128\"}','Success Insert User YUZA','Successfully'),
(107,'2025-12-01 14:10:03','{\"sn\":\"ZXRL23103798\",\"userid\":8,\"username\":\"YUZA\",\"type\":50,\"admin\":\"0\",\"cardnumber\":8711447,\"fdata\":\"http://127.0.0.1:8000/img/default.png\"}','Failed Insert User YUZA: Register face failed','Failed'),
(108,'2025-12-01 14:10:10','{\"sn\":\"ZXRL23103798\",\"userid\":8,\"username\":\"YUZA\",\"type\":50,\"admin\":\"0\",\"cardnumber\":8711447,\"fdata\":\"http://127.0.0.1:8000/img/default.png\"}','Failed Insert User YUZA: Register face failed','Failed'),
(109,'2025-12-01 14:10:38','{\"sn\":\"ZXRL23103798\",\"userid\":8,\"username\":\"YUZA\",\"type\":50,\"admin\":\"0\",\"cardnumber\":8711447,\"fdata\":\"http://127.0.0.1:8000/img/default.png\"}','Failed Insert User YUZA: Register face failed','Failed'),
(110,'2025-12-01 14:10:43','{\"sn\":\"ZXRL23103798\",\"userid\":8,\"username\":\"YUZA\",\"type\":50,\"admin\":\"0\",\"cardnumber\":8711447,\"fdata\":\"http://127.0.0.1:8000/img/default.png\"}','Failed Insert User YUZA: Register face failed','Failed'),
(111,'2025-12-01 14:10:49','{\"sn\":\"ZXRL23103798\",\"userid\":\"8\"}','Failed Delete User YUZA from ZXRL23103798','Failed'),
(112,'2025-12-01 14:18:38','{\"userId\":8,\"deviceId\":\"2\",\"weekzone\":0}','Weekzone synced: User YUZA → ZXRL23103798 (WZ: 0)','Successfully'),
(113,'2025-12-01 14:18:38','{\"user_id\":\"8\",\"sn\":\"ZXRL23103798\",\"username\":\"YUZA\",\"card_number\":\"8711447\",\"ip_address\":\"192.168.100.128\"}','Success Insert User YUZA','Successfully'),
(114,'2025-12-01 15:17:16','{\"user_id\":\"1\",\"card_number\":\"00015:30420\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-24-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"ayam\",\"type\":\"1\"}','Success Insert User ayam','Successfully'),
(115,'2025-12-01 15:29:13','{\"user_id\":\"2\",\"card_number\":\"23187:40951\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"07-22-2025\",\"expire_date\":\"12-31-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"13:57\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Andre\",\"type\":\"1\"}','Success Insert User Andre','Successfully'),
(116,'2025-12-02 14:25:36','{\"user_id\":\"1\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00015:30420\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User ayam','Successfully'),
(117,'2025-12-02 14:25:47','{\"user_id\":\"2\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"23187:40951\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User Andre','Successfully'),
(118,'2025-12-02 14:25:52','{\"user_id\":\"4\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00107:24131\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User Suti','Successfully'),
(119,'2025-12-02 14:25:56','{\"user_id\":\"7\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00093:21611\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User Yono','Successfully'),
(120,'2025-12-02 14:26:01','{\"user_id\":\"8\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00132:60695\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User YUZA','Successfully'),
(121,'2025-12-02 14:26:05','{\"user_id\":\"9\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00171:16688\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User bela','Successfully'),
(122,'2025-12-02 14:26:10','{\"user_id\":\"10\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00173:22915\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User Tatang','Successfully'),
(123,'2025-12-02 14:26:14','{\"user_id\":\"11\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00021:21656\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User Tatang','Successfully'),
(124,'2025-12-02 14:26:19','{\"user_id\":\"12\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00188:24907\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User untung','Successfully'),
(125,'2025-12-02 14:26:23','{\"user_id\":\"13\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00101:60437\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User ucup','Successfully'),
(126,'2025-12-02 14:26:54','{\"user_id\":\"1\",\"card_number\":\"00015:30420\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-24-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"ayam\",\"type\":\"1\"}','Success Insert User ayam','Successfully'),
(127,'2025-12-02 14:27:04','{\"user_id\":\"2\",\"card_number\":\"23187:40951\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"07-22-2025\",\"expire_date\":\"12-31-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"13:57\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Andre\",\"type\":\"1\"}','Success Insert User Andre','Successfully'),
(128,'2025-12-02 14:27:12','{\"user_id\":\"4\",\"card_number\":\"00107:24131\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-28-2025\",\"expire_date\":\"11-30-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"09:35\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Suti\",\"type\":\"1\"}','Success Insert User Suti','Successfully'),
(129,'2025-12-02 14:27:18','{\"user_id\":\"7\",\"card_number\":\"00093:21611\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-01-2024\",\"expire_date\":\"12-31-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"00:00\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Yono\",\"type\":\"1\"}','Success Insert User Yono','Successfully'),
(130,'2025-12-02 14:27:25','{\"user_id\":\"8\",\"card_number\":\"00132:60695\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-01-2024\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"00:00\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"YUZA\",\"type\":\"1\"}','Success Insert User YUZA','Successfully'),
(131,'2025-12-02 14:27:32','{\"user_id\":\"9\",\"card_number\":\"00171:16688\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"bela\",\"type\":\"1\"}','Success Insert User bela','Successfully'),
(132,'2025-12-02 14:27:38','{\"user_id\":\"10\",\"card_number\":\"00173:22915\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Tatang\",\"type\":\"1\"}','Success Insert User Tatang','Successfully'),
(133,'2025-12-02 14:27:44','{\"user_id\":\"11\",\"card_number\":\"00021:21656\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Tatang\",\"type\":\"1\"}','Success Insert User Tatang','Successfully'),
(134,'2025-12-02 14:27:54','{\"user_id\":\"12\",\"card_number\":\"00188:24907\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"untung\",\"type\":\"1\"}','Success Insert User untung','Successfully'),
(135,'2025-12-02 14:28:06','{\"user_id\":\"13\",\"card_number\":\"00101:60437\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"ucup\",\"type\":\"1\"}','Success Insert User ucup','Successfully'),
(136,'2025-12-02 14:28:48','{\"user_id\":1,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(137,'2025-12-02 14:29:45','{\"sn\":\"ZYSL20032921\",\"userid\":\"1\"}','Failed Delete User ayam from ZYSL20032921','Failed'),
(138,'2025-12-02 14:30:01','{\"sn\":\"ZYSL20032921\",\"userid\":\"2\"}','Failed Delete User Andre from ZYSL20032921','Failed'),
(139,'2025-12-02 14:30:02','{\"sn\":\"ZYSL20032921\",\"userid\":\"4\"}','Failed Delete User Suti from ZYSL20032921','Failed'),
(140,'2025-12-02 14:30:03','{\"sn\":\"ZYSL20032921\",\"userid\":\"7\"}','Failed Delete User Yono from ZYSL20032921','Failed'),
(141,'2025-12-02 14:30:04','{\"sn\":\"ZYSL20032921\",\"userid\":\"8\"}','Failed Delete User YUZA from ZYSL20032921','Failed'),
(142,'2025-12-02 14:30:05','{\"sn\":\"ZYSL20032921\",\"userid\":\"9\"}','Failed Delete User bela from ZYSL20032921','Failed'),
(143,'2025-12-02 14:30:07','{\"sn\":\"ZYSL20032921\",\"userid\":\"10\"}','Failed Delete User Tatang from ZYSL20032921','Failed'),
(144,'2025-12-02 14:30:08','{\"sn\":\"ZYSL20032921\",\"userid\":\"11\"}','Failed Delete User Tatang from ZYSL20032921','Failed'),
(145,'2025-12-02 14:30:09','{\"sn\":\"ZYSL20032921\",\"userid\":\"12\"}','Failed Delete User untung from ZYSL20032921','Failed'),
(146,'2025-12-02 14:30:10','{\"sn\":\"ZYSL20032921\",\"userid\":\"13\"}','Failed Delete User ucup from ZYSL20032921','Failed'),
(147,'2025-12-02 14:30:41','{\"sn\":\"ZYSL20032921\",\"userid\":\"1\"}','Failed Delete User ayam from ZYSL20032921','Failed'),
(148,'2025-12-02 14:30:42','{\"sn\":\"ZYSL20032921\",\"userid\":\"2\"}','Failed Delete User Andre from ZYSL20032921','Failed'),
(149,'2025-12-02 14:30:43','{\"sn\":\"ZYSL20032921\",\"userid\":\"4\"}','Failed Delete User Suti from ZYSL20032921','Failed'),
(150,'2025-12-02 14:30:44','{\"sn\":\"ZYSL20032921\",\"userid\":\"7\"}','Failed Delete User Yono from ZYSL20032921','Failed'),
(151,'2025-12-02 14:30:45','{\"sn\":\"ZYSL20032921\",\"userid\":\"8\"}','Failed Delete User YUZA from ZYSL20032921','Failed'),
(152,'2025-12-02 14:30:56','{\"user_id\":\"1\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00015:30420\",\"ip_address\":\"192.168.100.128\",\"node_id\":\"1\"}','Failed Delete User ayam - Check Network Connection','Failed'),
(153,'2025-12-02 14:31:04','{\"user_id\":\"2\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"23187:40951\",\"ip_address\":\"192.168.100.128\",\"node_id\":\"1\"}','Failed Delete User Andre - Check Network Connection','Failed'),
(154,'2025-12-02 14:31:12','{\"user_id\":\"4\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00107:24131\",\"ip_address\":\"192.168.100.128\",\"node_id\":\"1\"}','Failed Delete User Suti - Check Network Connection','Failed'),
(155,'2025-12-02 14:31:20','{\"user_id\":\"7\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00093:21611\",\"ip_address\":\"192.168.100.128\",\"node_id\":\"1\"}','Failed Delete User Yono - Check Network Connection','Failed'),
(156,'2025-12-02 14:31:28','{\"user_id\":\"8\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00132:60695\",\"ip_address\":\"192.168.100.128\",\"node_id\":\"1\"}','Failed Delete User YUZA - Check Network Connection','Failed'),
(157,'2025-12-02 14:32:02','{\"user_id\":\"1\",\"card_number\":\"00015:30420\",\"ip_address\":\"192.168.100.128\",\"node_id\":\"1\",\"timezone\":\"0\",\"begin_date\":\"11-24-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"ayam\",\"type\":\"1\"}','Failed Insert User ayam - Validation Error','Failed'),
(158,'2025-12-02 14:32:31','{\"user_id\":\"2\",\"card_number\":\"23187:40951\",\"ip_address\":\"192.168.100.128\",\"node_id\":\"1\",\"timezone\":\"0\",\"begin_date\":\"07-22-2025\",\"expire_date\":\"12-31-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"13:57\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Andre\",\"type\":\"1\"}','Failed Insert User Andre - Validation Error','Failed'),
(159,'2025-12-02 14:32:58','{\"user_id\":\"4\",\"card_number\":\"00107:24131\",\"ip_address\":\"192.168.100.128\",\"node_id\":\"1\",\"timezone\":\"0\",\"begin_date\":\"11-28-2025\",\"expire_date\":\"11-30-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"09:35\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Suti\",\"type\":\"1\"}','Failed Insert User Suti - Validation Error','Failed'),
(160,'2025-12-02 14:33:27','{\"user_id\":\"7\",\"card_number\":\"00093:21611\",\"ip_address\":\"192.168.100.128\",\"node_id\":\"1\",\"timezone\":\"0\",\"begin_date\":\"11-01-2024\",\"expire_date\":\"12-31-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"00:00\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Yono\",\"type\":\"1\"}','Failed Insert User Yono - Validation Error','Failed'),
(161,'2025-12-02 14:38:02','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(162,'2025-12-02 14:38:02','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(163,'2025-12-02 14:38:11','{\"userId\":2,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Andre → ZYSL20032920 (WZ: 0)','Successfully'),
(164,'2025-12-02 14:38:11','{\"user_id\":\"2\",\"sn\":\"ZYSL20032920\",\"username\":\"Andre\",\"card_number\":\"1519624183\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Andre','Successfully'),
(165,'2025-12-02 14:38:18','{\"userId\":4,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Suti → ZYSL20032920 (WZ: 0)','Successfully'),
(166,'2025-12-02 14:38:18','{\"user_id\":\"4\",\"sn\":\"ZYSL20032920\",\"username\":\"Suti\",\"card_number\":\"7036483\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Suti','Successfully'),
(167,'2025-12-02 14:38:23','{\"userId\":7,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Yono → ZYSL20032920 (WZ: 0)','Successfully'),
(168,'2025-12-02 14:38:23','{\"user_id\":\"7\",\"sn\":\"ZYSL20032920\",\"username\":\"Yono\",\"card_number\":\"6116459\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Yono','Successfully'),
(169,'2025-12-02 14:42:28','{\"userId\":7,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Yono → ZYSL20032920 (WZ: 0)','Successfully'),
(170,'2025-12-02 14:42:28','{\"user_id\":\"7\",\"sn\":\"ZYSL20032920\",\"username\":\"Yono\",\"card_number\":\"6116459\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Yono','Successfully'),
(171,'2025-12-02 14:42:40','{\"sn\":\"ZYSL20032920\",\"userid\":10,\"username\":\"Tatang\",\"type\":0,\"admin\":\"0\",\"cardnumber\":\"00173:22915\",\"fdata\":\"\"}','Failed Insert User Tatang: undefined','Failed'),
(172,'2025-12-02 16:06:03','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(173,'2025-12-02 16:06:03','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(174,'2025-12-02 16:06:10','{\"userId\":2,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Andre → ZYSL20032920 (WZ: 0)','Successfully'),
(175,'2025-12-02 16:06:10','{\"user_id\":\"2\",\"sn\":\"ZYSL20032920\",\"username\":\"Andre\",\"card_number\":\"1519624183\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Andre','Successfully'),
(176,'2025-12-02 16:06:16','{\"userId\":4,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Suti → ZYSL20032920 (WZ: 0)','Successfully'),
(177,'2025-12-02 16:06:16','{\"user_id\":\"4\",\"sn\":\"ZYSL20032920\",\"username\":\"Suti\",\"card_number\":\"7036483\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Suti','Successfully'),
(178,'2025-12-02 16:06:22','{\"userId\":7,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Yono → ZYSL20032920 (WZ: 0)','Successfully'),
(179,'2025-12-02 16:06:22','{\"user_id\":\"7\",\"sn\":\"ZYSL20032920\",\"username\":\"Yono\",\"card_number\":\"6116459\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Yono','Successfully'),
(180,'2025-12-02 16:06:32','{\"sn\":\"ZYSL20032920\",\"userid\":\"1\"}','Success Delete User ayam from ZYSL20032920','Successfully'),
(181,'2025-12-02 16:06:36','{\"sn\":\"ZYSL20032920\",\"userid\":\"2\"}','Success Delete User Andre from ZYSL20032920','Successfully'),
(182,'2025-12-02 16:06:39','{\"sn\":\"ZYSL20032920\",\"userid\":\"4\"}','Success Delete User Suti from ZYSL20032920','Successfully'),
(183,'2025-12-02 16:06:42','{\"sn\":\"ZYSL20032920\",\"userid\":\"7\"}','Success Delete User Yono from ZYSL20032920','Successfully'),
(184,'2025-12-02 16:06:57','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(185,'2025-12-02 16:06:57','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(186,'2025-12-02 16:07:05','{\"userId\":2,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Andre → ZYSL20032920 (WZ: 0)','Successfully'),
(187,'2025-12-02 16:07:05','{\"user_id\":\"2\",\"sn\":\"ZYSL20032920\",\"username\":\"Andre\",\"card_number\":\"1519624183\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Andre','Successfully'),
(188,'2025-12-02 16:07:11','{\"userId\":4,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Suti → ZYSL20032920 (WZ: 0)','Successfully'),
(189,'2025-12-02 16:07:11','{\"user_id\":\"4\",\"sn\":\"ZYSL20032920\",\"username\":\"Suti\",\"card_number\":\"7036483\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Suti','Successfully'),
(190,'2025-12-02 16:07:17','{\"userId\":7,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Yono → ZYSL20032920 (WZ: 0)','Successfully'),
(191,'2025-12-02 16:07:17','{\"user_id\":\"7\",\"sn\":\"ZYSL20032920\",\"username\":\"Yono\",\"card_number\":\"6116459\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Yono','Successfully'),
(192,'2025-12-02 16:08:06','{\"user_id\":1,\"sn\":\"ZYSL20032920\",\"ip\":\"192.168.100.128\"}','Failed: Machine ZYSL20032920 (192.168.100.128) OFFLINE','Failed'),
(193,'2025-12-02 16:08:17','{\"sn\":\"ZYSL20032920\",\"userid\":\"1\"}','Failed Delete User ayam from ZYSL20032920','Failed'),
(194,'2025-12-02 16:08:34','{\"user_id\":\"1\",\"card_number\":\"00015:30420\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-24-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"ayam\",\"type\":\"1\"}','Success Insert User ayam','Successfully'),
(195,'2025-12-02 16:08:46','{\"user_id\":\"1\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00015:30420\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User ayam','Successfully'),
(196,'2025-12-02 16:09:12','{\"user_id\":\"1\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00015:30420\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User ayam','Successfully'),
(197,'2025-12-02 16:09:15','{\"user_id\":\"2\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"23187:40951\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User Andre','Successfully'),
(198,'2025-12-02 16:09:20','{\"user_id\":\"4\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00107:24131\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User Suti','Successfully'),
(199,'2025-12-02 16:09:24','{\"user_id\":\"7\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00093:21611\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User Yono','Successfully'),
(200,'2025-12-02 16:09:28','{\"user_id\":\"8\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00132:60695\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User YUZA','Successfully'),
(201,'2025-12-02 16:09:32','{\"user_id\":\"9\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00171:16688\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User bela','Successfully'),
(202,'2025-12-02 16:09:38','{\"user_id\":\"10\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00173:22915\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User Tatang','Successfully'),
(203,'2025-12-02 16:09:44','{\"user_id\":\"11\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00021:21656\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User Tatang','Successfully'),
(204,'2025-12-02 16:09:48','{\"user_id\":\"12\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00188:24907\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User untung','Successfully'),
(205,'2025-12-02 16:09:53','{\"user_id\":\"13\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00101:60437\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User ucup','Successfully'),
(206,'2025-12-02 16:10:24','{\"user_id\":\"1\",\"card_number\":\"00015:30420\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-24-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"ayam\",\"type\":\"1\"}','Success Insert User ayam','Successfully'),
(207,'2025-12-02 16:10:28','{\"user_id\":\"2\",\"card_number\":\"23187:40951\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"07-22-2025\",\"expire_date\":\"12-31-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"13:57\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Andre\",\"type\":\"1\"}','Success Insert User Andre','Successfully'),
(208,'2025-12-02 16:10:33','{\"user_id\":\"4\",\"card_number\":\"00107:24131\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-28-2025\",\"expire_date\":\"11-30-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"09:35\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Suti\",\"type\":\"1\"}','Success Insert User Suti','Successfully'),
(209,'2025-12-02 16:10:36','{\"user_id\":\"7\",\"card_number\":\"00093:21611\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-01-2024\",\"expire_date\":\"12-31-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"00:00\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Yono\",\"type\":\"1\"}','Success Insert User Yono','Successfully'),
(210,'2025-12-02 16:10:40','{\"user_id\":\"8\",\"card_number\":\"00132:60695\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-01-2024\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"00:00\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"YUZA\",\"type\":\"1\"}','Success Insert User YUZA','Successfully'),
(211,'2025-12-02 16:10:44','{\"user_id\":\"9\",\"card_number\":\"00171:16688\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"bela\",\"type\":\"1\"}','Success Insert User bela','Successfully'),
(212,'2025-12-02 16:10:48','{\"user_id\":\"10\",\"card_number\":\"00173:22915\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Tatang\",\"type\":\"1\"}','Success Insert User Tatang','Successfully'),
(213,'2025-12-02 16:10:51','{\"user_id\":\"11\",\"card_number\":\"00021:21656\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Tatang\",\"type\":\"1\"}','Success Insert User Tatang','Successfully'),
(214,'2025-12-02 16:10:56','{\"user_id\":\"12\",\"card_number\":\"00188:24907\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"untung\",\"type\":\"1\"}','Success Insert User untung','Successfully'),
(215,'2025-12-02 16:11:00','{\"user_id\":\"13\",\"card_number\":\"00101:60437\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"ucup\",\"type\":\"1\"}','Success Insert User ucup','Successfully'),
(216,'2025-12-04 14:38:42','{\"user_id\":\"1\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00015:30420\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User ayam','Successfully'),
(217,'2025-12-04 14:38:46','{\"user_id\":\"2\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"23187:40951\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User Andre','Successfully'),
(218,'2025-12-04 14:38:49','{\"user_id\":\"4\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00107:24131\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User Suti','Successfully'),
(219,'2025-12-04 14:38:52','{\"user_id\":\"7\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00093:21611\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User Yono','Successfully'),
(220,'2025-12-04 14:38:55','{\"user_id\":\"8\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00132:60695\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User YUZA','Successfully'),
(221,'2025-12-04 14:38:58','{\"user_id\":\"9\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00171:16688\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User bela','Successfully'),
(222,'2025-12-04 14:39:01','{\"user_id\":\"10\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00173:22915\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User Tatang','Successfully'),
(223,'2025-12-04 14:39:04','{\"user_id\":\"11\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00021:21656\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User Tatang','Successfully'),
(224,'2025-12-04 14:39:07','{\"user_id\":\"12\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00188:24907\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User untung','Successfully'),
(225,'2025-12-04 14:39:10','{\"user_id\":\"13\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00101:60437\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User ucup','Successfully'),
(226,'2025-12-04 14:39:32','{\"user_id\":\"1\",\"card_number\":\"00015:30420\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-24-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"ayam\",\"type\":\"1\"}','Success Insert User ayam','Successfully'),
(227,'2025-12-04 14:39:36','{\"user_id\":\"2\",\"card_number\":\"23187:40951\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"07-22-2025\",\"expire_date\":\"12-31-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"13:57\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Andre\",\"type\":\"1\"}','Success Insert User Andre','Successfully'),
(228,'2025-12-04 14:39:40','{\"user_id\":\"4\",\"card_number\":\"00107:24131\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-28-2025\",\"expire_date\":\"11-30-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"09:35\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Suti\",\"type\":\"1\"}','Success Insert User Suti','Successfully'),
(229,'2025-12-04 14:39:44','{\"user_id\":\"7\",\"card_number\":\"00093:21611\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-01-2024\",\"expire_date\":\"12-31-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"00:00\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Yono\",\"type\":\"1\"}','Success Insert User Yono','Successfully'),
(230,'2025-12-04 14:39:48','{\"user_id\":\"8\",\"card_number\":\"00132:60695\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-01-2024\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"00:00\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"YUZA\",\"type\":\"1\"}','Success Insert User YUZA','Successfully'),
(231,'2025-12-04 14:39:52','{\"user_id\":\"9\",\"card_number\":\"00171:16688\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"bela\",\"type\":\"1\"}','Success Insert User bela','Successfully'),
(232,'2025-12-04 14:39:56','{\"user_id\":\"10\",\"card_number\":\"00173:22915\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Tatang\",\"type\":\"1\"}','Success Insert User Tatang','Successfully'),
(233,'2025-12-04 14:40:00','{\"user_id\":\"11\",\"card_number\":\"00021:21656\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Tatang\",\"type\":\"1\"}','Success Insert User Tatang','Successfully'),
(234,'2025-12-04 14:40:04','{\"user_id\":\"12\",\"card_number\":\"00188:24907\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"untung\",\"type\":\"1\"}','Success Insert User untung','Successfully'),
(235,'2025-12-04 14:40:08','{\"user_id\":\"13\",\"card_number\":\"00101:60437\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"ucup\",\"type\":\"1\"}','Success Insert User ucup','Successfully'),
(236,'2025-12-05 16:52:40','{\"user_id\":\"1\",\"card_number\":\"00015:30420\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-24-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"ayam\",\"type\":\"1\"}','Success Insert User ayam','Successfully'),
(237,'2025-12-05 16:52:48','{\"user_id\":\"2\",\"card_number\":\"23187:40951\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"07-22-2025\",\"expire_date\":\"12-31-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"13:57\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Andre\",\"type\":\"1\"}','Success Insert User Andre','Successfully'),
(238,'2025-12-05 16:52:53','{\"user_id\":\"4\",\"card_number\":\"00107:24131\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-28-2025\",\"expire_date\":\"11-30-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"09:35\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Suti\",\"type\":\"1\"}','Success Insert User Suti','Successfully'),
(239,'2025-12-05 16:52:58','{\"user_id\":\"7\",\"card_number\":\"00093:21611\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-01-2024\",\"expire_date\":\"12-31-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"00:00\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Yono\",\"type\":\"1\"}','Success Insert User Yono','Successfully'),
(240,'2025-12-05 16:53:06','{\"user_id\":\"8\",\"card_number\":\"00132:60695\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-01-2024\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"00:00\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"YUZA\",\"type\":\"1\"}','Success Insert User YUZA','Successfully'),
(241,'2025-12-05 16:53:13','{\"user_id\":\"9\",\"card_number\":\"00171:16688\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"bela\",\"type\":\"1\"}','Success Insert User bela','Successfully'),
(242,'2025-12-05 16:53:18','{\"user_id\":\"10\",\"card_number\":\"00173:22915\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Tatang\",\"type\":\"1\"}','Success Insert User Tatang','Successfully'),
(243,'2025-12-05 16:53:22','{\"user_id\":\"11\",\"card_number\":\"00021:21656\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Tatang\",\"type\":\"1\"}','Success Insert User Tatang','Successfully'),
(244,'2025-12-05 16:53:26','{\"user_id\":\"12\",\"card_number\":\"00188:24907\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"untung\",\"type\":\"1\"}','Success Insert User untung','Successfully'),
(245,'2025-12-05 16:53:30','{\"user_id\":\"13\",\"card_number\":\"00101:60437\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"ucup\",\"type\":\"1\"}','Success Insert User ucup','Successfully'),
(246,'2025-12-05 16:53:49','{\"user_id\":\"1\",\"card_number\":\"00015:30420\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-24-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"ayam\",\"type\":\"1\"}','Success Insert User ayam','Successfully'),
(247,'2025-12-05 16:53:54','{\"user_id\":\"2\",\"card_number\":\"23187:40951\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"07-22-2025\",\"expire_date\":\"12-31-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"13:57\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Andre\",\"type\":\"1\"}','Success Insert User Andre','Successfully'),
(248,'2025-12-05 16:53:58','{\"user_id\":\"4\",\"card_number\":\"00107:24131\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-28-2025\",\"expire_date\":\"11-30-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"09:35\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Suti\",\"type\":\"1\"}','Success Insert User Suti','Successfully'),
(249,'2025-12-05 16:54:02','{\"user_id\":\"7\",\"card_number\":\"00093:21611\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-01-2024\",\"expire_date\":\"12-31-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"00:00\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Yono\",\"type\":\"1\"}','Success Insert User Yono','Successfully'),
(250,'2025-12-05 16:54:06','{\"user_id\":\"8\",\"card_number\":\"00132:60695\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-01-2024\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"00:00\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"YUZA\",\"type\":\"1\"}','Success Insert User YUZA','Successfully'),
(251,'2025-12-05 16:54:10','{\"user_id\":\"9\",\"card_number\":\"00171:16688\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"bela\",\"type\":\"1\"}','Success Insert User bela','Successfully'),
(252,'2025-12-05 16:54:14','{\"user_id\":\"10\",\"card_number\":\"00173:22915\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Tatang\",\"type\":\"1\"}','Success Insert User Tatang','Successfully'),
(253,'2025-12-05 16:54:18','{\"user_id\":\"11\",\"card_number\":\"00021:21656\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Tatang\",\"type\":\"1\"}','Success Insert User Tatang','Successfully'),
(254,'2025-12-05 16:54:22','{\"user_id\":\"12\",\"card_number\":\"00188:24907\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"untung\",\"type\":\"1\"}','Success Insert User untung','Successfully'),
(255,'2025-12-05 16:54:26','{\"user_id\":\"13\",\"card_number\":\"00101:60437\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"ucup\",\"type\":\"1\"}','Success Insert User ucup','Successfully'),
(256,'2025-12-08 10:32:44','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(257,'2025-12-08 10:32:44','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(258,'2025-12-08 15:02:43','{\"userId\":1,\"deviceId\":\"1\"}','Failed to sync weekzone: Gagal mengirim data ke mesin: Client error: `POST http://localhost:787/sztimmy/userWeekzone` resulted in a `404 Not Found` response:\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-','Failed'),
(259,'2025-12-08 15:02:43','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(260,'2025-12-08 15:03:01','{\"userId\":2,\"deviceId\":\"1\"}','Failed to sync weekzone: Gagal mengirim data ke mesin: Client error: `POST http://localhost:787/sztimmy/userWeekzone` resulted in a `404 Not Found` response:\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-','Failed'),
(261,'2025-12-08 15:03:01','{\"user_id\":\"2\",\"sn\":\"ZYSL20032920\",\"username\":\"Andre\",\"card_number\":\"1519624183\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Andre','Successfully'),
(262,'2025-12-08 15:03:10','{\"userId\":4,\"deviceId\":\"1\"}','Failed to sync weekzone: Gagal mengirim data ke mesin: Client error: `POST http://localhost:787/sztimmy/userWeekzone` resulted in a `404 Not Found` response:\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-','Failed'),
(263,'2025-12-08 15:03:10','{\"user_id\":\"4\",\"sn\":\"ZYSL20032920\",\"username\":\"Suti\",\"card_number\":\"7036483\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Suti','Successfully'),
(264,'2025-12-10 09:11:33','{\"user_id\":\"1\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00015:30420\",\"ip_address\":\"192.168.100.114\",\"node_id\":\"14\"}','Failed Delete User ayam: Unexpected token \'<\', \"<!DOCTYPE \"... is not valid JSON','Failed'),
(265,'2025-12-10 09:13:38','{\"user_id\":\"1\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00015:30420\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Failed Delete User ayam: Unexpected token \'<\', \"<!DOCTYPE \"... is not valid JSON','Failed'),
(266,'2025-12-10 09:15:04','{\"sn\":\"ZYSL20032920\",\"userid\":\"1\"}','Success Delete User ayam from ZYSL20032920','Successfully'),
(267,'2025-12-10 09:23:29','{\"sn\":\"ZXRL23103798\",\"userid\":\"1\"}','Failed Delete User ayam from ZXRL23103798','Failed'),
(268,'2025-12-10 09:23:43','{\"sn\":\"ZYSL20032921\",\"userid\":\"1\"}','Failed Delete User ayam from ZYSL20032921','Failed'),
(269,'2025-12-10 09:23:51','{\"user_id\":\"1\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00015:30420\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Failed Delete User ayam: Unexpected token \'<\', \"<!DOCTYPE \"... is not valid JSON','Failed'),
(270,'2025-12-10 09:30:04','{\"user_id\":\"1\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00015:30420\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Failed Delete User ayam: HTTP 500: <!DOCTYPE html>\n<html lang=\"en\" class=\"auto\">\n<!--\nSymfony\\Component\\ErrorHandler\\Error\\FatalError: Maximum execution time of 60 seconds exceeded in file D:\\fariz\\laravel\\accesscontrol - update lift\\v','Failed'),
(271,'2025-12-10 09:50:44','{\"user_id\":\"2\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"23187:40951\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Failed Delete User Andre: signal timed out','Failed'),
(272,'2025-12-10 09:51:13','{\"sn\":\"ZYSL20032920\",\"userid\":\"2\"}','Success Delete User Andre from ZYSL20032920','Successfully'),
(273,'2025-12-10 09:51:13','{\"user_id\":\"2\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"23187:40951\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Failed Delete User Andre: signal timed out','Failed'),
(274,'2025-12-10 09:52:15','{\"sn\":\"ZYSL20032920\",\"userid\":\"2\"}','Success Delete User Andre from ZYSL20032920','Successfully'),
(275,'2025-12-10 09:52:15','{\"sn\":\"ZYSL20032920\",\"userid\":\"2\"}','Failed Delete User Andre: undefined','Failed'),
(276,'2025-12-10 09:53:25','{\"sn\":\"ZYSL20032920\",\"userid\":\"4\"}','Success Delete User Suti from ZYSL20032920','Successfully'),
(277,'2025-12-10 09:53:47','{\"sn\":\"ZYSL20032921\",\"userid\":\"4\"}','Failed Delete User Suti from ZYSL20032921','Failed'),
(278,'2025-12-10 09:53:53','{\"sn\":\"ZYSL20032921\",\"userid\":\"4\"}','Failed Delete User Suti from ZYSL20032921','Failed'),
(279,'2025-12-10 09:54:02','{\"sn\":\"ZYSL20032920\",\"userid\":\"4\"}','Success Delete User Suti from ZYSL20032920','Successfully'),
(280,'2025-12-10 09:54:02','{\"sn\":\"ZYSL20032920\",\"userid\":\"4\"}','Failed Delete User Suti: undefined','Failed'),
(281,'2025-12-10 09:54:17','{\"user_id\":\"4\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00107:24131\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Failed Delete User Suti: signal timed out','Failed'),
(282,'2025-12-10 09:56:18','{\"user_id\":\"1\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00015:30420\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Failed Delete User ayam: signal timed out','Failed'),
(283,'2025-12-10 09:56:52','{\"user_id\":1,\"sn\":\"ZYSL20032920\",\"ip\":\"192.168.100.128\"}','Failed: Machine ZYSL20032920 (192.168.100.128) OFFLINE','Failed'),
(284,'2025-12-10 09:56:57','{\"user_id\":1,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(285,'2025-12-10 09:57:02','{\"user_id\":2,\"sn\":\"ZYSL20032920\",\"ip\":\"192.168.100.128\"}','Failed: Machine ZYSL20032920 (192.168.100.128) OFFLINE','Failed'),
(286,'2025-12-10 09:57:07','{\"user_id\":2,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(287,'2025-12-10 09:57:12','{\"user_id\":4,\"sn\":\"ZYSL20032920\",\"ip\":\"192.168.100.128\"}','Failed: Machine ZYSL20032920 (192.168.100.128) OFFLINE','Failed'),
(288,'2025-12-10 09:57:17','{\"user_id\":4,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(289,'2025-12-10 09:57:22','{\"user_id\":7,\"sn\":\"ZYSL20032920\",\"ip\":\"192.168.100.128\"}','Failed: Machine ZYSL20032920 (192.168.100.128) OFFLINE','Failed'),
(290,'2025-12-10 09:57:27','{\"user_id\":7,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(291,'2025-12-10 09:57:32','{\"user_id\":8,\"sn\":\"ZYSL20032920\",\"ip\":\"192.168.100.128\"}','Failed: Machine ZYSL20032920 (192.168.100.128) OFFLINE','Failed'),
(292,'2025-12-10 09:57:36','{\"user_id\":8,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(293,'2025-12-10 09:57:38','{\"userId\":9,\"deviceId\":\"1\"}','Failed to sync weekzone: Gagal mengirim data ke mesin: Client error: `POST http://localhost:787/sztimmy/userWeekzone` resulted in a `404 Not Found` response:\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-','Failed'),
(294,'2025-12-10 09:57:38','{\"user_id\":\"9\",\"sn\":\"ZYSL20032920\",\"username\":\"bela\",\"card_number\":\"11223344\",\"ip_address\":\"192.168.100.128\"}','Success Insert User bela','Successfully'),
(295,'2025-12-10 09:57:52','{\"user_id\":9,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(296,'2025-12-10 09:57:54','{\"sn\":\"ZYSL20032920\",\"userid\":10,\"username\":\"Tatang\",\"type\":0,\"admin\":\"0\",\"cardnumber\":\"00173:22915\",\"fdata\":\"0\"}','Failed Insert User Tatang: undefined','Failed'),
(297,'2025-12-10 09:58:00','{\"user_id\":10,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(298,'2025-12-10 09:58:03','{\"userId\":11,\"deviceId\":\"1\"}','Failed to sync weekzone: Gagal mengirim data ke mesin: Client error: `POST http://localhost:787/sztimmy/userWeekzone` resulted in a `404 Not Found` response:\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-','Failed'),
(299,'2025-12-10 09:58:03','{\"user_id\":\"11\",\"sn\":\"ZYSL20032920\",\"username\":\"Tatang\",\"card_number\":\"1397912\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Tatang','Successfully'),
(300,'2025-12-10 09:58:09','{\"user_id\":11,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(301,'2025-12-10 09:58:11','{\"userId\":12,\"deviceId\":\"1\"}','Failed to sync weekzone: Gagal mengirim data ke mesin: Client error: `POST http://localhost:787/sztimmy/userWeekzone` resulted in a `404 Not Found` response:\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-','Failed'),
(302,'2025-12-10 09:58:11','{\"user_id\":\"12\",\"sn\":\"ZYSL20032920\",\"username\":\"untung\",\"card_number\":\"12345675\",\"ip_address\":\"192.168.100.128\"}','Success Insert User untung','Successfully'),
(303,'2025-12-10 09:58:17','{\"user_id\":12,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(304,'2025-12-10 09:58:19','{\"userId\":13,\"deviceId\":\"1\"}','Failed to sync weekzone: Gagal mengirim data ke mesin: Client error: `POST http://localhost:787/sztimmy/userWeekzone` resulted in a `404 Not Found` response:\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-','Failed'),
(305,'2025-12-10 09:58:19','{\"user_id\":\"13\",\"sn\":\"ZYSL20032920\",\"username\":\"ucup\",\"card_number\":\"6679573\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ucup','Successfully'),
(306,'2025-12-10 09:58:24','{\"user_id\":13,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(307,'2025-12-10 09:59:05','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(308,'2025-12-10 09:59:05','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(309,'2025-12-10 09:59:12','{\"userId\":2,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Andre → ZYSL20032920 (WZ: 0)','Successfully'),
(310,'2025-12-10 09:59:12','{\"user_id\":\"2\",\"sn\":\"ZYSL20032920\",\"username\":\"Andre\",\"card_number\":\"1519624183\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Andre','Successfully'),
(311,'2025-12-10 09:59:17','{\"userId\":4,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Suti → ZYSL20032920 (WZ: 0)','Successfully'),
(312,'2025-12-10 09:59:17','{\"user_id\":\"4\",\"sn\":\"ZYSL20032920\",\"username\":\"Suti\",\"card_number\":\"7036483\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Suti','Successfully'),
(313,'2025-12-10 09:59:23','{\"userId\":7,\"deviceId\":\"1\"}','Failed to sync weekzone: Gagal mengirim data ke mesin: Client error: `POST http://localhost:787/sztimmy/userWeekzone` resulted in a `404 Not Found` response:\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-','Failed'),
(314,'2025-12-10 09:59:23','{\"user_id\":\"7\",\"sn\":\"ZYSL20032920\",\"username\":\"Yono\",\"card_number\":\"6116459\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Yono','Successfully'),
(315,'2025-12-10 09:59:28','{\"userId\":8,\"deviceId\":\"1\"}','Failed to sync weekzone: Gagal mengirim data ke mesin: Client error: `POST http://localhost:787/sztimmy/userWeekzone` resulted in a `404 Not Found` response:\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-','Failed'),
(316,'2025-12-10 09:59:28','{\"user_id\":\"8\",\"sn\":\"ZYSL20032920\",\"username\":\"YUZA\",\"card_number\":\"8711447\",\"ip_address\":\"192.168.100.128\"}','Success Insert User YUZA','Successfully'),
(317,'2025-12-10 09:59:34','{\"userId\":9,\"deviceId\":\"1\"}','Failed to sync weekzone: Gagal mengirim data ke mesin: Client error: `POST http://localhost:787/sztimmy/userWeekzone` resulted in a `404 Not Found` response:\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-','Failed'),
(318,'2025-12-10 09:59:34','{\"user_id\":\"9\",\"sn\":\"ZYSL20032920\",\"username\":\"bela\",\"card_number\":\"11223344\",\"ip_address\":\"192.168.100.128\"}','Success Insert User bela','Successfully'),
(319,'2025-12-10 09:59:40','{\"sn\":\"ZYSL20032920\",\"userid\":10,\"username\":\"Tatang\",\"type\":0,\"admin\":\"0\",\"cardnumber\":\"00173:22915\",\"fdata\":\"1\"}','Failed Insert User Tatang: undefined','Failed'),
(320,'2025-12-10 09:59:43','{\"userId\":11,\"deviceId\":\"1\"}','Failed to sync weekzone: Gagal mengirim data ke mesin: Client error: `POST http://localhost:787/sztimmy/userWeekzone` resulted in a `404 Not Found` response:\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-','Failed'),
(321,'2025-12-10 09:59:43','{\"user_id\":\"11\",\"sn\":\"ZYSL20032920\",\"username\":\"Tatang\",\"card_number\":\"1397912\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Tatang','Successfully'),
(322,'2025-12-10 09:59:48','{\"userId\":12,\"deviceId\":\"1\"}','Failed to sync weekzone: Gagal mengirim data ke mesin: Client error: `POST http://localhost:787/sztimmy/userWeekzone` resulted in a `404 Not Found` response:\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-','Failed'),
(323,'2025-12-10 09:59:48','{\"user_id\":\"12\",\"sn\":\"ZYSL20032920\",\"username\":\"untung\",\"card_number\":\"12345675\",\"ip_address\":\"192.168.100.128\"}','Success Insert User untung','Successfully'),
(324,'2025-12-10 09:59:54','{\"userId\":13,\"deviceId\":\"1\"}','Failed to sync weekzone: Gagal mengirim data ke mesin: Client error: `POST http://localhost:787/sztimmy/userWeekzone` resulted in a `404 Not Found` response:\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-','Failed'),
(325,'2025-12-10 09:59:54','{\"user_id\":\"13\",\"sn\":\"ZYSL20032920\",\"username\":\"ucup\",\"card_number\":\"6679573\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ucup','Successfully'),
(326,'2025-12-10 10:00:22','{\"sn\":\"ZYSL20032920\",\"userid\":\"1\"}','Success Delete User ayam from ZYSL20032920','Successfully'),
(327,'2025-12-10 10:00:22','{\"sn\":\"ZYSL20032920\",\"userid\":\"2\"}','Success Delete User Andre from ZYSL20032920','Successfully'),
(328,'2025-12-10 10:00:22','{\"sn\":\"ZYSL20032920\",\"userid\":\"4\"}','Success Delete User Suti from ZYSL20032920','Successfully'),
(329,'2025-12-10 10:00:22','{\"sn\":\"ZYSL20032920\",\"userid\":\"7\"}','Success Delete User Yono from ZYSL20032920','Successfully'),
(330,'2025-12-10 10:00:22','{\"sn\":\"ZYSL20032920\",\"userid\":\"8\"}','Success Delete User YUZA from ZYSL20032920','Successfully'),
(331,'2025-12-10 10:00:22','{\"sn\":\"ZYSL20032920\",\"userid\":\"9\"}','Success Delete User bela from ZYSL20032920','Successfully'),
(332,'2025-12-10 10:00:22','{\"sn\":\"ZYSL20032920\",\"userid\":\"10\"}','Success Delete User Tatang from ZYSL20032920','Successfully'),
(333,'2025-12-10 10:00:22','{\"sn\":\"ZYSL20032920\",\"userid\":\"10\"}','Failed Delete User Tatang: undefined','Failed'),
(334,'2025-12-10 10:00:22','{\"sn\":\"ZYSL20032920\",\"userid\":\"11\"}','Success Delete User Tatang from ZYSL20032920','Successfully'),
(335,'2025-12-10 10:00:22','{\"sn\":\"ZYSL20032920\",\"userid\":\"12\"}','Success Delete User untung from ZYSL20032920','Successfully'),
(336,'2025-12-10 10:00:22','{\"sn\":\"ZYSL20032920\",\"userid\":\"13\"}','Success Delete User ucup from ZYSL20032920','Successfully'),
(337,'2025-12-10 10:01:07','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(338,'2025-12-10 10:01:07','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(339,'2025-12-10 10:01:13','{\"userId\":2,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Andre → ZYSL20032920 (WZ: 0)','Successfully'),
(340,'2025-12-10 10:01:13','{\"user_id\":\"2\",\"sn\":\"ZYSL20032920\",\"username\":\"Andre\",\"card_number\":\"1519624183\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Andre','Successfully'),
(341,'2025-12-10 10:01:19','{\"userId\":4,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Suti → ZYSL20032920 (WZ: 0)','Successfully'),
(342,'2025-12-10 10:01:19','{\"user_id\":\"4\",\"sn\":\"ZYSL20032920\",\"username\":\"Suti\",\"card_number\":\"7036483\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Suti','Successfully'),
(343,'2025-12-10 10:01:25','{\"userId\":7,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Yono → ZYSL20032920 (WZ: 0)','Successfully'),
(344,'2025-12-10 10:01:25','{\"user_id\":\"7\",\"sn\":\"ZYSL20032920\",\"username\":\"Yono\",\"card_number\":\"6116459\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Yono','Successfully'),
(345,'2025-12-10 10:01:30','{\"userId\":8,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User YUZA → ZYSL20032920 (WZ: 0)','Successfully'),
(346,'2025-12-10 10:01:30','{\"user_id\":\"8\",\"sn\":\"ZYSL20032920\",\"username\":\"YUZA\",\"card_number\":\"8711447\",\"ip_address\":\"192.168.100.128\"}','Success Insert User YUZA','Successfully'),
(347,'2025-12-10 10:01:35','{\"userId\":9,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User bela → ZYSL20032920 (WZ: 0)','Successfully'),
(348,'2025-12-10 10:01:35','{\"user_id\":\"9\",\"sn\":\"ZYSL20032920\",\"username\":\"bela\",\"card_number\":\"11223344\",\"ip_address\":\"192.168.100.128\"}','Success Insert User bela','Successfully'),
(349,'2025-12-10 10:01:39','{\"sn\":\"ZYSL20032920\",\"userid\":10,\"username\":\"Tatang\",\"type\":0,\"admin\":\"0\",\"cardnumber\":\"00173:22915\",\"fdata\":\"1\"}','Failed Insert User Tatang: undefined','Failed'),
(350,'2025-12-10 10:01:42','{\"userId\":11,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Tatang → ZYSL20032920 (WZ: 0)','Successfully'),
(351,'2025-12-10 10:01:42','{\"user_id\":\"11\",\"sn\":\"ZYSL20032920\",\"username\":\"Tatang\",\"card_number\":\"1397912\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Tatang','Successfully'),
(352,'2025-12-10 10:01:47','{\"userId\":12,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User untung → ZYSL20032920 (WZ: 0)','Successfully'),
(353,'2025-12-10 10:01:47','{\"user_id\":\"12\",\"sn\":\"ZYSL20032920\",\"username\":\"untung\",\"card_number\":\"12345675\",\"ip_address\":\"192.168.100.128\"}','Success Insert User untung','Successfully'),
(354,'2025-12-10 10:01:52','{\"userId\":13,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ucup → ZYSL20032920 (WZ: 0)','Successfully'),
(355,'2025-12-10 10:01:52','{\"user_id\":\"13\",\"sn\":\"ZYSL20032920\",\"username\":\"ucup\",\"card_number\":\"6679573\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ucup','Successfully'),
(356,'2025-12-10 10:02:29','{\"sn\":\"ZYSL20032920\",\"userid\":\"1\"}','Success Delete User ayam from ZYSL20032920','Successfully'),
(357,'2025-12-10 10:02:29','{\"sn\":\"ZYSL20032920\",\"userid\":\"2\"}','Success Delete User Andre from ZYSL20032920','Successfully'),
(358,'2025-12-10 10:02:29','{\"sn\":\"ZYSL20032920\",\"userid\":\"4\"}','Success Delete User Suti from ZYSL20032920','Successfully'),
(359,'2025-12-10 10:02:29','{\"sn\":\"ZYSL20032920\",\"userid\":\"7\"}','Success Delete User Yono from ZYSL20032920','Successfully'),
(360,'2025-12-10 10:02:29','{\"sn\":\"ZYSL20032920\",\"userid\":\"8\"}','Success Delete User YUZA from ZYSL20032920','Successfully'),
(361,'2025-12-10 10:02:29','{\"sn\":\"ZYSL20032920\",\"userid\":\"9\"}','Success Delete User bela from ZYSL20032920','Successfully'),
(362,'2025-12-10 10:02:29','{\"sn\":\"ZYSL20032920\",\"userid\":\"10\"}','Success Delete User Tatang from ZYSL20032920','Successfully'),
(363,'2025-12-10 10:02:29','{\"sn\":\"ZYSL20032920\",\"userid\":\"10\"}','Failed Delete User Tatang: undefined','Failed'),
(364,'2025-12-10 10:02:29','{\"sn\":\"ZYSL20032920\",\"userid\":\"11\"}','Success Delete User Tatang from ZYSL20032920','Successfully'),
(365,'2025-12-10 10:02:29','{\"sn\":\"ZYSL20032920\",\"userid\":\"12\"}','Success Delete User untung from ZYSL20032920','Successfully'),
(366,'2025-12-10 10:02:29','{\"sn\":\"ZYSL20032920\",\"userid\":\"13\"}','Success Delete User ucup from ZYSL20032920','Successfully'),
(367,'2025-12-10 10:03:08','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(368,'2025-12-10 10:03:08','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(369,'2025-12-10 10:03:13','{\"userId\":2,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Andre → ZYSL20032920 (WZ: 0)','Successfully'),
(370,'2025-12-10 10:03:13','{\"user_id\":\"2\",\"sn\":\"ZYSL20032920\",\"username\":\"Andre\",\"card_number\":\"1519624183\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Andre','Successfully'),
(371,'2025-12-10 10:03:18','{\"userId\":4,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Suti → ZYSL20032920 (WZ: 0)','Successfully'),
(372,'2025-12-10 10:03:18','{\"user_id\":\"4\",\"sn\":\"ZYSL20032920\",\"username\":\"Suti\",\"card_number\":\"7036483\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Suti','Successfully'),
(373,'2025-12-10 10:03:23','{\"userId\":7,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Yono → ZYSL20032920 (WZ: 0)','Successfully'),
(374,'2025-12-10 10:03:23','{\"user_id\":\"7\",\"sn\":\"ZYSL20032920\",\"username\":\"Yono\",\"card_number\":\"6116459\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Yono','Successfully'),
(375,'2025-12-10 10:03:29','{\"userId\":8,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User YUZA → ZYSL20032920 (WZ: 0)','Successfully'),
(376,'2025-12-10 10:03:29','{\"user_id\":\"8\",\"sn\":\"ZYSL20032920\",\"username\":\"YUZA\",\"card_number\":\"8711447\",\"ip_address\":\"192.168.100.128\"}','Success Insert User YUZA','Successfully'),
(377,'2025-12-10 10:03:33','{\"userId\":9,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User bela → ZYSL20032920 (WZ: 0)','Successfully'),
(378,'2025-12-10 10:03:33','{\"user_id\":\"9\",\"sn\":\"ZYSL20032920\",\"username\":\"bela\",\"card_number\":\"11223344\",\"ip_address\":\"192.168.100.128\"}','Success Insert User bela','Successfully'),
(379,'2025-12-10 10:03:38','{\"sn\":\"ZYSL20032920\",\"userid\":10,\"username\":\"Tatang\",\"type\":0,\"admin\":\"0\",\"cardnumber\":\"00173:22915\",\"fdata\":\"1\"}','Failed Insert User Tatang: undefined','Failed'),
(380,'2025-12-10 10:03:41','{\"userId\":11,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Tatang → ZYSL20032920 (WZ: 0)','Successfully'),
(381,'2025-12-10 10:03:41','{\"user_id\":\"11\",\"sn\":\"ZYSL20032920\",\"username\":\"Tatang\",\"card_number\":\"1397912\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Tatang','Successfully'),
(382,'2025-12-10 10:03:46','{\"userId\":12,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User untung → ZYSL20032920 (WZ: 0)','Successfully'),
(383,'2025-12-10 10:03:46','{\"user_id\":\"12\",\"sn\":\"ZYSL20032920\",\"username\":\"untung\",\"card_number\":\"12345675\",\"ip_address\":\"192.168.100.128\"}','Success Insert User untung','Successfully'),
(384,'2025-12-10 10:03:50','{\"userId\":13,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ucup → ZYSL20032920 (WZ: 0)','Successfully'),
(385,'2025-12-10 10:03:50','{\"user_id\":\"13\",\"sn\":\"ZYSL20032920\",\"username\":\"ucup\",\"card_number\":\"6679573\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ucup','Successfully'),
(386,'2025-12-10 10:04:32','{\"sn\":\"ZYSL20032920\",\"userid\":\"1\"}','Success Delete User ayam from ZYSL20032920','Successfully'),
(387,'2025-12-10 10:04:51','{\"sn\":\"ZYSL20032920\",\"userid\":\"2\"}','Success Delete User Andre from ZYSL20032920','Successfully'),
(388,'2025-12-10 10:05:25','{\"sn\":\"ZYSL20032920\",\"userid\":\"4\"}','Success Delete User Suti from ZYSL20032920','Successfully'),
(389,'2025-12-10 10:06:03','{\"sn\":\"ZYSL20032920\",\"userid\":\"4\"}','Success Delete User Suti from ZYSL20032920','Successfully'),
(390,'2025-12-10 10:06:03','{\"sn\":\"ZYSL20032920\",\"userid\":\"4\"}','Failed Delete User Suti: undefined','Failed'),
(391,'2025-12-10 10:06:40','{\"sn\":\"ZYSL20032920\",\"userid\":\"7\"}','Success Delete User Yono from ZYSL20032920','Successfully'),
(392,'2025-12-10 10:06:40','{\"sn\":\"ZYSL20032920\",\"userid\":\"8\"}','Success Delete User YUZA from ZYSL20032920','Successfully'),
(393,'2025-12-10 10:06:40','{\"sn\":\"ZYSL20032920\",\"userid\":\"9\"}','Success Delete User bela from ZYSL20032920','Successfully'),
(394,'2025-12-10 10:06:40','{\"sn\":\"ZYSL20032920\",\"userid\":\"10\"}','Success Delete User Tatang from ZYSL20032920','Successfully'),
(395,'2025-12-10 10:06:40','{\"sn\":\"ZYSL20032920\",\"userid\":\"10\"}','Failed Delete User Tatang: undefined','Failed'),
(396,'2025-12-10 10:06:40','{\"sn\":\"ZYSL20032920\",\"userid\":\"11\"}','Success Delete User Tatang from ZYSL20032920','Successfully'),
(397,'2025-12-10 10:06:40','{\"sn\":\"ZYSL20032920\",\"userid\":\"12\"}','Success Delete User untung from ZYSL20032920','Successfully'),
(398,'2025-12-10 10:06:40','{\"sn\":\"ZYSL20032920\",\"userid\":\"13\"}','Success Delete User ucup from ZYSL20032920','Successfully'),
(399,'2025-12-10 10:07:33','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(400,'2025-12-10 10:07:33','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(401,'2025-12-10 10:07:38','{\"user_id\":\"2\",\"sn\":\"ZYSL20032920\",\"username\":\"Andre\",\"card_number\":\"1519624183\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Andre','Successfully'),
(402,'2025-12-10 10:07:38','{\"userId\":2,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Andre → ZYSL20032920 (WZ: 0)','Successfully'),
(403,'2025-12-10 10:07:43','{\"userId\":4,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Suti → ZYSL20032920 (WZ: 0)','Successfully'),
(404,'2025-12-10 10:07:43','{\"user_id\":\"4\",\"sn\":\"ZYSL20032920\",\"username\":\"Suti\",\"card_number\":\"7036483\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Suti','Successfully'),
(405,'2025-12-10 10:07:48','{\"userId\":7,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Yono → ZYSL20032920 (WZ: 0)','Successfully'),
(406,'2025-12-10 10:07:48','{\"user_id\":\"7\",\"sn\":\"ZYSL20032920\",\"username\":\"Yono\",\"card_number\":\"6116459\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Yono','Successfully'),
(407,'2025-12-10 10:08:15','{\"sn\":\"ZYSL20032920\",\"userid\":\"1\"}','Success Delete User ayam from ZYSL20032920','Successfully'),
(408,'2025-12-10 10:08:15','{\"sn\":\"ZYSL20032920\",\"userid\":\"2\"}','Success Delete User Andre from ZYSL20032920','Successfully'),
(409,'2025-12-10 10:08:15','{\"sn\":\"ZYSL20032920\",\"userid\":\"4\"}','Success Delete User Suti from ZYSL20032920','Successfully'),
(410,'2025-12-10 10:08:15','{\"sn\":\"ZYSL20032920\",\"userid\":\"7\"}','Success Delete User Yono from ZYSL20032920','Successfully'),
(411,'2025-12-10 10:09:04','{\"userId\":8,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User YUZA → ZYSL20032920 (WZ: 0)','Successfully'),
(412,'2025-12-10 10:09:04','{\"user_id\":\"8\",\"sn\":\"ZYSL20032920\",\"username\":\"YUZA\",\"card_number\":\"8711447\",\"ip_address\":\"192.168.100.128\"}','Success Insert User YUZA','Successfully'),
(413,'2025-12-10 10:09:10','{\"userId\":9,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User bela → ZYSL20032920 (WZ: 0)','Successfully'),
(414,'2025-12-10 10:09:10','{\"user_id\":\"9\",\"sn\":\"ZYSL20032920\",\"username\":\"bela\",\"card_number\":\"11223344\",\"ip_address\":\"192.168.100.128\"}','Success Insert User bela','Successfully'),
(415,'2025-12-10 10:09:16','{\"sn\":\"ZYSL20032920\",\"userid\":10,\"username\":\"Tatang\",\"type\":0,\"admin\":\"0\",\"cardnumber\":\"00173:22915\",\"fdata\":\"1\"}','Failed Insert User Tatang: undefined','Failed'),
(416,'2025-12-10 10:09:19','{\"userId\":11,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Tatang → ZYSL20032920 (WZ: 0)','Successfully'),
(417,'2025-12-10 10:09:19','{\"user_id\":\"11\",\"sn\":\"ZYSL20032920\",\"username\":\"Tatang\",\"card_number\":\"1397912\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Tatang','Successfully'),
(418,'2025-12-10 10:10:14','{\"sn\":\"ZYSL20032920\",\"userid\":\"8\"}','Success Delete User YUZA from ZYSL20032920','Successfully'),
(419,'2025-12-10 10:10:14','{\"sn\":\"ZYSL20032920\",\"userid\":\"9\"}','Success Delete User bela from ZYSL20032920','Successfully'),
(420,'2025-12-10 10:10:14','{\"sn\":\"ZYSL20032920\",\"userid\":\"10\"}','Success Delete User Tatang from ZYSL20032920','Successfully'),
(421,'2025-12-10 10:10:14','{\"sn\":\"ZYSL20032920\",\"userid\":\"10\"}','Failed Delete User Tatang: undefined','Failed'),
(422,'2025-12-10 10:10:14','{\"sn\":\"ZYSL20032920\",\"userid\":\"11\"}','Success Delete User Tatang from ZYSL20032920','Successfully'),
(423,'2025-12-10 10:11:56','{\"user_id\":10,\"sn\":\"ZYSL20032920\",\"ip\":\"192.168.100.128\"}','Failed: Machine ZYSL20032920 (192.168.100.128) OFFLINE','Failed'),
(424,'2025-12-10 10:11:59','{\"sn\":\"ZYSL20032920\",\"userid\":11,\"username\":\"Tatang\",\"type\":0,\"admin\":\"0\",\"cardnumber\":1397912,\"fdata\":\"1\"}','Failed Insert User Tatang: Register face failed','Failed'),
(425,'2025-12-10 10:12:02','{\"user_id\":12,\"sn\":\"ZYSL20032920\",\"ip\":\"192.168.100.128\"}','Failed: Machine ZYSL20032920 (192.168.100.128) OFFLINE','Failed'),
(426,'2025-12-10 10:12:04','{\"sn\":\"ZYSL20032920\",\"userid\":13,\"username\":\"ucup\",\"type\":0,\"admin\":\"0\",\"cardnumber\":6679573,\"fdata\":\"1\"}','Failed Insert User ucup: Register face failed','Failed'),
(427,'2025-12-10 10:12:28','{\"userId\":10,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Tatang → ZYSL20032920 (WZ: 0)','Successfully'),
(428,'2025-12-10 10:12:28','{\"user_id\":\"10\",\"sn\":\"ZYSL20032920\",\"username\":\"Tatang\",\"card_number\":\"12244131\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Tatang','Successfully'),
(429,'2025-12-10 10:12:33','{\"userId\":11,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Tatang → ZYSL20032920 (WZ: 0)','Successfully'),
(430,'2025-12-10 10:12:33','{\"user_id\":\"11\",\"sn\":\"ZYSL20032920\",\"username\":\"Tatang\",\"card_number\":\"1397912\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Tatang','Successfully'),
(431,'2025-12-10 10:12:37','{\"userId\":12,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User untung → ZYSL20032920 (WZ: 0)','Successfully'),
(432,'2025-12-10 10:12:37','{\"user_id\":\"12\",\"sn\":\"ZYSL20032920\",\"username\":\"untung\",\"card_number\":\"12345675\",\"ip_address\":\"192.168.100.128\"}','Success Insert User untung','Successfully'),
(433,'2025-12-10 10:12:42','{\"userId\":13,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ucup → ZYSL20032920 (WZ: 0)','Successfully'),
(434,'2025-12-10 10:12:42','{\"user_id\":\"13\",\"sn\":\"ZYSL20032920\",\"username\":\"ucup\",\"card_number\":\"6679573\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ucup','Successfully'),
(435,'2025-12-10 10:13:01','{\"sn\":\"ZYSL20032920\",\"userid\":\"10\"}','Success Delete User Tatang from ZYSL20032920','Successfully'),
(436,'2025-12-10 10:13:01','{\"sn\":\"ZYSL20032920\",\"userid\":\"11\"}','Success Delete User Tatang from ZYSL20032920','Successfully'),
(437,'2025-12-10 10:13:01','{\"sn\":\"ZYSL20032920\",\"userid\":\"12\"}','Success Delete User untung from ZYSL20032920','Successfully'),
(438,'2025-12-10 10:13:01','{\"sn\":\"ZYSL20032920\",\"userid\":\"13\"}','Success Delete User ucup from ZYSL20032920','Successfully'),
(439,'2025-12-10 10:13:21','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(440,'2025-12-10 10:13:21','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(441,'2025-12-10 10:13:26','{\"userId\":2,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Andre → ZYSL20032920 (WZ: 0)','Successfully'),
(442,'2025-12-10 10:13:26','{\"user_id\":\"2\",\"sn\":\"ZYSL20032920\",\"username\":\"Andre\",\"card_number\":\"1519624183\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Andre','Successfully'),
(443,'2025-12-10 10:13:31','{\"userId\":4,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Suti → ZYSL20032920 (WZ: 0)','Successfully'),
(444,'2025-12-10 10:13:31','{\"user_id\":\"4\",\"sn\":\"ZYSL20032920\",\"username\":\"Suti\",\"card_number\":\"7036483\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Suti','Successfully'),
(445,'2025-12-10 10:13:36','{\"userId\":7,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Yono → ZYSL20032920 (WZ: 0)','Successfully'),
(446,'2025-12-10 10:13:36','{\"user_id\":\"7\",\"sn\":\"ZYSL20032920\",\"username\":\"Yono\",\"card_number\":\"6116459\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Yono','Successfully'),
(447,'2025-12-10 10:13:40','{\"userId\":8,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User YUZA → ZYSL20032920 (WZ: 0)','Successfully'),
(448,'2025-12-10 10:13:40','{\"user_id\":\"8\",\"sn\":\"ZYSL20032920\",\"username\":\"YUZA\",\"card_number\":\"8711447\",\"ip_address\":\"192.168.100.128\"}','Success Insert User YUZA','Successfully'),
(449,'2025-12-10 10:13:44','{\"userId\":9,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User bela → ZYSL20032920 (WZ: 0)','Successfully'),
(450,'2025-12-10 10:13:44','{\"user_id\":\"9\",\"sn\":\"ZYSL20032920\",\"username\":\"bela\",\"card_number\":\"11223344\",\"ip_address\":\"192.168.100.128\"}','Success Insert User bela','Successfully'),
(451,'2025-12-10 10:13:49','{\"userId\":10,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Tatang → ZYSL20032920 (WZ: 0)','Successfully'),
(452,'2025-12-10 10:13:49','{\"user_id\":\"10\",\"sn\":\"ZYSL20032920\",\"username\":\"Tatang\",\"card_number\":\"12244131\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Tatang','Successfully'),
(453,'2025-12-10 10:13:55','{\"userId\":11,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Tatang → ZYSL20032920 (WZ: 0)','Successfully'),
(454,'2025-12-10 10:13:55','{\"user_id\":\"11\",\"sn\":\"ZYSL20032920\",\"username\":\"Tatang\",\"card_number\":\"1397912\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Tatang','Successfully'),
(455,'2025-12-10 10:14:00','{\"userId\":12,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User untung → ZYSL20032920 (WZ: 0)','Successfully'),
(456,'2025-12-10 10:14:00','{\"user_id\":\"12\",\"sn\":\"ZYSL20032920\",\"username\":\"untung\",\"card_number\":\"12345675\",\"ip_address\":\"192.168.100.128\"}','Success Insert User untung','Successfully'),
(457,'2025-12-10 10:14:04','{\"userId\":13,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ucup → ZYSL20032920 (WZ: 0)','Successfully'),
(458,'2025-12-10 10:14:04','{\"user_id\":\"13\",\"sn\":\"ZYSL20032920\",\"username\":\"ucup\",\"card_number\":\"6679573\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ucup','Successfully'),
(459,'2025-12-10 10:14:39','{\"user_id\":1,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(460,'2025-12-10 10:14:42','{\"user_id\":\"2\",\"card_number\":\"23187:40951\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"07-22-2025\",\"expire_date\":\"12-31-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"13:57\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Andre\",\"type\":\"1\"}','Failed Insert User Andre - Validation Error','Failed'),
(461,'2025-12-10 10:18:06','{\"user_id\":4,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(462,'2025-12-10 10:18:09','{\"user_id\":\"7\",\"card_number\":\"00093:21611\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-01-2024\",\"expire_date\":\"12-31-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"00:00\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Yono\",\"type\":\"1\"}','Failed Insert User Yono - Validation Error','Failed'),
(463,'2025-12-10 10:21:33','{\"user_id\":8,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(464,'2025-12-10 10:21:36','{\"user_id\":\"9\",\"card_number\":\"00171:16688\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"bela\",\"type\":\"1\"}','Failed Insert User bela - Validation Error','Failed'),
(465,'2025-12-10 10:25:01','{\"user_id\":10,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(466,'2025-12-10 10:25:04','{\"user_id\":\"11\",\"card_number\":\"00021:21656\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-21-2025\",\"expire_date\":\"11-28-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"Tatang\",\"type\":\"1\"}','Failed Insert User Tatang - Validation Error','Failed'),
(467,'2025-12-10 10:29:31','{\"sn\":\"ZYSL20032920\",\"userid\":\"1\"}','Success Delete User ayam from ZYSL20032920','Successfully'),
(468,'2025-12-10 10:29:31','{\"sn\":\"ZYSL20032920\",\"userid\":\"2\"}','Success Delete User Andre from ZYSL20032920','Successfully'),
(469,'2025-12-10 10:29:31','{\"sn\":\"ZYSL20032920\",\"userid\":\"4\"}','Success Delete User Suti from ZYSL20032920','Successfully'),
(470,'2025-12-10 10:29:31','{\"sn\":\"ZYSL20032920\",\"userid\":\"7\"}','Success Delete User Yono from ZYSL20032920','Successfully'),
(471,'2025-12-10 10:29:31','{\"sn\":\"ZYSL20032920\",\"userid\":\"8\"}','Success Delete User YUZA from ZYSL20032920','Successfully'),
(472,'2025-12-10 10:29:31','{\"sn\":\"ZYSL20032920\",\"userid\":\"9\"}','Success Delete User bela from ZYSL20032920','Successfully'),
(473,'2025-12-10 10:29:31','{\"sn\":\"ZYSL20032920\",\"userid\":\"10\"}','Success Delete User Tatang from ZYSL20032920','Successfully'),
(474,'2025-12-10 10:29:31','{\"sn\":\"ZYSL20032920\",\"userid\":\"11\"}','Success Delete User Tatang from ZYSL20032920','Successfully'),
(475,'2025-12-10 10:29:31','{\"sn\":\"ZYSL20032920\",\"userid\":\"12\"}','Success Delete User untung from ZYSL20032920','Successfully'),
(476,'2025-12-10 10:29:31','{\"sn\":\"ZYSL20032920\",\"userid\":\"13\"}','Success Delete User ucup from ZYSL20032920','Successfully'),
(477,'2025-12-10 10:30:11','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(478,'2025-12-10 10:30:11','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(479,'2025-12-10 10:30:24','{\"user_id\":1,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(480,'2025-12-10 10:35:40','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(481,'2025-12-10 10:35:40','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(482,'2025-12-10 10:35:44','{\"userId\":2,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Andre → ZYSL20032920 (WZ: 0)','Successfully'),
(483,'2025-12-10 10:35:44','{\"user_id\":\"2\",\"sn\":\"ZYSL20032920\",\"username\":\"Andre\",\"card_number\":\"1519624183\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Andre','Successfully'),
(484,'2025-12-10 10:35:50','{\"userId\":4,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Suti → ZYSL20032920 (WZ: 0)','Successfully'),
(485,'2025-12-10 10:35:50','{\"user_id\":\"4\",\"sn\":\"ZYSL20032920\",\"username\":\"Suti\",\"card_number\":\"7036483\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Suti','Successfully'),
(486,'2025-12-10 10:35:54','{\"userId\":7,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Yono → ZYSL20032920 (WZ: 0)','Successfully'),
(487,'2025-12-10 10:35:54','{\"user_id\":\"7\",\"sn\":\"ZYSL20032920\",\"username\":\"Yono\",\"card_number\":\"6116459\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Yono','Successfully'),
(488,'2025-12-10 10:35:59','{\"userId\":8,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User YUZA → ZYSL20032920 (WZ: 0)','Successfully'),
(489,'2025-12-10 10:35:59','{\"user_id\":\"8\",\"sn\":\"ZYSL20032920\",\"username\":\"YUZA\",\"card_number\":\"8711447\",\"ip_address\":\"192.168.100.128\"}','Success Insert User YUZA','Successfully'),
(490,'2025-12-10 10:36:04','{\"userId\":9,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User bela → ZYSL20032920 (WZ: 0)','Successfully'),
(491,'2025-12-10 10:36:04','{\"user_id\":\"9\",\"sn\":\"ZYSL20032920\",\"username\":\"bela\",\"card_number\":\"11223344\",\"ip_address\":\"192.168.100.128\"}','Success Insert User bela','Successfully'),
(492,'2025-12-10 10:36:09','{\"userId\":10,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Tatang → ZYSL20032920 (WZ: 0)','Successfully'),
(493,'2025-12-10 10:36:09','{\"user_id\":\"10\",\"sn\":\"ZYSL20032920\",\"username\":\"Tatang\",\"card_number\":\"12244131\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Tatang','Successfully'),
(494,'2025-12-10 10:36:13','{\"userId\":11,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Tatang → ZYSL20032920 (WZ: 0)','Successfully'),
(495,'2025-12-10 10:36:13','{\"user_id\":\"11\",\"sn\":\"ZYSL20032920\",\"username\":\"Tatang\",\"card_number\":\"1397912\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Tatang','Successfully'),
(496,'2025-12-10 10:36:18','{\"userId\":12,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User untung → ZYSL20032920 (WZ: 0)','Successfully'),
(497,'2025-12-10 10:36:18','{\"user_id\":\"12\",\"sn\":\"ZYSL20032920\",\"username\":\"untung\",\"card_number\":\"12345675\",\"ip_address\":\"192.168.100.128\"}','Success Insert User untung','Successfully'),
(498,'2025-12-10 10:36:23','{\"userId\":13,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ucup → ZYSL20032920 (WZ: 0)','Successfully'),
(499,'2025-12-10 10:36:23','{\"user_id\":\"13\",\"sn\":\"ZYSL20032920\",\"username\":\"ucup\",\"card_number\":\"6679573\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ucup','Successfully'),
(500,'2025-12-10 10:54:03','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(501,'2025-12-10 10:54:03','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(502,'2025-12-10 10:54:10','{\"userId\":2,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Andre → ZYSL20032920 (WZ: 0)','Successfully'),
(503,'2025-12-10 10:54:10','{\"user_id\":\"2\",\"sn\":\"ZYSL20032920\",\"username\":\"Andre\",\"card_number\":\"1519624183\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Andre','Successfully'),
(504,'2025-12-10 10:54:15','{\"userId\":4,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Suti → ZYSL20032920 (WZ: 0)','Successfully'),
(505,'2025-12-10 10:54:15','{\"user_id\":\"4\",\"sn\":\"ZYSL20032920\",\"username\":\"Suti\",\"card_number\":\"7036483\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Suti','Successfully'),
(506,'2025-12-10 10:54:21','{\"userId\":7,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Yono → ZYSL20032920 (WZ: 0)','Successfully'),
(507,'2025-12-10 10:54:21','{\"user_id\":\"7\",\"sn\":\"ZYSL20032920\",\"username\":\"Yono\",\"card_number\":\"6116459\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Yono','Successfully'),
(508,'2025-12-10 10:54:29','{\"userId\":8,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User YUZA → ZYSL20032920 (WZ: 0)','Successfully'),
(509,'2025-12-10 10:54:29','{\"user_id\":\"8\",\"sn\":\"ZYSL20032920\",\"username\":\"YUZA\",\"card_number\":\"8711447\",\"ip_address\":\"192.168.100.128\"}','Success Insert User YUZA','Successfully'),
(510,'2025-12-10 10:54:35','{\"userId\":9,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User bela → ZYSL20032920 (WZ: 0)','Successfully'),
(511,'2025-12-10 10:54:35','{\"user_id\":\"9\",\"sn\":\"ZYSL20032920\",\"username\":\"bela\",\"card_number\":\"11223344\",\"ip_address\":\"192.168.100.128\"}','Success Insert User bela','Successfully'),
(512,'2025-12-10 10:54:41','{\"userId\":10,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Tatang → ZYSL20032920 (WZ: 0)','Successfully'),
(513,'2025-12-10 10:54:41','{\"user_id\":\"10\",\"sn\":\"ZYSL20032920\",\"username\":\"Tatang\",\"card_number\":\"12244131\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Tatang','Successfully'),
(514,'2025-12-10 10:54:46','{\"userId\":11,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Tatang → ZYSL20032920 (WZ: 0)','Successfully'),
(515,'2025-12-10 10:54:46','{\"user_id\":\"11\",\"sn\":\"ZYSL20032920\",\"username\":\"Tatang\",\"card_number\":\"1397912\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Tatang','Successfully'),
(516,'2025-12-10 10:54:51','{\"userId\":12,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User untung → ZYSL20032920 (WZ: 0)','Successfully'),
(517,'2025-12-10 10:54:51','{\"user_id\":\"12\",\"sn\":\"ZYSL20032920\",\"username\":\"untung\",\"card_number\":\"12345675\",\"ip_address\":\"192.168.100.128\"}','Success Insert User untung','Successfully'),
(518,'2025-12-10 10:54:57','{\"userId\":13,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ucup → ZYSL20032920 (WZ: 0)','Successfully'),
(519,'2025-12-10 10:54:57','{\"user_id\":\"13\",\"sn\":\"ZYSL20032920\",\"username\":\"ucup\",\"card_number\":\"6679573\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ucup','Successfully'),
(520,'2025-12-10 10:55:07','{\"userId\":14,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ujang karamou → ZYSL20032920 (WZ: 0)','Successfully'),
(521,'2025-12-10 10:55:07','{\"user_id\":\"14\",\"sn\":\"ZYSL20032920\",\"username\":\"ujang karamou\",\"card_number\":\"6764297\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ujang karamou','Successfully'),
(522,'2025-12-10 10:55:17','{\"userId\":15,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Tatang → ZYSL20032920 (WZ: 0)','Successfully'),
(523,'2025-12-10 10:55:17','{\"user_id\":\"15\",\"sn\":\"ZYSL20032920\",\"username\":\"Tatang\",\"card_number\":\"12345673\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Tatang','Successfully'),
(524,'2025-12-10 10:55:27','{\"userId\":16,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User jamet → ZYSL20032920 (WZ: 0)','Successfully'),
(525,'2025-12-10 10:55:27','{\"user_id\":\"16\",\"sn\":\"ZYSL20032920\",\"username\":\"jamet\",\"card_number\":\"3768243\",\"ip_address\":\"192.168.100.128\"}','Success Insert User jamet','Successfully'),
(526,'2025-12-10 10:55:39','{\"userId\":17,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User kda → ZYSL20032920 (WZ: 0)','Successfully'),
(527,'2025-12-10 10:55:39','{\"user_id\":\"17\",\"sn\":\"ZYSL20032920\",\"username\":\"kda\",\"card_number\":\"1223342\",\"ip_address\":\"192.168.100.128\"}','Success Insert User kda','Successfully'),
(528,'2025-12-10 10:56:30','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(529,'2025-12-10 10:56:30','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(530,'2025-12-10 11:00:30','{\"sn\":\"ZYSL20032920\",\"userid\":\"1\"}','Success Delete User ayam from ZYSL20032920','Successfully'),
(531,'2025-12-10 11:03:36','{\"sn\":\"ZYSL20032920\",\"userid\":\"1\"}','Success Delete User ayam from ZYSL20032920','Successfully'),
(532,'2025-12-10 11:05:57','{\"user_id\":1,\"sn\":\"ZYSL20032920\",\"ip\":\"192.168.100.128\"}','Failed: Machine ZYSL20032920 (192.168.100.128) OFFLINE','Failed'),
(533,'2025-12-10 11:07:34','{\"user_id\":1,\"sn\":\"ZYSL20032920\",\"ip\":\"192.168.100.128\"}','Failed: Machine ZYSL20032920 (192.168.100.128) OFFLINE','Failed'),
(534,'2025-12-10 11:08:40','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(535,'2025-12-10 11:08:40','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(536,'2025-12-10 11:14:00','{\"sn\":\"ZYSL20032920\",\"userid\":\"1\"}','Success Delete User ayam from ZYSL20032920','Successfully'),
(537,'2025-12-10 11:14:15','{\"sn\":\"ZYSL20032920\",\"userid\":1,\"username\":\"ayam\",\"type\":0,\"admin\":\"0\",\"cardnumber\":1013460,\"fdata\":\"0\"}','Failed Insert User ayam: Register face failed','Failed'),
(538,'2025-12-10 11:14:32','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(539,'2025-12-10 11:14:32','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(540,'2025-12-10 11:17:27','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(541,'2025-12-10 11:17:27','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(542,'2025-12-10 11:18:46','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(543,'2025-12-10 11:18:46','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(544,'2025-12-10 11:20:30','{\"sn\":\"ZYSL20032920\",\"userid\":\"1\"}','Success Delete User ayam from ZYSL20032920','Successfully'),
(545,'2025-12-10 11:20:58','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(546,'2025-12-10 11:20:58','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(547,'2025-12-10 11:21:27','{\"sn\":\"ZYSL20032920\",\"userid\":\"1\"}','Success Delete User ayam from ZYSL20032920','Successfully'),
(548,'2025-12-10 11:21:52','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(549,'2025-12-10 11:21:52','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(550,'2025-12-10 11:30:10','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(551,'2025-12-10 11:30:10','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(552,'2025-12-10 11:32:16','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(553,'2025-12-10 11:32:16','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(554,'2025-12-10 11:35:07','{\"sn\":\"ZYSL20032920\",\"userid\":\"1\"}','Success Delete User ayam from ZYSL20032920','Successfully'),
(555,'2025-12-10 11:35:21','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(556,'2025-12-10 11:35:21','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(557,'2025-12-10 11:35:43','{\"sn\":\"ZYSL20032920\",\"userid\":\"1\"}','Failed Delete User ayam from ZYSL20032920','Failed'),
(558,'2025-12-10 11:35:49','{\"sn\":\"ZYSL20032920\",\"userid\":\"1\"}','Success Delete User ayam from ZYSL20032920','Successfully'),
(559,'2025-12-10 11:36:09','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(560,'2025-12-10 11:36:09','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(561,'2025-12-10 11:46:09','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(562,'2025-12-10 11:46:09','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(563,'2025-12-10 11:46:43','{\"sn\":\"ZYSL20032920\",\"userid\":\"1\"}','Failed Delete User ayam from ZYSL20032920','Failed'),
(564,'2025-12-10 11:46:52','{\"sn\":\"ZYSL20032920\",\"userid\":\"1\"}','Success Delete User ayam from ZYSL20032920','Successfully'),
(565,'2025-12-10 11:47:25','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(566,'2025-12-10 11:47:25','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(567,'2025-12-10 11:48:17','{\"userId\":2,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Andre → ZYSL20032920 (WZ: 0)','Successfully'),
(568,'2025-12-10 11:48:17','{\"user_id\":\"2\",\"sn\":\"ZYSL20032920\",\"username\":\"Andre\",\"card_number\":\"1519624183\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Andre','Successfully'),
(569,'2025-12-10 13:35:27','{\"user_id\":1,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(570,'2025-12-10 13:35:32','{\"user_id\":1,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(571,'2025-12-10 13:35:38','{\"user_id\":1,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(572,'2025-12-10 13:36:31','{\"user_id\":1,\"sn\":\"SOYAL antai 3\",\"ip\":\"192.168.100.127\"}','Failed: Machine SOYAL antai 3 (192.168.100.127) OFFLINE','Failed'),
(573,'2025-12-10 13:46:59','{\"user_id\":\"1\",\"card_number\":\"00015:30420\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\",\"timezone\":\"0\",\"begin_date\":\"11-24-2025\",\"expire_date\":\"12-31-2025\",\"begin_time\":\"00:00\",\"expire_time\":\"23:59\",\"pin\":\"1\",\"lift1\":\"0\",\"lift2\":\"0\",\"lift3\":\"0\",\"lift4\":\"0\",\"user_name\":\"ayam\",\"type\":\"1\"}','Success Insert User ayam','Successfully'),
(574,'2025-12-10 13:47:08','{\"user_id\":\"1\",\"card_number\":\"00000:00000\",\"card_numberConvert\":\"00015:30420\",\"ip_address\":\"192.168.100.127\",\"node_id\":\"3\"}','Success Delete User ayam from Soyal SOYAL antai 3','Successfully'),
(575,'2025-12-11 10:16:58','{\"userId\":1,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ayam → ZYSL20032920 (WZ: 0)','Successfully'),
(576,'2025-12-11 10:16:58','{\"user_id\":\"1\",\"sn\":\"ZYSL20032920\",\"username\":\"ayam\",\"card_number\":\"1013460\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ayam','Successfully'),
(577,'2025-12-11 10:17:47','{\"user_id\":\"2\",\"sn\":\"ZYSL20032920\",\"username\":\"Andre\",\"card_number\":\"1519624183\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Andre','Successfully'),
(578,'2025-12-11 10:17:47','{\"userId\":2,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Andre → ZYSL20032920 (WZ: 0)','Successfully'),
(579,'2025-12-11 10:18:29','{\"user_id\":4,\"sn\":\"ZYSL20032920\",\"ip\":\"192.168.100.128\"}','Failed: Machine ZYSL20032920 (192.168.100.128) OFFLINE','Failed'),
(580,'2025-12-11 10:18:36','{\"userId\":7,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Yono → ZYSL20032920 (WZ: 0)','Successfully'),
(581,'2025-12-11 10:18:36','{\"user_id\":\"7\",\"sn\":\"ZYSL20032920\",\"username\":\"Yono\",\"card_number\":\"6116459\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Yono','Successfully'),
(582,'2025-12-11 10:18:44','{\"userId\":8,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User YUZA → ZYSL20032920 (WZ: 0)','Successfully'),
(583,'2025-12-11 10:18:44','{\"user_id\":\"8\",\"sn\":\"ZYSL20032920\",\"username\":\"YUZA\",\"card_number\":\"8711447\",\"ip_address\":\"192.168.100.128\"}','Success Insert User YUZA','Successfully'),
(584,'2025-12-11 10:18:54','{\"userId\":9,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User bela → ZYSL20032920 (WZ: 0)','Successfully'),
(585,'2025-12-11 10:18:54','{\"user_id\":\"9\",\"sn\":\"ZYSL20032920\",\"username\":\"bela\",\"card_number\":\"11223344\",\"ip_address\":\"192.168.100.128\"}','Success Insert User bela','Successfully'),
(586,'2025-12-11 10:19:03','{\"userId\":10,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Tatang → ZYSL20032920 (WZ: 0)','Successfully'),
(587,'2025-12-11 10:19:03','{\"user_id\":\"10\",\"sn\":\"ZYSL20032920\",\"username\":\"Tatang\",\"card_number\":\"12244131\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Tatang','Successfully'),
(588,'2025-12-11 10:19:18','{\"userId\":11,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User Tatang → ZYSL20032920 (WZ: 0)','Successfully'),
(589,'2025-12-11 10:19:18','{\"user_id\":\"11\",\"sn\":\"ZYSL20032920\",\"username\":\"Tatang\",\"card_number\":\"1397912\",\"ip_address\":\"192.168.100.128\"}','Success Insert User Tatang','Successfully'),
(590,'2025-12-11 10:19:27','{\"userId\":12,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User untung → ZYSL20032920 (WZ: 0)','Successfully'),
(591,'2025-12-11 10:19:27','{\"user_id\":\"12\",\"sn\":\"ZYSL20032920\",\"username\":\"untung\",\"card_number\":\"12345675\",\"ip_address\":\"192.168.100.128\"}','Success Insert User untung','Successfully'),
(592,'2025-12-11 10:19:37','{\"userId\":13,\"deviceId\":\"1\",\"weekzone\":0}','Weekzone synced: User ucup → ZYSL20032920 (WZ: 0)','Successfully'),
(593,'2025-12-11 10:19:37','{\"user_id\":\"13\",\"sn\":\"ZYSL20032920\",\"username\":\"ucup\",\"card_number\":\"6679573\",\"ip_address\":\"192.168.100.128\"}','Success Insert User ucup','Successfully');

/*Table structure for table `failed_jobs` */

DROP TABLE IF EXISTS `failed_jobs`;

CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `failed_jobs` */

/*Table structure for table `holidaycal` */

DROP TABLE IF EXISTS `holidaycal`;

CREATE TABLE `holidaycal` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `StartDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ID` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `holidaycal` */

insert  into `holidaycal`(`ID`,`Name`,`StartDate`,`EndDate`) values 
(21,'Libur','2025-06-09','2025-06-13');

/*Table structure for table `leaveprocess` */

DROP TABLE IF EXISTS `leaveprocess`;

CREATE TABLE `leaveprocess` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `leaveid` int(11) DEFAULT '0' COMMENT 'leavetypeid',
  `EmplID` int(11) DEFAULT '0' COMMENT 'userprofile id',
  `FromDate` datetime DEFAULT NULL,
  `ToDate` datetime DEFAULT NULL,
  `Notes` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'catetan',
  `approver_id` int(11) DEFAULT '0',
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `STATUS` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=Pending, 1=Approved, 2=Rejected, 3=Cancelled',
  `priv` tinyint(4) DEFAULT '1' COMMENT '0=user, 1=admin',
  PRIMARY KEY (`Id`),
  KEY `EmplID` (`EmplID`),
  KEY `Leave` (`leaveid`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `leaveprocess` */

insert  into `leaveprocess`(`Id`,`leaveid`,`EmplID`,`FromDate`,`ToDate`,`Notes`,`approver_id`,`approved_at`,`created_at`,`updated_at`,`STATUS`,`priv`) values 
(21,2,1,'2025-06-23 00:00:00','2025-06-23 00:00:00','Cuti',4,NULL,NULL,'2025-09-11 13:44:18',3,0),
(22,1,4,'2025-07-10 00:00:00','2025-07-10 00:00:00','test1',4,'2025-09-11 13:44:00',NULL,'2025-09-11 13:44:00',1,0),
(23,2,1,'2025-09-14 00:00:00','2025-09-16 00:00:00','test',4,NULL,'2025-09-09 00:00:00','2025-09-11 13:44:14',3,0),
(24,2,4,'2025-09-14 00:00:00','2025-09-19 00:00:00','test ya',4,'2025-09-11 13:44:04','2025-09-09 00:00:00','2025-09-11 13:44:04',1,0),
(25,1,4,'2025-09-11 00:00:00','2025-09-11 00:00:00','test',4,NULL,'2025-09-10 00:00:00','2025-09-11 13:44:09',2,0),
(26,1,8,'2025-09-11 00:00:00','2025-09-11 00:00:00',NULL,4,'2025-09-11 00:00:00','2025-09-11 00:00:00','2025-09-11 00:00:00',1,0),
(28,2,7,'2025-09-11 00:00:00','2025-09-11 00:00:00',NULL,4,'2025-09-11 00:00:00','2025-09-11 00:00:00','2025-09-11 00:00:00',1,0),
(31,1,1,'2025-09-15 00:00:00','2025-09-15 00:00:00','dasd',4,'2025-09-15 16:38:09','2025-09-15 00:00:00','2025-09-15 16:38:09',1,0);

/*Table structure for table `leavetype` */

DROP TABLE IF EXISTS `leavetype`;

CREATE TABLE `leavetype` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Type` int(4) DEFAULT NULL COMMENT 'default 0, tidak perlu di munculin di ui',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `leavetype` */

insert  into `leavetype`(`Id`,`Name`,`Type`) values 
(1,'Izin',0),
(2,'Cuti',0),
(3,'Cuti Melahirkan',0);

/*Table structure for table `migrations` */

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `migrations` */

insert  into `migrations`(`id`,`migration`,`batch`) values 
(1,'2014_10_12_000000_create_users_table',1),
(2,'2014_10_12_100000_create_password_reset_tokens_table',1),
(3,'2019_08_19_000000_create_failed_jobs_table',1),
(4,'2019_12_14_000001_create_personal_access_tokens_table',1);

/*Table structure for table `password_reset_tokens` */

DROP TABLE IF EXISTS `password_reset_tokens`;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `password_reset_tokens` */

/*Table structure for table `personal_access_tokens` */

DROP TABLE IF EXISTS `personal_access_tokens`;

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `personal_access_tokens` */

/*Table structure for table `pt_availability` */

DROP TABLE IF EXISTS `pt_availability`;

CREATE TABLE `pt_availability` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PT_ID` int(11) NOT NULL,
  `DOW` tinyint(1) NOT NULL COMMENT '1=Senin ... 7=Minggu',
  `START_TIME` time NOT NULL,
  `END_TIME` time NOT NULL,
  `IS_ACTIVE` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0=Nonaktif, 1=Aktif',
  PRIMARY KEY (`ID`),
  KEY `FK_PT_AVAILABILITY_PT` (`PT_ID`),
  CONSTRAINT `FK_PT_AVAILABILITY_PT` FOREIGN KEY (`PT_ID`) REFERENCES `usersprofile` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `pt_availability` */

insert  into `pt_availability`(`ID`,`PT_ID`,`DOW`,`START_TIME`,`END_TIME`,`IS_ACTIVE`) values 
(1,4,1,'07:00:00','11:00:00',0),
(2,4,2,'07:00:00','14:00:00',1);

/*Table structure for table `pt_schedule` */

DROP TABLE IF EXISTS `pt_schedule`;

CREATE TABLE `pt_schedule` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PT_ID` int(11) NOT NULL COMMENT 'FK ke usersprofile (Personal Trainer)',
  `MEMBER_ID` int(11) NOT NULL COMMENT 'FK ke usersprofile (Member)',
  `START_TIME` datetime NOT NULL,
  `END_TIME` datetime NOT NULL,
  `BOOKED_AT` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Tanggal & waktu booking dibuat',
  `STATUS` tinyint(1) DEFAULT '0' COMMENT '0 = booked, 1 = selesai, 2 = batal',
  `NOTE` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `CREATED_AT` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  KEY `FK_PT_ID` (`PT_ID`),
  KEY `FK_MEMBER_ID` (`MEMBER_ID`),
  CONSTRAINT `FK_PT_SCHEDULE_MEMBER` FOREIGN KEY (`MEMBER_ID`) REFERENCES `usersprofile` (`id`),
  CONSTRAINT `FK_PT_SCHEDULE_PT` FOREIGN KEY (`PT_ID`) REFERENCES `usersprofile` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `pt_schedule` */

insert  into `pt_schedule`(`ID`,`PT_ID`,`MEMBER_ID`,`START_TIME`,`END_TIME`,`BOOKED_AT`,`STATUS`,`NOTE`,`CREATED_AT`) values 
(5,4,2,'2025-10-22 07:00:00','2025-10-22 11:00:00','2025-10-20 14:48:46',0,NULL,'2025-10-20 14:48:46');

/*Table structure for table `shift` */

DROP TABLE IF EXISTS `shift`;

CREATE TABLE `shift` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ShiftNo` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ShiftName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Begin_Time` time DEFAULT NULL COMMENT 'jam Masuk',
  `Break_Time` time DEFAULT NULL COMMENT 'jam Break',
  `Resume_Time` time DEFAULT NULL COMMENT 'Jam resume',
  `Out_time` time DEFAULT NULL COMMENT 'jam pulang',
  `Start_In` time DEFAULT NULL COMMENT 'Batas awal masuk',
  `Start_Break` time DEFAULT NULL COMMENT 'Batas awal Break',
  `Start_Resume` time DEFAULT NULL COMMENT 'Batas awal Resume',
  `Start_Out` time DEFAULT NULL COMMENT 'Batas awal Pulang',
  `Range_In` time DEFAULT NULL COMMENT 'Batas akhir masuk',
  `Range_Break` time DEFAULT NULL COMMENT 'Batas akhir Break',
  `Range_Resume` time DEFAULT NULL COMMENT 'Batas akhir Resume',
  `Range_Out` time DEFAULT NULL COMMENT 'Batas akhir Pulang',
  `Tipe` int(11) DEFAULT '0' COMMENT '(1 hari kerja, 2 off)',
  `DDay` int(11) NOT NULL COMMENT '(0 normal shift, jika 1 untuk shift yang lewat hari)',
  PRIMARY KEY (`id`),
  KEY `Code` (`id`),
  KEY `Number` (`ShiftNo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `shift` */

insert  into `shift`(`id`,`ShiftNo`,`ShiftName`,`Begin_Time`,`Break_Time`,`Resume_Time`,`Out_time`,`Start_In`,`Start_Break`,`Start_Resume`,`Start_Out`,`Range_In`,`Range_Break`,`Range_Resume`,`Range_Out`,`Tipe`,`DDay`) values 
(1,'1','Shift Pagi','08:00:00','12:00:00','13:00:00','16:30:00','05:00:00','11:30:00','12:30:00','14:00:00','11:00:00','00:29:00','13:55:00','23:00:00',1,1),
(2,'2','Shift Sore','11:00:00','18:00:00','19:00:00','22:00:00','08:00:00','17:30:00','18:30:00','17:00:00','15:00:00','18:29:00','19:30:00','23:00:00',1,1),
(5,'3','Off Shift','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00','00:00:00',2,1);

/*Table structure for table `shiftpattern` */

DROP TABLE IF EXISTS `shiftpattern`;

CREATE TABLE `shiftpattern` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `PatternName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PatternType` int(11) DEFAULT '0' COMMENT '1. shift, 2 flexible (link ke table flexible), 3 hour, 4 no shift',
  `pola1` int(11) NOT NULL COMMENT 'pola senin',
  `pola2` int(11) NOT NULL COMMENT 'pola selasa',
  `pola3` int(11) NOT NULL COMMENT 'pola Rabu',
  `pola4` int(11) NOT NULL COMMENT 'pola Kamis',
  `pola5` int(11) NOT NULL COMMENT 'pola Jumat',
  `pola6` int(11) NOT NULL COMMENT 'pola sabtu',
  `pola7` int(11) NOT NULL COMMENT 'pola minggu',
  PRIMARY KEY (`Id`),
  KEY `Id` (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `shiftpattern` */

insert  into `shiftpattern`(`Id`,`PatternName`,`PatternType`,`pola1`,`pola2`,`pola3`,`pola4`,`pola5`,`pola6`,`pola7`) values 
(1,'Shift day',1,1,1,1,1,1,5,5),
(2,'Shift Night',2,2,2,2,2,2,5,5),
(3,'security',3,1,1,1,3,3,2,2);

/*Table structure for table `tbl_deviceevent` */

DROP TABLE IF EXISTS `tbl_deviceevent`;

CREATE TABLE `tbl_deviceevent` (
  `TM_EVENT` datetime NOT NULL,
  `DEVICESN` varchar(15) NOT NULL DEFAULT '',
  `MODELNAME` varchar(30) DEFAULT '',
  `useduser` int(11) DEFAULT NULL,
  `usedfp` int(11) DEFAULT NULL,
  `usedcard` int(11) DEFAULT NULL,
  `usedpwd` int(11) DEFAULT NULL,
  `usedlog` int(11) DEFAULT NULL,
  `usednewlog` int(11) DEFAULT NULL,
  `firmware` varchar(30) DEFAULT '',
  `devicetime` datetime DEFAULT NULL,
  PRIMARY KEY (`TM_EVENT`,`DEVICESN`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `tbl_deviceevent` */

insert  into `tbl_deviceevent`(`TM_EVENT`,`DEVICESN`,`MODELNAME`,`useduser`,`usedfp`,`usedcard`,`usedpwd`,`usedlog`,`usednewlog`,`firmware`,`devicetime`) values 
('2025-09-22 11:40:29','AYSK17105912','TAS-F05QR',0,0,0,0,11,11,'ai810_f06v_v5.02','2025-09-22 11:34:48'),
('2025-09-22 11:46:28','AYSK17105912','TAS-F05QR',0,0,0,0,11,11,'ai810_f06v_v5.02','2025-09-22 11:35:08'),
('2025-09-22 11:46:43','AYSK17105912','TAS-F05QR',0,0,0,0,11,11,'ai810_f06v_v5.02','2025-09-22 11:35:28'),
('2025-09-22 11:46:44','AYSK17105912','TAS-F05QR',0,0,0,0,11,11,'ai810_f06v_v5.02','2025-09-22 11:46:10'),
('2025-09-22 11:46:57','AYSK17105912','TAS-F05QR',0,0,0,0,11,11,'ai810_f06v_v5.02','2025-09-22 11:35:48'),
('2025-09-22 11:47:03','AYSK17105912','TAS-F05QR',0,0,0,0,11,11,'ai810_f06v_v5.02','2025-09-22 11:46:30'),
('2025-09-22 11:47:30','AYSK17105912','TAS-F05QR',0,0,0,0,11,11,'ai810_f06v_v5.02','2025-09-22 11:36:08'),
('2025-09-22 11:47:38','AYSK17105912','TAS-F05QR',0,0,0,0,11,11,'ai810_f06v_v5.02','2025-09-22 11:36:28'),
('2025-09-22 11:47:43','AYSK17105912','TAS-F05QR',0,0,0,0,11,11,'ai810_f06v_v5.02','2025-09-22 11:36:48'),
('2025-09-22 11:47:45','AYSK17105912','TAS-F05QR',0,0,0,0,11,11,'ai810_f06v_v5.02','2025-09-22 11:47:20'),
('2025-09-22 11:47:47','AYSK17105912','TAS-F05QR',0,0,0,0,11,11,'ai810_f06v_v5.02','2025-09-22 11:37:08'),
('2025-09-22 11:47:49','AYSK17105912','TAS-F05QR',0,0,0,0,11,11,'ai810_f06v_v5.02','2025-09-22 11:47:40'),
('2025-09-22 13:26:47','AYSK17105912','TAS-F05QR',0,0,0,0,11,11,'ai810_f06v_v5.02','2025-09-22 13:26:27'),
('2025-09-22 13:32:02','AYSK17105912','TAS-F05QR',0,0,0,0,11,11,'ai810_f06v_v5.02','2025-09-22 13:31:56'),
('2025-09-22 13:35:41','AYSK17105912','TAS-F05QR',0,0,0,0,11,11,'ai810_f06v_v5.02','2025-09-22 13:35:35'),
('2025-09-22 15:42:44','ZYSL20032920','tfs30',11,1,11,0,33,33,'TFS70 V4.4','2025-09-22 16:42:15'),
('2025-09-22 15:42:57','ZYSL20032920','tfs30',11,1,11,0,33,33,'TFS70 V4.4','2025-09-22 16:42:36'),
('2025-09-22 15:43:00','ZYSL20032920','tfs30',11,1,11,0,33,33,'TFS70 V4.4','2025-09-22 16:42:56'),
('2025-09-22 15:43:02','ZYSL20032920','tfs30',11,1,11,0,33,33,'TFS70 V4.4','2025-09-22 16:43:16'),
('2025-09-23 09:24:56','ZYSL20032920','tfs30',11,1,11,0,33,33,'TFS70 V4.4','2025-09-23 10:25:45'),
('2025-09-23 09:33:29','ZYSL20032920','tfs30',11,1,11,0,33,33,'TFS70 V4.4','2025-09-23 10:34:20'),
('2025-09-23 09:37:07','ZYSL20032920','tfs30',11,1,11,0,33,33,'TFS70 V4.4','2025-09-23 10:37:22'),
('2025-09-25 11:04:17','ZYSL20032920','tfs30',11,1,11,0,33,33,'TFS70 V4.4','2025-09-25 12:05:22'),
('2025-09-25 11:07:19','ZYSL20032920','tfs30',11,1,11,0,33,33,'TFS70 V4.4','2025-09-25 12:08:24'),
('2025-09-25 11:32:12','ZYSL20032920','tfs30',11,1,11,0,33,33,'TFS70 V4.4','2025-09-25 12:33:17'),
('2025-10-06 09:38:51','ZYSL20032920','tfs30',2,4,2,0,61,61,'TFS70 V4.4','2025-10-06 09:36:27'),
('2025-10-06 09:39:06','ZYSL20032920','tfs30',2,4,2,0,61,61,'TFS70 V4.4','2025-10-06 09:36:47'),
('2025-10-06 09:39:13','ZYSL20032920','tfs30',2,4,2,0,61,61,'TFS70 V4.4','2025-10-06 09:37:07'),
('2025-10-06 09:41:22','ZYSL20032920','tfs30',2,4,2,0,62,62,'TFS70 V4.4','2025-10-06 09:40:14'),
('2025-10-06 09:45:04','ZYSL20032920','tfs30',2,4,2,0,67,67,'TFS70 V4.4','2025-10-06 09:43:59'),
('2025-10-07 11:27:38','AYSK17105912','TAS-F05QR',0,0,0,0,0,0,'ai810_f06v_v5.02','2025-10-07 11:27:21'),
('2025-10-07 11:34:55','AYSK17105912','TAS-F05QR',0,0,0,0,0,0,'ai810_f06v_v5.02','2025-10-07 11:34:38'),
('2025-10-07 11:35:51','AYSK17105912','TAS-F05QR',0,0,0,0,0,0,'ai810_f06v_v5.02','2025-10-07 11:35:25'),
('2025-10-07 11:36:25','AYSK17105912','TAS-F05QR',0,0,0,0,0,0,'ai810_f06v_v5.02','2025-10-07 11:36:03'),
('2025-10-07 11:41:16','AYSK17105912','TAS-F05QR',0,0,0,0,0,0,'ai810_f06v_v5.02','2025-10-07 11:40:53'),
('2025-10-07 11:44:57','AYSK17105912','TAS-F05QR',0,0,0,0,0,0,'ai810_f06v_v5.02','2025-10-07 11:44:30'),
('2025-10-07 11:54:54','AYSK17105912','TAS-F05QR',2,0,2,0,0,0,'ai810_f06v_v5.02','2025-10-07 11:57:35'),
('2025-10-07 11:58:33','AYSK17105912','TAS-F05QR',2,0,2,0,1,1,'ai810_f06v_v5.02','2025-10-07 12:01:05'),
('2025-10-07 11:59:23','AYSK17105912','TAS-F05QR',2,0,2,0,2,2,'ai810_f06v_v5.02','2025-10-07 12:01:43'),
('2025-10-07 11:59:56','AYSK17105912','TAS-F05QR',2,0,2,0,3,3,'ai810_f06v_v5.02','2025-10-07 12:02:30'),
('2025-10-07 12:00:41','AYSK17105912','TAS-F05QR',2,0,2,0,3,3,'ai810_f06v_v5.02','2025-10-07 12:03:12'),
('2025-10-07 12:00:55','AYSK17105912','TAS-F05QR',2,0,2,0,3,3,'ai810_f06v_v5.02','2025-10-07 12:03:36'),
('2025-10-07 14:59:20','AYSK17105912','TAS-F05QR',2,0,2,0,4,4,'ai810_f06v_v5.02','2025-10-07 15:01:55'),
('2025-10-09 13:55:25','AYSK17105912','TAS-F05QR',2,0,2,0,4,4,'ai810_f06v_v5.02','2025-10-09 13:58:15'),
('2025-10-09 13:58:30','AYSK17105912','TAS-F05QR',2,0,2,0,4,4,'ai810_f06v_v5.02','2025-10-09 14:01:14'),
('2025-10-09 14:00:22','AYSK17105912','TAS-F05QR',2,0,2,0,5,5,'ai810_f06v_v5.02','2025-10-09 14:03:06'),
('2025-10-09 14:00:50','AYSK17105912','TAS-F05QR',2,0,2,0,5,5,'ai810_f06v_v5.02','2025-10-09 14:03:41'),
('2025-10-09 14:23:39','AYSK17105912','TAS-F05QR',2,0,2,0,6,6,'ai810_f06v_v5.02','2025-10-09 14:26:31'),
('2025-10-09 14:25:28','AYSK17105912','TAS-F05QR',2,0,2,0,6,6,'ai810_f06v_v5.02','2025-10-09 14:30:43'),
('2025-10-09 14:43:01','AYSK17105912','TAS-F05QR',2,0,2,0,6,6,'ai810_f06v_v5.02','2025-10-09 14:48:15'),
('2025-10-09 14:45:26','AYSK17105912','TAS-F05QR',2,0,2,0,7,7,'ai810_f06v_v5.02','2025-10-09 14:50:41'),
('2025-10-14 14:27:46','AYSK17105912','TAS-F05QR',3,0,3,0,19,19,'ai810_f06v_v5.02','2025-10-14 14:33:01'),
('2025-10-14 14:28:00','AYSK17105912','TAS-F05QR',3,0,3,0,19,19,'ai810_f06v_v5.02','2025-10-14 14:33:15'),
('2025-10-14 14:32:10','AYSK17105912','TAS-F05QR',3,0,3,0,19,19,'ai810_f06v_v5.02','2025-10-14 14:33:05'),
('2025-10-21 09:31:40','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 09:31:25'),
('2025-10-21 09:32:12','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 09:31:48'),
('2025-10-21 09:32:23','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 09:32:10'),
('2025-10-21 10:17:17','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 10:17:02'),
('2025-10-21 10:21:30','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 10:21:17'),
('2025-10-21 10:27:08','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 10:26:56'),
('2025-10-21 10:29:59','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 10:29:47'),
('2025-10-21 10:30:44','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 10:30:33'),
('2025-10-21 10:34:08','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 10:33:11'),
('2025-10-21 10:34:12','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 10:33:57'),
('2025-10-21 10:37:00','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 10:36:39'),
('2025-10-21 10:38:06','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 10:37:49'),
('2025-10-21 10:40:17','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 10:39:55'),
('2025-10-21 10:48:11','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 10:47:57'),
('2025-10-21 10:49:10','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 10:48:59'),
('2025-10-21 10:53:32','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 10:53:20'),
('2025-10-21 10:59:37','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 10:59:24'),
('2025-10-21 11:00:35','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 11:00:24'),
('2025-10-21 11:41:41','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 11:41:29'),
('2025-10-21 11:46:32','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 11:46:19'),
('2025-10-21 11:47:03','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 11:46:53'),
('2025-10-21 11:49:43','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 11:49:32'),
('2025-10-21 11:51:49','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 11:51:38'),
('2025-10-21 11:53:37','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 11:53:25'),
('2025-10-21 11:56:14','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 11:56:04'),
('2025-10-21 11:56:59','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 11:56:49'),
('2025-10-21 11:57:03','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 11:56:54'),
('2025-10-21 11:59:07','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 11:58:52'),
('2025-10-21 12:01:36','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 12:01:24'),
('2025-10-21 12:03:47','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 12:03:37'),
('2025-10-21 12:05:54','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 12:05:43'),
('2025-10-21 12:06:42','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 12:06:33'),
('2025-10-21 12:08:05','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 12:07:53'),
('2025-10-21 12:08:57','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 12:08:47'),
('2025-10-21 12:09:31','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 12:09:21'),
('2025-10-21 12:09:33','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 12:09:24'),
('2025-10-21 13:36:02','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 13:35:51'),
('2025-10-21 13:38:02','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 13:37:51'),
('2025-10-21 13:46:16','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 13:46:03'),
('2025-10-21 13:47:54','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 13:47:44'),
('2025-10-21 13:49:18','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 13:49:07'),
('2025-10-21 13:50:55','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 13:50:44'),
('2025-10-21 14:02:52','AYSK17105912','TAS-F05QR',2,0,2,0,19,19,'ai810_f06v_v5.02','2025-10-21 14:02:41'),
('2025-10-21 14:37:58','AYSK17105912','TAS-F05QR',2,0,2,0,21,21,'ai810_f06v_v5.02','2025-10-21 14:37:46'),
('2025-10-21 14:44:22','AYSK17105912','TAS-F05QR',2,0,2,0,21,21,'ai810_f06v_v5.02','2025-10-21 14:44:11'),
('2025-10-21 14:45:13','AYSK17105912','TAS-F05QR',2,0,2,0,21,21,'ai810_f06v_v5.02','2025-10-21 14:45:03'),
('2025-10-21 14:47:11','AYSK17105912','TAS-F05QR',2,0,2,0,21,21,'ai810_f06v_v5.02','2025-10-21 14:47:00'),
('2025-10-21 14:51:35','AYSK17105912','TAS-F05QR',2,0,2,0,21,21,'ai810_f06v_v5.02','2025-10-21 14:51:17'),
('2025-10-21 14:57:16','AYSK17105912','TAS-F05QR',2,0,2,0,21,21,'ai810_f06v_v5.02','2025-10-21 14:57:05'),
('2025-10-21 15:45:39','AYSK17105912','TAS-F05QR',2,0,2,0,21,21,'ai810_f06v_v5.02','2025-10-21 15:45:28'),
('2025-10-22 09:30:15','ZYSL20032920','tfs30',2,2,2,0,109,109,'TFS70 V4.4','2025-10-22 14:26:58'),
('2025-10-22 09:53:23','ZYSL20032920','tfs30',2,2,2,0,109,109,'TFS70 V4.4','2025-10-22 14:50:06'),
('2025-10-22 09:56:09','ZYSL20032920','tfs30',2,3,2,0,110,110,'TFS70 V4.4','2025-10-22 14:52:53'),
('2025-10-22 09:56:15','ZYSL20032920','tfs30',2,3,2,0,111,111,'TFS70 V4.4','2025-10-22 14:52:59'),
('2025-10-22 10:24:05','ZYSL20032920','tfs30',2,3,2,0,113,113,'TFS70 V4.4','2025-10-22 10:23:37'),
('2025-10-24 14:08:25','ZYSL20032920','tfs30',3,3,3,0,126,126,'TFS70 V4.4','2025-10-24 14:07:58'),
('2025-10-24 14:10:20','ZYSL20032920','tfs30',3,3,3,0,126,126,'TFS70 V4.4','2025-10-24 14:09:54'),
('2025-10-27 10:00:09','ZYSL20032920','tfs30',4,3,4,0,128,128,'TFS70 V4.4','2025-10-27 09:59:32'),
('2025-10-27 10:01:33','ZYSL20032920','tfs30',4,3,4,0,128,128,'TFS70 V4.4','2025-10-27 10:01:00'),
('2025-10-27 10:37:12','ZYSL20032920','tfs30',4,3,4,0,128,128,'TFS70 V4.4','2025-10-27 10:36:38'),
('2025-10-27 10:41:16','ZYSL20032920','tfs30',4,3,4,0,128,128,'TFS70 V4.4','2025-10-27 10:40:42'),
('2025-10-27 10:42:35','ZYSL20032920','tfs30',4,3,4,0,128,128,'TFS70 V4.4','2025-10-27 10:42:01'),
('2025-10-27 10:56:14','ZYSL20032920','tfs30',4,3,4,0,128,128,'TFS70 V4.4','2025-10-27 10:55:41'),
('2025-10-27 11:00:07','ZYSL20032920','tfs30',4,3,4,0,128,128,'TFS70 V4.4','2025-10-27 10:59:31'),
('2025-10-27 11:03:41','ZYSL20032920','tfs30',4,3,4,0,128,128,'TFS70 V4.4','2025-10-27 11:03:07'),
('2025-10-27 11:06:38','ZYSL20032920','tfs30',4,3,4,0,128,128,'TFS70 V4.4','2025-10-27 11:06:05'),
('2025-10-27 11:13:51','ZYSL20032920','tfs30',4,3,4,0,128,128,'TFS70 V4.4','2025-10-27 11:13:18'),
('2025-10-27 11:17:50','ZYSL20032920','tfs30',4,3,4,0,128,128,'TFS70 V4.4','2025-10-27 11:17:17'),
('2025-10-27 11:18:22','ZYSL20032920','tfs30',4,3,4,0,128,128,'TFS70 V4.4','2025-10-27 11:17:48'),
('2025-10-27 11:47:25','ZYSL20032920','tfs30',4,3,4,0,128,128,'TFS70 V4.4','2025-10-27 11:46:52'),
('2025-10-27 11:55:29','ZYSL20032920','tfs30',4,3,4,0,128,128,'TFS70 V4.4','2025-10-27 11:54:56'),
('2025-10-27 11:57:09','ZYSL20032920','tfs30',4,3,4,0,128,128,'TFS70 V4.4','2025-10-27 11:56:36'),
('2025-10-27 12:02:16','ZYSL20032920','tfs30',4,3,4,0,128,128,'TFS70 V4.4','2025-10-27 12:01:42'),
('2025-10-27 13:20:13','ZYSL20032920','tfs30',4,3,4,0,128,128,'TFS70 V4.4','2025-10-27 13:19:37'),
('2025-10-27 13:34:01','ZYSL20032920','tfs30',4,3,4,0,128,128,'TFS70 V4.4','2025-10-27 13:33:27'),
('2025-10-27 13:38:57','ZYSL20032920','tfs30',4,3,4,0,128,128,'TFS70 V4.4','2025-10-27 13:38:24'),
('2025-10-29 14:36:50','ZYSJ28009690','TH900',1,1,0,0,1,1,'SS50 V3.6','2025-10-29 15:39:03'),
('2025-10-29 14:38:23','ZYSJ28009690','TH900',1,1,0,0,1,1,'SS50 V3.6','2025-10-29 15:40:36'),
('2025-11-07 09:50:05','ZYSL20032920','tfs30',0,0,0,0,9,9,'TFS70 V4.4','2025-11-07 09:49:40'),
('2025-11-07 10:04:55','ZYSL20032920','tfs30',1,0,1,0,9,9,'TFS70 V4.4','2025-11-07 10:04:28'),
('2025-11-07 12:44:07','ZYSL20032920','tfs30',6,1,6,0,14,14,'TFS70 V4.4','2025-11-07 12:43:41'),
('2025-11-07 13:12:06','AYSK17105912','TAS-F05QR',5,0,5,0,0,0,'ai810_f06v_v5.02','2025-11-07 13:11:55'),
('2025-11-07 13:12:07','AYSK17105912','TAS-F05QR',5,0,5,0,0,0,'ai810_f06v_v5.02','2025-11-07 13:11:57'),
('2025-11-07 13:12:08','AYSK17105912','TAS-F05QR',5,0,5,0,0,0,'ai810_f06v_v5.02','2025-11-07 13:11:57'),
('2025-11-07 13:52:20','AYSK17105912','TAS-F05QR',6,0,6,0,4,4,'ai810_f06v_v5.02','2025-11-07 13:52:09'),
('2025-11-07 14:04:06','AYSK17105912','TAS-F05QR',6,0,6,0,6,6,'ai810_f06v_v5.02','2025-11-07 14:03:55'),
('2025-11-07 14:57:04','AYSK17105912','TAS-F05QR',6,0,6,0,7,7,'ai810_f06v_v5.02','2025-11-07 14:56:54'),
('2025-11-07 14:57:24','AYSK17105912','TAS-F05QR',6,0,6,0,7,7,'ai810_f06v_v5.02','2025-11-07 14:57:14'),
('2025-11-07 14:57:45','AYSK17105912','TAS-F05QR',6,0,6,0,7,7,'ai810_f06v_v5.02','2025-11-07 14:57:34'),
('2025-11-07 14:58:05','AYSK17105912','TAS-F05QR',6,0,6,0,7,7,'ai810_f06v_v5.02','2025-11-07 14:57:54'),
('2025-11-07 14:58:25','AYSK17105912','TAS-F05QR',6,0,6,0,7,7,'ai810_f06v_v5.02','2025-11-07 14:58:14'),
('2025-11-07 14:58:45','AYSK17105912','TAS-F05QR',6,0,6,0,7,7,'ai810_f06v_v5.02','2025-11-07 14:58:34'),
('2025-11-07 14:59:05','AYSK17105912','TAS-F05QR',6,0,6,0,7,7,'ai810_f06v_v5.02','2025-11-07 14:58:54'),
('2025-11-07 14:59:25','AYSK17105912','TAS-F05QR',6,0,6,0,7,7,'ai810_f06v_v5.02','2025-11-07 14:59:14'),
('2025-11-10 16:15:33','AYSK17105912','TAS-F05QR',6,0,6,0,7,7,'ai810_f06v_v5.02','2025-11-10 16:15:18'),
('2025-11-11 08:48:13','AYSK17105912','TAS-F05QR',7,0,6,0,8,8,'ai810_f06v_v5.02','2025-11-11 08:47:48'),
('2025-11-11 09:05:28','AYSK17105912','TAS-F05QR',6,0,5,0,8,8,'ai810_f06v_v5.02','2025-11-11 09:05:16'),
('2025-11-11 09:08:02','AYSK17105912','TAS-F05QR',1,0,0,0,8,8,'ai810_f06v_v5.02','2025-11-11 09:07:51'),
('2025-11-11 09:08:22','AYSK17105912','TAS-F05QR',1,0,0,0,8,8,'ai810_f06v_v5.02','2025-11-11 09:08:11'),
('2025-11-11 09:09:54','AYSK17105912','TAS-F05QR',1,0,0,0,8,8,'ai810_f06v_v5.02','2025-11-11 09:08:31'),
('2025-11-11 09:10:29','AYSK17105912','TAS-F05QR',1,0,0,0,8,8,'ai810_f06v_v5.02','2025-11-11 09:10:18'),
('2025-11-11 09:14:44','AYSK17105912','TAS-F05QR',7,0,6,0,9,9,'ai810_f06v_v5.02','2025-11-11 09:14:33'),
('2025-11-11 10:13:46','AYSK17105912','TAS-F05QR',5,0,4,0,10,10,'ai810_f06v_v5.02','2025-11-11 10:13:35'),
('2025-11-11 10:35:39','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 10:35:27'),
('2025-11-11 10:40:57','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 10:40:44'),
('2025-11-11 10:41:15','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 10:41:04'),
('2025-11-11 10:42:21','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 10:42:10'),
('2025-11-11 10:43:21','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 10:43:10'),
('2025-11-11 10:47:40','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 10:47:29'),
('2025-11-11 10:49:40','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 10:49:29'),
('2025-11-11 10:55:01','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 10:54:49'),
('2025-11-11 10:56:21','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 10:56:11'),
('2025-11-11 10:58:07','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 10:57:56'),
('2025-11-11 11:05:19','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:05:07'),
('2025-11-11 11:06:11','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:06:00'),
('2025-11-11 11:07:13','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:07:02'),
('2025-11-11 11:07:33','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:07:22'),
('2025-11-11 11:07:53','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:07:42'),
('2025-11-11 11:08:13','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:08:02'),
('2025-11-11 11:08:33','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:08:22'),
('2025-11-11 11:08:53','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:08:42'),
('2025-11-11 11:09:13','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:09:02'),
('2025-11-11 11:09:33','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:09:22'),
('2025-11-11 11:10:04','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:09:53'),
('2025-11-11 11:12:38','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:12:27'),
('2025-11-11 11:12:58','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:12:47'),
('2025-11-11 11:13:18','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:13:07'),
('2025-11-11 11:13:39','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:13:28'),
('2025-11-11 11:14:49','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:14:38'),
('2025-11-11 11:17:20','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:17:09'),
('2025-11-11 11:17:40','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:17:29'),
('2025-11-11 11:18:00','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:17:49'),
('2025-11-11 11:18:20','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:18:09'),
('2025-11-11 11:18:40','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:18:29'),
('2025-11-11 11:19:00','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:18:49'),
('2025-11-11 11:19:20','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:19:09'),
('2025-11-11 11:20:13','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:20:02'),
('2025-11-11 11:20:33','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:20:22'),
('2025-11-11 11:20:53','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:20:42'),
('2025-11-11 11:21:13','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:21:02'),
('2025-11-11 11:21:33','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:21:22'),
('2025-11-11 11:22:12','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:21:42'),
('2025-11-11 11:22:13','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:22:02'),
('2025-11-11 11:22:33','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:22:22'),
('2025-11-11 11:22:53','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:22:42'),
('2025-11-11 11:23:13','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:23:02'),
('2025-11-11 11:23:40','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:23:30'),
('2025-11-11 11:24:01','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:23:50'),
('2025-11-11 11:52:13','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:52:02'),
('2025-11-11 11:55:25','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:55:14'),
('2025-11-11 11:55:57','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:55:46'),
('2025-11-11 11:56:53','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:56:42'),
('2025-11-11 11:58:25','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 11:58:14'),
('2025-11-11 12:01:05','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 12:00:54'),
('2025-11-11 12:59:12','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 12:59:00'),
('2025-11-11 12:59:31','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 12:59:20'),
('2025-11-11 12:59:51','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 12:59:40'),
('2025-11-11 13:00:11','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:00:00'),
('2025-11-11 13:00:31','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:00:20'),
('2025-11-11 13:00:51','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:00:40'),
('2025-11-11 13:01:11','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:01:00'),
('2025-11-11 13:01:31','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:01:20'),
('2025-11-11 13:01:51','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:01:40'),
('2025-11-11 13:02:11','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:02:00'),
('2025-11-11 13:02:36','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:02:26'),
('2025-11-11 13:02:57','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:02:46'),
('2025-11-11 13:04:05','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:03:06'),
('2025-11-11 13:04:06','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:03:46'),
('2025-11-11 13:04:17','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:04:06'),
('2025-11-11 13:04:37','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:04:26'),
('2025-11-11 13:05:01','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:04:50'),
('2025-11-11 13:09:23','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:09:10'),
('2025-11-11 13:13:21','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:13:10'),
('2025-11-11 13:14:05','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:13:54'),
('2025-11-11 13:15:33','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:15:22'),
('2025-11-11 13:16:37','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:16:26'),
('2025-11-11 13:17:24','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:17:13'),
('2025-11-11 13:19:51','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:19:41'),
('2025-11-11 13:21:12','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:21:01'),
('2025-11-11 13:21:32','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:21:21'),
('2025-11-11 13:21:52','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:21:41'),
('2025-11-11 13:22:12','AYSK17105912','TAS-F05QR',1,0,0,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:22:01'),
('2025-11-11 13:22:32','AYSK17105912','TAS-F05QR',1,0,0,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:22:21'),
('2025-11-11 13:22:52','AYSK17105912','TAS-F05QR',1,0,0,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:22:41'),
('2025-11-11 13:23:12','AYSK17105912','TAS-F05QR',1,0,0,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:23:01'),
('2025-11-11 13:23:17','AYSK17105912','TAS-F05QR',1,0,0,0,10,10,'ai810_f06v_v5.02','2025-11-11 13:23:06'),
('2025-11-11 14:01:02','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 14:00:40'),
('2025-11-11 14:05:20','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 14:05:09'),
('2025-11-11 14:05:40','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 14:05:29'),
('2025-11-11 14:06:00','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 14:05:49'),
('2025-11-11 14:06:20','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 14:06:09'),
('2025-11-11 14:06:40','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 14:06:29'),
('2025-11-11 14:07:25','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 14:06:49'),
('2025-11-11 14:07:45','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 14:07:34'),
('2025-11-11 14:15:05','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 14:14:52'),
('2025-11-11 14:15:23','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 14:15:12'),
('2025-11-11 14:15:43','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 14:15:32'),
('2025-11-11 14:16:03','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 14:15:52'),
('2025-11-11 14:20:45','AYSK17105912','TAS-F05QR',2,0,1,0,10,10,'ai810_f06v_v5.02','2025-11-11 14:20:34'),
('2025-11-11 14:53:04','AYSK17105912','TAS-F05QR',4,0,3,0,10,10,'ai810_f06v_v5.02','2025-11-11 14:52:22'),
('2025-11-11 14:53:39','AYSK17105912','TAS-F05QR',4,0,3,0,10,10,'ai810_f06v_v5.02','2025-11-11 14:53:28'),
('2025-11-11 14:53:59','AYSK17105912','TAS-F05QR',4,0,3,0,10,10,'ai810_f06v_v5.02','2025-11-11 14:53:48'),
('2025-11-11 14:54:41','AYSK17105912','TAS-F05QR',4,0,3,0,10,10,'ai810_f06v_v5.02','2025-11-11 14:54:08'),
('2025-11-11 14:54:42','AYSK17105912','TAS-F05QR',4,0,3,0,10,10,'ai810_f06v_v5.02','2025-11-11 14:54:28'),
('2025-11-12 08:47:35','AYSK17105912','TAS-F05QR',4,0,3,0,11,11,'ai810_f06v_v5.02','2025-11-12 08:47:02'),
('2025-11-12 08:47:37','AYSK17105912','TAS-F05QR',4,0,3,0,11,11,'ai810_f06v_v5.02','2025-11-12 08:47:25'),
('2025-11-12 09:16:54','AYSK17105912','TAS-F05QR',4,0,3,0,11,11,'ai810_f06v_v5.02','2025-11-12 09:16:28'),
('2025-11-12 09:17:41','AYSK17105912','TAS-F05QR',4,0,3,0,11,11,'ai810_f06v_v5.02','2025-11-12 09:17:29'),
('2025-11-12 09:19:46','AYSK17105912','TAS-F05QR',4,0,3,0,11,11,'ai810_f06v_v5.02','2025-11-12 09:19:34'),
('2025-11-12 09:22:48','AYSK17105912','TAS-F05QR',4,0,3,0,11,11,'ai810_f06v_v5.02','2025-11-12 09:22:36'),
('2025-11-12 10:09:18','AYSK17105912','TAS-F05QR',6,0,5,0,11,11,'ai810_f06v_v5.02','2025-11-12 10:09:07'),
('2025-11-13 08:51:30','AYSK17105912','TAS-F05QR',0,0,0,0,5,5,'ai810_f06v_v5.02','2025-11-13 08:51:06'),
('2025-11-13 08:55:44','AYSK17105912','TAS-F05QR',0,0,0,0,5,5,'ai810_f06v_v5.02','2025-11-13 08:55:33'),
('2025-11-13 09:38:10','AYSK17105912','TAS-F05QR',1,0,1,0,5,5,'ai810_f06v_v5.02','2025-11-13 09:37:54'),
('2025-11-13 10:05:56','AYSK17105912','TAS-F05QR',2,0,2,0,3,3,'ai810_f06v_v5.02','2025-11-13 10:05:40'),
('2025-11-13 10:06:10','AYSK17105912','TAS-F05QR',2,0,2,0,3,3,'ai810_f06v_v5.02','2025-11-13 10:05:54'),
('2025-11-13 10:07:52','AYSK17105912','TAS-F05QR',2,0,2,0,3,3,'ai810_f06v_v5.02','2025-11-13 10:07:41'),
('2025-11-13 10:10:52','AYSK17105912','TAS-F05QR',2,0,1,0,3,3,'ai810_f06v_v5.02','2025-11-13 10:10:41'),
('2025-11-13 10:14:45','AYSK17105912','TAS-F05QR',2,0,1,0,3,3,'ai810_f06v_v5.02','2025-11-13 10:14:34'),
('2025-11-13 10:29:50','AYSK17105912','TAS-F05QR',2,0,1,0,3,3,'ai810_f06v_v5.02','2025-11-13 10:29:38'),
('2025-11-13 10:31:23','AYSK17105912','TAS-F05QR',2,0,1,0,3,3,'ai810_f06v_v5.02','2025-11-13 10:31:11'),
('2025-11-13 11:06:32','AYSK17105912','TAS-F05QR',2,0,1,0,3,3,'ai810_f06v_v5.02','2025-11-13 11:06:20'),
('2025-11-13 11:09:39','AYSK17105912','TAS-F05QR',2,0,1,0,3,3,'ai810_f06v_v5.02','2025-11-13 11:09:27'),
('2025-11-13 11:53:50','AYSK17105912','TAS-F05QR',2,0,1,0,8,8,'ai810_f06v_v5.02','2025-11-13 11:53:38'),
('2025-11-13 11:53:54','AYSK17105912','TAS-F05QR',2,0,1,0,8,8,'ai810_f06v_v5.02','2025-11-13 11:53:43'),
('2025-11-13 11:53:55','AYSK17105912','TAS-F05QR',2,0,1,0,8,8,'ai810_f06v_v5.02','2025-11-13 11:53:44'),
('2025-11-13 12:03:35','AYSK17105912','TAS-F05QR',2,0,1,0,1,1,'ai810_f06v_v5.02','2025-11-13 12:03:24'),
('2025-11-13 12:06:11','AYSK17105912','TAS-F05QR',2,0,1,0,1,1,'ai810_f06v_v5.02','2025-11-13 12:06:00'),
('2025-11-13 12:07:19','AYSK17105912','TAS-F05QR',2,0,1,0,1,1,'ai810_f06v_v5.02','2025-11-13 12:07:08'),
('2025-11-13 13:20:11','AYSK17105912','TAS-F05QR',2,0,1,0,1,1,'ai810_f06v_v5.02','2025-11-13 13:19:54'),
('2025-11-13 14:21:58','AYSK17105912','TAS-F05QR',2,0,1,0,8,8,'ai810_f06v_v5.02','2025-11-13 14:21:47'),
('2025-11-13 15:27:25','AYSK17105912','TAS-F05QR',2,0,1,0,2,2,'ai810_f06v_v5.02','2025-11-13 15:27:08'),
('2025-11-13 16:58:00','AYSK17105912','TAS-F05QR',2,0,1,0,7,7,'ai810_f06v_v5.02','2025-11-13 16:57:48'),
('2025-11-13 17:06:28','AYSK17105912','TAS-F05QR',2,0,1,0,8,8,'ai810_f06v_v5.02','2025-11-13 17:06:16'),
('2025-11-13 17:06:29','AYSK17105912','TAS-F05QR',2,0,1,0,8,8,'ai810_f06v_v5.02','2025-11-13 17:06:16'),
('2025-11-14 10:19:09','AYSK17105912','TAS-F05QR',4,0,3,0,30,30,'ai810_f06v_v5.02','2025-11-14 10:18:49'),
('2025-11-14 10:19:14','AYSK17105912','TAS-F05QR',4,0,3,0,30,30,'ai810_f06v_v5.02','2025-11-14 10:19:02'),
('2025-11-14 10:19:24','AYSK17105912','TAS-F05QR',4,0,3,0,30,30,'ai810_f06v_v5.02','2025-11-14 10:19:12'),
('2025-11-14 10:19:32','AYSK17105912','TAS-F05QR',4,0,3,0,31,31,'ai810_f06v_v5.02','2025-11-14 10:19:20'),
('2025-11-14 10:19:49','AYSK17105912','TAS-F05QR',4,0,3,0,31,31,'ai810_f06v_v5.02','2025-11-14 10:19:37'),
('2025-11-14 10:44:51','AYSK17105912','TAS-F05QR',2,0,2,0,36,36,'ai810_f06v_v5.02','2025-11-14 10:44:39'),
('2025-11-14 10:44:59','AYSK17105912','TAS-F05QR',2,0,2,0,36,36,'ai810_f06v_v5.02','2025-11-14 10:44:47'),
('2025-11-14 11:20:46','AYSK17105912','TAS-F05QR',3,0,3,0,43,43,'ai810_f06v_v5.02','2025-11-14 11:20:34'),
('2025-11-14 11:20:49','AYSK17105912','TAS-F05QR',3,0,3,0,43,43,'ai810_f06v_v5.02','2025-11-14 11:20:37'),
('2025-11-14 11:26:53','AYSK17105912','TAS-F05QR',3,0,3,0,44,44,'ai810_f06v_v5.02','2025-11-14 11:26:41'),
('2025-11-14 11:35:55','AYSK17105912','TAS-F05QR',3,0,3,0,44,44,'ai810_f06v_v5.02','2025-11-14 11:35:43'),
('2025-11-14 11:43:21','AYSK17105912','TAS-F05QR',1,0,1,0,45,45,'ai810_f06v_v5.02','2025-11-14 11:43:09'),
('2025-11-14 12:28:54','AYSK17105912','TAS-F05QR',1,0,1,0,1,1,'ai810_f06v_v5.02','2025-11-14 12:28:41'),
('2025-11-14 13:34:35','AYSK17105912','TAS-F05QR',0,0,0,0,9,9,'ai810_f06v_v5.02','2025-11-14 13:34:22'),
('2025-11-14 13:58:18','AYSK17105912','TAS-F05QR',1,0,1,0,13,13,'ai810_f06v_v5.02','2025-11-14 13:58:06'),
('2025-11-14 15:18:05','AYSK17105912','TAS-F05QR',3,0,3,0,25,25,'ai810_f06v_v5.02','2025-11-14 15:17:52'),
('2025-11-14 15:37:02','AYSK17105912','TAS-F05QR',3,0,3,0,2,2,'ai810_f06v_v5.02','2025-11-14 15:36:50'),
('2025-11-14 15:37:46','AYSK17105912','TAS-F05QR',3,0,3,0,2,2,'ai810_f06v_v5.02','2025-11-14 15:37:34'),
('2025-11-14 15:39:41','AYSK17105912','TAS-F05QR',3,0,3,0,2,2,'ai810_f06v_v5.02','2025-11-14 15:39:29'),
('2025-11-14 15:40:19','AYSK17105912','TAS-F05QR',3,0,3,0,2,2,'ai810_f06v_v5.02','2025-11-14 15:40:07'),
('2025-11-14 15:43:49','AYSK17105912','TAS-F05QR',3,0,3,0,2,2,'ai810_f06v_v5.02','2025-11-14 15:43:37'),
('2025-11-17 08:58:52','AYSK17105912','TAS-F05QR',3,0,3,0,9,9,'ai810_f06v_v5.02','2025-11-17 08:58:05'),
('2025-11-17 08:58:54','AYSK17105912','TAS-F05QR',3,0,3,0,9,9,'ai810_f06v_v5.02','2025-11-17 08:58:43'),
('2025-11-17 09:16:36','AYSK17105912','TAS-F05QR',0,0,0,0,0,0,'ai810_f06v_v5.02','2025-11-17 09:16:22'),
('2025-11-17 09:55:48','AYSK17105912','TAS-F05QR',3,0,3,0,1,1,'ai810_f06v_v5.02','2025-11-17 09:55:35'),
('2025-11-17 11:26:21','AYSK17105912','TAS-F05QR',1,0,1,0,0,0,'ai810_f06v_v5.02','2025-11-17 11:26:06'),
('2025-11-18 10:39:01','AYSK17105912','TAS-F05QR',1,0,1,0,18,18,'ai810_f06v_v5.02','2025-11-18 10:38:39'),
('2025-11-18 10:56:57','AYSK17105912','TAS-F05QR',2,0,2,0,18,18,'ai810_f06v_v5.02','2025-11-18 10:56:40'),
('2025-11-18 12:21:25','AYSK17105912','TAS-F05QR',0,0,0,0,18,18,'ai810_f06v_v5.02','2025-11-18 12:21:07'),
('2025-11-18 13:00:16','AYSK17105912','TAS-F05QR',0,0,0,0,18,18,'ai810_f06v_v5.02','2025-11-18 13:00:01'),
('2025-11-18 13:02:15','AYSK17105912','TAS-F05QR',0,0,0,0,18,18,'ai810_f06v_v5.02','2025-11-18 13:02:02'),
('2025-11-18 13:03:25','AYSK17105912','TAS-F05QR',0,0,0,0,18,18,'ai810_f06v_v5.02','2025-11-18 13:03:12'),
('2025-11-18 13:03:35','AYSK17105912','TAS-F05QR',0,0,0,0,18,18,'ai810_f06v_v5.02','2025-11-18 13:03:22'),
('2025-11-18 13:04:25','AYSK17105912','TAS-F05QR',0,0,0,0,18,18,'ai810_f06v_v5.02','2025-11-18 13:04:12'),
('2025-11-18 13:04:47','AYSK17105912','TAS-F05QR',0,0,0,0,18,18,'ai810_f06v_v5.02','2025-11-18 13:04:34'),
('2025-11-18 13:21:21','AYSK17105912','TAS-F05QR',1,0,1,0,18,18,'ai810_f06v_v5.02','2025-11-18 13:21:07'),
('2025-11-18 16:08:59','AYSK17105912','TAS-F05QR',3,0,3,0,1,1,'ai810_f06v_v5.02','2025-11-18 16:08:40'),
('2025-11-18 16:26:45','AYSK17105912','TAS-F05QR',3,0,3,0,1,1,'ai810_f06v_v5.02','2025-11-18 16:26:29'),
('2025-11-19 09:16:27','AYSK17105912','TAS-F05QR',3,0,3,0,1,1,'ai810_f06v_v5.02','2025-11-19 09:15:10'),
('2025-11-19 13:36:40','AYSK17105912','TAS-F05QR',3,0,3,0,2,2,'ai810_f06v_v5.02','2025-11-19 13:36:13'),
('2025-11-27 09:19:37','ZYSL20032920','tfs30',6,2,5,0,20,20,'TFS70 V4.4','2025-11-27 09:19:15'),
('2025-11-27 09:45:03','ZYSL20032920','tfs30',6,2,5,0,21,21,'TFS70 V4.4','2025-11-27 09:44:43'),
('2025-11-27 09:46:27','ZYSL20032920','tfs30',6,2,5,0,22,22,'TFS70 V4.4','2025-11-27 09:46:07'),
('2025-11-27 09:47:57','ZYSL20032920','tfs30',5,2,5,0,23,23,'TFS70 V4.4','2025-11-27 09:47:37'),
('2025-11-27 09:48:58','ZYSL20032920','tfs30',5,2,5,0,24,24,'TFS70 V4.4','2025-11-27 09:48:39'),
('2025-11-27 12:41:42','ZYSL20032920','tfs30',5,2,5,0,24,24,'TFS70 V4.4','2025-11-27 12:41:18'),
('2025-11-27 12:51:46','ZYSL20032920','tfs30',5,2,5,0,27,27,'TFS70 V4.4','2025-11-27 12:51:26'),
('2025-12-01 13:44:50','ZYSL20032920','tfs30',5,2,5,0,29,29,'TFS70 V4.4','2025-12-01 13:44:28'),
('2025-12-01 14:17:57','ZXRL23103798','TAS-F09W',5,0,4,1,3733,2035,'ai810_f80v_v5.10','2025-12-01 14:18:01'),
('2025-12-01 14:18:15','ZXRL23103798','TAS-F09W',5,0,4,1,3733,2035,'ai810_f80v_v5.10','2025-12-01 14:18:20'),
('2025-12-02 10:10:44','ZYSL20032920','tfs30',5,2,5,0,34,34,'TFS70 V4.4','2025-12-02 10:10:22'),
('2025-12-02 14:29:26','ZYSL20032920','tfs30',5,2,5,0,34,34,'TFS70 V4.4','2025-12-02 14:28:59'),
('2025-12-02 14:42:06','ZYSL20032920','tfs30',4,0,4,0,36,36,'TFS70 V4.4','2025-12-02 14:41:48'),
('2025-12-02 14:56:50','ZYSL20032920','tfs30',4,0,4,0,38,38,'TFS70 V4.4','2025-12-02 14:56:32'),
('2025-12-02 15:54:35','ZYSL20032920','tfs30',4,0,4,0,38,38,'TFS70 V4.4','2025-12-02 15:54:14'),
('2025-12-02 16:05:35','ZYSL20032920','tfs30',4,0,4,0,38,38,'TFS70 V4.4','2025-12-02 16:05:32'),
('2025-12-08 10:30:25','ZYSL20032920','tfs30',4,0,4,0,40,40,'TFS70 V4.4','2025-12-08 10:30:16'),
('2025-12-08 10:31:19','ZYSL20032920','tfs30',4,1,4,0,41,41,'TFS70 V4.4','2025-12-08 10:31:17'),
('2025-12-08 13:25:06','ZYSL20032920','tfs30',4,1,4,0,42,42,'TFS70 V4.4','2025-12-08 13:25:01'),
('2025-12-08 13:45:15','ZYSL20032920','tfs30',4,1,4,0,42,42,'TFS70 V4.4','2025-12-08 13:45:06'),
('2025-12-08 13:47:15','ZYSL20032920','tfs30',4,1,4,0,42,42,'TFS70 V4.4','2025-12-08 13:47:14'),
('2025-12-08 14:07:35','ZYSL20032920','tfs30',4,1,4,0,42,42,'TFS70 V4.4','2025-12-08 14:07:31'),
('2025-12-08 16:25:33','ZYSL20032920','tfs30',3,1,3,0,42,42,'TFS70 V4.4','2025-12-08 16:25:22'),
('2025-12-10 09:14:01','ZYSL20032920','tfs30',3,1,3,0,42,42,'TFS70 V4.4','2025-12-10 09:13:48'),
('2025-12-10 09:20:59','ZYSL20032920','tfs30',2,0,2,0,42,42,'TFS70 V4.4','2025-12-10 09:20:53'),
('2025-12-10 10:12:23','ZYSL20032920','tfs30',0,0,0,0,42,42,'TFS70 V4.4','2025-12-10 10:12:21'),
('2025-12-10 10:41:22','ZYSL20032920','tfs30',10,1,10,0,43,43,'TFS70 V4.4','2025-12-10 10:41:21'),
('2025-12-10 10:53:40','ZYSL20032920','tfs30',10,1,10,0,43,43,'TFS70 V4.4','2025-12-10 10:53:39'),
('2025-12-10 10:53:59','ZYSL20032920','tfs30',10,1,10,0,43,43,'TFS70 V4.4','2025-12-10 10:53:58'),
('2025-12-10 11:14:20','ZYSL20032920','tfs30',13,0,13,0,45,45,'TFS70 V4.4','2025-12-10 11:14:18'),
('2025-12-10 11:21:18','ZYSL20032920','tfs30',1,1,1,0,47,47,'TFS70 V4.4','2025-12-10 11:21:18'),
('2025-12-10 11:35:45','ZYSL20032920','tfs30',1,1,1,0,48,48,'TFS70 V4.4','2025-12-10 11:35:44'),
('2025-12-10 11:46:48','ZYSL20032920','tfs30',1,1,1,0,49,49,'TFS70 V4.4','2025-12-10 11:46:47'),
('2025-12-10 12:33:09','ZYSL20032920','tfs30',2,1,2,0,50,50,'TFS70 V4.4','2025-12-10 12:33:08'),
('2025-12-11 10:14:43','ZYSL20032920','tfs30',2,1,2,0,50,50,'TFS70 V4.4','2025-12-11 10:14:20'),
('2025-12-11 10:49:35','ZYSL20032920','tfs30',8,2,7,0,50,50,'TFS70 V4.4','2025-12-11 10:49:30'),
('2025-12-11 11:51:26','ZYSL20032920','tfs30',8,2,7,0,50,50,'TFS70 V4.4','2025-12-11 11:51:22'),
('2025-12-11 16:46:41','ZYSL20032920','tfs30',8,2,7,0,50,50,'TFS70 V4.4','2025-12-11 16:46:03'),
('2025-12-11 17:18:17','ZYSL20032920','tfs30',8,2,7,0,50,50,'TFS70 V4.4','2025-12-11 17:17:50');

/*Table structure for table `tbl_userlog` */

DROP TABLE IF EXISTS `tbl_userlog`;

CREATE TABLE `tbl_userlog` (
  `REC_INDEX` bigint(20) NOT NULL AUTO_INCREMENT,
  `USER_ADDR` int(15) DEFAULT NULL,
  `TM_EVENT` datetime DEFAULT NULL,
  `DEVICESN` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `IMGPATH` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `devicetype` int(11) DEFAULT NULL,
  `card` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `stat` tinyint(4) DEFAULT '0' COMMENT '0=Normal, 1=expired',
  PRIMARY KEY (`REC_INDEX`),
  UNIQUE KEY `tbl_userlog_unique1` (`USER_ADDR`,`TM_EVENT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_userlog` */

/*Table structure for table `tbl_usersdata` */

DROP TABLE IF EXISTS `tbl_usersdata`;

CREATE TABLE `tbl_usersdata` (
  `fid` int(10) unsigned DEFAULT '0',
  `Type` tinyint(4) DEFAULT '0' COMMENT '0:bmp, 1:jpeg, 2:gif, 3:tiff, 4:png, 5:MPEG, 6:AVI',
  `Picture` mediumblob,
  `FaceNo` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `Fp` varchar(10000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  UNIQUE KEY `fid` (`fid`,`Type`,`FaceNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='v1';

/*Data for the table `tbl_usersdata` */

insert  into `tbl_usersdata`(`fid`,`Type`,`Picture`,`FaceNo`,`Fp`) values 
(1,0,NULL,'ZYSL20032920','c51a68018688fa89e88419867c87bae900c7cc068abac365e07a0dc36d85d37d0009fa4582b8f37de885cc46e284eb96ff3e4c878579dbc6e081ce0480b933ede8c3cac5df850bfef83e2a84658574251749f9c208ba0c8d183a170083baac920041d6c173b7ad590847b8870fba35adef7816c387babdc90f7fc6c30f8bcb41ff43fa848c8bfb7ddffe0d827ab8f636f847aac60f87c24dfefe19058d8a3a36efc3fd4285879a76dfc3fa47168ada81f83bddc2928b02b1eff9cc816e484e5ef889f704e446f661effe3c48e6480e910ffe4705(272)4034463163482537134282322564432545cdbf991873ea82575a5332611e4f184675631933632a(73)15683a4b187001a1506340349e70da2524618cc19c0e2240e6e115288ae68d814f5f5027a060014f160302c1692ab09ba99078102392d7d06f20403796604e0e6143eaa132190311c1a10f0b7035e1600017601400a16f0e3333e791ed46634023718b0c607207a17c2332438cc072592217be713b75555141d0b1131703dcd0(54)070a04060c0b0109020d03080e1100170f16191513(9)edaf'),
(2,0,NULL,'ZYSL20032920','j1uuuhsaj11212'),
(7,0,NULL,'ZYSL20032920',NULL),
(8,0,NULL,'ZYSL20032920',NULL),
(9,0,NULL,'ZYSL20032920',NULL),
(10,0,NULL,'ZYSL20032920',NULL),
(11,0,NULL,'ZYSL20032920',NULL),
(12,0,NULL,'ZYSL20032920',NULL),
(13,0,NULL,'ZYSL20032920',NULL);

/*Table structure for table `userlogin` */

DROP TABLE IF EXISTS `userlogin`;

CREATE TABLE `userlogin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priv` json DEFAULT NULL,
  `bactive` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `userlogin` */

insert  into `userlogin`(`id`,`username`,`password`,`priv`,`bactive`) values 
(4,'admin','$2y$12$fnOu9hpQegB7ccGaXdt7WO7C/RQsXbZNitvPUllvjENSwpz5T8Y66','1','{\"user\":true,\"device\":true,\"log\":true,\"setting\":true,\"userAdmin\":true,\"attendance\":true,\"schedule\":true}'),
(5,'Tatang','$2y$12$uA3S02RATlZdd6Um6ll/je3dYjpqWe1rGOnm1J90Lta9hLs.8gCfa','0','{\"user\":true,\"device\":true,\"log\":true,\"setting\":true,\"userAdmin\":true,\"attendance\":true}'),
(6,'soyal','$2y$12$2yrqqzYpRdZ2Kfg6NmZwhuFfGaCgDU04Y4z7qq.5yyHh.Np9mmUxe','0','{\"user\":true,\"device\":true,\"log\":true,\"setting\":true,\"userAdmin\":true,\"attendance\":false,\"schedule\":false}');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `users` */

/*Table structure for table `usersdata` */

DROP TABLE IF EXISTS `usersdata`;

CREATE TABLE `usersdata` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `Type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0:bmp, 1:jpeg, 2:gif, 3:tiff, 4:png, 5:MPEG, 6:AVI',
  `Picture` mediumblob,
  `FaceNo` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Fp` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'Fingerprint',
  PRIMARY KEY (`uid`,`Type`,`FaceNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `usersdata` */

/*Table structure for table `usersprofile` */

DROP TABLE IF EXISTS `usersprofile`;

CREATE TABLE `usersprofile` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ACC_MODE` tinyint(4) DEFAULT '0' COMMENT 'Sementara tidak usah muncul di UI',
  `PIN` int(10) unsigned DEFAULT NULL COMMENT 'Password',
  `DOOR_GRP` int(5) unsigned DEFAULT '0' COMMENT 'Sementara tidak usah muncul di UI',
  `BEGIN_DATE` datetime DEFAULT CURRENT_TIMESTAMP,
  `END_DATE` datetime DEFAULT '2079-12-31 23:59:59',
  `BIRTHDAY` date DEFAULT NULL,
  `Depid` int(11) DEFAULT '1' COMMENT 'DepartementID',
  `MemberNo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'Link Ke Member Dreampos, tidak usah muncul di UI',
  `Branchid` int(11) DEFAULT '1' COMMENT 'branchID',
  `photo` mediumblob COMMENT 'photo',
  `Card` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nomerkartu',
  `NoIdentitas` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nomer Identitas',
  `Timezone` int(11) DEFAULT '0',
  `shiftpatternID` int(4) DEFAULT '0',
  `PASSWORD` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=Staff/User, 1=Member, 2=Personal Trainer',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ADDR_of_CTL` (`ID`),
  KEY `user_names` (`NAME`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='v4';

/*Data for the table `usersprofile` */

insert  into `usersprofile`(`ID`,`NAME`,`ACC_MODE`,`PIN`,`DOOR_GRP`,`BEGIN_DATE`,`END_DATE`,`BIRTHDAY`,`Depid`,`MemberNo`,`Branchid`,`photo`,`Card`,`NoIdentitas`,`Timezone`,`shiftpatternID`,`PASSWORD`,`user_type`) values 
(1,'Sobirin',0,NULL,0,'2025-11-24 00:00:00','2025-12-31 23:59:00','1984-09-09',1,'1',12,NULL,'1013460','0',0,0,'7878787',1),
(2,'Andre',0,NULL,0,'2025-07-22 00:00:00','2025-12-31 13:57:00','1990-09-16',1,'2',12,'data:image/jpeg;base64,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','1519624183','12222',0,0,'1234567',1),
(4,'Suti',0,NULL,0,'2025-11-28 00:00:00','2025-11-30 09:35:00','1988-09-01',1,'4',12,'data:image/jpeg;base64,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','7036483','333',0,0,'888888',2),
(7,'Yono',0,NULL,0,'2024-11-01 00:00:00','2025-12-31 00:00:00','2025-09-15',1,'7',12,NULL,'6116459','111',0,0,'123456',2),
(8,'YUZA',0,NULL,0,'2024-11-01 00:00:00','2025-11-28 00:00:00','1990-05-25',2,'8',12,NULL,'8711447','2211',0,0,'7355E29CAB',0),
(9,'bela',0,NULL,0,'2025-11-21 00:00:00','2025-11-28 23:59:00','2000-02-05',2,'9',12,'data:image/jpeg;base64,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','11223344','1122',0,0,'63CC9224C4',0),
(10,'Tatang',0,NULL,0,'2025-11-21 00:00:00','2025-11-28 23:59:00','2025-12-11',1,'',12,NULL,'12244131','7129713130',0,0,'A6C6CB',0),
(12,'untung',0,NULL,0,'2025-11-21 00:00:00','2025-11-28 23:59:00',NULL,1,'',1,NULL,'12345675',NULL,0,0,'1B762D',0),
(13,'ucup',0,NULL,0,'2025-11-21 00:00:00','2025-11-28 23:59:00',NULL,1,'',1,NULL,'6679573',NULL,0,0,'98CF9A',0),
(14,'ujang karamou',0,NULL,0,'2025-11-21 00:00:00','2025-11-28 23:59:00',NULL,1,'',1,NULL,'6764297',NULL,0,0,'4E8BC5',0);

/*Table structure for table `usersxdevice` */

DROP TABLE IF EXISTS `usersxdevice`;

CREATE TABLE `usersxdevice` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) DEFAULT NULL,
  `gateId` int(10) DEFAULT NULL,
  `Lift1` tinyint(4) unsigned DEFAULT '0' COMMENT 'Setting Lift 1- 16',
  `Lift2` tinyint(4) unsigned DEFAULT '0' COMMENT 'Setting Lift 17- 32',
  `Lift3` tinyint(4) unsigned DEFAULT '0' COMMENT 'Setting Lift 33- 48',
  `Lift4` tinyint(4) unsigned DEFAULT '0' COMMENT 'Setting Lift 49- 64',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `usersxdevice` */

/*Table structure for table `weekzone` */

DROP TABLE IF EXISTS `weekzone`;

CREATE TABLE `weekzone` (
  `ID` int(11) NOT NULL,
  `Name` varchar(15) DEFAULT '',
  `Descriptio` varchar(50) DEFAULT '' COMMENT 'Untuk Keterangan',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `weekzone` */

insert  into `weekzone`(`ID`,`Name`,`Descriptio`) values 
(1,'weekzone1','pola security'),
(2,'weekzone2',''),
(3,'weekzone3',''),
(4,'weekzone4',''),
(5,'weekzone5',''),
(6,'weekzone6',''),
(7,'weekzone7',''),
(8,'weekzone8','');

/*Table structure for table `weekzonedetail` */

DROP TABLE IF EXISTS `weekzonedetail`;

CREATE TABLE `weekzonedetail` (
  `ID` int(11) NOT NULL,
  `wz` int(11) DEFAULT NULL COMMENT 'relasi ke weekzone id',
  `day1` int(11) DEFAULT '0',
  `day2` int(11) DEFAULT '0',
  `day3` int(11) DEFAULT '0',
  `day4` int(11) DEFAULT '0',
  `day5` int(11) DEFAULT '0',
  `day6` int(11) DEFAULT '0',
  `day7` int(11) DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `weekzonedetail` */

insert  into `weekzonedetail`(`ID`,`wz`,`day1`,`day2`,`day3`,`day4`,`day5`,`day6`,`day7`) values 
(1,1,1,1,1,1,1,1,1),
(2,2,2,2,2,2,2,2,2),
(3,3,3,3,3,3,3,3,3),
(4,4,4,4,4,4,4,4,4);

/*Table structure for table `weekzoneuser` */

DROP TABLE IF EXISTS `weekzoneuser`;

CREATE TABLE `weekzoneuser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT NULL COMMENT 'User id',
  `wzid` int(11) DEFAULT NULL COMMENT 'weekzone Id',
  `deviceid` int(11) DEFAULT NULL COMMENT 'device ID',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wzu` (`userid`,`wzid`,`deviceid`),
  KEY `wzid` (`userid`,`deviceid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `weekzoneuser` */

/* Trigger structure for table `tbl_userlog` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `fill_userlog_card` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'localhost' */ /*!50003 TRIGGER `fill_userlog_card` BEFORE INSERT ON `tbl_userlog` FOR EACH ROW BEGIN
    DECLARE user_card VARCHAR(255);
    
    -- Ambil nilai 'Card' dari tabel usersprofile berdasarkan USER_ADDR
    SELECT Card INTO user_card 
    FROM usersprofile 
    WHERE ID = NEW.USER_ADDR;
    
    -- Jika ditemukan, isi nilai card di tbl_userlog
    IF user_card IS NOT NULL THEN
        SET NEW.card = user_card;
    END IF;
END */$$


DELIMITER ;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
