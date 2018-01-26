/*
Navicat MySQL Data Transfer

Source Server         : SGTSI
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : payroll_version_3

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2017-03-13 11:04:35
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `payroll_details`
-- ----------------------------
DROP TABLE IF EXISTS `payroll_details`;
CREATE TABLE `payroll_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payroll_id` varchar(50) DEFAULT NULL,
  `payroll_code` varchar(255) DEFAULT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `tax_compensation` varchar(50) DEFAULT NULL,
  `basic_salary` varchar(50) DEFAULT NULL,
  `late` varchar(50) DEFAULT NULL,
  `absent` varchar(50) DEFAULT NULL,
  `overtime` varchar(50) DEFAULT NULL,
  `tax_allowance` varchar(50) DEFAULT NULL,
  `receivable` varchar(50) DEFAULT NULL,
  `de_minimis` varchar(50) DEFAULT NULL,
  `company_deduction` varchar(50) DEFAULT NULL,
  `government_deduction` varchar(50) DEFAULT NULL,
  `tax_earning` varchar(50) DEFAULT NULL,
  `withholding_tax` varchar(50) DEFAULT NULL,
  `total_deduction` varchar(50) DEFAULT NULL,
  `payroll_adjustment_minus` varchar(50) DEFAULT NULL,
  `payroll_adjustment_plus` varchar(50) DEFAULT NULL,
  `payroll_year` varchar(50) DEFAULT NULL,
  `13_month` varchar(50) DEFAULT NULL,
  `done_13_month` varchar(50) DEFAULT NULL,
  `net_pay` varchar(50) DEFAULT NULL,
  `loan` varchar(50) DEFAULT NULL,
  `is_deleted` varchar(50) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=900 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of payroll_details
-- ----------------------------
INSERT INTO `payroll_details` VALUES ('861', '157', 'P2017010120170110', '8', 'S', '9000', '0', null, '0.00', '0.00', '0.00', '0.00', '0.00', '396.9', '10,060.80', '0.00', '396.90', null, '0.00', '2017', '750.00', null, '9663.9', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('862', '157', 'P2017010120170110', '16', 'S', '10000', '65.91', null, '0.00', '0.00', '0.00', '0.00', '0.00', '125.5', '11,109.29', '0.00', '125.50', null, '0.00', '2017', '827.84', null, '10983.79', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('863', '157', 'P2017010120170110', '18', 'S', '10000', '313.01', null, '0.00', '0.00', '0.00', '0.00', '0.00', '125.5', '10,862.19', '0.00', '125.50', null, '0.00', '2017', '807.25', null, '10736.69', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('864', '157', 'P2017010120170110', '28', 'ME2', '15000', '306.57', null, '0.00', '0.00', '0.00', '0.00', '0.00', '323.5', '16,461.43', '0.00', '323.50', null, '0.00', '2017', '1,224.45', null, '16137.93', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('865', '157', 'P2017010120170110', '30', 'S', '10000', '0', null, '0.00', '0.00', '0.00', '0.00', '0.00', '125.5', '13,525.60', '2,391.01', '2,516.51', null, '0.00', '2017', '833.33', null, '7675.7566666667', '3,333.33', '0');
INSERT INTO `payroll_details` VALUES ('866', '157', 'P2017010120170110', '34', 'S', '10000', '0', null, '0.00', '0.00', '0.00', '0.00', '0.00', '125.5', '10,000.00', '0.00', '125.50', null, '0.00', '2017', '833.33', null, '9874.5', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('867', '157', 'P2017010120170110', '36', 'ME2', '57500', '748.7700000000001', null, '0.00', '0.00', '0.00', '0.00', '0.00', '390.65', '63,542.43', '17,541.91', '17,932.56', null, '0.00', '2017', '4,729.27', null, '45609.8724', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('868', '157', 'P2017010120170110', '38', 'S', '13500', '156.06', null, '0.00', '0.00', '0.00', '0.00', '0.00', '295.75', '14,935.14', '2,813.87', '3,109.62', null, '0.00', '2017', '1,112.00', null, '11825.518', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('869', '158', 'P2017011120170120', '8', 'S', '9000', '696.6600000000001', null, '0.00', '0.00', '0.00', '0.00', '0.00', '396.9', '9,761.94', '998.71', '1,395.61', null, '0.00', '2017', '691.95', null, '8366.3292', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('870', '158', 'P2017011120170120', '16', 'S', '10000', '154.43', null, '0.00', '0.00', '0.00', '0.00', '0.00', '125.5', '12,195.97', '0.00', '125.50', null, '0.00', '2017', '820.46', null, '12070.47', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('871', '158', 'P2017011120170120', '18', 'S', '10000', '1', null, '0.00', '0.00', '0.00', '0.00', '0.00', '125.5', '11,174.20', '0.00', '125.50', null, '0.00', '2017', '833.25', null, '11048.7', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('872', '158', 'P2017011120170120', '28', 'ME2', '15000', '3', null, '0.00', '0.00', '0.00', '0.00', '0.00', '323.5', '16,765.00', '0.00', '323.50', null, '0.00', '2017', '1,249.75', null, '16441.5', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('873', '158', 'P2017011120170120', '30', 'S', '10000', '48.59', null, '0.00', '0.00', '0.00', '0.00', '0.00', '125.5', '13,477.01', '2,376.43', '2,501.93', null, '0.00', '2017', '829.28', null, '7641.7436666667', '3,333.33', '0');
INSERT INTO `payroll_details` VALUES ('874', '158', 'P2017011120170120', '34', 'S', '10000', '99.81', null, '0.00', '0.00', '0.00', '0.00', '0.00', '125.5', '11,075.39', '0.00', '125.50', null, '0.00', '2017', '825.02', null, '10949.89', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('875', '158', 'P2017011120170120', '36', 'ME2', '57500', '350.44', null, '0.00', '0.00', '0.00', '0.00', '0.00', '390.65', '70,731.96', '19,842.56', '20,233.21', null, '0.00', '2017', '4,762.46', null, '50498.7528', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('876', '158', 'P2017011120170120', '38', 'S', '13500', '1462.17', null, '0.00', '0.00', '0.00', '0.00', '0.00', '295.75', '14,822.43', '2,780.06', '3,075.81', null, '0.00', '2017', '1,003.15', null, '11746.621', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('877', '159', 'P2017012120170131', '8', 'S', '9000', '207.4', null, '0.00', '0.00', '0.00', '0.00', '0.00', '396.9', '9,853.40', '1,027.98', '1,424.88', null, '0.00', '2017', '732.72', null, '8428.522', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('878', '159', 'P2017012120170131', '16', 'S', '10000', '184.56', null, '0.00', '0.00', '0.00', '0.00', '0.00', '125.5', '10,990.64', '0.00', '125.50', null, '0.00', '2017', '817.95', null, '10865.14', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('879', '159', 'P2017012120170131', '18', 'S', '10000', '627.15', null, '0.00', '0.00', '0.00', '0.00', '0.00', '125.5', '10,548.05', '0.00', '125.50', null, '0.00', '2017', '781.07', null, '10422.55', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('880', '159', 'P2017012120170131', '28', 'ME2', '15000', '1265.8', null, '0.00', '0.00', '0.00', '0.00', '0.00', '323.5', '14,397.20', '0.00', '323.50', null, '0.00', '2017', '1,144.52', null, '14073.7', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('881', '159', 'P2017012120170131', '30', 'S', '10000', '0', null, '0.00', '0.00', '0.00', '0.00', '0.00', '125.5', '13,525.60', '2,391.01', '2,516.51', null, '0.00', '2017', '833.33', null, '7675.7566666667', '3,333.33', '0');
INSERT INTO `payroll_details` VALUES ('882', '159', 'P2017012120170131', '34', 'S', '10000', '84.75', null, '0.00', '0.00', '0.00', '0.00', '0.00', '125.5', '11,090.45', '0.00', '125.50', null, '0.00', '2017', '826.27', null, '10964.95', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('883', '159', 'P2017012120170131', '36', 'ME2', '57500', '404.85999999999996', null, '0.00', '0.00', '0.00', '0.00', '0.00', '390.65', '63,886.34', '17,651.96', '18,042.61', null, '0.00', '2017', '4,757.93', null, '45843.7312', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('884', '159', 'P2017012120170131', '38', 'S', '13500', '852.21', null, '0.00', '0.00', '0.00', '0.00', '0.00', '295.75', '14,238.99', '2,605.03', '2,900.78', null, '0.00', '2017', '1,053.98', null, '11338.213', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('885', '160', 'P2017020120170210', '8', 'S', '9000', '0', null, '382.50', '0.00', '0.00', '0.00', '0.00', '396.9', '10,443.30', '0.00', '396.90', null, '0.00', '2017', '750.00', null, '10046.4', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('886', '160', 'P2017020120170210', '16', 'S', '10000', '28.25', null, '0.00', '0.00', '0.00', '0.00', '0.00', '125.5', '11,146.95', '0.00', '125.50', null, '0.00', '2017', '830.98', null, '11021.45', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('887', '160', 'P2017020120170210', '18', 'S', '10000', '627.1500000000001', null, '0.00', '0.00', '0.00', '0.00', '0.00', '125.5', '16,062.45', '3,152.07', '3,277.57', null, '0.00', '2017', '781.07', null, '12784.885', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('888', '160', 'P2017020120170210', '28', 'ME2', '15000', '1354.9', null, '1,309.00', '0.00', '0.00', '0.00', '0.00', '323.5', '25,698.10', '5,431.72', '5,755.22', null, '0.00', '2017', '1,137.09', null, '19942.878', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('889', '160', 'P2017020120170210', '30', 'S', '10000', '59.89', null, '1,661.10', '0.00', '0.00', '0.00', '0.00', '125.5', '13,951.61', '2,518.81', '2,644.31', null, '0.00', '2017', '828.34', null, '7973.96366667', '3,333.33', '0');
INSERT INTO `payroll_details` VALUES ('890', '160', 'P2017020120170210', '34', 'S', '10000', '82.86', null, '0.00', '0.00', '0.00', '0.00', '0.00', '125.5', '11,092.34', '0.00', '125.50', null, '0.00', '2017', '826.43', null, '10966.84', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('891', '160', 'P2017020120170210', '36', 'ME2', '57500', '1244.8700000000001', null, '0.00', '0.00', '0.00', '0.00', '0.00', '390.65', '88,186.83', '25,428.12', '25,818.77', null, '0.00', '2017', '4,687.93', null, '62368.0644', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('892', '160', 'P2017020120170210', '38', 'S', '13500', '1119.96', null, '0.00', '0.00', '0.00', '0.00', '0.00', '295.75', '21,254.04', '958.55', '1,254.30', null, '0.00', '2017', '1,031.67', null, '19999.738', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('893', '161', 'P2017021120170220', '8', 'S', '9000', '150.96', null, '0.00', '0.00', '0.00', '0.00', '0.00', '396.9', '9,909.84', '1,046.04', '1,442.94', null, '0.00', '2017', '737.42', null, '8466.9012', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('894', '161', 'P2017021120170220', '16', 'S', '10000', '73.44', null, '0.00', '0.00', '0.00', '0.00', '0.00', '125.5', '11,101.76', '0.00', '125.50', null, '0.00', '2017', '827.21', null, '10976.26', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('895', '161', 'P2017021120170220', '18', 'S', '10000', '28.25', null, '0.00', '0.00', '0.00', '0.00', '0.00', '125.5', '11,146.95', '0.00', '125.50', null, '0.00', '2017', '830.98', null, '11021.45', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('896', '161', 'P2017021120170220', '28', 'ME2', '15000', '235.17000000000002', null, '0.00', '0.00', '0.00', '0.00', '0.00', '323.5', '16,532.83', '0.00', '323.50', null, '0.00', '2017', '1,230.40', null, '16209.33', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('897', '161', 'P2017021120170220', '30', 'S', '10000', '0', null, '0.00', '0.00', '0.00', '0.00', '0.00', '125.5', '12,350.40', '0.00', '125.50', null, '0.00', '2017', '833.33', null, '12224.9', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('898', '161', 'P2017021120170220', '34', 'S', '10000', '197.75', null, '0.00', '0.00', '0.00', '0.00', '0.00', '125.5', '10,977.45', '0.00', '125.50', null, '0.00', '2017', '816.85', null, '10851.95', '0.00', '0');
INSERT INTO `payroll_details` VALUES ('899', '161', 'P2017021120170220', '38', 'S', '13500', '1108.23', null, '0.00', '0.00', '0.00', '0.00', '0.00', '295.75', '13,585.17', '2,408.88', '2,704.63', null, '0.00', '2017', '1,032.65', null, '10880.539', '0.00', '0');
