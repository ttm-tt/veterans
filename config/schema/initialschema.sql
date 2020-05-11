-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 24, 2019 at 02:37 PM
-- Server version: 10.3.15-MariaDB-1
-- PHP Version: 7.3.4-2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `veterans-v3`
--

-- --------------------------------------------------------

--
-- Table structure for table `acos`
--

CREATE TABLE `acos` (
  `id` int(10) NOT NULL,
  `parent_id` int(10) DEFAULT NULL,
  `model` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `foreign_key` int(10) DEFAULT NULL,
  `alias` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aros`
--

CREATE TABLE `aros` (
  `id` int(10) NOT NULL,
  `parent_id` int(10) DEFAULT NULL,
  `model` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `foreign_key` int(10) DEFAULT NULL,
  `alias` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aros_acos`
--

CREATE TABLE `aros_acos` (
  `id` int(10) NOT NULL,
  `aro_id` int(10) NOT NULL,
  `aco_id` int(10) NOT NULL,
  `_create` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `_read` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `_update` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `_delete` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `competitions`
--

CREATE TABLE `competitions` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `name` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `sex` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `type_of` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `born` int(11) DEFAULT NULL,
  `entries` int(11) DEFAULT NULL,
  `entries_host` int(11) DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `type_ids` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `i18n`
--

CREATE TABLE `i18n` (
  `id` int(11) NOT NULL,
  `locale` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `model` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `foreign_key` int(10) NOT NULL,
  `field` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int(11) NOT NULL,
  `name` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(64) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nations`
--

CREATE TABLE `nations` (
  `id` int(11) NOT NULL,
  `name` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `continent` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `all_notifications` tinyint(4) NOT NULL DEFAULT 1,
  `new_player` tinyint(4) NOT NULL DEFAULT 1,
  `delete_registration_player_after` tinyint(4) NOT NULL DEFAULT 1,
  `edit_registration_player_after` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `organisations`
--

CREATE TABLE `organisations` (
  `id` int(11) NOT NULL,
  `name` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fax` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `participants`
--

CREATE TABLE `participants` (
  `id` int(11) NOT NULL,
  `registration_id` int(11) NOT NULL,
  `single_id` int(11) DEFAULT NULL,
  `single_cancelled` tinyint(1) NOT NULL DEFAULT 0,
  `double_id` int(11) DEFAULT NULL,
  `double_partner_id` int(11) DEFAULT NULL,
  `double_cancelled` tinyint(1) NOT NULL DEFAULT 0,
  `mixed_id` int(11) DEFAULT NULL,
  `mixed_partner_id` int(11) DEFAULT NULL,
  `mixed_cancelled` tinyint(1) NOT NULL DEFAULT 0,
  `team_id` int(11) DEFAULT NULL,
  `team_cancelled` tinyint(1) NOT NULL DEFAULT 0,
  `cancelled` tinyint(1) NOT NULL DEFAULT 0,
  `replaced_by_id` int(11) DEFAULT NULL,
  `start_no` int(11) DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `participant_histories`
--

CREATE TABLE `participant_histories` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `registration_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `field_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `old_value` varchar(4096) COLLATE utf8_unicode_ci DEFAULT NULL,
  `new_value` varchar(4096) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `people`
--

CREATE TABLE `people` (
  `id` int(11) NOT NULL,
  `first_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `display_name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `extern_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sex` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `dob` date DEFAULT NULL,
  `nation_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `cancelled` datetime DEFAULT NULL,
  `comment` text CHARACTER SET utf8 DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `setting` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_allotments`
--

CREATE TABLE `shop_allotments` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `allotment` int(11) NOT NULL,
  `modified` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_articles`
--

CREATE TABLE `shop_articles` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `name` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(5,2) NOT NULL DEFAULT 0.00,
  `cancellation_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `available` int(11) DEFAULT NULL,
  `available_from` date DEFAULT NULL,
  `available_until` date DEFAULT NULL,
  `sort_order` int(11) NOT NULL,
  `article_description` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `article_image` mediumblob DEFAULT NULL,
  `article_url` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_article_variants`
--

CREATE TABLE `shop_article_variants` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `name` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `variant_type` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `sort_order` int(11) NOT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `modified` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_cancellation_fees`
--

CREATE TABLE `shop_cancellation_fees` (
  `id` int(11) NOT NULL,
  `shop_settings_id` int(11) NOT NULL,
  `fee` int(11) NOT NULL,
  `start` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_countries`
--

CREATE TABLE `shop_countries` (
  `id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `iso_code_2` varchar(2) NOT NULL,
  `iso_code_3` varchar(3) NOT NULL,
  `modified` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shop_orders`
--

CREATE TABLE `shop_orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `order_status_id` int(11) NOT NULL,
  `total` decimal(15,2) NOT NULL,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `cancellation_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `cancellation_discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `paid` decimal(15,2) NOT NULL DEFAULT 0.00,
  `invoice` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `invoice_no` int(11) NOT NULL DEFAULT 0,
  `invoice_paid` datetime DEFAULT NULL,
  `invoice_split` int(11) NOT NULL DEFAULT 0,
  `invoice_cancelled` datetime DEFAULT NULL,
  `accepted` datetime DEFAULT NULL,
  `payment_method` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ticket` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `language` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_order_addresses`
--

CREATE TABLE `shop_order_addresses` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `type` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `first_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `street` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `zip_code` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `country_id` int(11) NOT NULL,
  `phone` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `modified` datetime NOT NULL,
  `created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_order_articles`
--

CREATE TABLE `shop_order_articles` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `article_variant_id` int(11) DEFAULT NULL,
  `detail` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `person_id` int(11) DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `total` decimal(15,2) NOT NULL,
  `cancelled` datetime DEFAULT NULL,
  `cancellation_fee` decimal(15,2) DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_order_article_histories`
--

CREATE TABLE `shop_order_article_histories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_article_id` int(11) NOT NULL,
  `field_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `old_value` varchar(4096) COLLATE utf8_unicode_ci DEFAULT NULL,
  `new_value` varchar(4096) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_order_authorizenet`
--

CREATE TABLE `shop_order_authorizenet` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `x_response_code` int(11) DEFAULT NULL,
  `x_response_reason_code` int(11) DEFAULT NULL,
  `x_response_reason_text` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x_auth_code` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x_avs_code` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x_trans_id` bigint(11) DEFAULT NULL,
  `x_invoice_num` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x_description` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x_amount` decimal(15,2) DEFAULT NULL,
  `x_method` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x_type` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x_account_number` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x_card_type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x_split_tender_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x_cust_id` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x_first_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x_last_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x_company` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x_address` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x_city` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x_state` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x_zip` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x_country` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `xMD5_Hash` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x_cvv2_resp_code` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x_cavv_response` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x_test_request` tinyint(1) DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_order_bpayment`
--

CREATE TABLE `shop_order_bpayment` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `orderhash` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `authorizationcode` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `creditcardnumber` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `step` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `errordescription` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `errorcode` int(11) DEFAULT NULL,
  `errordetail1` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `auditlog1` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_order_card_pointe`
--

CREATE TABLE `shop_order_card_pointe` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `paymentType` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ipAddress` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `total` decimal(15,2) DEFAULT NULL,
  `invoice` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `number` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL,
  `expirationDateMonth` int(11) DEFAULT NULL,
  `experationDateYear` int(11) DEFAULT NULL,
  `billCompany` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billFName` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billLName` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billAddress1` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billAddress2` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billCity` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billState` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billCountry` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cardType` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gatewayTransactionId` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `merchantId` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `cf_orderid` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_order_comments`
--

CREATE TABLE `shop_order_comments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_order_dibs`
--

CREATE TABLE `shop_order_dibs` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `acquirer` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `agreement` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `approvalcode` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `authkey` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cardcountry` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `currency` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dibsmd5` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fee` int(11) DEFAULT NULL,
  `ip` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lang` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `newDIBSTransactionID` int(11) DEFAULT NULL,
  `newDIBSTransactionIDVerification` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `orderid` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ordertext` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `paytype` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `statuscode` int(11) DEFAULT NULL,
  `severity` int(11) DEFAULT NULL,
  `suspect` tinyint(1) DEFAULT NULL,
  `test` tinyint(1) DEFAULT NULL,
  `threeDstatus` int(11) NOT NULL DEFAULT 0,
  `token` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `transact` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_order_histories`
--

CREATE TABLE `shop_order_histories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_id` int(11) NOT NULL,
  `field_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `old_value` varchar(4096) COLLATE utf8_unicode_ci DEFAULT NULL,
  `new_value` varchar(4096) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_order_ipayment`
--

CREATE TABLE `shop_order_ipayment` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `addr_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `addr_street` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `addr_zip` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `addr_city` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `addr_country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `trxuser_id` int(11) NOT NULL,
  `trx_amount` int(11) NOT NULL,
  `trx_currency` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `trx_typ` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `trx_paymenttyp` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `ret_transdate` date DEFAULT NULL,
  `ret_transtime` time DEFAULT NULL,
  `ret_errorcode` int(11) NOT NULL,
  `ret_fatalerror` tinyint(1) DEFAULT NULL,
  `ret_errormsg` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ret_additionalmsg` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ret_authcode` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `ret_ip` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `ret_booknr` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `ret_trx_number` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `redirect_needed` tinyint(1) NOT NULL,
  `trx_paymentmethod` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `trx_paymentdata_country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `trx_remoteip_country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `trx_issuer_avs_response` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trx_payauth_status` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `paydata_cc_cardowner` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `paydata_cc_number` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `paydata_cc_expdata` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `paydata_cc_typ` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ret_status` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_order_payment_details`
--

CREATE TABLE `shop_order_payment_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_order_paypal`
--

CREATE TABLE `shop_order_paypal` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payer_id` varchar(13) NOT NULL,
  `payer_email` varchar(127) NOT NULL,
  `first_name` varchar(64) NOT NULL,
  `last_name` varchar(64) NOT NULL,
  `residence_country` varchar(2) NOT NULL,
  `payer_status` varchar(16) DEFAULT NULL,
  `receiver_id` varchar(13) DEFAULT NULL,
  `receiver_email` varchar(127) NOT NULL,
  `invoice` varchar(128) NOT NULL,
  `reason_code` varchar(16) DEFAULT NULL,
  `payment_type` varchar(16) NOT NULL,
  `payment_status` varchar(64) NOT NULL,
  `payment_date` datetime NOT NULL,
  `payment_gross` decimal(15,2) NOT NULL,
  `payment_fee` decimal(15,2) NOT NULL,
  `mc_currency` varchar(3) NOT NULL,
  `mc_gross` decimal(15,2) NOT NULL,
  `mc_fee` decimal(64,0) NOT NULL,
  `tax` decimal(15,2) DEFAULT NULL,
  `shipping` decimal(15,2) NOT NULL,
  `handling_amount` decimal(15,2) NOT NULL,
  `parent_txn_id` varchar(64) DEFAULT NULL,
  `txn_id` varchar(64) NOT NULL,
  `txn_type` varchar(64) DEFAULT NULL,
  `item_name` varchar(127) NOT NULL,
  `item_number` varchar(127) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `test_ipn` tinyint(1) NOT NULL,
  `transaction_subject` varchar(127) NOT NULL,
  `custom` varchar(255) NOT NULL,
  `protection_eligibility` varchar(64) NOT NULL,
  `notify_version` varchar(8) NOT NULL,
  `charset` varchar(64) NOT NULL,
  `ipn_track_id` varchar(64) NOT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shop_order_redsys`
--

CREATE TABLE `shop_order_redsys` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `Ds_Date` date DEFAULT NULL,
  `Ds_Hour` time DEFAULT NULL,
  `Ds_Amount` int(11) DEFAULT NULL,
  `Ds_Card_Country` int(11) DEFAULT NULL,
  `Ds_Currency` int(11) DEFAULT NULL,
  `Ds_Order` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Ds_Terminal` int(11) DEFAULT NULL,
  `Ds_Signature` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Ds_Response` int(11) DEFAULT NULL,
  `Ds_MechantData` varchar(1024) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Ds_SecurePayment` tinyint(1) DEFAULT NULL,
  `Ds_TransactionType` int(11) DEFAULT NULL,
  `Ds_AuthorisationCode` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Ds_ConsumerLang` int(11) DEFAULT NULL,
  `Ds_Card_Type` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Ds_ErrorCode` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_order_status`
--

CREATE TABLE `shop_order_status` (
  `id` int(11) NOT NULL,
  `name` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(64) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_settings`
--

CREATE TABLE `shop_settings` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `open_from` date DEFAULT NULL,
  `open_until` date DEFAULT NULL,
  `cancellation_date_50` date DEFAULT NULL,
  `cancellation_date_100` date DEFAULT NULL,
  `invoice_no_prefix` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `invoice_no_postfix` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `currency` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `tax` float DEFAULT NULL,
  `name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vat` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `correspondent_bank` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `bank_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bank_address` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `account_holder` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `account_no` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `iban` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bic` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `swift` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aba` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fax` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `add_footer` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `header` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `footer` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `creditcard` tinyint(1) NOT NULL DEFAULT 0,
  `banktransfer` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tournaments`
--

CREATE TABLE `tournaments` (
  `id` int(11) NOT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `start_on` date NOT NULL,
  `end_on` date NOT NULL,
  `enter_after` date NOT NULL,
  `enter_before` date NOT NULL,
  `accreditation_start` date DEFAULT NULL,
  `modify_before` date NOT NULL,
  `nation_id` int(11) DEFAULT NULL,
  `location` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `organizer_id` int(11) DEFAULT NULL,
  `committee_id` int(11) DEFAULT NULL,
  `host_id` int(11) DEFAULT NULL,
  `contractor_id` int(11) DEFAULT NULL,
  `dpa_id` int(11) DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `types`
--

CREATE TABLE `types` (
  `id` int(11) NOT NULL,
  `name` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(64) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `login_token` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `email` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `add_email` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `group_id` int(11) NOT NULL,
  `nation_id` int(11) DEFAULT NULL,
  `tournament_id` int(11) DEFAULT NULL,
  `language_id` int(11) DEFAULT NULL,
  `prefix_people` int(11) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `count_successful` int(11) NOT NULL DEFAULT 0,
  `count_failed` int(11) NOT NULL DEFAULT 0,
  `count_failed_since` int(11) NOT NULL DEFAULT 0,
  `count_requests` int(11) NOT NULL DEFAULT 0,
  `ticket` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ticket_expires` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acos`
--
ALTER TABLE `acos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `acos_lft_rght` (`lft`,`rght`),
  ADD KEY `acos_alias` (`alias`),
  ADD KEY `acos_model` (`model`);

--
-- Indexes for table `aros`
--
ALTER TABLE `aros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aros_lft_rght` (`lft`,`rght`),
  ADD KEY `aros_alias` (`alias`),
  ADD KEY `aros_model` (`model`);

--
-- Indexes for table `aros_acos`
--
ALTER TABLE `aros_acos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ARO_ACO_KEY` (`aro_id`,`aco_id`),
  ADD KEY `aros_acos_aro_id` (`aro_id`),
  ADD KEY `aros_acos_aco_id` (`aco_id`);

--
-- Indexes for table `competitions`
--
ALTER TABLE `competitions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tournament_id` (`tournament_id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `i18n`
--
ALTER TABLE `i18n`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `I18N_LOCALE_FIELD` (`locale`,`model`,`foreign_key`,`field`),
  ADD KEY `I18N_FIELD` (`model`,`foreign_key`,`field`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nations`
--
ALTER TABLE `nations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `organisations`
--
ALTER TABLE `organisations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `registration_id` (`registration_id`),
  ADD KEY `double_id` (`double_id`),
  ADD KEY `double_partner_id` (`double_partner_id`),
  ADD KEY `mixed_id` (`mixed_id`),
  ADD KEY `mixed_partner_id` (`mixed_partner_id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `single_id` (`single_id`),
  ADD KEY `replaced_by_id` (`replaced_by_id`);

--
-- Indexes for table `participant_histories`
--
ALTER TABLE `participant_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tournament_id` (`tournament_id`),
  ADD KEY `registration_id` (`registration_id`);

--
-- Indexes for table `people`
--
ALTER TABLE `people`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `display_name` (`display_name`),
  ADD KEY `nation_id` (`nation_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `person_id` (`person_id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `tournament_id` (`tournament_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting` (`setting`);

--
-- Indexes for table `shop_allotments`
--
ALTER TABLE `shop_allotments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shop_articles`
--
ALTER TABLE `shop_articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tournament_id` (`tournament_id`);

--
-- Indexes for table `shop_article_variants`
--
ALTER TABLE `shop_article_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`);

--
-- Indexes for table `shop_cancellation_fees`
--
ALTER TABLE `shop_cancellation_fees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shop_settings_id` (`shop_settings_id`);

--
-- Indexes for table `shop_countries`
--
ALTER TABLE `shop_countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shop_orders`
--
ALTER TABLE `shop_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_status_id` (`order_status_id`),
  ADD KEY `tournament_id` (`tournament_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shop_order_addresses`
--
ALTER TABLE `shop_order_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `country_id` (`country_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `shop_order_articles`
--
ALTER TABLE `shop_order_articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `article_variant_id` (`article_variant_id`),
  ADD KEY `person_id` (`person_id`);

--
-- Indexes for table `shop_order_article_histories`
--
ALTER TABLE `shop_order_article_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_article_id` (`order_article_id`);

--
-- Indexes for table `shop_order_authorizenet`
--
ALTER TABLE `shop_order_authorizenet`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `shop_order_bpayment`
--
ALTER TABLE `shop_order_bpayment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `shop_order_card_pointe`
--
ALTER TABLE `shop_order_card_pointe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `shop_order_comments`
--
ALTER TABLE `shop_order_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shop_order_dibs`
--
ALTER TABLE `shop_order_dibs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `shop_order_histories`
--
ALTER TABLE `shop_order_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shop_order_ipayment`
--
ALTER TABLE `shop_order_ipayment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Indexes for table `shop_order_payment_details`
--
ALTER TABLE `shop_order_payment_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `shop_order_paypal`
--
ALTER TABLE `shop_order_paypal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `shop_order_redsys`
--
ALTER TABLE `shop_order_redsys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `shop_order_status`
--
ALTER TABLE `shop_order_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shop_settings`
--
ALTER TABLE `shop_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tournament_id` (`tournament_id`);

--
-- Indexes for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `organizer_id` (`organizer_id`),
  ADD UNIQUE KEY `host_id` (`host_id`),
  ADD KEY `nation_id` (`nation_id`),
  ADD KEY `committee_id` (`committee_id`),
  ADD KEY `contractor_id` (`contractor_id`),
  ADD KEY `dpa_id` (`dpa_id`);

--
-- Indexes for table `types`
--
ALTER TABLE `types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `nation_id` (`nation_id`),
  ADD KEY `tournament_id` (`tournament_id`),
  ADD KEY `language_id` (`language_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acos`
--
ALTER TABLE `acos`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=633;
--
-- AUTO_INCREMENT for table `aros`
--
ALTER TABLE `aros`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1032;
--
-- AUTO_INCREMENT for table `aros_acos`
--
ALTER TABLE `aros_acos`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=453;
--
-- AUTO_INCREMENT for table `competitions`
--
ALTER TABLE `competitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;
--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT for table `i18n`
--
ALTER TABLE `i18n`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `nations`
--
ALTER TABLE `nations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=261;
--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `organisations`
--
ALTER TABLE `organisations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `participants`
--
ALTER TABLE `participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `participant_histories`
--
ALTER TABLE `participant_histories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `people`
--
ALTER TABLE `people`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shop_allotments`
--
ALTER TABLE `shop_allotments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shop_articles`
--
ALTER TABLE `shop_articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT for table `shop_article_variants`
--
ALTER TABLE `shop_article_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shop_cancellation_fees`
--
ALTER TABLE `shop_cancellation_fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shop_countries`
--
ALTER TABLE `shop_countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=253;
--
-- AUTO_INCREMENT for table `shop_orders`
--
ALTER TABLE `shop_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shop_order_addresses`
--
ALTER TABLE `shop_order_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shop_order_articles`
--
ALTER TABLE `shop_order_articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shop_order_article_histories`
--
ALTER TABLE `shop_order_article_histories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shop_order_authorizenet`
--
ALTER TABLE `shop_order_authorizenet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shop_order_bpayment`
--
ALTER TABLE `shop_order_bpayment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shop_order_card_pointe`
--
ALTER TABLE `shop_order_card_pointe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shop_order_comments`
--
ALTER TABLE `shop_order_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shop_order_dibs`
--
ALTER TABLE `shop_order_dibs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shop_order_histories`
--
ALTER TABLE `shop_order_histories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shop_order_ipayment`
--
ALTER TABLE `shop_order_ipayment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shop_order_payment_details`
--
ALTER TABLE `shop_order_payment_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shop_order_paypal`
--
ALTER TABLE `shop_order_paypal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shop_order_redsys`
--
ALTER TABLE `shop_order_redsys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shop_order_status`
--
ALTER TABLE `shop_order_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `shop_settings`
--
ALTER TABLE `shop_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `tournaments`
--
ALTER TABLE `tournaments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `types`
--
ALTER TABLE `types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=716;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `aros_acos`
--
ALTER TABLE `aros_acos`
  ADD CONSTRAINT `aros_acos_ibfk_1` FOREIGN KEY (`aro_id`) REFERENCES `aros` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `aros_acos_ibfk_2` FOREIGN KEY (`aco_id`) REFERENCES `acos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `competitions`
--
ALTER TABLE `competitions`
  ADD CONSTRAINT `competitions_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`),
  ADD CONSTRAINT `competitions_ibfk_2` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `participants`
--
ALTER TABLE `participants`
  ADD CONSTRAINT `participants_ibfk_1` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `participants_ibfk_10` FOREIGN KEY (`mixed_id`) REFERENCES `competitions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `participants_ibfk_11` FOREIGN KEY (`team_id`) REFERENCES `competitions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `participants_ibfk_12` FOREIGN KEY (`double_partner_id`) REFERENCES `registrations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `participants_ibfk_13` FOREIGN KEY (`replaced_by_id`) REFERENCES `registrations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `participants_ibfk_19` FOREIGN KEY (`mixed_partner_id`) REFERENCES `registrations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `participants_ibfk_20` FOREIGN KEY (`single_id`) REFERENCES `competitions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `participants_ibfk_21` FOREIGN KEY (`double_id`) REFERENCES `competitions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `participant_histories`
--
ALTER TABLE `participant_histories`
  ADD CONSTRAINT `participant_histories_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `participant_histories_ibfk_2` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `participant_histories_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `people`
--
ALTER TABLE `people`
  ADD CONSTRAINT `people_ibfk_1` FOREIGN KEY (`nation_id`) REFERENCES `nations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `people_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_19` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registrations_ibfk_20` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`),
  ADD CONSTRAINT `registrations_ibfk_21` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shop_allotments`
--
ALTER TABLE `shop_allotments`
  ADD CONSTRAINT `shop_allotments_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `shop_articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shop_allotments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shop_articles`
--
ALTER TABLE `shop_articles`
  ADD CONSTRAINT `shop_articles_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shop_article_variants`
--
ALTER TABLE `shop_article_variants`
  ADD CONSTRAINT `shop_article_variants_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `shop_articles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shop_cancellation_fees`
--
ALTER TABLE `shop_cancellation_fees`
  ADD CONSTRAINT `shop_cancellation_fees_ibfk_1` FOREIGN KEY (`shop_settings_id`) REFERENCES `shop_settings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shop_orders`
--
ALTER TABLE `shop_orders`
  ADD CONSTRAINT `shop_orders_ibfk_4` FOREIGN KEY (`order_status_id`) REFERENCES `shop_order_status` (`id`),
  ADD CONSTRAINT `shop_orders_ibfk_5` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`),
  ADD CONSTRAINT `shop_orders_ibfk_6` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `shop_order_addresses`
--
ALTER TABLE `shop_order_addresses`
  ADD CONSTRAINT `shop_order_addresses_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `shop_countries` (`id`),
  ADD CONSTRAINT `shop_order_addresses_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `shop_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shop_order_articles`
--
ALTER TABLE `shop_order_articles`
  ADD CONSTRAINT `shop_order_articles_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `shop_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shop_order_articles_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `shop_articles` (`id`),
  ADD CONSTRAINT `shop_order_articles_ibfk_3` FOREIGN KEY (`article_variant_id`) REFERENCES `shop_article_variants` (`id`),
  ADD CONSTRAINT `shop_order_articles_ibfk_4` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`);

--
-- Constraints for table `shop_order_article_histories`
--
ALTER TABLE `shop_order_article_histories`
  ADD CONSTRAINT `shop_order_article_histories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `shop_order_article_histories_ibfk_2` FOREIGN KEY (`order_article_id`) REFERENCES `shop_order_articles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shop_order_authorizenet`
--
ALTER TABLE `shop_order_authorizenet`
  ADD CONSTRAINT `shop_order_authorizenet_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `shop_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shop_order_bpayment`
--
ALTER TABLE `shop_order_bpayment`
  ADD CONSTRAINT `shop_order_bpayment_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `shop_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shop_order_card_pointe`
--
ALTER TABLE `shop_order_card_pointe`
  ADD CONSTRAINT `shop_order_card_pointe_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `shop_orders` (`id`);

--
-- Constraints for table `shop_order_comments`
--
ALTER TABLE `shop_order_comments`
  ADD CONSTRAINT `shop_order_comments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `shop_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shop_order_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `shop_order_dibs`
--
ALTER TABLE `shop_order_dibs`
  ADD CONSTRAINT `shop_order_dibs_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `shop_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shop_order_histories`
--
ALTER TABLE `shop_order_histories`
  ADD CONSTRAINT `shop_order_histories_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `shop_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shop_order_histories_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `shop_order_ipayment`
--
ALTER TABLE `shop_order_ipayment`
  ADD CONSTRAINT `shop_order_ipayment_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `shop_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shop_order_payment_details`
--
ALTER TABLE `shop_order_payment_details`
  ADD CONSTRAINT `shop_order_payment_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `shop_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shop_order_paypal`
--
ALTER TABLE `shop_order_paypal`
  ADD CONSTRAINT `shop_order_paypal_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `shop_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shop_settings`
--
ALTER TABLE `shop_settings`
  ADD CONSTRAINT `shop_settings_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD CONSTRAINT `tournaments_ibfk_1` FOREIGN KEY (`nation_id`) REFERENCES `nations` (`id`) ON DELETE NO ACTION,
  ADD CONSTRAINT `tournaments_ibfk_2` FOREIGN KEY (`organizer_id`) REFERENCES `organisations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tournaments_ibfk_3` FOREIGN KEY (`host_id`) REFERENCES `organisations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tournaments_ibfk_4` FOREIGN KEY (`committee_id`) REFERENCES `organisations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tournaments_ibfk_5` FOREIGN KEY (`contractor_id`) REFERENCES `organisations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tournaments_ibfk_6` FOREIGN KEY (`dpa_id`) REFERENCES `organisations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`nation_id`) REFERENCES `nations` (`id`),
  ADD CONSTRAINT `users_ibfk_3` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`),
  ADD CONSTRAINT `users_ibfk_4` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
