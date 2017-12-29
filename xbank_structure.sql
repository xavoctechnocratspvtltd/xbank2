/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MariaDB
 Source Server Version : 100118
 Source Host           : localhost
 Source Database       : xbank2_setup

 Target Server Type    : MariaDB
 Target Server Version : 100118
 File Encoding         : utf-8

 Date: 12/29/2017 12:38:15 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `Comment`
-- ----------------------------
DROP TABLE IF EXISTS `Comment`;
CREATE TABLE `Comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL,
  `account_id` int(11) DEFAULT NULL,
  `narration` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_member_id` (`member_id`),
  KEY `fk_account_id` (`account_id`),
  CONSTRAINT `Comment_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  CONSTRAINT `Comment_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `account_guarantors`
-- ----------------------------
DROP TABLE IF EXISTS `account_guarantors`;
CREATE TABLE `account_guarantors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_account_id` (`account_id`),
  KEY `fk_member_id` (`member_id`),
  CONSTRAINT `account_guarantors_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),
  CONSTRAINT `account_guarantors_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11876 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `accounts`
-- ----------------------------
DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) DEFAULT NULL,
  `OpeningBalanceDr` decimal(20,3) DEFAULT '0.000',
  `OpeningBalanceCr` decimal(20,3) DEFAULT '0.000',
  `ClosingBalance` double DEFAULT '0',
  `CurrentBalanceDr` decimal(20,3) DEFAULT '0.000' COMMENT '	',
  `CurrentInterest` varchar(45) DEFAULT '0',
  `ActiveStatus` tinyint(1) DEFAULT '1',
  `Nominee` varchar(45) DEFAULT NULL,
  `NomineeAge` smallint(6) DEFAULT NULL,
  `RelationWithNominee` varchar(45) DEFAULT NULL,
  `MinorNomineeDOB` varchar(20) DEFAULT NULL,
  `MinorNomineeParentName` varchar(45) DEFAULT NULL,
  `ModeOfOperation` varchar(6) DEFAULT 'Self',
  `member_id` int(11) NOT NULL,
  `DefaultAC` tinyint(1) DEFAULT '0',
  `scheme_id` int(11) DEFAULT NULL,
  `AccountNumber` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `branch_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `CurrentBalanceCr` decimal(20,3) DEFAULT '0.000',
  `LastCurrentInterestUpdatedAt` datetime DEFAULT NULL,
  `intrest_to_account_id` int(11) DEFAULT NULL,
  `Amount` double DEFAULT NULL,
  `LoanInsurranceDate` datetime DEFAULT NULL,
  `dealer_id` int(11) NOT NULL,
  `LockingStatus` tinyint(1) DEFAULT '0',
  `LoanAgainstAccount_id` int(11) DEFAULT NULL,
  `affectsBalanceSheet` tinyint(1) DEFAULT '0',
  `MaturedStatus` tinyint(1) DEFAULT '0',
  `collector_id` int(11) DEFAULT '0',
  `CollectorAccountNumber` varchar(50) DEFAULT NULL,
  `AccountDisplayName` varchar(50) DEFAULT NULL,
  `PAndLGroup` varchar(255) NOT NULL,
  `sig_image_id` int(11) DEFAULT NULL,
  `Group` varchar(255) DEFAULT NULL,
  `account_type` varchar(255) DEFAULT NULL,
  `extra_info` text,
  `mo_id` int(11) DEFAULT NULL,
  `team_id` int(11) DEFAULT NULL,
  `MaturityToAccount_id` int(11) DEFAULT NULL,
  `related_account_id` int(11) DEFAULT NULL,
  `doc_image_id` int(11) DEFAULT NULL,
  `is_dirty` tinyint(4) NOT NULL DEFAULT '0',
  `bike_surrendered` tinyint(4) NOT NULL,
  `bike_surrendered_on` date DEFAULT NULL,
  `is_given_for_legal_process` tinyint(4) DEFAULT '0',
  `legal_process_given_date` date DEFAULT NULL,
  `is_in_legal` tinyint(4) NOT NULL,
  `is_godowncharge_debited` tinyint(4) DEFAULT NULL,
  `godowncharge_debited_on` datetime DEFAULT NULL,
  `is_legal_notice_sent_for_bike_auction` tinyint(4) DEFAULT NULL,
  `legal_notice_sent_for_bike_auction_on` datetime DEFAULT NULL,
  `is_bike_auctioned` tinyint(4) DEFAULT NULL,
  `bike_auctioned_on` datetime DEFAULT NULL,
  `is_final_recovery_notice_sent` tinyint(4) DEFAULT NULL,
  `final_recovery_notice_sent_on` datetime DEFAULT NULL,
  `is_cheque_presented_in_bank` tinyint(4) DEFAULT NULL,
  `cheque_presented_in_bank_on` datetime DEFAULT NULL,
  `is_cheque_returned` tinyint(4) DEFAULT NULL,
  `cheque_returned_on` datetime DEFAULT NULL,
  `is_notice_sent_after_cheque_returned` tinyint(4) DEFAULT NULL,
  `notice_sent_after_cheque_returned_on` datetime DEFAULT NULL,
  `is_legal_case_finalised` tinyint(4) DEFAULT NULL,
  `legal_case_finalised_on` datetime DEFAULT NULL,
  `is_bike_returned` tinyint(4) DEFAULT NULL,
  `bike_returned_on` datetime DEFAULT NULL,
  `bike_not_sold_reason` text,
  `legal_case_not_submitted_reason` text,
  `legal_filing_date` date DEFAULT NULL,
  `repayment_mode` varchar(255) DEFAULT NULL,
  `new_or_renew` varchar(255) DEFAULT NULL,
  `bike_surrendered_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `AccountNumber_UNIQUE` (`AccountNumber`),
  KEY `fk_accounts_agents1` (`agent_id`),
  KEY `fk_accounts_member1` (`member_id`),
  KEY `fk_accounts_schemes1` (`scheme_id`),
  KEY `fk_accounts_branch1` (`branch_id`),
  KEY `fk_accounts_staff1` (`staff_id`)
) ENGINE=InnoDB AUTO_INCREMENT=163991 DEFAULT CHARSET=latin1 COMMENT='Various Accounts for users';

-- ----------------------------
--  Table structure for `accounts_pending`
-- ----------------------------
DROP TABLE IF EXISTS `accounts_pending`;
CREATE TABLE `accounts_pending` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) DEFAULT NULL,
  `OpeningBalanceDr` double DEFAULT '0',
  `OpeningBalanceCr` double DEFAULT '0',
  `ClosingBalance` double DEFAULT '0',
  `CurrentBalanceDr` double DEFAULT '0' COMMENT '	',
  `CurrentInterest` varchar(45) DEFAULT '0',
  `ActiveStatus` tinyint(1) DEFAULT '1',
  `Nominee` varchar(45) DEFAULT NULL,
  `NomineeAge` smallint(6) DEFAULT NULL,
  `RelationWithNominee` varchar(45) DEFAULT NULL,
  `MinorNomineeDOB` varchar(20) DEFAULT NULL,
  `MinorNomineeParentName` varchar(45) DEFAULT NULL,
  `ModeOfOperation` varchar(6) DEFAULT 'Self',
  `member_id` int(11) NOT NULL,
  `DefaultAC` tinyint(1) DEFAULT '0',
  `scheme_id` int(11) DEFAULT NULL,
  `AccountNumber` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `sig_image_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `CurrentBalanceCr` double DEFAULT '0',
  `LastCurrentInterestUpdatedAt` datetime DEFAULT NULL,
  `intrest_to_account_id` int(11) DEFAULT NULL,
  `MaturityToAccount_id` int(11) DEFAULT NULL,
  `Amount` double DEFAULT NULL,
  `LoanInsurranceDate` datetime DEFAULT NULL,
  `dealer_id` int(11) NOT NULL,
  `LockingStatus` tinyint(1) DEFAULT '0',
  `LoanAgainstAccount_id` int(11) DEFAULT NULL,
  `affectsBalanceSheet` tinyint(1) DEFAULT '0',
  `MaturedStatus` tinyint(1) DEFAULT '0',
  `collector_id` int(11) DEFAULT '0',
  `CollectorAccountNumber` varchar(50) DEFAULT NULL,
  `AccountDisplayName` varchar(50) DEFAULT NULL,
  `PAndLGroup` varchar(255) NOT NULL,
  `Group` varchar(255) DEFAULT NULL,
  `account_type` varchar(255) DEFAULT NULL,
  `related_account_id` int(11) DEFAULT NULL,
  `extra_info` text,
  `is_approved` tinyint(1) DEFAULT '0',
  `doc_image_id` int(11) DEFAULT NULL,
  `mo_id` int(11) DEFAULT NULL,
  `team_id` int(11) DEFAULT NULL,
  `is_dirty` tinyint(4) DEFAULT '0',
  `bike_surrendered` tinyint(4) NOT NULL,
  `bike_surrendered_on` tinyint(4) NOT NULL,
  `is_in_legal` tinyint(4) NOT NULL,
  `legal_filing_date` date NOT NULL,
  `repayment_mode` varchar(255) DEFAULT NULL,
  `bike_surrendered_by` varchar(255) DEFAULT NULL,
  `is_godowncharge_debited` tinyint(4) DEFAULT NULL,
  `godowncharge_debited_on` datetime DEFAULT NULL,
  `is_legal_notice_sent_for_bike_auction` tinyint(4) DEFAULT NULL,
  `legal_notice_sent_for_bike_auction_on` datetime DEFAULT NULL,
  `is_bike_auctioned` tinyint(4) DEFAULT NULL,
  `bike_auctioned_on` datetime DEFAULT NULL,
  `is_final_recovery_notice_sent` tinyint(4) DEFAULT NULL,
  `final_recovery_notice_sent_on` datetime DEFAULT NULL,
  `is_cheque_presented_in_bank` tinyint(4) DEFAULT NULL,
  `cheque_presented_in_bank_on` datetime DEFAULT NULL,
  `is_cheque_returned` tinyint(4) DEFAULT NULL,
  `cheque_returned_on` datetime DEFAULT NULL,
  `is_notice_sent_after_cheque_returned` tinyint(4) DEFAULT NULL,
  `notice_sent_after_cheque_returned_on` datetime DEFAULT NULL,
  `is_legal_case_finalised` tinyint(4) DEFAULT NULL,
  `legal_case_finalised_on` datetime DEFAULT NULL,
  `is_bike_returned` tinyint(4) DEFAULT NULL,
  `bike_returned_on` datetime DEFAULT NULL,
  `bike_not_sold_reason` text,
  `legal_case_not_submitted_reason` text,
  `new_or_renew` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_accounts_agents1` (`agent_id`),
  KEY `fk_accounts_member1` (`member_id`),
  KEY `fk_accounts_schemes1` (`scheme_id`),
  KEY `fk_accounts_branch1` (`branch_id`),
  KEY `fk_accounts_staff1` (`staff_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10674 DEFAULT CHARSET=latin1 COMMENT='Various Accounts for users';

-- ----------------------------
--  Table structure for `acls`
-- ----------------------------
DROP TABLE IF EXISTS `acls`;
CREATE TABLE `acls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) DEFAULT NULL,
  `class` varchar(255) DEFAULT NULL,
  `can_view` tinyint(1) DEFAULT NULL,
  `is_all_branch_allowed` tinyint(1) DEFAULT NULL,
  `allow_add` tinyint(1) DEFAULT NULL,
  `allow_edit` tinyint(1) DEFAULT NULL,
  `allow_del` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_staff_id` (`staff_id`),
  CONSTRAINT `acls_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staffs` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7446 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Records of `acls`
-- ----------------------------
BEGIN;
INSERT INTO `acls` VALUES ('7439', '374', 'Model_Premium', '1', '0', '0', '0', '0'), ('7440', '374', 'Model_Active_Account_DDS', '1', '0', '0', '0', '0'), ('7441', '374', 'Model_Active_Account_Recurring', '1', '0', '0', '0', '0'), ('7442', '374', 'Model_Active_Account_FixedAndMis', '1', '0', '0', '0', '0'), ('7443', '374', 'Model_Active_Account', '1', '0', '0', '0', '0'), ('7444', '374', 'Model_Account', '1', '0', '0', '0', '0'), ('7445', '374', 'Model_Active_Account_Loan', '1', '0', '0', '0', '0');
COMMIT;

-- ----------------------------
--  Table structure for `agent_guarantors`
-- ----------------------------
DROP TABLE IF EXISTS `agent_guarantors`;
CREATE TABLE `agent_guarantors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL,
  `agent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_member_id` (`member_id`),
  KEY `fk_agent_id` (`agent_id`),
  CONSTRAINT `agent_guarantors_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  CONSTRAINT `agent_guarantors_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=428 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `agents`
-- ----------------------------
DROP TABLE IF EXISTS `agents`;
CREATE TABLE `agents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `ActiveStatus` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `AccountNumber` varchar(100) DEFAULT NULL,
  `Gaurantor1Name` varchar(100) DEFAULT NULL,
  `Gaurantor1FatherHusbandName` varchar(100) DEFAULT NULL,
  `Gaurantor1Address` varchar(200) DEFAULT NULL,
  `Gaurantor1Occupation` varchar(100) DEFAULT NULL,
  `Gaurantor2Name` varchar(100) DEFAULT NULL,
  `Gaurantor2FatherHusbandName` varchar(100) DEFAULT NULL,
  `Gaurantor2Address` varchar(200) DEFAULT NULL,
  `Gaurantor2Occupation` varchar(100) DEFAULT NULL,
  `sponsor_id` int(11) DEFAULT NULL,
  `AgentCode` varchar(20) DEFAULT NULL,
  `Path` varchar(200) DEFAULT NULL,
  `LegCount` int(11) DEFAULT '0',
  `Rank` int(11) DEFAULT '0',
  `Tree_id` int(11) DEFAULT '0',
  `BusinessCreditPoints` int(11) DEFAULT '0',
  `CumulativeBusinessCreditPoints` int(11) DEFAULT '0',
  `Rank_1_Count` int(11) DEFAULT '0',
  `Rank_2_Count` int(11) DEFAULT '0',
  `Rank_3_Count` int(11) DEFAULT '0',
  `Rank_4_Count` int(11) DEFAULT '0',
  `Rank_5_Count` int(11) DEFAULT '0',
  `Rank_6_Count` int(11) DEFAULT '0',
  `Rank_7_Count` int(11) DEFAULT '0',
  `Rank_8_Count` int(11) DEFAULT '0',
  `Rank_9_Count` int(11) DEFAULT '0',
  `Rank_10_Count` int(11) DEFAULT '0',
  `Rank_11_Count` int(11) DEFAULT '0',
  `Rank_12_Count` int(11) DEFAULT '0',
  `Rank_13_Count` int(11) DEFAULT '0',
  `Rank_14_Count` int(11) DEFAULT '0',
  `Rank_15_Count` int(11) DEFAULT '0',
  `Rank_16_Count` int(11) DEFAULT '0',
  `Rank_17_Count` int(11) DEFAULT '0',
  `Rank_18_Count` int(11) DEFAULT '0',
  `Rank_19_Count` int(11) DEFAULT '0',
  `Rank_20_Count` int(11) DEFAULT '0',
  `account_id` int(11) DEFAULT NULL,
  `cadre_id` int(11) DEFAULT NULL,
  `current_individual_crpb` int(11) DEFAULT NULL,
  `current_individual_crpb_old` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `added_by` varchar(255) NOT NULL,
  `code_no` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_agents_member1` (`member_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1453 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `balance_sheet`
-- ----------------------------
DROP TABLE IF EXISTS `balance_sheet`;
CREATE TABLE `balance_sheet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `positive_side` varchar(2) NOT NULL,
  `is_pandl` tinyint(4) NOT NULL,
  `show_sub` varchar(20) NOT NULL,
  `subtract_from` varchar(2) NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Records of `balance_sheet`
-- ----------------------------
BEGIN;
INSERT INTO `balance_sheet` VALUES ('1', 'Deposits - Liabilities', 'LT', '0', 'SchemeGroup', 'Cr', '2'), ('2', 'Current Assets', 'RT', '0', 'SchemeGroup', 'Dr', '5'), ('3', 'Capital Account', 'LT', '0', 'SchemeGroup', 'Cr', '1'), ('4', 'Expenses', 'LT', '1', 'Accounts', 'Dr', '9'), ('5', 'Income', 'RT', '1', 'Accounts', 'Cr', '8'), ('6', 'Suspence Account', 'LT', '0', 'SchemeGroup', 'Cr', '7'), ('7', 'Fixed Assets', 'RT', '0', 'SchemeGroup', 'Dr', '4'), ('8', 'Branch/Divisions', 'LT', '0', 'Accounts', 'Cr', '6'), ('9', 'Current Liabilities', 'LT', '0', 'SchemeGroup', 'Cr', '3');
COMMIT;

-- ----------------------------
--  Table structure for `bank`
-- ----------------------------
DROP TABLE IF EXISTS `bank`;
CREATE TABLE `bank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Table structure for `bank_branches`
-- ----------------------------
DROP TABLE IF EXISTS `bank_branches`;
CREATE TABLE `bank_branches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `IFSC` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_bank_id` (`bank_id`) USING BTREE,
  CONSTRAINT `fk_bank_id` FOREIGN KEY (`bank_id`) REFERENCES `bank` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Table structure for `branches`
-- ----------------------------
DROP TABLE IF EXISTS `branches`;
CREATE TABLE `branches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `Address` text,
  `Code` varchar(3) DEFAULT NULL,
  `PerformClosings` tinyint(4) DEFAULT '1',
  `SendSMS` tinyint(4) DEFAULT NULL,
  `published` tinyint(4) DEFAULT '1',
  `next_voucher_no` int(11) NOT NULL,
  `allow_login` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Name_UNIQUE` (`name`),
  UNIQUE KEY `Code_UNIQUE` (`Code`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=latin1 COMMENT='The collection of Branches Information';

-- ----------------------------
--  Records of `branches`
-- ----------------------------
BEGIN;
INSERT INTO `branches` VALUES ('27', 'Default', null, 'DFL', '1', '1', '1', '0', '1');
COMMIT;

-- ----------------------------
--  Table structure for `cadres`
-- ----------------------------
DROP TABLE IF EXISTS `cadres`;
CREATE TABLE `cadres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `percentage_share` int(11) DEFAULT NULL,
  `total_crpb` varchar(255) DEFAULT NULL,
  `req_under` varchar(255) DEFAULT NULL,
  `nextcadre_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `closings`
-- ----------------------------
DROP TABLE IF EXISTS `closings`;
CREATE TABLE `closings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `daily` datetime DEFAULT NULL,
  `weekly` datetime DEFAULT NULL,
  `monthly` datetime DEFAULT NULL,
  `halfyearly` datetime DEFAULT NULL,
  `yearly` datetime DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_closings_branch1` (`branch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `dealers`
-- ----------------------------
DROP TABLE IF EXISTS `dealers`;
CREATE TABLE `dealers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `Address` text,
  `loan_panelty_per_day` varchar(255) DEFAULT NULL,
  `time_over_charge` varchar(255) DEFAULT NULL,
  `dealer_monthly_date` varchar(255) DEFAULT NULL,
  `properitor_name` varchar(255) DEFAULT NULL,
  `properitor_phone_no_1` varchar(255) DEFAULT NULL,
  `properitor_phone_no_2` varchar(255) DEFAULT NULL,
  `email_id_1` varchar(255) DEFAULT NULL,
  `email_id_2` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `dsa_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_dsa_id` (`dsa_id`),
  CONSTRAINT `dealers_ibfk_1` FOREIGN KEY (`dsa_id`) REFERENCES `dsa` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `documents`
-- ----------------------------
DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `SavingAccount` tinyint(1) DEFAULT NULL,
  `FixedMISAccount` tinyint(1) DEFAULT NULL,
  `LoanAccount` tinyint(1) DEFAULT NULL,
  `RDandDDSAccount` tinyint(1) DEFAULT NULL,
  `CCAccount` tinyint(1) DEFAULT NULL,
  `OtherAccounts` tinyint(1) DEFAULT NULL,
  `MemberDocuments` tinyint(1) DEFAULT NULL,
  `AgentDocuments` tinyint(1) DEFAULT NULL,
  `DSADocuments` tinyint(1) DEFAULT NULL,
  `AgentGuarantor` tinyint(1) DEFAULT NULL,
  `DSAGuarantor` tinyint(1) DEFAULT NULL,
  `is_addable_by_staff` tinyint(1) DEFAULT NULL,
  `is_editable_by_staff` tinyint(1) DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `documents_submitted`
-- ----------------------------
DROP TABLE IF EXISTS `documents_submitted`;
CREATE TABLE `documents_submitted` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accounts_id` int(11) DEFAULT NULL,
  `documents_id` int(11) DEFAULT NULL,
  `Description` text,
  `member_id` int(11) DEFAULT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `dealer_id` int(11) DEFAULT NULL,
  `agentguarantor_id` int(11) DEFAULT NULL,
  `dsa_id` int(11) DEFAULT NULL,
  `dsaguarantor_id` int(11) DEFAULT NULL,
  `doc_image_id` int(11) DEFAULT NULL,
  `submitted_on` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_documents_submitted_documents1` (`documents_id`),
  KEY `fk_documents_submitted_accounts1` (`accounts_id`),
  KEY `fk_member_id` (`member_id`),
  KEY `fk_agent_id` (`agent_id`),
  KEY `fk_dealer_id` (`dealer_id`),
  KEY `fk_agentguarantor_id` (`agentguarantor_id`),
  KEY `fk_dsa_id` (`dsa_id`),
  KEY `fk_dsaguarantor_id` (`dsaguarantor_id`),
  CONSTRAINT `documents_submitted_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  CONSTRAINT `documents_submitted_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`),
  CONSTRAINT `documents_submitted_ibfk_3` FOREIGN KEY (`dealer_id`) REFERENCES `dealers` (`id`),
  CONSTRAINT `documents_submitted_ibfk_4` FOREIGN KEY (`agentguarantor_id`) REFERENCES `agent_guarantors` (`id`),
  CONSTRAINT `documents_submitted_ibfk_5` FOREIGN KEY (`dsa_id`) REFERENCES `dsa` (`id`),
  CONSTRAINT `documents_submitted_ibfk_6` FOREIGN KEY (`dsaguarantor_id`) REFERENCES `dsa_guarantors` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=142850 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `dsa`
-- ----------------------------
DROP TABLE IF EXISTS `dsa`;
CREATE TABLE `dsa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone_no_1` varchar(255) DEFAULT NULL,
  `phone_no_2` varchar(255) DEFAULT NULL,
  `email_id_1` varchar(255) DEFAULT NULL,
  `email_id_2` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_member_id` (`member_id`),
  CONSTRAINT `dsa_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `dsa_guarantors`
-- ----------------------------
DROP TABLE IF EXISTS `dsa_guarantors`;
CREATE TABLE `dsa_guarantors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL,
  `dsa_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_member_id` (`member_id`),
  KEY `fk_dsa_id` (`dsa_id`),
  CONSTRAINT `dsa_guarantors_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  CONSTRAINT `dsa_guarantors_ibfk_2` FOREIGN KEY (`dsa_id`) REFERENCES `dsa` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `employee_salary_record`
-- ----------------------------
DROP TABLE IF EXISTS `employee_salary_record`;
CREATE TABLE `employee_salary_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `month` varchar(255) DEFAULT NULL,
  `year` varchar(255) DEFAULT NULL,
  `total_days` varchar(255) DEFAULT NULL,
  `paid_days` varchar(255) DEFAULT NULL,
  `leave` varchar(255) DEFAULT NULL,
  `salary` varchar(255) DEFAULT NULL,
  `ded` varchar(255) DEFAULT NULL,
  `pf_amount` varchar(255) DEFAULT NULL,
  `other_allowance` varchar(255) DEFAULT NULL,
  `incentive` varchar(255) NOT NULL,
  `allow_paid` varchar(255) DEFAULT NULL,
  `net_payable` varchar(255) DEFAULT NULL,
  `narration` text,
  `pf_salary` varchar(255) DEFAULT NULL,
  `CL` varchar(255) DEFAULT NULL,
  `CCL` varchar(255) DEFAULT NULL,
  `LWP` varchar(255) DEFAULT NULL,
  `ABSENT` varchar(255) DEFAULT NULL,
  `monthly_off` varchar(255) DEFAULT NULL,
  `total_month_day` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_branch_id` (`branch_id`),
  KEY `fk_employee_id` (`employee_id`),
  CONSTRAINT `employee_salary_record_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  CONSTRAINT `employee_salary_record_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `xbank_employees` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2729 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `filestore_file`
-- ----------------------------
DROP TABLE IF EXISTS `filestore_file`;
CREATE TABLE `filestore_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filestore_type_id` int(11) NOT NULL DEFAULT '0',
  `filestore_volume_id` int(11) NOT NULL DEFAULT '0',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `original_filename` varchar(255) DEFAULT NULL,
  `filesize` int(11) NOT NULL DEFAULT '0',
  `filenum` int(11) NOT NULL DEFAULT '0',
  `deleted` varchar(2) DEFAULT 'N',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=186404 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `filestore_image`
-- ----------------------------
DROP TABLE IF EXISTS `filestore_image`;
CREATE TABLE `filestore_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `original_file_id` int(11) NOT NULL DEFAULT '0',
  `thumb_file_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=128245 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `filestore_type`
-- ----------------------------
DROP TABLE IF EXISTS `filestore_type`;
CREATE TABLE `filestore_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `mime_type` varchar(64) NOT NULL DEFAULT '',
  `extension` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `filestore_volume`
-- ----------------------------
DROP TABLE IF EXISTS `filestore_volume`;
CREATE TABLE `filestore_volume` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '',
  `dirname` varchar(255) NOT NULL DEFAULT '',
  `total_space` bigint(20) NOT NULL DEFAULT '0',
  `used_space` bigint(20) NOT NULL DEFAULT '0',
  `stored_files_cnt` int(11) NOT NULL DEFAULT '0',
  `enabled` enum('Y','N') DEFAULT 'Y',
  `last_filenum` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `jointmembers`
-- ----------------------------
DROP TABLE IF EXISTS `jointmembers`;
CREATE TABLE `jointmembers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_account_id` (`account_id`),
  KEY `fk_member_id` (`member_id`),
  CONSTRAINT `jointmembers_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),
  CONSTRAINT `jointmembers_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=218 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xaccess_system`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xaccess_system`;
CREATE TABLE `jos_xaccess_system` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_access_system_staff1` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xagentcommissionreport`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xagentcommissionreport`;
CREATE TABLE `jos_xagentcommissionreport` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agents_id` int(11) DEFAULT '0',
  `collector_id` int(11) DEFAULT '0',
  `accounts_id` int(11) DEFAULT NULL,
  `Commission` varchar(10) DEFAULT '0',
  `Collection` varchar(10) DEFAULT '0',
  `CommissionPayableDate` datetime DEFAULT NULL,
  `CommissionPaidDate` datetime DEFAULT NULL,
  `Narration` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `jos_xatk_attendance`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xatk_attendance`;
CREATE TABLE `jos_xatk_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` int(11) NOT NULL,
  `Created_at` date NOT NULL,
  `TimeHour` int(11) NOT NULL,
  `TimeMinute` int(11) NOT NULL,
  `Mode` varchar(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xatk_emphistrory`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xatk_emphistrory`;
CREATE TABLE `jos_xatk_emphistrory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Created_At` date NOT NULL,
  `Post` varchar(255) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `remarks` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xatk_employee`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xatk_employee`;
CREATE TABLE `jos_xatk_employee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `FatherName` varchar(255) NOT NULL,
  `PresentAddress` text NOT NULL,
  `PermanentAddress` text NOT NULL,
  `MobileNo` int(11) NOT NULL,
  `LandlineNo` int(11) NOT NULL,
  `DOB` date NOT NULL,
  `OtherDetails` text NOT NULL,
  `Salary` int(11) NOT NULL,
  `Allownces` int(11) NOT NULL,
  `PFSalary` int(11) NOT NULL,
  `isPFApplicable` tinyint(4) NOT NULL DEFAULT '0',
  `PFAmount` int(11) NOT NULL,
  `TDSAmount` int(11) NOT NULL,
  `Account_Number` int(11) NOT NULL,
  `Bank_Name` varchar(255) NOT NULL,
  `SalaryMode` int(11) NOT NULL,
  `is_Active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xatk_holidays`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xatk_holidays`;
CREATE TABLE `jos_xatk_holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) NOT NULL,
  `HolidayDate` datetime NOT NULL,
  `Remark` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xatk_leaves_alloted`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xatk_leaves_alloted`;
CREATE TABLE `jos_xatk_leaves_alloted` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` int(11) NOT NULL,
  `Created_At` datetime NOT NULL,
  `Leaves` int(11) NOT NULL,
  `Narretion` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xatk_leaves_used`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xatk_leaves_used`;
CREATE TABLE `jos_xatk_leaves_used` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` int(11) NOT NULL,
  `Created_At` datetime NOT NULL,
  `leaves` int(11) NOT NULL,
  `Narretion` text NOT NULL,
  `isPaid` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xatk_payment`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xatk_payment`;
CREATE TABLE `jos_xatk_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` int(11) NOT NULL,
  `TotalWorkingDays` int(11) NOT NULL,
  `PresentDays` int(11) NOT NULL,
  `HoliDays` int(11) NOT NULL,
  `Sundays` int(11) NOT NULL,
  `Leaves` int(11) NOT NULL,
  `LeavesPaid` int(11) NOT NULL,
  `Absent` int(11) NOT NULL,
  `Salary` float(11,2) NOT NULL,
  `PFAmount` int(11) NOT NULL,
  `Deduction` int(11) NOT NULL,
  `MonthYear` varchar(6) NOT NULL,
  `Narration` text NOT NULL,
  `Created_At` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xbalance_sheet_old`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xbalance_sheet_old`;
CREATE TABLE `jos_xbalance_sheet_old` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Head` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xbank_holidays`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xbank_holidays`;
CREATE TABLE `jos_xbank_holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `HolidayDate` date DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `branch_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_bank_holidays_branch1` (`branch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xcategory`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xcategory`;
CREATE TABLE `jos_xcategory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(45) DEFAULT NULL,
  `Description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xcideveloper_projects`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xcideveloper_projects`;
CREATE TABLE `jos_xcideveloper_projects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `component` varchar(50) CHARACTER SET utf8 NOT NULL,
  `com_name` varchar(50) CHARACTER SET utf8 NOT NULL,
  `extension_type` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `params` text CHARACTER SET utf8,
  `published` tinyint(4) DEFAULT NULL,
  `manifest` text CHARACTER SET utf8,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xcommissionslab`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xcommissionslab`;
CREATE TABLE `jos_xcommissionslab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Rank` int(11) DEFAULT NULL,
  `AdvisorLevel` varchar(100) DEFAULT NULL,
  `TotalCreditBusinessForPromotion` bigint(20) DEFAULT NULL,
  `TotalAdvisors` varchar(100) DEFAULT NULL,
  `Level1` int(11) DEFAULT NULL,
  `Level2` int(11) DEFAULT NULL,
  `Level3` int(11) DEFAULT NULL,
  `Level4` int(11) DEFAULT NULL,
  `Level5` int(11) DEFAULT NULL,
  `Level6` int(11) DEFAULT NULL,
  `Level7` int(11) DEFAULT NULL,
  `Level8` int(11) DEFAULT NULL,
  `Level9` int(11) DEFAULT NULL,
  `Level10` int(11) DEFAULT NULL,
  `Level11` int(11) DEFAULT NULL,
  `Level12` int(11) DEFAULT NULL,
  `Level13` int(11) DEFAULT NULL,
  `Level14` int(11) DEFAULT NULL,
  `Level15` int(11) DEFAULT NULL,
  `Level16` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `jos_xconfig`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xconfig`;
CREATE TABLE `jos_xconfig` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(50) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xevents`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xevents`;
CREATE TABLE `jos_xevents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Event` varchar(45) DEFAULT NULL,
  `CodeSQL` text,
  `schemes_id` int(11) NOT NULL,
  `Sno` smallint(6) DEFAULT NULL,
  `Description` text,
  `ActiveStatus` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`,`schemes_id`),
  KEY `fk_events_schemes1` (`schemes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xitems`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xitems`;
CREATE TABLE `jos_xitems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(45) DEFAULT NULL,
  `Price` float DEFAULT NULL,
  `Description` text,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_items_category1` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=159 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xjointmembers`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xjointmembers`;
CREATE TABLE `jos_xjointmembers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accounts_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xlog`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xlog`;
CREATE TABLE `jos_xlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Message` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `accounts_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `staff_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_log_accounts1` (`accounts_id`)
) ENGINE=InnoDB AUTO_INCREMENT=43971 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xreports`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xreports`;
CREATE TABLE `jos_xreports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(45) DEFAULT NULL,
  `formFields` text,
  `CodeToRun` text,
  `Results` text,
  `CodeBeforeForm` text,
  `published` tinyint(4) DEFAULT NULL,
  `ReportTitle` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xschemes_old`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xschemes_old`;
CREATE TABLE `jos_xschemes_old` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(45) DEFAULT NULL,
  `MinLimit` double DEFAULT NULL,
  `MaxLimit` double DEFAULT NULL,
  `Interest` varchar(45) DEFAULT NULL,
  `InterestMode` varchar(45) DEFAULT NULL,
  `InterestRateMode` varchar(45) DEFAULT NULL,
  `LoanType` tinyint(1) DEFAULT NULL,
  `AccountOpenningCommission` varchar(200) DEFAULT '0',
  `Commission` double DEFAULT NULL,
  `ActiveStatus` tinyint(1) DEFAULT NULL,
  `branch_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ProcessingFees` double DEFAULT NULL,
  `balance_sheet_id` int(11) NOT NULL,
  `PostingMode` varchar(45) DEFAULT NULL COMMENT 'Y,HF,Q,M...',
  `PremiumMode` varchar(45) DEFAULT NULL,
  `CreateDefaultAccount` tinyint(1) DEFAULT NULL,
  `SchemeType` varchar(45) DEFAULT NULL,
  `InterestToAnotherAccount` tinyint(1) DEFAULT '0',
  `NumberOfPremiums` int(11) DEFAULT NULL,
  `MaturityPeriod` int(11) DEFAULT NULL,
  `InterestToAnotherAccountPercent` varchar(45) DEFAULT NULL,
  `isDepriciable` tinyint(4) DEFAULT '0',
  `DepriciationPercentBeforeSep` varchar(45) DEFAULT NULL,
  `DepriciationPercentAfterSep` varchar(45) DEFAULT NULL,
  `ProcessingFeesinPercent` tinyint(1) DEFAULT '0' COMMENT 'whether the processing fees for accounts is in percentage',
  `published` tinyint(1) DEFAULT '1',
  `SchemePoints` float(11,0) DEFAULT '0',
  `AgentSponsorCommission` varchar(2500) DEFAULT NULL,
  `CollectorCommissionRate` varchar(255) DEFAULT '0',
  `ReducingOrFlatRate` varchar(45) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_account_type_branch1` (`branch_id`),
  KEY `fk_schemes_balance_sheet1` (`balance_sheet_id`)
) ENGINE=InnoDB AUTO_INCREMENT=146 DEFAULT CHARSET=latin1 COMMENT='Various Accounts that a bank can manage';

-- ----------------------------
--  Table structure for `jos_xstaff_attendance`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xstaff_attendance`;
CREATE TABLE `jos_xstaff_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Date` datetime DEFAULT NULL,
  `Attendance` varchar(45) DEFAULT NULL,
  `Narration` varchar(200) DEFAULT NULL,
  `staff_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_staff_attendance_staff1` (`staff_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3171 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xstaff_details`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xstaff_details`;
CREATE TABLE `jos_xstaff_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `JoiningDate` datetime DEFAULT NULL,
  `BasicPay` varchar(45) DEFAULT NULL,
  `PF` varchar(45) DEFAULT NULL,
  `VariablePay` varchar(45) DEFAULT NULL,
  `SavingAccount` varchar(45) DEFAULT NULL,
  `staff_id` int(11) NOT NULL,
  `Name` varchar(45) DEFAULT NULL,
  `FatherName` varchar(45) DEFAULT NULL,
  `PresentAddress` varchar(200) DEFAULT NULL,
  `PermanentAddress` varchar(200) DEFAULT NULL,
  `MobileNo` varchar(15) DEFAULT NULL,
  `LandlineNo` varchar(25) DEFAULT NULL,
  `DOB` datetime DEFAULT NULL,
  `OtherDetails` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_staff_details_staff1` (`staff_id`)
) ENGINE=InnoDB AUTO_INCREMENT=175 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xstaff_payments`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xstaff_payments`;
CREATE TABLE `jos_xstaff_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Date` datetime DEFAULT NULL,
  `Payment` varchar(45) DEFAULT NULL,
  `PaymentAgainst` varchar(45) DEFAULT NULL,
  `staff_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_staff_payments_staff1` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xstock`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xstock`;
CREATE TABLE `jos_xstock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=535 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xstock_consume`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xstock_consume`;
CREATE TABLE `jos_xstock_consume` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `Quantity` float NOT NULL,
  `remarks` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8729 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xstock_log`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xstock_log`;
CREATE TABLE `jos_xstock_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `StockAllotedDate` datetime DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `QuantityAlloted` int(11) DEFAULT NULL,
  `StockStatus` int(11) DEFAULT NULL,
  `items_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_stock_log_items1` (`items_id`)
) ENGINE=InnoDB AUTO_INCREMENT=326 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xstock_purchase`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xstock_purchase`;
CREATE TABLE `jos_xstock_purchase` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `Quantity` float NOT NULL,
  `Remarks` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=920 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xstock_transfer`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xstock_transfer`;
CREATE TABLE `jos_xstock_transfer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_id` int(11) NOT NULL,
  `to_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `Quantity` float NOT NULL,
  `date` datetime NOT NULL,
  `Remarks` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1686 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xtemp`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xtemp`;
CREATE TABLE `jos_xtemp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `AccountNumber` varchar(100) DEFAULT NULL,
  `LoanFromAccountPrevious` varchar(100) DEFAULT NULL,
  `LoanFromAccountNew` varchar(100) DEFAULT NULL,
  `taskdone` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xtemp_loan_accounts`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xtemp_loan_accounts`;
CREATE TABLE `jos_xtemp_loan_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `AccountNumber` varchar(255) NOT NULL,
  `premiums_paid` varchar(11) DEFAULT NULL,
  `penalty` double DEFAULT NULL,
  `amount_paid` double DEFAULT NULL,
  `interest_amount` double DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xtemp_saving_accounts`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xtemp_saving_accounts`;
CREATE TABLE `jos_xtemp_saving_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `AccountNumber` varchar(255) NOT NULL,
  `premiums_paid` int(11) DEFAULT '0',
  `penalty` double DEFAULT NULL,
  `amount_paid` double DEFAULT NULL,
  `interest_amount` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xtemp_share_accounts`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xtemp_share_accounts`;
CREATE TABLE `jos_xtemp_share_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `memberid` varchar(100) DEFAULT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `amountToDeposit` varchar(100) DEFAULT NULL,
  `branchid` varchar(100) DEFAULT '3',
  `taskdone` varchar(100) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `jos_xxyz`
-- ----------------------------
DROP TABLE IF EXISTS `jos_xxyz`;
CREATE TABLE `jos_xxyz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `AccountNumber` varchar(255) NOT NULL,
  `premiums_paid` int(11) DEFAULT NULL,
  `penalty` double DEFAULT NULL,
  `amount_paid` double DEFAULT NULL,
  `interest_amount` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `members`
-- ----------------------------
DROP TABLE IF EXISTS `members`;
CREATE TABLE `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `CurrentAddress` text,
  `FatherName` varchar(45) DEFAULT NULL,
  `Cast` varchar(45) DEFAULT NULL,
  `PermanentAddress` text,
  `Occupation` varchar(45) DEFAULT NULL,
  `Nominee` varchar(45) DEFAULT NULL,
  `RelationWithNominee` varchar(45) DEFAULT NULL,
  `NomineeAge` smallint(6) DEFAULT NULL,
  `Witness1Name` varchar(45) DEFAULT NULL,
  `Witness1FatherName` varchar(45) DEFAULT NULL,
  `Witness1Address` text,
  `Witness2Name` varchar(45) DEFAULT NULL,
  `Witness2FatherName` varchar(45) DEFAULT NULL,
  `Witness2Address` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `branch_id` int(11) NOT NULL,
  `PhoneNos` text,
  `PanNo` varchar(10) DEFAULT NULL,
  `IsMinor` tinyint(1) DEFAULT NULL,
  `MinorDOB` date DEFAULT NULL,
  `ParentName` varchar(45) DEFAULT NULL,
  `RelationWithParent` varchar(45) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `netmember_id` int(11) DEFAULT NULL,
  `MemberCode` varchar(45) DEFAULT NULL,
  `DOB` datetime DEFAULT NULL,
  `FilledForm60` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Has the member filled form 60 if Pan Number not there',
  `CustomerCode` varchar(45) DEFAULT NULL,
  `parent_member_id` int(11) NOT NULL DEFAULT '0',
  `customer_created_at` datetime DEFAULT NULL,
  `OfficeAddress` varchar(200) DEFAULT NULL,
  `OfficePhoneNos` varchar(100) DEFAULT NULL,
  `Email` varchar(50) DEFAULT NULL,
  `BloodGroup` varchar(10) DEFAULT NULL,
  `MaritalStatus` varchar(20) DEFAULT NULL,
  `NumberOfChildren` int(11) DEFAULT NULL,
  `MarriageDate` date DEFAULT NULL,
  `HighestQualification` varchar(50) DEFAULT NULL,
  `OccupationDetails` varchar(50) DEFAULT NULL,
  `EmployerAddress` varchar(200) DEFAULT NULL,
  `SelfEmployeeDetails` varchar(100) DEFAULT NULL,
  `FamilyMonthlyIncome` varchar(10) DEFAULT NULL,
  `Bank` varchar(50) DEFAULT NULL,
  `Branch` varchar(50) DEFAULT NULL,
  `AccountNumber` varchar(30) DEFAULT NULL,
  `DebitCreditCardNo` varchar(45) DEFAULT NULL,
  `DebitCreditCardIssuingBank` varchar(50) DEFAULT NULL,
  `PassportNo` varchar(20) DEFAULT NULL,
  `PassportIssuedAt` varchar(50) DEFAULT NULL,
  `EmployerCard` tinyint(4) DEFAULT NULL,
  `Passport` tinyint(4) DEFAULT NULL,
  `PanCard` tinyint(4) DEFAULT NULL,
  `VoterIdCard` tinyint(4) DEFAULT NULL,
  `DrivingLicense` tinyint(4) DEFAULT NULL,
  `GovtArmyIdCard` tinyint(4) DEFAULT NULL,
  `RationCard` tinyint(4) DEFAULT NULL,
  `OtherDocument` tinyint(4) DEFAULT NULL,
  `DocumentDescription` varchar(100) DEFAULT NULL,
  `CameToKnowByNewspaper` tinyint(4) DEFAULT NULL,
  `CameToKnowByTelevision` tinyint(4) DEFAULT NULL,
  `CameToKnowByAdvertisement` tinyint(4) DEFAULT NULL,
  `CameToKnowByFriends` tinyint(4) DEFAULT NULL,
  `CameToKnowByFieldworker` tinyint(4) DEFAULT NULL,
  `OtherDetails` varchar(200) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `is_agent` tinyint(1) DEFAULT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `tehsil` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `pin_code` varchar(255) DEFAULT NULL,
  `doc_image_id` int(11) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `ParentAddress` text,
  `is_active` tinyint(1) DEFAULT NULL,
  `is_defaulter` tinyint(1) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `member_no` int(11) DEFAULT NULL,
  `bankbranch_a_id` int(11) DEFAULT NULL,
  `bank_account_number_1` varchar(255) DEFAULT NULL,
  `bankbranch_b_id` int(11) DEFAULT NULL,
  `bank_account_number_2` varchar(255) DEFAULT NULL,
  `memebr_type` varchar(255) DEFAULT NULL,
  `defaulter_on` datetime DEFAULT NULL,
  `AdharNumber` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_user_branch` (`branch_id`),
  KEY `fk_member_staff1` (`staff_id`)
) ENGINE=InnoDB AUTO_INCREMENT=37021 DEFAULT CHARSET=latin1 COMMENT='Account Holders are the users of bank system';

-- ----------------------------
--  Records of `members`
-- ----------------------------
BEGIN;
INSERT INTO `members` VALUES ('37020', 'DFL Default', null, null, null, null, 'Service', null, null, null, null, null, null, null, null, null, '2017-12-29 12:14:49', '2017-12-29 12:14:49', '27', null, null, null, null, null, null, null, null, null, null, '0', null, '0', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, 'Mr.', '0', null, null, null, null, null, null, null, ' ', '1', '0', '37020', '71809', '1', null, null, null, null, 'General', null, null);
COMMIT;

-- ----------------------------
--  Table structure for `mos`
-- ----------------------------
DROP TABLE IF EXISTS `mos`;
CREATE TABLE `mos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_branch_id` (`branch_id`),
  CONSTRAINT `mos_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `premiums`
-- ----------------------------
DROP TABLE IF EXISTS `premiums`;
CREATE TABLE `premiums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `Amount` varchar(45) DEFAULT NULL,
  `Paid` tinyint(1) DEFAULT NULL,
  `Skipped` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `PaidOn` datetime DEFAULT NULL,
  `AgentCommissionSend` tinyint(1) DEFAULT NULL,
  `AgentCommissionPercentage` double DEFAULT NULL,
  `AgentCollectionChargesPercentage` decimal(10,2) DEFAULT NULL,
  `DueDate` datetime DEFAULT NULL,
  `PaneltyCharged` decimal(10,2) DEFAULT NULL,
  `PaneltyPosted` decimal(10,2) DEFAULT NULL,
  `AgentCollectionChargesSend` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_premiums_accounts1` (`account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=559819 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `schemes`
-- ----------------------------
DROP TABLE IF EXISTS `schemes`;
CREATE TABLE `schemes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `MinLimit` double DEFAULT NULL,
  `MaxLimit` double DEFAULT NULL,
  `Interest` varchar(45) DEFAULT NULL,
  `InterestMode` varchar(45) DEFAULT NULL,
  `InterestRateMode` varchar(45) DEFAULT NULL,
  `AccountOpenningCommission` varchar(200) DEFAULT '0',
  `Commission` double DEFAULT NULL,
  `ActiveStatus` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ProcessingFees` double DEFAULT NULL,
  `balance_sheet_id` int(11) NOT NULL,
  `PostingMode` varchar(45) DEFAULT NULL COMMENT 'Y,HF,Q,M...',
  `PremiumMode` varchar(45) DEFAULT NULL,
  `CreateDefaultAccount` tinyint(1) DEFAULT NULL,
  `SchemeType` varchar(45) DEFAULT NULL,
  `SchemeGroup` varchar(45) DEFAULT NULL,
  `InterestToAnotherAccount` tinyint(1) DEFAULT '0',
  `NumberOfPremiums` int(11) DEFAULT NULL,
  `MaturityPeriod` int(11) DEFAULT NULL,
  `InterestToAnotherAccountPercent` varchar(45) DEFAULT NULL,
  `isDepriciable` tinyint(4) DEFAULT '0',
  `DepriciationPercentBeforeSep` varchar(45) DEFAULT NULL,
  `DepriciationPercentAfterSep` varchar(45) DEFAULT NULL,
  `ProcessingFeesinPercent` tinyint(1) DEFAULT '0' COMMENT 'whether the processing fees for accounts is in percentage',
  `published` tinyint(1) DEFAULT '1',
  `SchemePoints` float(11,0) DEFAULT '0',
  `AgentSponsorCommission` varchar(2500) DEFAULT NULL,
  `CollectorCommissionRate` varchar(255) DEFAULT '0',
  `ReducingOrFlatRate` varchar(45) DEFAULT '0',
  `type` varchar(255) DEFAULT NULL,
  `CRPB` int(11) DEFAULT NULL,
  `percent_loan_on_deposit` int(11) DEFAULT NULL,
  `no_loan_on_deposit_till` int(11) DEFAULT NULL,
  `pre_mature_interests` varchar(255) DEFAULT NULL,
  `valid_till` date NOT NULL,
  `mature_interests_for_uncomplete_product` decimal(10,4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_schemes_balance_sheet1` (`balance_sheet_id`)
) ENGINE=InnoDB AUTO_INCREMENT=958 DEFAULT CHARSET=latin1 COMMENT='Various Accounts that a bank can manage';

-- ----------------------------
--  Records of `schemes`
-- ----------------------------
BEGIN;
INSERT INTO `schemes` VALUES ('923', 'Cash Account', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:25', '2011-02-05 11:23:25', null, '2', 'Y', null, '1', 'Default', 'Cash Account', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('924', 'Bank Accounts', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:25', '2011-02-05 11:23:25', null, '2', 'Y', null, '0', 'Default', 'Bank Accounts', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('925', 'Bank OD', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:25', '2011-02-05 11:23:25', null, '1', 'Y', null, '0', 'Default', 'Bank OD', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('926', 'F.D. Assets', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:25', '2011-02-05 11:23:25', null, '2', 'Y', null, '0', 'Default', 'F.D. Assets', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('927', 'Share Capital', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:25', '2011-02-05 11:23:25', null, '3', 'Y', null, '1', 'Default', 'Share Capital', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('928', 'Current Liabilities', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:25', '2011-02-05 11:23:25', null, '1', 'Y', null, '0', 'Default', 'Current Liabilities', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('929', 'Deposit(Assest)', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:25', '2011-02-05 11:23:25', null, '2', 'Y', null, '0', 'Default', 'Deposit-Assest', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('930', 'Direct Expenses', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:26', '2011-02-05 11:23:26', null, '4', 'Y', null, '1', 'Default', 'Direct Expenses', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('931', 'Direct Income', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:26', '2011-02-05 11:23:26', null, '5', 'Y', null, '1', 'Default', 'Direct Income', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('932', 'Duties Taxes', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:26', '2011-02-05 11:23:26', null, '9', 'Y', null, '1', 'Default', 'Duties Taxes', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('933', 'Fixed Assets', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:26', '2011-02-05 11:23:26', null, '7', 'Y', null, '0', 'Default', 'Fixed Assets', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('934', 'Indirect Expenses', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:26', '2011-02-05 11:23:26', null, '4', 'Y', null, '1', 'Default', 'Indirect Expenses', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('935', 'Indirect Income', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:26', '2011-02-05 11:23:26', null, '5', 'Y', null, '1', 'Default', 'Indirect Income', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('936', 'Investment', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:26', '2011-02-05 11:23:26', null, '2', 'Y', null, '0', 'Default', 'Investment', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('937', 'Loan Advance(Assets)', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:27', '2011-02-05 11:23:27', null, '2', 'Y', null, '0', 'Default', 'Loan Advance(Assets)', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('938', 'Loan(Liabilities)', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:27', '2011-02-05 11:23:27', null, '1', 'Y', null, '0', 'Default', 'Loan(Liabilities)', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('939', 'Misc Expenses(Assets)', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:27', '2011-02-05 11:23:27', null, '2', 'Y', null, '0', 'Default', 'Misc Expenses(Assets)', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('940', 'Provision', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:27', '2011-02-05 11:23:27', null, '1', 'Y', null, '0', 'Default', 'Provision', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('941', 'Reserve Surpuls', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:27', '2011-02-05 11:23:27', null, '3', 'Y', null, '0', 'Default', 'Reserve Surpuls', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('942', 'Retained Earnings', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:27', '2011-02-05 11:23:27', null, '3', 'Y', null, '0', 'Default', 'Retained Earnings', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('943', 'Secured(Loan)', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:27', '2011-02-05 11:23:27', null, '1', 'Y', null, '0', 'Default', 'Secured(Loan)', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('944', 'Sundry Creditor', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:27', '2011-02-05 11:23:27', null, '1', 'Y', null, '0', 'Default', 'Sundry Creditor', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('945', 'Sundry Debtor', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:27', '2011-02-05 11:23:27', null, '2', 'Y', null, '0', 'Default', 'Sundry Debtor', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('946', 'Suspence Account', '0', '-1', '0', 'Y', null, '0', null, '1', '2011-02-05 11:23:27', '2011-02-05 11:23:27', null, '6', 'Y', null, '0', 'Default', 'Suspence Account', '0', null, null, '0', null, null, null, '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('948', 'computer & printer', '0', '-1', '0', 'Y', null, '0', '0', '1', '2011-04-08 15:34:39', '2011-04-08 15:34:39', '0', '7', '', '0', '1', 'Default', 'computer & printer', '0', '0', '0', '0', '1', '60', '30', '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('949', 'furniture & fix', '0', '-1', '0', 'Y', null, '0', '0', '1', '2011-04-08 15:35:55', '2011-04-08 15:37:56', '0', '7', '', '0', '1', 'Default', 'furniture & fix', '0', '0', '0', '0', '1', '10', '5', '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('950', 'plant & machionary', '0', '-1', '0', 'Y', null, '0', '0', '1', '2011-04-08 15:41:07', '2011-04-08 15:41:07', '0', '7', '', '0', '1', 'Default', 'plant & machionary', '0', '0', '0', '0', '1', '15', '7.5', '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('951', 'Branch & Divisions', '0', '-1', '0', 'Y', null, '0', '0', '1', '2011-04-08 15:44:16', '2011-04-08 15:44:16', '0', '8', '', '0', '1', 'Default', 'Branch & Divisions', '0', '0', '0', '0', '0', '', '', '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('952', 'Profit And Loss', '0', '-1', '0', 'Y', null, '0', '0', '1', '2011-04-26 14:43:12', '2011-04-26 14:43:12', '0', '3', '', '0', '1', 'Default', 'Profit And Loss', '0', '0', '0', '', '0', '', '', '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('953', 'Loss And Profit', '0', '-1', '0', 'Y', null, '0', '0', '1', '2011-04-26 14:58:19', '2011-04-26 14:58:19', '0', '2', '', '0', '1', 'Default', 'Loss And Profit', '0', '0', '0', '', '0', '', '', '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('954', 'stock in hand', '0', '0', '0', '0', null, '0', '0', '1', '2013-01-14 06:41:55', null, '0', '2', '0', '0', '1', 'Default', 'stock in hand', '0', '0', '0', '0', '0', '0', '0', '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('955', 'Adminssion Fee', '0', '-1', '0', '0', null, '0', '0', '1', '2013-09-22 08:31:16', null, '0', '3', '0', '0', '1', 'Default', 'Default', '0', '0', '0', '0', '0', '0', '0', '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('956', 'TDS refundable', '0', '-1', '0', '0', null, '0', '0', '1', '2014-07-04 09:09:10', null, '0', '2', '0', '0', '1', 'Default', 'TDS Refundable', '0', '0', '0', '0', '0', '0', '0', '0', '1', '0', null, '0', '0', null, null, null, null, null, '2037-03-31', null), ('957', 'PROVISION (BANK INTEREST)', '0', '-1', null, null, null, '0', null, '1', '2016-04-11 11:54:09', '2016-04-11 11:54:09', null, '2', null, null, null, 'Default', 'PROVISION', '0', null, null, null, '0', '', '', '0', '1', '0', null, '0', '0', '1', null, null, null, null, '2037-03-31', null);
COMMIT;

-- ----------------------------
--  Table structure for `staffs`
-- ----------------------------
DROP TABLE IF EXISTS `staffs`;
CREATE TABLE `staffs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `AccessLevel` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `pf_amount` varchar(255) DEFAULT NULL,
  `basic_pay` varchar(255) DEFAULT NULL,
  `variable_pay` varchar(255) DEFAULT NULL,
  `created_at` varchar(255) DEFAULT NULL,
  `present_address` text,
  `parmanent_address` text,
  `mobile_no` varchar(255) DEFAULT NULL,
  `landline_no` varchar(255) DEFAULT NULL,
  `DOB` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `marriatal_status` varchar(255) DEFAULT NULL,
  `blood_group` varchar(255) DEFAULT NULL,
  `emp_code` varchar(255) DEFAULT NULL,
  `emergency_no` varchar(255) DEFAULT NULL,
  `pan_no` varchar(255) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `last_qualification` varchar(255) DEFAULT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `remark` text,
  `amount_of_increment` varchar(255) DEFAULT NULL,
  `yearly_increment_amount` varchar(255) DEFAULT NULL,
  `salary` varchar(255) DEFAULT NULL,
  `relaving_date_if_not_active` varchar(255) DEFAULT NULL,
  `security_amount` varchar(255) DEFAULT NULL,
  `deposit_date` date DEFAULT NULL,
  `total_dep_amount` varchar(255) DEFAULT NULL,
  `posting_at` varchar(255) DEFAULT NULL,
  `pf_no` varchar(255) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `ifsc_code` varchar(255) DEFAULT NULL,
  `account_no` varchar(255) DEFAULT NULL,
  `last_date_of_increment` varchar(255) DEFAULT NULL,
  `nominee_name` varchar(255) DEFAULT NULL,
  `nominee_age` varchar(255) DEFAULT NULL,
  `relation_with_nominee` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `StaffID_UNIQUE` (`username`),
  KEY `fk_staff_branch1` (`branch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=375 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Records of `staffs`
-- ----------------------------
BEGIN;
INSERT INTO `staffs` VALUES ('374', 'xadmin', '27', '100', 'Default_admin', null, null, null, null, null, null, null, null, null, null, 'admin', null, '1', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
COMMIT;

-- ----------------------------
--  Table structure for `stock_categories`
-- ----------------------------
DROP TABLE IF EXISTS `stock_categories`;
CREATE TABLE `stock_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_branch_id` (`branch_id`),
  CONSTRAINT `stock_categories_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `stock_containerrowitemqty`
-- ----------------------------
DROP TABLE IF EXISTS `stock_containerrowitemqty`;
CREATE TABLE `stock_containerrowitemqty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `container_id` int(11) DEFAULT NULL,
  `row_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `qty` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_branch_id` (`branch_id`),
  KEY `fk_container_id` (`container_id`),
  KEY `fk_row_id` (`row_id`),
  KEY `fk_item_id` (`item_id`),
  CONSTRAINT `stock_containerrowitemqty_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  CONSTRAINT `stock_containerrowitemqty_ibfk_2` FOREIGN KEY (`container_id`) REFERENCES `stock_containers` (`id`),
  CONSTRAINT `stock_containerrowitemqty_ibfk_3` FOREIGN KEY (`row_id`) REFERENCES `stock_rows` (`id`),
  CONSTRAINT `stock_containerrowitemqty_ibfk_4` FOREIGN KEY (`item_id`) REFERENCES `stock_items` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2522 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `stock_containers`
-- ----------------------------
DROP TABLE IF EXISTS `stock_containers`;
CREATE TABLE `stock_containers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_branch_id` (`branch_id`),
  CONSTRAINT `stock_containers_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Records of `stock_containers`
-- ----------------------------
BEGIN;
INSERT INTO `stock_containers` VALUES ('110', '27', 'General'), ('111', '27', 'Dead'), ('112', '27', 'UsedDefault');
COMMIT;

-- ----------------------------
--  Table structure for `stock_items`
-- ----------------------------
DROP TABLE IF EXISTS `stock_items`;
CREATE TABLE `stock_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `is_consumable` tinyint(1) DEFAULT NULL,
  `is_issueable` tinyint(1) DEFAULT NULL,
  `is_fixedassets` tinyint(1) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_branch_id` (`branch_id`),
  KEY `fk_category_id` (`category_id`),
  CONSTRAINT `stock_items_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  CONSTRAINT `stock_items_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `stock_categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=185 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `stock_members`
-- ----------------------------
DROP TABLE IF EXISTS `stock_members`;
CREATE TABLE `stock_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `ph_no` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_branch_id` (`branch_id`),
  CONSTRAINT `stock_members_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=620 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `stock_rows`
-- ----------------------------
DROP TABLE IF EXISTS `stock_rows`;
CREATE TABLE `stock_rows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `container_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_branch_id` (`branch_id`),
  KEY `fk_container_id` (`container_id`),
  CONSTRAINT `stock_rows_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  CONSTRAINT `stock_rows_ibfk_2` FOREIGN KEY (`container_id`) REFERENCES `stock_containers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=179 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Records of `stock_rows`
-- ----------------------------
BEGIN;
INSERT INTO `stock_rows` VALUES ('176', '27', '110', 'General'), ('177', '27', '111', 'Dead'), ('178', '27', '112', 'UsedDefault');
COMMIT;

-- ----------------------------
--  Table structure for `stock_transactions`
-- ----------------------------
DROP TABLE IF EXISTS `stock_transactions`;
CREATE TABLE `stock_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `from_container_id` int(11) DEFAULT NULL,
  `to_container_id` int(11) DEFAULT NULL,
  `to_row_id` int(11) DEFAULT NULL,
  `from_row_id` int(11) DEFAULT NULL,
  `qty` varchar(255) DEFAULT NULL,
  `rate` decimal(10,2) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `narration` varchar(255) DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `issue_date` varchar(255) DEFAULT NULL,
  `submit_date` varchar(255) DEFAULT NULL,
  `transaction_type` varchar(255) DEFAULT NULL,
  `to_branch_id` varchar(255) DEFAULT NULL,
  `is_used_submit` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_branch_id` (`branch_id`),
  KEY `fk_item_id` (`item_id`),
  KEY `fk_member_id` (`member_id`),
  KEY `fk_from_container_id` (`from_container_id`),
  KEY `fk_to_container_id` (`to_container_id`),
  KEY `fk_to_row_id` (`to_row_id`),
  KEY `fk_from_row_id` (`from_row_id`),
  CONSTRAINT `stock_transactions_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  CONSTRAINT `stock_transactions_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `stock_items` (`id`),
  CONSTRAINT `stock_transactions_ibfk_3` FOREIGN KEY (`member_id`) REFERENCES `stock_members` (`id`),
  CONSTRAINT `stock_transactions_ibfk_4` FOREIGN KEY (`from_container_id`) REFERENCES `stock_containers` (`id`),
  CONSTRAINT `stock_transactions_ibfk_5` FOREIGN KEY (`to_container_id`) REFERENCES `stock_containers` (`id`),
  CONSTRAINT `stock_transactions_ibfk_6` FOREIGN KEY (`to_row_id`) REFERENCES `stock_rows` (`id`),
  CONSTRAINT `stock_transactions_ibfk_7` FOREIGN KEY (`from_row_id`) REFERENCES `stock_rows` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41498 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `teams`
-- ----------------------------
DROP TABLE IF EXISTS `teams`;
CREATE TABLE `teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `transaction_row`
-- ----------------------------
DROP TABLE IF EXISTS `transaction_row`;
CREATE TABLE `transaction_row` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `transaction_type_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `voucher_no` bigint(20) DEFAULT NULL,
  `Narration` text,
  `amountDr` decimal(20,3) DEFAULT '0.000',
  `amountCr` decimal(20,3) DEFAULT '0.000',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `branch_id` int(11) NOT NULL,
  `reference_account_id` int(11) DEFAULT NULL,
  `display_voucher_no` bigint(20) DEFAULT '0',
  `side` varchar(2) NOT NULL DEFAULT '--',
  `accounts_in_side` int(11) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `scheme_id` int(11) NOT NULL,
  `balance_sheet_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_transactions_accounts1` (`account_id`),
  KEY `fk_transactions_transaction_type1` (`transaction_type_id`),
  KEY `fk_transactions_staff1` (`staff_id`),
  KEY `fk_transactions_branch1` (`branch_id`),
  KEY `voucher_no` (`voucher_no`),
  KEY `side` (`side`),
  KEY `TRansactio ID` (`transaction_id`),
  KEY `scheme_id` (`scheme_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2630523 DEFAULT CHARSET=latin1 COMMENT='Store all kind of transactions Here .. ';

-- ----------------------------
--  Table structure for `transaction_types`
-- ----------------------------
DROP TABLE IF EXISTS `transaction_types`;
CREATE TABLE `transaction_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `FromAC` varchar(45) DEFAULT NULL,
  `ToAC` varchar(45) DEFAULT NULL,
  `Default_Narration` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `transactions`
-- ----------------------------
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_type_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `voucher_no_original` int(11) DEFAULT NULL,
  `voucher_no` decimal(11,3) DEFAULT NULL,
  `Narration` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_transaction_type_id` (`transaction_type_id`),
  KEY `fk_staff_id` (`staff_id`),
  KEY `fk_reference_id` (`reference_id`),
  KEY `fk_branch_id` (`branch_id`),
  KEY `voucher_no_original` (`voucher_no_original`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1211881 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `xLog`
-- ----------------------------
DROP TABLE IF EXISTS `xLog`;
CREATE TABLE `xLog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) DEFAULT NULL,
  `model_class` varchar(255) DEFAULT NULL,
  `pk_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `name` text,
  `type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_staff_id` (`staff_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1951759 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Records of `xLog`
-- ----------------------------
BEGIN;
INSERT INTO `xLog` VALUES ('1951757', null, 'Model_Member', '37020', '2017-12-29 12:14:52', '{\"username\":{\"from\":\"\",\"to\":\"37020\"},\"password\":{\"from\":\"\",\"to\":71809}}', 'Edit'), ('1951758', '374', 'Model_Scheme_Default', '947', '2017-12-29 12:18:44', '{\"name\":\"P and M_Wrong\",\"MinLimit\":\"0\",\"MaxLimit\":\"-1\",\"ActiveStatus\":\"0\",\"balance_sheet_id\":\"7\",\"balance_sheet\":null,\"SchemePoints\":\"0\",\"SchemeGroup\":\"P and M_Wrong\",\"isDepriciable\":\"1\",\"DepriciationPercentBeforeSep\":\"15\",\"DepriciationPercentAfterSep\":\"7.5\",\"total_accounts\":\"0\",\"total_active_accounts\":\"0\",\"valid_till\":\"0000-00-00\",\"id\":\"947\",\"created_at\":\"2011-03-17 15:22:26\",\"updated_at\":\"2013-04-01 09:21:30\",\"SchemeType\":\"Default\"}', 'Delete');
COMMIT;

-- ----------------------------
--  Table structure for `xbank_employees`
-- ----------------------------
DROP TABLE IF EXISTS `xbank_employees`;
CREATE TABLE `xbank_employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `DOB` date DEFAULT NULL,
  `experince` varchar(255) DEFAULT NULL,
  `prev_company` varchar(255) DEFAULT NULL,
  `prev_department` varchar(255) DEFAULT NULL,
  `prev_leaving_company_date` date DEFAULT NULL,
  `leaving_resion` varchar(255) DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `marital_status` varchar(255) DEFAULT NULL,
  `contact_no` varchar(255) DEFAULT NULL,
  `email_id` varchar(255) DEFAULT NULL,
  `permanent_address` varchar(255) DEFAULT NULL,
  `present_address` varchar(255) DEFAULT NULL,
  `date_of_joining` date DEFAULT NULL,
  `date_of_leaving` date DEFAULT NULL,
  `pf_no` varchar(255) DEFAULT NULL,
  `pf_nominee` varchar(255) DEFAULT NULL,
  `esi_no` varchar(255) DEFAULT NULL,
  `esi_nominee` varchar(255) DEFAULT NULL,
  `pan_no` varchar(255) DEFAULT NULL,
  `driving_licence_no` varchar(255) DEFAULT NULL,
  `validity_of_driving_licence` varchar(255) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `bank_account_no` varchar(255) DEFAULT NULL,
  `paymemt_mode` varchar(255) DEFAULT NULL,
  `pf_deduct` varchar(255) DEFAULT NULL,
  `employee_status` varchar(255) DEFAULT NULL,
  `basic_salary` varchar(255) DEFAULT NULL,
  `other_allowance` varchar(255) DEFAULT NULL,
  `society_contri` varchar(255) DEFAULT NULL,
  `net_payable` varchar(255) DEFAULT NULL,
  `net_salary` varchar(255) DEFAULT NULL,
  `emp_code` varchar(255) DEFAULT NULL,
  `relation_with_nominee` varchar(255) DEFAULT NULL,
  `last_qualification` varchar(255) DEFAULT NULL,
  `pf_joining_date` varchar(255) DEFAULT NULL,
  `agreement_date` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `employee_image_photo` int(11) DEFAULT NULL,
  `employee_image_signature` int(11) DEFAULT NULL,
  `emergency_no` varchar(255) DEFAULT NULL,
  `opening_cl` varchar(255) DEFAULT NULL,
  `effective_cl_date` date DEFAULT NULL,
  `employee_image_photo_id` int(11) DEFAULT NULL,
  `employee_image_signature_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_branch_id` (`branch_id`),
  CONSTRAINT `xbank_employees_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=413 DEFAULT CHARSET=latin1;

SET FOREIGN_KEY_CHECKS = 1;
