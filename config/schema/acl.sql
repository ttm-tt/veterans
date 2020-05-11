-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 26, 2019 at 12:02 PM
-- Server version: 10.1.37-MariaDB-3
-- PHP Version: 7.3.1-1

SET FOREIGN_KEY_CHECKS=0;
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

--
-- Dumping data for table `acos`
--

INSERT INTO `acos` (`id`, `parent_id`, `model`, `foreign_key`, `alias`, `lft`, `rght`) VALUES
(347, NULL, NULL, NULL, 'controllers', 1, 328),
(348, 347, NULL, NULL, 'Pages', 2, 13),
(349, 348, NULL, NULL, 'display', 3, 4),
(350, 347, NULL, NULL, 'Groups', 14, 25),
(351, 350, NULL, NULL, 'index', 15, 16),
(352, 350, NULL, NULL, 'view', 17, 18),
(353, 350, NULL, NULL, 'add', 19, 20),
(354, 350, NULL, NULL, 'edit', 21, 22),
(355, 350, NULL, NULL, 'delete', 23, 24),
(356, 347, NULL, NULL, 'Nations', 26, 37),
(357, 356, NULL, NULL, 'index', 27, 28),
(358, 356, NULL, NULL, 'view', 29, 30),
(359, 356, NULL, NULL, 'add', 31, 32),
(360, 356, NULL, NULL, 'edit', 33, 34),
(361, 356, NULL, NULL, 'delete', 35, 36),
(362, 347, NULL, NULL, 'Types', 38, 49),
(363, 362, NULL, NULL, 'index', 39, 40),
(364, 362, NULL, NULL, 'view', 41, 42),
(365, 362, NULL, NULL, 'add', 43, 44),
(366, 362, NULL, NULL, 'edit', 45, 46),
(367, 362, NULL, NULL, 'delete', 47, 48),
(368, 347, NULL, NULL, 'Users', 50, 77),
(369, 368, NULL, NULL, 'login', 51, 52),
(370, 368, NULL, NULL, 'logout', 53, 54),
(371, 368, NULL, NULL, 'index', 55, 56),
(372, 368, NULL, NULL, 'view', 57, 58),
(373, 368, NULL, NULL, 'add', 59, 60),
(374, 368, NULL, NULL, 'edit', 61, 62),
(375, 368, NULL, NULL, 'delete', 63, 64),
(376, 347, NULL, NULL, 'Registrations', 78, 131),
(377, 376, NULL, NULL, 'index', 79, 80),
(378, 376, NULL, NULL, 'view', 81, 82),
(379, 376, NULL, NULL, 'add', 83, 84),
(380, 376, NULL, NULL, 'edit', 85, 86),
(381, 376, NULL, NULL, 'delete', 87, 88),
(382, 347, NULL, NULL, 'People', 132, 143),
(383, 382, NULL, NULL, 'index', 133, 134),
(384, 382, NULL, NULL, 'view', 135, 136),
(385, 382, NULL, NULL, 'add', 137, 138),
(386, 382, NULL, NULL, 'edit', 139, 140),
(387, 382, NULL, NULL, 'delete', 141, 142),
(388, 347, NULL, NULL, 'Tournaments', 144, 155),
(389, 388, NULL, NULL, 'index', 145, 146),
(390, 388, NULL, NULL, 'view', 147, 148),
(391, 388, NULL, NULL, 'add', 149, 150),
(392, 388, NULL, NULL, 'edit', 151, 152),
(393, 388, NULL, NULL, 'delete', 153, 154),
(394, 347, NULL, NULL, 'Competitions', 156, 167),
(395, 394, NULL, NULL, 'index', 157, 158),
(396, 394, NULL, NULL, 'view', 159, 160),
(397, 394, NULL, NULL, 'add', 161, 162),
(398, 394, NULL, NULL, 'edit', 163, 164),
(399, 394, NULL, NULL, 'delete', 165, 166),
(400, 347, NULL, NULL, 'AclEdit', 168, 223),
(401, 400, NULL, NULL, 'Acos', 169, 180),
(402, 401, NULL, NULL, 'admin_index', 170, 171),
(403, 401, NULL, NULL, 'admin_empty_acos', 172, 173),
(404, 401, NULL, NULL, 'admin_build_acl', 174, 175),
(405, 400, NULL, NULL, 'Aros', 181, 216),
(406, 405, NULL, NULL, 'admin_index', 182, 183),
(407, 405, NULL, NULL, 'admin_check', 184, 185),
(408, 405, NULL, NULL, 'admin_users', 186, 187),
(409, 405, NULL, NULL, 'admin_update_user_role', 188, 189),
(410, 405, NULL, NULL, 'admin_role_permissions', 190, 191),
(411, 405, NULL, NULL, 'admin_user_permissions', 192, 193),
(412, 405, NULL, NULL, 'admin_empty_permissions', 194, 195),
(413, 405, NULL, NULL, 'admin_grant_all_controllers', 196, 197),
(414, 405, NULL, NULL, 'admin_deny_all_controllers', 198, 199),
(415, 405, NULL, NULL, 'admin_grant_role_permission', 200, 201),
(416, 405, NULL, NULL, 'admin_deny_role_permission', 202, 203),
(417, 405, NULL, NULL, 'admin_grant_user_permission', 204, 205),
(418, 405, NULL, NULL, 'admin_deny_user_permission', 206, 207),
(419, 376, NULL, NULL, 'onChangePerson', 89, 90),
(421, 376, NULL, NULL, 'onChangeMixed', 91, 92),
(422, 376, NULL, NULL, 'onChangeDouble', 93, 94),
(423, 347, NULL, NULL, 'Rpc2', 224, 227),
(425, 423, NULL, NULL, 'index', 225, 226),
(426, 376, NULL, NULL, 'list_partner_wanted', 95, 96),
(436, 348, NULL, NULL, 'home', 5, 6),
(438, 376, NULL, NULL, 'count', 97, 98),
(455, 376, NULL, NULL, 'assign_numbers', 99, 100),
(456, 376, NULL, NULL, 'export', 101, 102),
(469, 368, NULL, NULL, 'forgot_password', 65, 66),
(482, 368, NULL, NULL, 'profile', 67, 68),
(483, 376, NULL, NULL, 'print_accreditation', 103, 104),
(491, 376, NULL, NULL, 'export_count', 105, 106),
(492, 376, NULL, NULL, 'print_accreditation_settings', 107, 108),
(494, 368, NULL, NULL, 'request_password', 69, 70),
(495, 376, NULL, NULL, 'import', 109, 110),
(496, 376, NULL, NULL, 'import_partner', 111, 112),
(498, 376, NULL, NULL, 'history', 113, 114),
(499, 376, NULL, NULL, 'revision', 115, 116),
(500, 400, NULL, NULL, 'AclEdit', 217, 222),
(501, 500, NULL, NULL, 'index', 218, 219),
(502, 500, NULL, NULL, 'admin_index', 220, 221),
(503, 401, NULL, NULL, 'admin_prune_acos', 176, 177),
(504, 401, NULL, NULL, 'admin_synchronize', 178, 179),
(505, 405, NULL, NULL, 'admin_ajax_role_permissions', 208, 209),
(506, 405, NULL, NULL, 'admin_clear_user_specific_permissions', 210, 211),
(507, 405, NULL, NULL, 'admin_get_role_controller_permission', 212, 213),
(508, 405, NULL, NULL, 'admin_get_user_controller_permission', 214, 215),
(549, 347, NULL, NULL, 'Shop', 228, 327),
(550, 549, NULL, NULL, 'Shops', 229, 276),
(551, 550, NULL, NULL, 'wizard', 230, 231),
(552, 550, NULL, NULL, 'add_person', 232, 233),
(553, 550, NULL, NULL, 'remove_person', 234, 235),
(554, 550, NULL, NULL, 'onAddItem', 236, 237),
(555, 550, NULL, NULL, 'onChangeQuantity', 238, 239),
(556, 550, NULL, NULL, 'onRemoveItem', 240, 241),
(561, 549, NULL, NULL, 'Articles', 277, 290),
(562, 561, NULL, NULL, 'index', 278, 279),
(563, 561, NULL, NULL, 'view', 280, 281),
(564, 561, NULL, NULL, 'add', 282, 283),
(565, 561, NULL, NULL, 'edit', 284, 285),
(566, 561, NULL, NULL, 'delete', 286, 287),
(567, 549, NULL, NULL, 'Orders', 291, 320),
(568, 567, NULL, NULL, 'index', 292, 293),
(569, 567, NULL, NULL, 'view', 294, 295),
(570, 567, NULL, NULL, 'settings', 296, 297),
(571, 567, NULL, NULL, 'history', 298, 299),
(572, 567, NULL, NULL, 'revision', 300, 301),
(573, 567, NULL, NULL, 'storno', 302, 303),
(574, 550, NULL, NULL, 'test', 242, 243),
(576, 550, NULL, NULL, 'receipt', 244, 245),
(577, 376, NULL, NULL, 'requests', 117, 118),
(578, 376, NULL, NULL, 'accept', 119, 120),
(579, 376, NULL, NULL, 'reject', 121, 122),
(580, 549, NULL, NULL, 'Pages', 321, 326),
(583, 550, NULL, NULL, 'onPrepareCreditcard', 246, 247),
(585, 376, NULL, NULL, 'add_participant', 123, 124),
(586, 567, NULL, NULL, 'export', 304, 305),
(587, 550, NULL, NULL, 'viewVoucher', 248, 249),
(588, 550, NULL, NULL, 'sendVoucher', 250, 251),
(589, 550, NULL, NULL, 'viewInvoice', 252, 253),
(590, 550, NULL, NULL, 'sendInvoice', 254, 255),
(592, 550, NULL, NULL, 'import', 256, 257),
(593, 567, NULL, NULL, 'unstorno', 306, 307),
(594, 567, NULL, NULL, 'edit_person', 308, 309),
(595, 368, NULL, NULL, 'notifications', 71, 72),
(596, 376, NULL, NULL, 'edit_participant', 125, 126),
(597, 376, NULL, NULL, 'delete_participant', 127, 128),
(598, 376, NULL, NULL, 'export_participants', 129, 130),
(599, 550, NULL, NULL, 'payment_error', 258, 259),
(600, 550, NULL, NULL, 'payment_complete', 260, 261),
(601, 550, NULL, NULL, 'setPending', 262, 263),
(602, 550, NULL, NULL, 'setPaid', 264, 265),
(603, 550, NULL, NULL, 'payment_success', 266, 267),
(604, 550, NULL, NULL, 'saveCart', 268, 269),
(605, 348, NULL, NULL, 'onParticipantData', 7, 8),
(606, 348, NULL, NULL, 'onParticipantCount', 9, 10),
(607, 368, NULL, NULL, 'onChangeLanguage', 73, 74),
(608, 567, NULL, NULL, 'search', 310, 311),
(609, 567, NULL, NULL, 'edit_invoice', 312, 313),
(610, 567, NULL, NULL, 'edit_address', 314, 315),
(613, 550, NULL, NULL, 'testPayment', 270, 271),
(614, 348, NULL, NULL, 'participants', 11, 12),
(615, 561, NULL, NULL, 'chart', 288, 289),
(616, 567, NULL, NULL, 'storno_pending', 316, 317),
(617, 580, NULL, NULL, 'shop_agb', 322, 323),
(618, 580, NULL, NULL, 'players_privacy', 324, 325),
(619, 550, NULL, NULL, 'send_reminder', 272, 273),
(620, 550, NULL, NULL, 'setDelayed', 274, 275),
(621, 368, NULL, NULL, 'send_welcome_mail', 75, 76),
(622, 567, NULL, NULL, 'export_players', 318, 319);

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

--
-- Dumping data for table `aros`
--

INSERT INTO `aros` (`id`, `parent_id`, `model`, `foreign_key`, `alias`, `lft`, `rght`) VALUES
(974, NULL, 'Groups', 1, 'Administrator', 1, 12),
(978, NULL, 'Groups', 5, 'Organizer', 13, 14),
(979, NULL, 'Groups', 7, 'Referee', 15, 16),
(980, NULL, 'Groups', 8, 'Participant', 17, 18),
(981, NULL, 'Groups', 9, 'Rpc', 19, 22),
(982, NULL, 'Groups', 10, 'Guest', 23, 24),
(983, 974, 'Users', 81, 'admin', 2, 3),
(985, 974, 'Users', 84, 'ettu', 4, 5),
(986, 974, 'Users', 89, 'theis', 6, 7),
(988, 981, 'Users', 713, 'rpc2', 20, 21),
(1028, NULL, 'Groups', 11, NULL, 25, 26),
(1029, NULL, 'Groups', 12, NULL, 27, 28),
(1030, 974, 'Users', 714, NULL, 8, 9),
(1031, 974, 'Users', 715, NULL, 10, 11);

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

--
-- Dumping data for table `aros_acos`
--

INSERT INTO `aros_acos` (`id`, `aro_id`, `aco_id`, `_create`, `_read`, `_update`, `_delete`) VALUES
(243, 974, 347, '1', '1', '1', '1'),
(310, 978, 395, '1', '1', '1', '1'),
(316, 978, 357, '1', '1', '1', '1'),
(322, 978, 349, '1', '1', '1', '1'),
(325, 978, 384, '1', '1', '1', '1'),
(326, 978, 456, '1', '1', '1', '1'),
(327, 978, 491, '1', '1', '1', '1'),
(328, 978, 377, '1', '1', '1', '1'),
(329, 978, 426, '1', '1', '1', '1'),
(330, 978, 483, '1', '1', '1', '1'),
(331, 978, 492, '1', '1', '1', '1'),
(332, 978, 378, '1', '1', '1', '1'),
(333, 978, 363, '1', '1', '1', '1'),
(334, 978, 482, '1', '1', '1', '1'),
(342, 979, 385, '1', '1', '1', '1'),
(343, 979, 386, '1', '1', '1', '1'),
(344, 979, 383, '1', '1', '1', '1'),
(347, 979, 384, '1', '1', '1', '1'),
(348, 979, 379, '1', '1', '1', '1'),
(349, 979, 381, '1', '1', '1', '1'),
(350, 979, 380, '1', '1', '1', '1'),
(351, 979, 377, '1', '1', '1', '1'),
(352, 979, 419, '1', '1', '1', '1'),
(353, 979, 378, '1', '1', '1', '1'),
(354, 980, 380, '1', '1', '1', '1'),
(355, 980, 377, '1', '1', '1', '1'),
(356, 980, 426, '1', '1', '1', '1'),
(357, 980, 422, '1', '1', '1', '1'),
(358, 980, 421, '1', '1', '1', '1'),
(360, 981, 425, '1', '1', '1', '1'),
(361, 982, 426, '1', '1', '1', '1'),
(362, 985, 404, '-1', '-1', '-1', '-1'),
(363, 985, 403, '-1', '-1', '-1', '-1'),
(364, 985, 402, '-1', '-1', '-1', '-1'),
(365, 985, 407, '-1', '-1', '-1', '-1'),
(366, 985, 414, '-1', '-1', '-1', '-1'),
(367, 985, 416, '-1', '-1', '-1', '-1'),
(368, 985, 418, '-1', '-1', '-1', '-1'),
(369, 985, 412, '-1', '-1', '-1', '-1'),
(370, 985, 413, '-1', '-1', '-1', '-1'),
(371, 985, 415, '-1', '-1', '-1', '-1'),
(372, 985, 417, '-1', '-1', '-1', '-1'),
(373, 985, 406, '-1', '-1', '-1', '-1'),
(374, 985, 410, '-1', '-1', '-1', '-1'),
(375, 985, 409, '-1', '-1', '-1', '-1'),
(376, 985, 411, '-1', '-1', '-1', '-1'),
(377, 985, 408, '-1', '-1', '-1', '-1'),
(378, 978, 564, '1', '1', '1', '1'),
(379, 978, 566, '1', '1', '1', '1'),
(380, 978, 565, '1', '1', '1', '1'),
(381, 978, 562, '1', '1', '1', '1'),
(382, 978, 563, '1', '1', '1', '1'),
(383, 978, 571, '1', '1', '1', '1'),
(384, 978, 568, '1', '1', '1', '1'),
(385, 978, 572, '1', '1', '1', '1'),
(386, 978, 570, '1', '1', '1', '1'),
(387, 978, 573, '1', '1', '1', '1'),
(388, 978, 569, '1', '1', '1', '1'),
(389, 980, 578, '1', '1', '1', '1'),
(390, 980, 579, '1', '1', '1', '1'),
(391, 980, 577, '1', '1', '1', '1'),
(392, 978, 385, '1', '1', '1', '1'),
(393, 978, 387, '1', '1', '1', '1'),
(394, 978, 386, '1', '1', '1', '1'),
(395, 978, 383, '1', '1', '1', '1'),
(397, 978, 379, '1', '1', '1', '1'),
(398, 978, 438, '1', '1', '1', '1'),
(399, 978, 381, '1', '1', '1', '1'),
(400, 978, 380, '1', '1', '1', '1'),
(401, 978, 498, '1', '1', '1', '1'),
(402, 980, 482, '1', '1', '1', '1'),
(403, 978, 586, '1', '1', '1', '1'),
(405, 978, 576, '1', '1', '1', '1'),
(406, 978, 590, '1', '1', '1', '1'),
(408, 978, 588, '1', '1', '1', '1'),
(409, 978, 589, '1', '1', '1', '1'),
(410, 978, 587, '1', '1', '1', '1'),
(411, 1028, 386, '1', '1', '1', '1'),
(412, 1028, 578, '1', '1', '1', '1'),
(415, 1028, 377, '1', '1', '1', '1'),
(416, 1028, 426, '1', '1', '1', '1'),
(417, 1028, 422, '1', '1', '1', '1'),
(418, 1028, 421, '1', '1', '1', '1'),
(419, 1028, 579, '1', '1', '1', '1'),
(420, 1028, 577, '1', '1', '1', '1'),
(421, 1028, 482, '1', '1', '1', '1'),
(422, 978, 593, '1', '1', '1', '1'),
(423, 978, 594, '1', '1', '1', '1'),
(424, 1028, 585, '1', '1', '1', '1'),
(425, 1028, 597, '1', '1', '1', '1'),
(426, 1028, 596, '1', '1', '1', '1'),
(427, 978, 608, '1', '1', '1', '1'),
(428, 978, 601, '1', '1', '1', '1'),
(429, 978, 602, '1', '1', '1', '1'),
(430, 978, 553, '-1', '-1', '-1', '-1'),
(431, 978, 610, '1', '1', '1', '1'),
(432, 978, 609, '1', '1', '1', '1'),
(433, 1029, 383, '1', '1', '1', '1'),
(434, 1029, 438, '1', '1', '1', '1'),
(435, 1029, 456, '1', '1', '1', '1'),
(436, 1029, 491, '1', '1', '1', '1'),
(437, 1029, 498, '1', '1', '1', '1'),
(438, 1029, 377, '1', '1', '1', '1'),
(439, 1029, 426, '1', '1', '1', '1'),
(440, 1029, 499, '1', '1', '1', '1'),
(441, 1029, 378, '1', '1', '1', '1'),
(442, 1028, 598, '1', '1', '1', '1'),
(443, 978, 615, '1', '1', '1', '1'),
(444, 978, 616, '1', '1', '1', '1'),
(445, 978, 619, '1', '1', '1', '1'),
(446, 978, 620, '1', '1', '1', '1'),
(447, 978, 622, '1', '1', '1', '1');

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

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `parent_id`, `type_ids`, `modified`, `created`) VALUES
(1, 'Administrator', NULL, '', '2011-05-08 18:01:48', '2010-11-21 17:13:55'),
(5, 'Organizer', NULL, '', '2011-07-31 20:27:16', '2011-07-31 20:27:16'),
(7, 'Referee', NULL, '6,7', '2012-03-22 20:23:02', '2012-03-22 20:23:02'),
(8, 'Participant', NULL, '1,5', '2012-04-22 13:54:00', '2012-04-22 13:54:00'),
(9, 'Rpc', NULL, '', '2012-04-22 17:40:13', '2012-04-22 17:40:13'),
(10, 'Guest', NULL, '1', '2012-08-20 15:51:22', '2012-08-20 15:38:00'),
(11, 'Tour Operator', NULL, '1,5', '2015-04-29 11:03:16', '2015-04-29 11:03:16'),
(12, 'Competition Manager', NULL, '', NULL, '2016-05-02 18:19:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `login_token` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `email` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `add_email` text COLLATE utf8_unicode_ci,
  `group_id` int(11) NOT NULL,
  `nation_id` int(11) DEFAULT NULL,
  `tournament_id` int(11) DEFAULT NULL,
  `language_id` int(11) DEFAULT NULL,
  `prefix_people` int(11) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `count_successful` int(11) NOT NULL DEFAULT '0',
  `count_failed` int(11) NOT NULL DEFAULT '0',
  `count_failed_since` int(11) NOT NULL DEFAULT '0',
  `ticket` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ticket_expires` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `login_token`, `enabled`, `email`, `add_email`, `group_id`, `nation_id`, `tournament_id`, `language_id`, `prefix_people`, `last_login`, `count_successful`, `count_failed`, `count_failed_since`, `ticket`, `ticket_expires`, `modified`, `created`) VALUES
(81, 'admin', '8dac7f85e01fbf6bad33c65c7c8dac8a43539d32', NULL, 1, 'theis@gmx.at', '', 1, NULL, NULL, NULL, NULL, '2012-06-10 20:00:54', 0, 0, 0, 'cbb2e4fff365794e382e175f57d213fb', '2015-05-19 17:07:19', '2012-08-06 09:30:12', '2012-04-21 17:22:02'),
(84, 'ettu', '3a7c67b4d4dd554e05efbbb13eca87d02ab060c2', NULL, 1, NULL, '', 1, NULL, NULL, NULL, NULL, '2012-06-22 15:46:35', 0, 0, 0, NULL, NULL, '2012-07-04 10:47:23', '2012-04-23 09:40:56'),
(89, 'theis', '$2y$10$Z5slcGN/q4BAxzPuL2SeH.YieiAeWWtsFGni0cGKxsxxaAC0eVvAa', NULL, 1, 'theis@gmx.at', '', 1, NULL, NULL, NULL, NULL, '2019-01-26 11:52:03', 1, 0, 0, NULL, NULL, '2016-08-10 18:24:13', '2012-06-10 12:44:07'),
(713, 'rpc2', 'ec2eff1d51d70b8def779db2d900197d5d28af56', NULL, 1, NULL, '', 9, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2012-08-16 17:21:30', '2012-08-15 11:46:31'),
(714, 'michael', '$2y$10$olWygkwPBpy7hEzX9oNKWey0k7yq1vXMfA8tJeuAWtsKTl3g45bH6', NULL, 1, 'agatonsax@gmx.de', NULL, 1, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2018-12-12 12:06:51', '2018-12-12 12:06:51'),
(715, 'torben', '$2y$10$OReRSXXQh2J.MSBDFf4YFufwGD1KJI/kGxPDA.qFoz3W5bpF.Klf.', NULL, 1, 'ttg207@gmail.com', NULL, 1, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2018-12-12 12:07:16', '2018-12-12 12:07:16');

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
-- Indexes for table `groups`
--
ALTER TABLE `groups`
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
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=623;
--
-- AUTO_INCREMENT for table `aros`
--
ALTER TABLE `aros`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1032;
--
-- AUTO_INCREMENT for table `aros_acos`
--
ALTER TABLE `aros_acos`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=448;
--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
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
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`nation_id`) REFERENCES `nations` (`id`),
  ADD CONSTRAINT `users_ibfk_3` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`),
  ADD CONSTRAINT `users_ibfk_4` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`);
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
