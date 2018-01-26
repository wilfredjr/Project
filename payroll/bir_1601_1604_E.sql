/*
SQLyog Community v12.2.0 (64 bit)
MySQL - 5.5.29 : Database - secret_6_payroll_fusion
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`secret_6_payroll_fusion` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `secret_6_payroll_fusion`;

/*Table structure for table `bir_1601_e_details` */

DROP TABLE IF EXISTS `bir_1601_e_details`;

CREATE TABLE `bir_1601_e_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bir_1601_e_master_id` bigint(20) DEFAULT NULL,
  `nature_of_business` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) DEFAULT NULL,
  `atc_code` varchar(100) DEFAULT NULL,
  `tax_base` double DEFAULT NULL,
  `tax_rate` double DEFAULT NULL,
  `tax_withheld` double DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

/*Data for the table `bir_1601_e_details` */

insert  into `bir_1601_e_details`(`id`,`bir_1601_e_master_id`,`nature_of_business`,`reference_id`,`atc_code`,`tax_base`,`tax_rate`,`tax_withheld`,`is_deleted`) values 
(1,2,'This is a test 123',3,'WI 011	\r\n',20500,15,3075,0),
(2,2,'test22',2,'WC 010\r\n',12302,10,1230.2,0),
(3,3,'test',7,'WI 030',12010,10,1201,0),
(4,3,'tttt',8,'WI 031',22204,15,3330.6,0),
(5,3,'ra',5,'WI 020	\r\n	',50111,10,5011.1,0),
(6,4,'testing',1,'WI 010\r\n',54444,10,5444.4,0),
(7,4,'sadsdas',2,'WC 010\r\n',616161,10,61616.1,0),
(8,5,'qqqqqqqqqqq',3,'WI 011	\r\n',515151,15,77272.65,0),
(9,6,'this is a test',4,'WC 011\r\n',40000,15,6000,0),
(10,6,'this is a test 2',6,'WI 021	\r\n',90000,15,13500,0),
(11,6,'this is a test 3',7,'WI 030',600100,10,60010,0);

/*Table structure for table `bir_1601_e_master` */

DROP TABLE IF EXISTS `bir_1601_e_master`;

CREATE TABLE `bir_1601_e_master` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `month_year` varchar(50) DEFAULT NULL,
  `tin_no` varchar(100) DEFAULT NULL,
  `rdo_no` varchar(50) DEFAULT NULL,
  `line_of_business` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `telephone_no` varchar(20) DEFAULT NULL,
  `registered_add` varchar(255) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `category` tinyint(1) DEFAULT NULL,
  `total_tax` double DEFAULT NULL,
  `date_generated` date DEFAULT NULL,
  `date_processed` date DEFAULT NULL,
  `is_processed` tinyint(1) DEFAULT '0',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

/*Data for the table `bir_1601_e_master` */

insert  into `bir_1601_e_master`(`id`,`month_year`,`tin_no`,`rdo_no`,`line_of_business`,`company_name`,`telephone_no`,`registered_add`,`zip_code`,`category`,`total_tax`,`date_generated`,`date_processed`,`is_processed`,`is_deleted`) values 
(1,'FEB-','3333333333','54a','test','Spark Global Tech Systems Inc.','09124735132','1805A East Tower, Philippine Stock Exchange Centre, Exchange Rd, Ortigas, Pasig City','1604',1,NULL,'2017-07-12','0000-00-00',0,1),
(2,'01-2017','3333333333','54a','test','Spark Global Tech Systems Inc.','09124735132','1805A East Tower, Philippine Stock Exchange Centre, Exchange Rd, Ortigas, Pasig City','1604',1,4305.2,'2017-07-12','2017-07-14',1,0),
(3,'02-2017','3333333333','54a','test','Spark Global Tech Systems Inc.','09124735132','1805A East Tower, Philippine Stock Exchange Centre, Exchange Rd, Ortigas, Pasig City','1604',1,9542.7,'2017-07-14','2017-07-14',1,0),
(4,'03-2017','3333333333','54a','test','Spark Global Tech Systems Inc.','09124735132','1805A East Tower, Philippine Stock Exchange Centre, Exchange Rd, Ortigas, Pasig City','1604',1,67060.5,'2017-07-14','2017-07-14',1,0),
(5,'12-2016','3333333333','54a','test','Spark Global Tech Systems Inc.','09124735132','1805A East Tower, Philippine Stock Exchange Centre, Exchange Rd, Ortigas, Pasig City','1604',1,77272.65,'2017-07-14','2017-07-14',1,0),
(6,'11-2016','3333333333','54a','test','Spark Global Tech Systems Inc.','09124735132','1805A East Tower, Philippine Stock Exchange Centre, Exchange Rd, Ortigas, Pasig City','1604',1,79510,'2017-07-14','2017-07-14',0,1);

/*Table structure for table `bir_1601_e_reference` */

DROP TABLE IF EXISTS `bir_1601_e_reference`;

CREATE TABLE `bir_1601_e_reference` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `nature_of_business` varchar(255) DEFAULT NULL,
  `tax_rate` double DEFAULT NULL,
  `atc_type` tinyint(1) DEFAULT NULL,
  `atc_code` varchar(10) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

/*Data for the table `bir_1601_e_reference` */

insert  into `bir_1601_e_reference`(`id`,`nature_of_business`,`tax_rate`,`atc_type`,`atc_code`,`is_deleted`) values 
(1,'EWT- professionals (lawyers, CPAs, engineers, etc)/talent fees paid to juridical persons - if the current year\'s gross income is P720,000 and below',10,1,'WI 010\r\n',0),
(2,'EWT- professionals (lawyers, CPAs, engineers, etc)/talent fees paid to juridical persons - if the current year\'s gross income is P720,000 and below',10,2,'WC 010\r\n',0),
(3,'EWT- professionals (lawyers, CPAs, engineers, etc)/talent fees paid to juridical persons - if the current year\'s gross income exceeds P720,000',15,1,'WI 011	\r\n',0),
(4,'EWT- professionals (lawyers, CPAs, engineers, etc)/talent fees paid to juridical persons - if the current year\'s gross income exceeds P720,000',15,2,'WC 011\r\n',0),
(5,'EWT- professional entertainers - if the current year\'s gross income does not exceed P720,000.00',10,1,'WI 020	\r\n	',0),
(6,'EWT- professional entertainers - if the current year\'s gross income exceeds P720,000.00',15,1,'WI 021	\r\n',0),
(7,'EWT- professional athletes - if the current year\'s gross income does not exceed P720,000.00',10,1,'WI 030',0),
(8,'EWT- professionals (lawyers, CPAs, engineers, etc)/talent fees paid to juridical persons - - if the current year\'s gross income exceeds P720,000.00',15,1,'WI 031',0),
(9,'test1111',2,2,'test123456',1);

/*Table structure for table `bir_1604_e_master` */

DROP TABLE IF EXISTS `bir_1604_e_master`;

CREATE TABLE `bir_1604_e_master` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `for_year` varchar(10) DEFAULT NULL,
  `month_year_start` varchar(50) DEFAULT NULL,
  `month_year_end` varchar(50) DEFAULT NULL,
  `tin_no` varchar(50) DEFAULT NULL,
  `rdo_no` varchar(50) DEFAULT NULL,
  `line_of_business` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `telephone_no` varchar(50) DEFAULT NULL,
  `registered_add` varchar(255) DEFAULT NULL,
  `zip_code` varchar(50) DEFAULT NULL,
  `total_tax_withheld` double DEFAULT NULL,
  `date_generated` date DEFAULT NULL,
  `date_processed` date DEFAULT NULL,
  `is_processed` tinyint(1) DEFAULT '0',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Data for the table `bir_1604_e_master` */

insert  into `bir_1604_e_master`(`id`,`for_year`,`month_year_start`,`month_year_end`,`tin_no`,`rdo_no`,`line_of_business`,`company_name`,`telephone_no`,`registered_add`,`zip_code`,`total_tax_withheld`,`date_generated`,`date_processed`,`is_processed`,`is_deleted`) values 
(1,'2017','07-2016','06-2017','3333333333','34a','test','Spark Global Tech Systems Inc.','09124735132','1805A East Tower, Philippine Stock Exchange Centre, Exchange Rd, Ortigas, Pasig City','1604',241002.05,'2017-07-14','2017-07-18',1,0),
(2,'2017','07-2016','06-2017','3333333333','34a','test','Spark Global Tech Systems Inc.','09124735132','1805A East Tower, Philippine Stock Exchange Centre, Exchange Rd, Ortigas, Pasig City','1604',NULL,'2017-07-18','0000-00-00',0,1);

/*Table structure for table `bir_1604_e_schedule_1` */

DROP TABLE IF EXISTS `bir_1604_e_schedule_1`;

CREATE TABLE `bir_1604_e_schedule_1` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bir_1604_e_master_id` bigint(20) DEFAULT NULL,
  `bir_1601_e_master_id` bigint(20) DEFAULT NULL,
  `month` varchar(10) DEFAULT NULL,
  `date_remittance` date DEFAULT NULL,
  `ror_details` varchar(255) DEFAULT NULL,
  `tax_withheld` double DEFAULT NULL,
  `penalties` double DEFAULT NULL,
  `total_amount_remitted` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

/*Data for the table `bir_1604_e_schedule_1` */

insert  into `bir_1604_e_schedule_1`(`id`,`bir_1604_e_master_id`,`bir_1601_e_master_id`,`month`,`date_remittance`,`ror_details`,`tax_withheld`,`penalties`,`total_amount_remitted`) values 
(1,1,6,'11','2017-07-14','this is a test 4',79510,490,80000),
(2,1,5,'12','2017-07-14','this is a test 5',77272.65,728,78000.65),
(3,1,2,'01','2017-07-14','ABC Testing',4305.2,695,5000.2),
(4,1,3,'02','2017-07-14','this is a test 2',9542.7,458,10000.7),
(5,1,4,'03','2017-07-14','this is a test 3',67060.5,940,68000.5),
(6,2,6,'11','2017-07-14',NULL,79510,NULL,NULL),
(7,2,5,'12','2017-07-14',NULL,77272.65,NULL,NULL),
(8,2,2,'01','2017-07-14',NULL,4305.2,NULL,NULL),
(9,2,3,'02','2017-07-14',NULL,9542.7,NULL,NULL),
(10,2,4,'03','2017-07-14',NULL,67060.5,NULL,NULL);

/*Table structure for table `bir_1604_e_schedule_4` */

DROP TABLE IF EXISTS `bir_1604_e_schedule_4`;

CREATE TABLE `bir_1604_e_schedule_4` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bir_1604_e_master_id` bigint(20) DEFAULT NULL,
  `bir_1601_e_details_id` bigint(20) DEFAULT NULL,
  `seq_no` bigint(20) DEFAULT NULL,
  `tin_tax_payer` varchar(50) DEFAULT NULL,
  `name_payees` varchar(255) DEFAULT NULL,
  `atc` varchar(100) DEFAULT NULL,
  `nature_of_income_payment` varchar(255) DEFAULT NULL,
  `tax_base` double DEFAULT NULL,
  `tax_rate` double DEFAULT NULL,
  `tax_withheld` double DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

/*Data for the table `bir_1604_e_schedule_4` */

insert  into `bir_1604_e_schedule_4`(`id`,`bir_1604_e_master_id`,`bir_1601_e_details_id`,`seq_no`,`tin_tax_payer`,`name_payees`,`atc`,`nature_of_income_payment`,`tax_base`,`tax_rate`,`tax_withheld`,`is_deleted`) values 
(1,1,8,NULL,'5234143','Trixia Albelda','WI 011	\r\n','qqqqqqqqqqq',515151,15,77272.65,0),
(2,1,1,NULL,'7371-181764','Trixia Albelda','WI 011	\r\n','This is a test 123',20500,15,3075,0),
(3,1,2,NULL,'6818-1923-12','Trixia Albelda','WC 010\r\n','test22',12302,10,1230.2,0),
(4,1,6,NULL,'000-9823-71029-1','Trixia Marie Albelda','WI 010\r\n','testing',54444,10,5444.4,0);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
