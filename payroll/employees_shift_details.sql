/*
Navicat MySQL Data Transfer

Source Server         : SGTSI
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : payroll_version_3

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2017-02-15 15:36:42
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `employees_shift_details`
-- ----------------------------
DROP TABLE IF EXISTS `employees_shift_details`;
CREATE TABLE `employees_shift_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_shift_master_id` bigint(20) DEFAULT NULL,
  `employee_id` bigint(20) DEFAULT NULL,
  `is_deleted` varchar(255) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of employees_shift_details
-- ----------------------------
INSERT INTO `employees_shift_details` VALUES ('46', '19', '4', '1');
INSERT INTO `employees_shift_details` VALUES ('47', '19', '6', '1');
INSERT INTO `employees_shift_details` VALUES ('48', '19', '9', '1');
INSERT INTO `employees_shift_details` VALUES ('49', '20', '4', '0');
INSERT INTO `employees_shift_details` VALUES ('50', '20', '6', '0');
INSERT INTO `employees_shift_details` VALUES ('51', '19', '35', '1');
INSERT INTO `employees_shift_details` VALUES ('52', '19', '34', '1');
INSERT INTO `employees_shift_details` VALUES ('53', '19', '31', '1');
INSERT INTO `employees_shift_details` VALUES ('54', '19', '36', '1');
INSERT INTO `employees_shift_details` VALUES ('55', '19', '37', '1');
INSERT INTO `employees_shift_details` VALUES ('56', '19', '38', '1');
INSERT INTO `employees_shift_details` VALUES ('57', '19', '39', '1');
INSERT INTO `employees_shift_details` VALUES ('58', '19', '41', '1');
INSERT INTO `employees_shift_details` VALUES ('59', '20', '12', '0');
INSERT INTO `employees_shift_details` VALUES ('60', '20', '13', '0');
INSERT INTO `employees_shift_details` VALUES ('61', '20', '41', '0');
INSERT INTO `employees_shift_details` VALUES ('62', '20', '14', '0');
INSERT INTO `employees_shift_details` VALUES ('63', '20', '15', '0');
INSERT INTO `employees_shift_details` VALUES ('64', '20', '8', '0');
INSERT INTO `employees_shift_details` VALUES ('65', '20', '9', '0');
INSERT INTO `employees_shift_details` VALUES ('66', '19', '5', '1');
INSERT INTO `employees_shift_details` VALUES ('67', '19', '8', '1');
INSERT INTO `employees_shift_details` VALUES ('68', '20', '18', '0');
INSERT INTO `employees_shift_details` VALUES ('69', '20', '28', '0');
INSERT INTO `employees_shift_details` VALUES ('70', '26', '5', '1');
INSERT INTO `employees_shift_details` VALUES ('71', '27', '15', '1');
INSERT INTO `employees_shift_details` VALUES ('72', '19', '14', '1');
INSERT INTO `employees_shift_details` VALUES ('73', '28', '14', '1');
INSERT INTO `employees_shift_details` VALUES ('74', '29', '14', '1');
INSERT INTO `employees_shift_details` VALUES ('81', '36', '9', '1');
INSERT INTO `employees_shift_details` VALUES ('82', '37', '8', '0');
INSERT INTO `employees_shift_details` VALUES ('83', '38', '36', '0');
INSERT INTO `employees_shift_details` VALUES ('84', '39', '41', '1');
INSERT INTO `employees_shift_details` VALUES ('85', '40', '16', '0');
INSERT INTO `employees_shift_details` VALUES ('86', '40', '17', '0');
INSERT INTO `employees_shift_details` VALUES ('87', '40', '18', '0');
INSERT INTO `employees_shift_details` VALUES ('88', '40', '28', '0');
INSERT INTO `employees_shift_details` VALUES ('89', '40', '21', '0');
INSERT INTO `employees_shift_details` VALUES ('90', '40', '19', '0');
INSERT INTO `employees_shift_details` VALUES ('91', '40', '30', '0');
INSERT INTO `employees_shift_details` VALUES ('92', '40', '31', '0');
INSERT INTO `employees_shift_details` VALUES ('93', '40', '34', '0');
INSERT INTO `employees_shift_details` VALUES ('94', '40', '35', '0');
INSERT INTO `employees_shift_details` VALUES ('95', '41', '41', '0');
INSERT INTO `employees_shift_details` VALUES ('96', '42', '4', '0');
INSERT INTO `employees_shift_details` VALUES ('97', '42', '6', '1');
INSERT INTO `employees_shift_details` VALUES ('98', '42', '9', '0');
INSERT INTO `employees_shift_details` VALUES ('99', '42', '13', '0');
INSERT INTO `employees_shift_details` VALUES ('100', '42', '15', '0');
INSERT INTO `employees_shift_details` VALUES ('101', '42', '17', '0');
INSERT INTO `employees_shift_details` VALUES ('102', '42', '28', '0');
INSERT INTO `employees_shift_details` VALUES ('103', '42', '19', '0');
INSERT INTO `employees_shift_details` VALUES ('104', '42', '31', '0');
INSERT INTO `employees_shift_details` VALUES ('105', '42', '35', '0');
INSERT INTO `employees_shift_details` VALUES ('106', '43', '6', '0');

-- ----------------------------
-- Table structure for `employees_shift_master`
-- ----------------------------
DROP TABLE IF EXISTS `employees_shift_master`;
CREATE TABLE `employees_shift_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shift_id` bigint(20) DEFAULT NULL,
  `date_from` varchar(255) DEFAULT NULL,
  `date_to` varchar(255) DEFAULT NULL,
  `date_applied` varchar(255) DEFAULT NULL,
  `is_deleted` varchar(255) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of employees_shift_master
-- ----------------------------
INSERT INTO `employees_shift_master` VALUES ('19', '1', '2017-02-01', '2017-03-01', '2017-02-09', '1');
INSERT INTO `employees_shift_master` VALUES ('20', '9', '2017-03-01', '2017-03-01', '2017-02-09', '0');
INSERT INTO `employees_shift_master` VALUES ('21', null, '2017-02-10', '2017-02-10', '2017-02-10', '0');
INSERT INTO `employees_shift_master` VALUES ('22', null, '2017-02-10', '2017-02-10', '2017-02-10', '0');
INSERT INTO `employees_shift_master` VALUES ('23', null, '2017-02-10', '2017-02-10', '2017-02-10', '0');
INSERT INTO `employees_shift_master` VALUES ('24', null, '2017-02-10', '2017-02-10', '2017-02-10', '0');
INSERT INTO `employees_shift_master` VALUES ('25', null, '2017-02-10', '2017-02-10', '2017-02-10', '0');
INSERT INTO `employees_shift_master` VALUES ('26', '1', '2017-02-01', '2017-03-01', '2017-02-10', '1');
INSERT INTO `employees_shift_master` VALUES ('27', '9', '2017-03-01', '2017-03-01', '2017-02-10', '1');
INSERT INTO `employees_shift_master` VALUES ('28', '1', '2017-04-01', '2017-04-01', '2017-02-10', '1');
INSERT INTO `employees_shift_master` VALUES ('29', '1', '2017-02-10', '2017-02-11', '2017-02-10', '1');
INSERT INTO `employees_shift_master` VALUES ('36', '1', '2017-02-01', '2017-03-01', '2017-02-10', '1');
INSERT INTO `employees_shift_master` VALUES ('37', '1', '2017-02-01', '2017-03-01', '2017-02-10', '0');
INSERT INTO `employees_shift_master` VALUES ('38', '1', '2017-02-01', '2017-03-01', '2017-02-10', '0');
INSERT INTO `employees_shift_master` VALUES ('39', '1', '2017-02-01', '2017-03-01', '2017-02-10', '0');
INSERT INTO `employees_shift_master` VALUES ('40', '9', '2017-02-15', '2017-02-17', '2017-02-10', '0');
INSERT INTO `employees_shift_master` VALUES ('41', '1', '2017-02-01', '2017-03-01', '2017-02-13', '0');
INSERT INTO `employees_shift_master` VALUES ('42', '1', '2017-02-20', '2017-02-20', '2017-02-13', '0');
INSERT INTO `employees_shift_master` VALUES ('43', '1', '2017-02-20', '2017-02-20', '2017-02-13', '0');
INSERT INTO `employees_shift_master` VALUES ('44', null, '2017-02-15', '2017-02-15', '2017-02-15', '0');

-- ----------------------------
-- Table structure for `payroll_adjustments`
-- ----------------------------
DROP TABLE IF EXISTS `payroll_adjustments`;
CREATE TABLE `payroll_adjustments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(50) DEFAULT NULL,
  `payroll_code` varchar(50) DEFAULT NULL,
  `date_created` varchar(50) DEFAULT NULL,
  `date_occur` varchar(50) DEFAULT NULL,
  `amount` varchar(50) DEFAULT NULL,
  `reason` varchar(200) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `adjustment_type` varchar(50) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of payroll_adjustments
-- ----------------------------
INSERT INTO `payroll_adjustments` VALUES ('1', '5', null, '2017-02-14', '2017-02-22', '2001', 'sample', '0', '0', '0');
INSERT INTO `payroll_adjustments` VALUES ('2', '8', null, '2017-02-15', '2017-02-14', '1000', 'test', '1', '0', '0');
INSERT INTO `payroll_adjustments` VALUES ('4', '9', null, '2017-02-15', '2017-02-15', '555', '555', '0', '1', '0');
INSERT INTO `payroll_adjustments` VALUES ('5', '30', null, '2017-02-15', '2017-02-15', '500', 'test2', '1', '0', '0');
INSERT INTO `payroll_adjustments` VALUES ('7', '15', null, '2017-02-15', '2017-03-01', '1501', 'test34', '0', '0', '0');

-- ----------------------------
-- Table structure for `shifts`
-- ----------------------------
DROP TABLE IF EXISTS `shifts`;
CREATE TABLE `shifts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shift_name` varchar(250) DEFAULT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `beginning_time_in` time DEFAULT NULL,
  `beginning_time_out` time DEFAULT NULL,
  `ending_time_in` time DEFAULT NULL,
  `ending_time_out` time DEFAULT NULL,
  `break_one_start` time DEFAULT NULL,
  `break_one_end` time DEFAULT NULL,
  `break_two_start` time DEFAULT NULL,
  `break_two_end` time DEFAULT NULL,
  `break_three_start` time DEFAULT NULL,
  `break_three_end` time DEFAULT NULL,
  `is_deleted` varchar(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of shifts
-- ----------------------------
INSERT INTO `shifts` VALUES ('1', 'Day Shift', '22:01:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '0');
INSERT INTO `shifts` VALUES ('9', 'Mid Shift', '02:00:00', '11:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '0');
INSERT INTO `shifts` VALUES ('10', 'Night Shift', '10:00:00', '07:00:00', null, null, null, null, null, null, null, null, null, null, '0');
INSERT INTO `shifts` VALUES ('14', 'Day Shift', '08:31:00', '17:31:00', null, null, null, null, null, null, null, null, null, null, '1');
INSERT INTO `shifts` VALUES ('15', 'Day Shift', '08:31:00', '17:31:00', null, null, null, null, null, null, null, null, null, null, '1');
INSERT INTO `shifts` VALUES ('16', 'Day Shift', '10:03:00', '19:30:00', null, null, null, null, null, null, null, null, null, null, '1');
INSERT INTO `shifts` VALUES ('17', 'samples123', '01:01:00', '10:00:00', '14:22:00', '14:22:00', '03:33:00', '15:33:00', '04:44:00', '04:44:00', '17:55:00', '05:55:00', '18:06:00', '06:06:00', '0');
