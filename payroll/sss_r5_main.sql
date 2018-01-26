/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : secret_6_payroll_fusion

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2017-07-19 15:07:56
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `sss_r5_main`
-- ----------------------------
DROP TABLE IF EXISTS `sss_r5_main`;
CREATE TABLE `sss_r5_main` (
  `ref_no` bigint(20) NOT NULL AUTO_INCREMENT,
  `ss_contribution` varchar(255) DEFAULT NULL,
  `ec_contribution` varchar(255) DEFAULT NULL,
  `for_date_of` date DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `amt_ss_contribution` varchar(255) DEFAULT NULL,
  `amt_ec_contribution` varchar(255) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `w_underpayment` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`ref_no`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of sss_r5_main
-- ----------------------------
INSERT INTO `sss_r5_main` VALUES ('1', '20500.23', '900.00', '2017-07-01', '1', '20500.23', '900.00', null, '0');
INSERT INTO `sss_r5_main` VALUES ('2', '20500.23', '900.00', '2017-06-01', '0', '20500.23', '900.00', '', '0');
INSERT INTO `sss_r5_main` VALUES ('3', '20500.23', '900.00', '2017-05-01', '1', '20500.23', '900.00', null, '0');
INSERT INTO `sss_r5_main` VALUES ('4', '20500.23', '900.00', '2017-04-01', '1', '20500.23', '900.00', null, '0');
INSERT INTO `sss_r5_main` VALUES ('5', '20500.23', '900.00', '2017-03-01', '1', '20500.23', '900.00', null, '0');
INSERT INTO `sss_r5_main` VALUES ('6', '20500.23', '900.00', '2016-03-01', '1', '20500.23', '900.00', null, '0');
INSERT INTO `sss_r5_main` VALUES ('7', '20500.23', '900.00', '2017-07-01', '1', '20500.23', '900.00', null, '0');
INSERT INTO `sss_r5_main` VALUES ('13', '12320.00', '70.00', '2017-07-01', '0', '12320.00', '70.00', '', '0');
INSERT INTO `sss_r5_main` VALUES ('23', '12320.00', '70.00', '2017-07-01', '0', '12320.00', '70.00', '', '0');
