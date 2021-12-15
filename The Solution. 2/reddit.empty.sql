/*
SQLyog Ultimate v13.1.1 (64 bit)
MySQL - 10.4.21-MariaDB : Database - reddit
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP DATABASE IF EXISTS `reddit`;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`reddit` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `reddit`;

/*Table structure for table `comments` */

DROP TABLE IF EXISTS `comments`;

CREATE TABLE `comments` (
  `id` varchar(100) CHARACTER SET ascii NOT NULL,
  `parent_id` varchar(100) CHARACTER SET ascii NOT NULL,
  `link_id` varchar(100) CHARACTER SET ascii NOT NULL,
  `author_id` varchar(100) CHARACTER SET ascii NOT NULL,
  `created_utc` datetime NOT NULL,
  `body` text DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `ups` int(11) DEFAULT NULL,
  `downs` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `link_id` (`link_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `comments` */

/*Table structure for table `links` */

DROP TABLE IF EXISTS `links`;

CREATE TABLE `links` (
  `link_id` varchar(100) CHARACTER SET ascii NOT NULL,
  `subreddit_id` varchar(100) CHARACTER SET ascii NOT NULL,
  `created_at1` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`link_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `links` */

/*Table structure for table `subreddits` */

DROP TABLE IF EXISTS `subreddits`;

CREATE TABLE `subreddits` (
  `subreddit_id` varchar(100) CHARACTER SET ascii NOT NULL,
  `subreddit` text DEFAULT NULL,
  `created_at2` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`subreddit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `subreddits` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
