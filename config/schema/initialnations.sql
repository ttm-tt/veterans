-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 26, 2019 at 11:56 AM
-- Server version: 10.1.37-MariaDB-3
-- PHP Version: 7.3.1-1

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `veterans-v3`
--

--
-- Dumping data for table `nations`
--

INSERT INTO `nations` (`id`, `name`, `description`, `continent`, `modified`, `created`) VALUES
(1, 'AUT', 'Austria', 'Europe', '2013-09-25 09:39:24', '2010-09-13 10:38:19'),
(2, 'CZE', 'Czech Republic', 'Europe', '2010-09-13 10:38:38', '2010-09-13 10:38:38'),
(3, 'GER', 'Germany', 'Europe', '2010-09-13 10:38:46', '2010-09-13 10:38:46'),
(4, 'NED', 'Netherlands', 'Europe', '2010-09-13 10:38:55', '2010-09-13 10:38:55'),
(42, 'ARM', 'Armenia', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(43, 'AZE', 'Azerbaijan', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(44, 'BEL', 'Belgium', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(45, 'BIH', 'Bosnia and Herzegovina', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(46, 'BLR', 'Belarus', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(47, 'BUL', 'Bulgaria', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(48, 'CYP', 'Cyprus', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(49, 'DEN', 'Denmark', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(50, 'ENG', 'England', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(51, 'ESP', 'Spain', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(52, 'EST', 'Estonia', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(53, 'FIN', 'Finland', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(54, 'FRA', 'France', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(55, 'GEO', 'Georgia', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(56, 'GRE', 'Greece', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(57, 'CRO', 'Croatia', 'Europe', '2012-11-16 12:08:53', '2010-10-14 19:15:48'),
(58, 'HUN', 'Hungary', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(59, 'IRL', 'Ireland', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(60, 'ISR', 'Israel', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(61, 'ITA', 'Italy', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(62, 'KOS', 'Kosovo', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(63, 'LAT', 'Latvia', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(64, 'LTU', 'Lithuania', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(65, 'LUX', 'Luxembourg', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(66, 'MDA', 'Moldova', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(67, 'MKD', 'Macedonia', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(68, 'MNE', 'Montenegro', 'Europe', '2010-10-14 19:15:48', '2010-10-14 19:15:48'),
(69, 'NOR', 'Norway', 'Europe', '2010-10-14 19:15:49', '2010-10-14 19:15:49'),
(70, 'POL', 'Poland', 'Europe', '2010-10-14 19:15:49', '2010-10-14 19:15:49'),
(71, 'POR', 'Portugal', 'Europe', '2010-10-14 19:15:49', '2010-10-14 19:15:49'),
(72, 'ROU', 'Romania', 'Europe', '2010-10-14 19:15:49', '2010-10-14 19:15:49'),
(73, 'RUS', 'Russia', 'Europe', '2010-10-14 19:15:49', '2010-10-14 19:15:49'),
(74, 'SCO', 'Scotland', 'Europe', '2010-10-14 19:15:49', '2010-10-14 19:15:49'),
(75, 'SMR', 'San Marino', 'Europe', '2010-10-14 19:15:49', '2010-10-14 19:15:49'),
(76, 'SRB', 'Serbia', 'Europe', '2010-10-14 19:15:49', '2010-10-14 19:15:49'),
(77, 'SUI', 'Switzerland', 'Europe', '2010-10-14 19:15:49', '2010-10-14 19:15:49'),
(78, 'SVK', 'Slovakia', 'Europe', '2010-10-14 19:15:49', '2010-10-14 19:15:49'),
(79, 'SLO', 'Slovenia', 'Europe', '2012-11-16 12:09:06', '2010-10-14 19:15:49'),
(80, 'SWE', 'Sweden', 'Europe', '2010-10-14 19:15:49', '2010-10-14 19:15:49'),
(81, 'TUR', 'Turkey', 'Europe', '2010-10-14 19:15:49', '2010-10-14 19:15:49'),
(82, 'UKR', 'Ukraine', 'Europe', '2010-10-14 19:15:49', '2010-10-14 19:15:49'),
(83, 'WAL', 'Wales', 'Europe', '2010-10-14 19:15:49', '2010-10-14 19:15:49'),
(84, 'GGY', 'Guernsey', 'Europe', '2011-08-27 11:03:36', '2011-08-27 11:03:36'),
(85, 'ALB', 'Albania', 'Europe', '2011-08-27 11:04:04', '2011-08-27 11:04:04'),
(86, 'AND', 'Andorra', 'Europe', '2011-08-27 11:04:21', '2011-08-27 11:04:21'),
(87, 'FAR', 'Faroe Island', 'Europe', '2011-08-27 11:06:21', '2011-08-27 11:06:21'),
(88, 'IMN', 'Isle of Man', 'Europe', '2011-08-27 11:07:31', '2011-08-27 11:07:31'),
(89, 'ISL', 'Iceland', 'Europe', '2011-08-27 11:07:58', '2011-08-27 11:07:58'),
(90, 'JEY', 'Jersey', 'Europe', '2011-08-27 11:08:28', '2011-08-27 11:08:28'),
(91, 'LIE', 'Liechtenstein', 'Europe', '2011-08-27 11:08:57', '2011-08-27 11:08:57'),
(92, 'MLT', 'Malta', 'Europe', '2011-08-27 11:09:22', '2011-08-27 11:09:22'),
(93, 'MON', 'Monaco', 'Europe', '2011-08-27 11:09:45', '2011-08-27 11:09:45'),
(94, 'NZL', 'New Zealand', 'Oceania', '2013-12-29 17:44:15', '2013-12-29 17:44:15'),
(95, 'AFG', 'Afghanistan', 'Asia', NULL, NULL),
(96, 'ALG', 'Algeria', 'Africa', NULL, NULL),
(97, 'ASA', 'American Samoa', 'Oceania', NULL, NULL),
(98, 'ANG', 'Angola', 'Africa', NULL, NULL),
(99, 'AIA', 'Anguilla', 'Latin America', NULL, NULL),
(100, 'ANT', 'Antigua and Barbados', 'Latin America', NULL, NULL),
(101, 'ARG', 'Argentina', 'Latin America', NULL, NULL),
(102, 'ARU', 'Aruba', 'Latin America', NULL, NULL),
(103, 'AUS', 'Australia', 'Australia', NULL, NULL),
(104, 'BRN', 'Bahrain', 'Asia', NULL, NULL),
(105, 'BAN', 'Bangladesh', 'Asia', NULL, NULL),
(106, 'BAR', 'Barbados', 'Latin America', NULL, NULL),
(107, 'BIZ', 'Belize', 'Laatin America', NULL, NULL),
(108, 'BEN', 'Benin', 'Africa', NULL, NULL),
(109, 'BER', 'Bermuda', 'North America', NULL, NULL),
(110, 'BHU', 'Bhutan', 'Asia', NULL, NULL),
(111, 'BOL', 'Bolivia', 'Latin America', NULL, NULL),
(112, 'BES', 'Bonaire', 'Latin America', NULL, NULL),
(113, 'BOT', 'Botswana', 'Africa', NULL, NULL),
(114, 'BRA', 'Brazil', 'Latin America', NULL, NULL),
(115, 'IVB', 'British Virgin Islands', 'ITTF', NULL, NULL),
(116, 'BRU', 'Brunei', 'Asia', NULL, NULL),
(117, 'BUR', 'Burkina Faso', 'Africa', NULL, NULL),
(118, 'CAM', 'Cambodia', 'Asia', NULL, NULL),
(119, 'CMR', 'Cameroon', 'Africa', NULL, NULL),
(120, 'CAN', 'Canada', 'North America', NULL, NULL),
(121, 'CAY', 'Cayman Islands', 'Latin America', NULL, NULL),
(122, 'CAF', 'Central Africa', 'Africa', NULL, NULL),
(123, 'CHA', 'Chad', 'Africa', NULL, NULL),
(124, 'CHI', 'Chile', 'Latin America', NULL, NULL),
(125, 'CHN', 'China', 'Asia', NULL, NULL),
(126, 'TPE', 'Chinese Taipei', 'Asia', NULL, NULL),
(127, 'COL', 'Colombia', 'Latin America', NULL, NULL),
(128, 'COM', 'Comoros', 'Africa', NULL, NULL),
(129, 'CGO', 'Congo Brazzaville', 'Africa', NULL, NULL),
(130, 'COD', 'Congo Democratic', 'Africa', NULL, NULL),
(131, 'COK', 'Cook Islands', 'Oceania', NULL, NULL),
(132, 'CRC', 'Costa Rica', 'Latin America', NULL, NULL),
(133, 'CIV', 'Cote D\'Ivoire', 'Africa', NULL, NULL),
(134, 'CUB', 'Cuba', 'Latin America', NULL, NULL),
(135, 'CUW', 'Curacao', 'Latin America', NULL, NULL),
(136, 'DJI', 'Djibouti', 'Africa', NULL, NULL),
(137, 'DMA', 'Dominica W.I.', 'Latin America', NULL, NULL),
(138, 'DOM', 'Dominican Republic', 'Latin America', NULL, NULL),
(139, 'TLS', 'East Timor', 'Asia', NULL, NULL),
(140, 'ECU', 'Ecuador', 'Latin America', NULL, NULL),
(141, 'EGY', 'Egypt', 'Africa', NULL, NULL),
(142, 'ESA', 'El Salvador', 'Latin America', NULL, NULL),
(143, 'GEQ', 'Equatorial Guinea', 'Africa', NULL, NULL),
(144, 'ETH', 'Ethiopia', 'Africa', NULL, NULL),
(145, 'FIJ', 'Fiji Islands', 'Oceania', NULL, NULL),
(146, 'GAB', 'Gabon', 'Africa', NULL, NULL),
(147, 'GAM', 'Gambia', 'Africa', NULL, NULL),
(148, 'GHA', 'Ghana', 'Africa', NULL, NULL),
(149, 'GIB', 'Gibraltar', 'Europe', NULL, NULL),
(150, 'GRL', 'Greenland', 'Europe', NULL, NULL),
(151, 'GRN', 'Grenada', 'Latin America', NULL, NULL),
(152, 'GUM', 'Guam', 'Oceania', NULL, NULL),
(153, 'GUA', 'Guatemala', 'Latin America', NULL, NULL),
(154, 'GUI', 'Guinea', 'Africa', NULL, NULL),
(155, 'GUY', 'Guayana', 'Latin America', NULL, NULL),
(156, 'HAI', 'Haiti', 'Latin America', NULL, NULL),
(157, 'HON', 'Honduras', 'Latin America', NULL, NULL),
(158, 'HKG', 'Hong Kong', 'Asia', NULL, NULL),
(159, 'IND', 'India', 'Asia', NULL, NULL),
(160, 'INA', 'Indonesia', 'Asia', NULL, NULL),
(161, 'IRI', 'Iran', 'Asia', NULL, NULL),
(162, 'IRQ', 'Iraq', 'Asia', NULL, NULL),
(163, 'JAM', 'Jamaica', 'Latin America', NULL, NULL),
(164, 'JPN', 'Japan', 'Asia', NULL, NULL),
(165, 'JOR', 'Jordan', 'Asia', NULL, NULL),
(166, 'KAZ', 'Kazakhstan', 'Asia', NULL, NULL),
(167, 'KEN', 'Kenya', 'Africa', NULL, NULL),
(168, 'KIR', 'Kiribati', 'Oceania', NULL, NULL),
(169, 'PRK', 'Korea DPR', 'Asia', NULL, NULL),
(170, 'KOR', 'Korea Republic', 'Asia', NULL, NULL),
(171, 'KUW', 'Kuwait', 'Asia', NULL, NULL),
(172, 'KGZ', 'Kyrgyzstan', 'Asia', NULL, NULL),
(173, 'LAO', 'Laos', 'Asia', NULL, NULL),
(174, 'LIB', 'Lebanon', 'Asia', NULL, NULL),
(175, 'LES', 'Lesotho', 'Africa', NULL, NULL),
(176, 'LBR', 'Liberia', 'Africa', NULL, NULL),
(177, 'LBA', 'Libya', 'Africa', NULL, NULL),
(178, 'MAC', 'Macao', 'Asia', NULL, NULL),
(179, 'MAD', 'Madagascar', 'Africa', NULL, NULL),
(180, 'MAW', 'Malawi', 'Africa', NULL, NULL),
(181, 'MAS', 'Malaysia', 'Asia', NULL, NULL),
(182, 'MDV', 'Maledives', 'Asia', NULL, NULL),
(183, 'MLI', 'Mali', 'Africa', NULL, NULL),
(184, 'MHL', 'Marshall Islands', 'Oceania', NULL, NULL),
(185, 'MTN', 'Mauritania', 'Africa', NULL, NULL),
(186, 'MRI', 'Mauritius', 'Africa', NULL, NULL),
(187, 'MEX', 'Mexico', 'Latin America', NULL, NULL),
(188, 'FSM', 'Micronesia', 'Oceania', NULL, NULL),
(189, 'MGL', 'Mongolia', 'Asia', NULL, NULL),
(190, 'MAR', 'Marocco', 'Africa', NULL, NULL),
(191, 'MOZ', 'Mozambique', 'Africa', NULL, NULL),
(192, 'MYA', 'Myanmar', 'Asia', NULL, NULL),
(193, 'NAM', 'Namibia', 'Africa', NULL, NULL),
(194, 'NRU', 'Nauru', 'Oceania', NULL, NULL),
(195, 'NEP', 'Nepal', 'Asia', NULL, NULL),
(196, 'NCL', 'New Caledonia', 'Oceania', NULL, NULL),
(197, 'NCA', 'Nicaragua', 'Latin America', NULL, NULL),
(198, 'NIG', 'Niger', 'Africa', NULL, NULL),
(199, 'NGR', 'Nigeria', 'Africa', NULL, NULL),
(200, 'NIU', 'Niue', 'Oceania', NULL, NULL),
(201, 'NFK', 'Norfolk Island', 'Oceania', NULL, NULL),
(202, 'MNP', 'North Mariana Islands', 'Oceania', NULL, NULL),
(203, 'OMA', 'Oman', 'Asia', NULL, NULL),
(204, 'PAK', 'Pakistan', 'Asia', NULL, NULL),
(205, 'PLW', 'Palau', 'Oceania', NULL, NULL),
(206, 'PLE', 'Palestine', 'Asia', NULL, NULL),
(207, 'PAN', 'Panama', 'Latin America', NULL, NULL),
(208, 'PNG', 'Papua New Guinea', 'Oceania', NULL, NULL),
(209, 'PAR', 'Paraguay', 'Latin America', NULL, NULL),
(210, 'PER', 'Peru', 'Latin America', NULL, NULL),
(211, 'PHI', 'Philippines', 'Asia', NULL, NULL),
(212, 'PUR', 'Puerto Rico', 'Latin America', NULL, NULL),
(213, 'QAT', 'Qatar', 'Asia', NULL, NULL),
(214, 'RWA', 'Rwanda', 'Africa', NULL, NULL),
(215, 'SKN', 'Saint Kitts and Nevis', 'Latin America', NULL, NULL),
(216, 'SAM', 'Samoa', 'Oceania', NULL, NULL),
(217, 'STP', 'Sao Tome and Principe', 'Africa', NULL, NULL),
(218, 'KSA', 'Saudi Arabia', 'Asia', NULL, NULL),
(219, 'SEN', 'Senegal', 'Africa', NULL, NULL),
(220, 'SEY', 'Seychelles', 'Africa', NULL, NULL),
(221, 'SLE', 'Sierra Leone', 'Africa', NULL, NULL),
(222, 'SIN', 'Singapore', 'Asia', NULL, NULL),
(223, 'SOL', 'Solomon Islands', 'Oceania', NULL, NULL),
(224, 'SOM', 'Somalia', 'Africa', NULL, NULL),
(225, 'RSA', 'South Africa', 'Africa', NULL, NULL),
(226, 'SSD', 'South Sudan', 'Africa', NULL, NULL),
(227, 'SRI', 'Sri Lanka', 'Asia', NULL, NULL),
(228, 'LCA', 'St. Lucia', 'Latin America', NULL, NULL),
(229, 'MAF', 'St. Maarten', 'ITTF', NULL, NULL),
(230, 'VIN', 'St. Vincent', 'Latin America', NULL, NULL),
(231, 'SUD', 'Sudan', 'Africa', NULL, NULL),
(232, 'SUR', 'Suriname', 'Latin America', NULL, NULL),
(233, 'SWZ', 'Swaziland', 'Africa', NULL, NULL),
(234, 'SYR', 'Syria', 'Asia', NULL, NULL),
(235, 'PYF', 'Tahiti', 'Oceania', NULL, NULL),
(236, 'TJK', 'Tajikistan', 'Asisa', NULL, NULL),
(237, 'TAN', 'Tanzania', 'Africa', NULL, NULL),
(238, 'THA', 'Thailand', 'Asia', NULL, NULL),
(239, 'TOG', 'Togo', 'Africa', NULL, NULL),
(240, 'TKL', 'Tokelau', 'Oceania', NULL, NULL),
(241, 'TGA', 'Tonga', 'Oceania', NULL, NULL),
(242, 'TTO', 'Trinidad and Tobago', 'Latin America', NULL, NULL),
(243, 'TUN', 'Tunisia', 'Africa', NULL, NULL),
(244, 'TKM', 'Turkmenistan', 'Asia', NULL, NULL),
(245, 'TUV', 'Tuvalu', 'Oceania', NULL, NULL),
(246, 'UGA', 'Uganda', 'Africa', NULL, NULL),
(247, 'UAE', 'United Arab Emirates', 'Asia', NULL, NULL),
(248, 'URU', 'Ururguay', 'Latin America', NULL, NULL),
(249, 'ISV', 'US Virgin Islands', 'North America', NULL, NULL),
(250, 'USA', 'USA', 'North America', NULL, NULL),
(251, 'UZB', 'Uzbekistan', 'Asia', NULL, NULL),
(252, 'VAN', 'Vanuatu', 'Oceania', NULL, NULL),
(253, 'VEN', 'Venezuela', 'Latin America', NULL, NULL),
(254, 'VIE', 'Vietnam', 'Asia', NULL, NULL),
(255, 'WLF', 'Wallis and Futuna', 'Oceania', NULL, NULL),
(256, 'YEM', 'Yemen', 'Asia', NULL, NULL),
(257, 'ZAM', 'Zambia', 'Africa', NULL, NULL),
(258, 'ZIM', 'Zimbabwe', 'Afrcia', NULL, NULL),
(259, 'BAH', 'Bahamas', 'Latin America', NULL, NULL);

--
-- Dumping data for table `shop_countries`
--

INSERT INTO `shop_countries` (`id`, `name`, `iso_code_2`, `iso_code_3`, `modified`, `created`) VALUES
(1, 'Afghanistan', 'AF', 'AFG', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(2, 'Albania', 'AL', 'ALB', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(3, 'Algeria', 'DZ', 'DZA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(4, 'American Samoa', 'AS', 'ASM', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(5, 'Andorra', 'AD', 'AND', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(6, 'Angola', 'AO', 'AGO', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(7, 'Anguilla', 'AI', 'AIA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(8, 'Antarctica', 'AQ', 'ATA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(9, 'Antigua and Barbuda', 'AG', 'ATG', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(10, 'Argentina', 'AR', 'ARG', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(11, 'Armenia', 'AM', 'ARM', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(12, 'Aruba', 'AW', 'ABW', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(13, 'Australia', 'AU', 'AUS', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(14, 'Austria', 'AT', 'AUT', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(15, 'Azerbaijan', 'AZ', 'AZE', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(16, 'Bahamas', 'BS', 'BHS', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(17, 'Bahrain', 'BH', 'BHR', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(18, 'Bangladesh', 'BD', 'BGD', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(19, 'Barbados', 'BB', 'BRB', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(20, 'Belarus', 'BY', 'BLR', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(21, 'Belgium', 'BE', 'BEL', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(22, 'Belize', 'BZ', 'BLZ', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(23, 'Benin', 'BJ', 'BEN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(24, 'Bermuda', 'BM', 'BMU', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(25, 'Bhutan', 'BT', 'BTN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(26, 'Bolivia', 'BO', 'BOL', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(27, 'Bosnia and Herzegovina', 'BA', 'BIH', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(28, 'Botswana', 'BW', 'BWA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(29, 'Bouvet Island', 'BV', 'BVT', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(30, 'Brazil', 'BR', 'BRA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(31, 'British Indian Ocean Territory', 'IO', 'IOT', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(32, 'Brunei Darussalam', 'BN', 'BRN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(33, 'Bulgaria', 'BG', 'BGR', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(34, 'Burkina Faso', 'BF', 'BFA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(35, 'Burundi', 'BI', 'BDI', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(36, 'Cambodia', 'KH', 'KHM', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(37, 'Cameroon', 'CM', 'CMR', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(38, 'Canada', 'CA', 'CAN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(39, 'Cape Verde', 'CV', 'CPV', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(40, 'Cayman Islands', 'KY', 'CYM', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(41, 'Central African Republic', 'CF', 'CAF', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(42, 'Chad', 'TD', 'TCD', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(43, 'Chile', 'CL', 'CHL', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(44, 'China', 'CN', 'CHN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(45, 'Christmas Island', 'CX', 'CXR', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(46, 'Cocos (Keeling) Islands', 'CC', 'CCK', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(47, 'Colombia', 'CO', 'COL', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(48, 'Comoros', 'KM', 'COM', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(49, 'Congo', 'CG', 'COG', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(50, 'Cook Islands', 'CK', 'COK', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(51, 'Costa Rica', 'CR', 'CRI', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(52, 'Cote D\'Ivoire', 'CI', 'CIV', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(53, 'Croatia', 'HR', 'HRV', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(54, 'Cuba', 'CU', 'CUB', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(55, 'Cyprus', 'CY', 'CYP', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(56, 'Czech Republic', 'CZ', 'CZE', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(57, 'Denmark', 'DK', 'DNK', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(58, 'Djibouti', 'DJ', 'DJI', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(59, 'Dominica', 'DM', 'DMA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(60, 'Dominican Republic', 'DO', 'DOM', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(61, 'East Timor', 'TL', 'TLS', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(62, 'Ecuador', 'EC', 'ECU', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(63, 'Egypt', 'EG', 'EGY', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(64, 'El Salvador', 'SV', 'SLV', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(65, 'Equatorial Guinea', 'GQ', 'GNQ', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(66, 'Eritrea', 'ER', 'ERI', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(67, 'Estonia', 'EE', 'EST', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(68, 'Ethiopia', 'ET', 'ETH', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(69, 'Falkland Islands (Malvinas)', 'FK', 'FLK', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(70, 'Faroe Islands', 'FO', 'FRO', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(71, 'Fiji', 'FJ', 'FJI', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(72, 'Finland', 'FI', 'FIN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(74, 'France, Metropolitan', 'FR', 'FRA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(75, 'French Guiana', 'GF', 'GUF', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(76, 'French Polynesia', 'PF', 'PYF', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(77, 'French Southern Territories', 'TF', 'ATF', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(78, 'Gabon', 'GA', 'GAB', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(79, 'Gambia', 'GM', 'GMB', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(80, 'Georgia', 'GE', 'GEO', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(81, 'Germany', 'DE', 'DEU', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(82, 'Ghana', 'GH', 'GHA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(83, 'Gibraltar', 'GI', 'GIB', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(84, 'Greece', 'GR', 'GRC', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(85, 'Greenland', 'GL', 'GRL', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(86, 'Grenada', 'GD', 'GRD', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(87, 'Guadeloupe', 'GP', 'GLP', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(88, 'Guam', 'GU', 'GUM', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(89, 'Guatemala', 'GT', 'GTM', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(90, 'Guinea', 'GN', 'GIN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(91, 'Guinea-Bissau', 'GW', 'GNB', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(92, 'Guyana', 'GY', 'GUY', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(93, 'Haiti', 'HT', 'HTI', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(94, 'Heard and Mc Donald Islands', 'HM', 'HMD', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(95, 'Honduras', 'HN', 'HND', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(96, 'Hong Kong', 'HK', 'HKG', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(97, 'Hungary', 'HU', 'HUN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(98, 'Iceland', 'IS', 'ISL', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(99, 'India', 'IN', 'IND', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(100, 'Indonesia', 'ID', 'IDN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(101, 'Iran (Islamic Republic of)', 'IR', 'IRN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(102, 'Iraq', 'IQ', 'IRQ', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(103, 'Ireland', 'IE', 'IRL', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(104, 'Israel', 'IL', 'ISR', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(105, 'Italy', 'IT', 'ITA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(106, 'Jamaica', 'JM', 'JAM', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(107, 'Japan', 'JP', 'JPN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(108, 'Jordan', 'JO', 'JOR', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(109, 'Kazakhstan', 'KZ', 'KAZ', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(110, 'Kenya', 'KE', 'KEN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(111, 'Kiribati', 'KI', 'KIR', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(112, 'North Korea', 'KP', 'PRK', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(113, 'Korea, Republic of', 'KR', 'KOR', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(114, 'Kuwait', 'KW', 'KWT', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(115, 'Kyrgyzstan', 'KG', 'KGZ', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(116, 'Lao People\'s Democratic Republic', 'LA', 'LAO', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(117, 'Latvia', 'LV', 'LVA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(118, 'Lebanon', 'LB', 'LBN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(119, 'Lesotho', 'LS', 'LSO', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(120, 'Liberia', 'LR', 'LBR', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(121, 'Libyan Arab Jamahiriya', 'LY', 'LBY', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(122, 'Liechtenstein', 'LI', 'LIE', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(123, 'Lithuania', 'LT', 'LTU', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(124, 'Luxembourg', 'LU', 'LUX', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(125, 'Macau', 'MO', 'MAC', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(126, 'FYROM', 'MK', 'MKD', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(127, 'Madagascar', 'MG', 'MDG', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(128, 'Malawi', 'MW', 'MWI', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(129, 'Malaysia', 'MY', 'MYS', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(130, 'Maldives', 'MV', 'MDV', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(131, 'Mali', 'ML', 'MLI', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(132, 'Malta', 'MT', 'MLT', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(133, 'Marshall Islands', 'MH', 'MHL', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(134, 'Martinique', 'MQ', 'MTQ', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(135, 'Mauritania', 'MR', 'MRT', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(136, 'Mauritius', 'MU', 'MUS', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(137, 'Mayotte', 'YT', 'MYT', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(138, 'Mexico', 'MX', 'MEX', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(139, 'Micronesia, Federated States of', 'FM', 'FSM', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(140, 'Moldova, Republic of', 'MD', 'MDA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(141, 'Monaco', 'MC', 'MCO', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(142, 'Mongolia', 'MN', 'MNG', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(143, 'Montserrat', 'MS', 'MSR', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(144, 'Morocco', 'MA', 'MAR', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(145, 'Mozambique', 'MZ', 'MOZ', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(146, 'Myanmar', 'MM', 'MMR', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(147, 'Namibia', 'NA', 'NAM', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(148, 'Nauru', 'NR', 'NRU', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(149, 'Nepal', 'NP', 'NPL', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(150, 'Netherlands', 'NL', 'NLD', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(151, 'Netherlands Antilles', 'AN', 'ANT', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(152, 'New Caledonia', 'NC', 'NCL', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(153, 'New Zealand', 'NZ', 'NZL', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(154, 'Nicaragua', 'NI', 'NIC', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(155, 'Niger', 'NE', 'NER', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(156, 'Nigeria', 'NG', 'NGA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(157, 'Niue', 'NU', 'NIU', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(158, 'Norfolk Island', 'NF', 'NFK', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(159, 'Northern Mariana Islands', 'MP', 'MNP', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(160, 'Norway', 'NO', 'NOR', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(161, 'Oman', 'OM', 'OMN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(162, 'Pakistan', 'PK', 'PAK', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(163, 'Palau', 'PW', 'PLW', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(164, 'Panama', 'PA', 'PAN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(165, 'Papua New Guinea', 'PG', 'PNG', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(166, 'Paraguay', 'PY', 'PRY', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(167, 'Peru', 'PE', 'PER', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(168, 'Philippines', 'PH', 'PHL', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(169, 'Pitcairn', 'PN', 'PCN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(170, 'Poland', 'PL', 'POL', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(171, 'Portugal', 'PT', 'PRT', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(172, 'Puerto Rico', 'PR', 'PRI', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(173, 'Qatar', 'QA', 'QAT', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(174, 'Reunion', 'RE', 'REU', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(175, 'Romania', 'RO', 'ROM', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(176, 'Russian Federation', 'RU', 'RUS', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(177, 'Rwanda', 'RW', 'RWA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(178, 'Saint Kitts and Nevis', 'KN', 'KNA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(179, 'Saint Lucia', 'LC', 'LCA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(180, 'Saint Vincent and the Grenadines', 'VC', 'VCT', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(181, 'Samoa', 'WS', 'WSM', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(182, 'San Marino', 'SM', 'SMR', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(183, 'Sao Tome and Principe', 'ST', 'STP', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(184, 'Saudi Arabia', 'SA', 'SAU', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(185, 'Senegal', 'SN', 'SEN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(186, 'Seychelles', 'SC', 'SYC', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(187, 'Sierra Leone', 'SL', 'SLE', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(188, 'Singapore', 'SG', 'SGP', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(189, 'Slovak Republic', 'SK', 'SVK', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(190, 'Slovenia', 'SI', 'SVN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(191, 'Solomon Islands', 'SB', 'SLB', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(192, 'Somalia', 'SO', 'SOM', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(193, 'South Africa', 'ZA', 'ZAF', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(194, 'South Georgia &amp; South Sandwich Islands', 'GS', 'SGS', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(195, 'Spain', 'ES', 'ESP', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(196, 'Sri Lanka', 'LK', 'LKA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(197, 'St. Helena', 'SH', 'SHN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(198, 'St. Pierre and Miquelon', 'PM', 'SPM', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(199, 'Sudan', 'SD', 'SDN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(200, 'Suriname', 'SR', 'SUR', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(201, 'Svalbard and Jan Mayen Islands', 'SJ', 'SJM', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(202, 'Swaziland', 'SZ', 'SWZ', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(203, 'Sweden', 'SE', 'SWE', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(204, 'Switzerland', 'CH', 'CHE', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(205, 'Syrian Arab Republic', 'SY', 'SYR', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(206, 'Taiwan', 'TW', 'TWN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(207, 'Tajikistan', 'TJ', 'TJK', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(208, 'Tanzania, United Republic of', 'TZ', 'TZA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(209, 'Thailand', 'TH', 'THA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(210, 'Togo', 'TG', 'TGO', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(211, 'Tokelau', 'TK', 'TKL', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(212, 'Tonga', 'TO', 'TON', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(213, 'Trinidad and Tobago', 'TT', 'TTO', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(214, 'Tunisia', 'TN', 'TUN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(215, 'Turkey', 'TR', 'TUR', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(216, 'Turkmenistan', 'TM', 'TKM', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(217, 'Turks and Caicos Islands', 'TC', 'TCA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(218, 'Tuvalu', 'TV', 'TUV', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(219, 'Uganda', 'UG', 'UGA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(220, 'Ukraine', 'UA', 'UKR', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(221, 'United Arab Emirates', 'AE', 'ARE', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(222, 'United Kingdom', 'GB', 'GBR', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(223, 'United States', 'US', 'USA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(224, 'United States Minor Outlying Islands', 'UM', 'UMI', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(225, 'Uruguay', 'UY', 'URY', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(226, 'Uzbekistan', 'UZ', 'UZB', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(227, 'Vanuatu', 'VU', 'VUT', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(228, 'Vatican City State (Holy See)', 'VA', 'VAT', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(229, 'Venezuela', 'VE', 'VEN', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(230, 'Viet Nam', 'VN', 'VNM', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(231, 'Virgin Islands (British)', 'VG', 'VGB', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(232, 'Virgin Islands (U.S.)', 'VI', 'VIR', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(233, 'Wallis and Futuna Islands', 'WF', 'WLF', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(234, 'Western Sahara', 'EH', 'ESH', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(235, 'Yemen', 'YE', 'YEM', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(237, 'Democratic Republic of Congo', 'CD', 'COD', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(238, 'Zambia', 'ZM', 'ZMB', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(239, 'Zimbabwe', 'ZW', 'ZWE', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(240, 'Jersey', 'JE', 'JEY', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(241, 'Guernsey', 'GG', 'GGY', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(242, 'Montenegro', 'ME', 'MNE', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(243, 'Serbia', 'RS', 'SRB', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(244, 'Aaland Islands', 'AX', 'ALA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(245, 'Bonaire, Sint Eustatius and Saba', 'BQ', 'BES', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(246, 'Curacao', 'CW', 'CUW', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(247, 'Palestinian Territory, Occupied', 'PS', 'PSE', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(248, 'South Sudan', 'SS', 'SSD', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(249, 'St. Barthelemy', 'BL', 'BLM', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(250, 'St. Martin (French part)', 'MF', 'MAF', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(251, 'Canary Islands', 'IC', 'ICA', '2013-10-09 14:31:06', '2013-10-09 14:31:06'),
(252, 'Kosovo', 'XK', 'XKX', '2019-05-11 00:00:00', '2019-05-11 00:00:00');
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
