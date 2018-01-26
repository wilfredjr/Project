/*
Navicat MySQL Data Transfer

Source Server         : SGTSI
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : payroll_version_3

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2017-02-08 17:00:38
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `shifts`
-- ----------------------------
DROP TABLE IF EXISTS `shifts`;
CREATE TABLE `shifts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shift_name` varchar(50) DEFAULT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `is_deleted` varchar(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of shifts
-- ----------------------------
INSERT INTO `shifts` VALUES ('1', 'Day Shift', '20:30:00', '05:30:00', '0');
INSERT INTO `shifts` VALUES ('9', 'Mid Shift ', '02:00:00', '11:00:00', '0');
INSERT INTO `shifts` VALUES ('10', 'Night Shift', '10:00:00', '07:00:00', '1');
INSERT INTO `shifts` VALUES ('14', 'CNE12', '08:31:00', '17:31:00', '1');
INSERT INTO `shifts` VALUES ('15', 'CNE1', '08:31:00', '17:31:00', '1');
INSERT INTO `shifts` VALUES ('16', 'CNE1', '10:03:00', '19:30:00', '1');
