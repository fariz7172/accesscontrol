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

USE `accesscontrol_baru`;

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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tbl_userlog` */

insert  into `tbl_userlog`(`REC_INDEX`,`USER_ADDR`,`TM_EVENT`,`DEVICESN`,`IMGPATH`,`devicetype`,`card`,`stat`) values 
(1,2,'2025-12-05 14:32:17','SOYAL antai 3','',2,'1519624183',0),
(2,7,'2025-12-05 14:32:21','SOYAL antai 3','',2,'6116459',0),
(3,4,'2025-12-05 14:32:23','SOYAL antai 3','',2,'7036483',1),
(4,10,'2025-12-05 14:32:29','SOYAL antai 3','',2,'00173:22915',1),
(5,10,'2025-12-05 14:33:41','SOYAL antai 3','',2,'00173:22915',1),
(6,2,'2025-12-05 14:34:06','SOYAL antai 3','',2,'1519624183',0),
(7,4,'2025-12-05 14:34:29','SOYAL antai 3','',2,'7036483',1),
(8,2,'2025-12-05 15:54:25','SOYAL antai 3','',2,'1519624183',0),
(9,2,'2025-12-05 15:54:38','SOYAL antai 3','',2,'1519624183',0),
(10,4,'2025-12-05 15:54:44','SOYAL antai 3','',2,'7036483',1),
(11,2,'2025-12-05 16:27:17','SOYAL antai 3','',2,'1519624183',0),
(12,4,'2025-12-05 16:27:19','SOYAL antai 3','',2,'7036483',1),
(13,2,'2025-12-05 16:59:22','SOYAL antai 3','',2,'1519624183',0),
(14,7,'2025-12-08 09:05:47','SOYAL antai 3','',2,'6116459',0),
(15,4,'2025-12-08 09:05:53','SOYAL antai 3','',2,'7036483',1),
(16,2,'2025-12-08 09:07:10','SOYAL antai 3','',2,'1519624183',0),
(17,1,'2025-12-08 10:30:56','ZYSL20032920','',1,'1013460',0),
(18,1,'2025-12-08 10:33:00','ZYSL20032920','',1,'1013460',0);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
