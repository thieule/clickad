/*
SQLyog Enterprise - MySQL GUI v7.02 
MySQL - 5.5.8-log : Database - medium
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/`medium` /*!40100 DEFAULT CHARACTER SET latin1 */;

/*Table structure for table `medium` */

DROP TABLE IF EXISTS `medium`;

CREATE TABLE `medium` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option` int(11) NOT NULL COMMENT '1 bloger, 2 published, 3 IFrame, 4 Email',
  `type` int(11) NOT NULL DEFAULT '0' COMMENT '0 view, 1 click',
  `author` int(11) NOT NULL,
  `taget` int(11) NOT NULL DEFAULT '0' COMMENT '0 home page, 1 download page',
  `ip` varchar(20) DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `medium` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
