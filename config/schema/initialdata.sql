-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 24, 2019 at 02:41 PM
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

--
-- Dumping data for table `acos`
--

INSERT INTO `acos` (`id`, `parent_id`, `model`, `foreign_key`, `alias`, `lft`, `rght`) VALUES
(347, NULL, NULL, NULL, 'controllers', 1, 348),
(348, 347, NULL, NULL, 'Pages', 2, 15),
(349, 348, NULL, NULL, 'display', 3, 4),
(350, 347, NULL, NULL, 'Groups', 16, 27),
(351, 350, NULL, NULL, 'index', 17, 18),
(352, 350, NULL, NULL, 'view', 19, 20),
(353, 350, NULL, NULL, 'add', 21, 22),
(354, 350, NULL, NULL, 'edit', 23, 24),
(355, 350, NULL, NULL, 'delete', 25, 26),
(356, 347, NULL, NULL, 'Nations', 28, 39),
(357, 356, NULL, NULL, 'index', 29, 30),
(358, 356, NULL, NULL, 'view', 31, 32),
(359, 356, NULL, NULL, 'add', 33, 34),
(360, 356, NULL, NULL, 'edit', 35, 36),
(361, 356, NULL, NULL, 'delete', 37, 38),
(362, 347, NULL, NULL, 'Types', 40, 51),
(363, 362, NULL, NULL, 'index', 41, 42),
(364, 362, NULL, NULL, 'view', 43, 44),
(365, 362, NULL, NULL, 'add', 45, 46),
(366, 362, NULL, NULL, 'edit', 47, 48),
(367, 362, NULL, NULL, 'delete', 49, 50),
(368, 347, NULL, NULL, 'Users', 52, 79),
(369, 368, NULL, NULL, 'login', 53, 54),
(370, 368, NULL, NULL, 'logout', 55, 56),
(371, 368, NULL, NULL, 'index', 57, 58),
(372, 368, NULL, NULL, 'view', 59, 60),
(373, 368, NULL, NULL, 'add', 61, 62),
(374, 368, NULL, NULL, 'edit', 63, 64),
(375, 368, NULL, NULL, 'delete', 65, 66),
(376, 347, NULL, NULL, 'Registrations', 80, 133),
(377, 376, NULL, NULL, 'index', 81, 82),
(378, 376, NULL, NULL, 'view', 83, 84),
(379, 376, NULL, NULL, 'add', 85, 86),
(380, 376, NULL, NULL, 'edit', 87, 88),
(381, 376, NULL, NULL, 'delete', 89, 90),
(382, 347, NULL, NULL, 'People', 134, 145),
(383, 382, NULL, NULL, 'index', 135, 136),
(384, 382, NULL, NULL, 'view', 137, 138),
(385, 382, NULL, NULL, 'add', 139, 140),
(386, 382, NULL, NULL, 'edit', 141, 142),
(387, 382, NULL, NULL, 'delete', 143, 144),
(388, 347, NULL, NULL, 'Tournaments', 146, 157),
(389, 388, NULL, NULL, 'index', 147, 148),
(390, 388, NULL, NULL, 'view', 149, 150),
(391, 388, NULL, NULL, 'add', 151, 152),
(392, 388, NULL, NULL, 'edit', 153, 154),
(393, 388, NULL, NULL, 'delete', 155, 156),
(394, 347, NULL, NULL, 'Competitions', 158, 169),
(395, 394, NULL, NULL, 'index', 159, 160),
(396, 394, NULL, NULL, 'view', 161, 162),
(397, 394, NULL, NULL, 'add', 163, 164),
(398, 394, NULL, NULL, 'edit', 165, 166),
(399, 394, NULL, NULL, 'delete', 167, 168),
(400, 347, NULL, NULL, 'AclEdit', 170, 225),
(401, 400, NULL, NULL, 'Acos', 171, 182),
(402, 401, NULL, NULL, 'admin_index', 172, 173),
(403, 401, NULL, NULL, 'admin_empty_acos', 174, 175),
(404, 401, NULL, NULL, 'admin_build_acl', 176, 177),
(405, 400, NULL, NULL, 'Aros', 183, 218),
(406, 405, NULL, NULL, 'admin_index', 184, 185),
(407, 405, NULL, NULL, 'admin_check', 186, 187),
(408, 405, NULL, NULL, 'admin_users', 188, 189),
(409, 405, NULL, NULL, 'admin_update_user_role', 190, 191),
(410, 405, NULL, NULL, 'admin_role_permissions', 192, 193),
(411, 405, NULL, NULL, 'admin_user_permissions', 194, 195),
(412, 405, NULL, NULL, 'admin_empty_permissions', 196, 197),
(413, 405, NULL, NULL, 'admin_grant_all_controllers', 198, 199),
(414, 405, NULL, NULL, 'admin_deny_all_controllers', 200, 201),
(415, 405, NULL, NULL, 'admin_grant_role_permission', 202, 203),
(416, 405, NULL, NULL, 'admin_deny_role_permission', 204, 205),
(417, 405, NULL, NULL, 'admin_grant_user_permission', 206, 207),
(418, 405, NULL, NULL, 'admin_deny_user_permission', 208, 209),
(419, 376, NULL, NULL, 'onChangePerson', 91, 92),
(421, 376, NULL, NULL, 'onChangeMixed', 93, 94),
(422, 376, NULL, NULL, 'onChangeDouble', 95, 96),
(423, 347, NULL, NULL, 'Rpc2', 226, 229),
(425, 423, NULL, NULL, 'index', 227, 228),
(426, 376, NULL, NULL, 'list_partner_wanted', 97, 98),
(436, 348, NULL, NULL, 'home', 5, 6),
(438, 376, NULL, NULL, 'count', 99, 100),
(455, 376, NULL, NULL, 'assign_numbers', 101, 102),
(456, 376, NULL, NULL, 'export', 103, 104),
(469, 368, NULL, NULL, 'forgot_password', 67, 68),
(482, 368, NULL, NULL, 'profile', 69, 70),
(483, 376, NULL, NULL, 'print_accreditation', 105, 106),
(491, 376, NULL, NULL, 'export_count', 107, 108),
(492, 376, NULL, NULL, 'print_accreditation_settings', 109, 110),
(494, 368, NULL, NULL, 'request_password', 71, 72),
(495, 376, NULL, NULL, 'import', 111, 112),
(496, 376, NULL, NULL, 'import_partner', 113, 114),
(498, 376, NULL, NULL, 'history', 115, 116),
(499, 376, NULL, NULL, 'revision', 117, 118),
(500, 400, NULL, NULL, 'AclEdit', 219, 224),
(501, 500, NULL, NULL, 'index', 220, 221),
(502, 500, NULL, NULL, 'admin_index', 222, 223),
(503, 401, NULL, NULL, 'admin_prune_acos', 178, 179),
(504, 401, NULL, NULL, 'admin_synchronize', 180, 181),
(505, 405, NULL, NULL, 'admin_ajax_role_permissions', 210, 211),
(506, 405, NULL, NULL, 'admin_clear_user_specific_permissions', 212, 213),
(507, 405, NULL, NULL, 'admin_get_role_controller_permission', 214, 215),
(508, 405, NULL, NULL, 'admin_get_user_controller_permission', 216, 217),
(549, 347, NULL, NULL, 'Shop', 230, 347),
(550, 549, NULL, NULL, 'Shops', 231, 284),
(551, 550, NULL, NULL, 'wizard', 232, 233),
(552, 550, NULL, NULL, 'add_person', 234, 235),
(553, 550, NULL, NULL, 'remove_person', 236, 237),
(554, 550, NULL, NULL, 'onAddItem', 238, 239),
(555, 550, NULL, NULL, 'onChangeQuantity', 240, 241),
(556, 550, NULL, NULL, 'onRemoveItem', 242, 243),
(561, 549, NULL, NULL, 'Articles', 285, 298),
(562, 561, NULL, NULL, 'index', 286, 287),
(563, 561, NULL, NULL, 'view', 288, 289),
(564, 561, NULL, NULL, 'add', 290, 291),
(565, 561, NULL, NULL, 'edit', 292, 293),
(566, 561, NULL, NULL, 'delete', 294, 295),
(567, 549, NULL, NULL, 'Orders', 299, 328),
(568, 567, NULL, NULL, 'index', 300, 301),
(569, 567, NULL, NULL, 'view', 302, 303),
(570, 567, NULL, NULL, 'settings', 304, 305),
(571, 567, NULL, NULL, 'history', 306, 307),
(572, 567, NULL, NULL, 'revision', 308, 309),
(573, 567, NULL, NULL, 'storno', 310, 311),
(574, 550, NULL, NULL, 'test', 244, 245),
(576, 550, NULL, NULL, 'receipt', 246, 247),
(577, 376, NULL, NULL, 'requests', 119, 120),
(578, 376, NULL, NULL, 'accept', 121, 122),
(579, 376, NULL, NULL, 'reject', 123, 124),
(580, 549, NULL, NULL, 'Pages', 329, 334),
(583, 550, NULL, NULL, 'onPrepareCreditcard', 248, 249),
(585, 376, NULL, NULL, 'add_participant', 125, 126),
(586, 567, NULL, NULL, 'export', 312, 313),
(587, 550, NULL, NULL, 'viewVoucher', 250, 251),
(588, 550, NULL, NULL, 'sendVoucher', 252, 253),
(589, 550, NULL, NULL, 'viewInvoice', 254, 255),
(590, 550, NULL, NULL, 'sendInvoice', 256, 257),
(592, 550, NULL, NULL, 'import', 258, 259),
(593, 567, NULL, NULL, 'unstorno', 314, 315),
(594, 567, NULL, NULL, 'edit_person', 316, 317),
(595, 368, NULL, NULL, 'notifications', 73, 74),
(596, 376, NULL, NULL, 'edit_participant', 127, 128),
(597, 376, NULL, NULL, 'delete_participant', 129, 130),
(598, 376, NULL, NULL, 'export_participants', 131, 132),
(599, 550, NULL, NULL, 'payment_error', 260, 261),
(600, 550, NULL, NULL, 'payment_complete', 262, 263),
(601, 550, NULL, NULL, 'setPending', 264, 265),
(602, 550, NULL, NULL, 'setPaid', 266, 267),
(603, 550, NULL, NULL, 'payment_success', 268, 269),
(604, 550, NULL, NULL, 'saveCart', 270, 271),
(605, 348, NULL, NULL, 'onParticipantData', 7, 8),
(606, 348, NULL, NULL, 'onParticipantCount', 9, 10),
(607, 368, NULL, NULL, 'onChangeLanguage', 75, 76),
(608, 567, NULL, NULL, 'search', 318, 319),
(609, 567, NULL, NULL, 'edit_invoice', 320, 321),
(610, 567, NULL, NULL, 'edit_address', 322, 323),
(613, 550, NULL, NULL, 'testPayment', 272, 273),
(614, 348, NULL, NULL, 'participants', 11, 12),
(615, 561, NULL, NULL, 'chart', 296, 297),
(616, 567, NULL, NULL, 'storno_pending', 324, 325),
(617, 580, NULL, NULL, 'shop_agb', 330, 331),
(618, 580, NULL, NULL, 'players_privacy', 332, 333),
(619, 550, NULL, NULL, 'send_reminder', 274, 275),
(620, 550, NULL, NULL, 'setDelayed', 276, 277),
(621, 368, NULL, NULL, 'send_welcome_mail', 77, 78),
(622, 567, NULL, NULL, 'export_players', 326, 327),
(623, 348, NULL, NULL, 'count_participants', 13, 14),
(624, 550, NULL, NULL, 'process_waiting_list', 278, 279),
(625, 550, NULL, NULL, 'count_participants', 280, 281),
(626, 550, NULL, NULL, 'article_image', 282, 283),
(627, 549, NULL, NULL, 'Allotments', 335, 346),
(628, 627, NULL, NULL, 'index', 336, 337),
(629, 627, NULL, NULL, 'view', 338, 339),
(630, 627, NULL, NULL, 'add', 340, 341),
(631, 627, NULL, NULL, 'edit', 342, 343),
(632, 627, NULL, NULL, 'delete', 344, 345);

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
(447, 978, 622, '1', '1', '1', '1'),
(448, 978, 630, '1', '1', '1', '1'),
(449, 978, 632, '1', '1', '1', '1'),
(450, 978, 631, '1', '1', '1', '1'),
(451, 978, 629, '1', '1', '1', '1'),
(452, 978, 628, '1', '1', '1', '1');

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

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `name`, `description`) VALUES
(1, 'de', 'Deutsch'),
(2, 'en', 'English'),
(3, 'es', 'Español'),
(4, 'fr', 'Français');

--
-- Dumping data for table `shop_order_status`
--

INSERT INTO `shop_order_status` (`id`, `name`, `description`) VALUES
(5, 'PEND', 'Pending'),
(6, 'PAID', 'Paid'),
(7, 'ERR', 'Error'),
(8, 'CANC', 'Cancelled'),
(9, 'FRD', 'Fraud'),
(10, 'INVO', 'Invoice'),
(11, 'INIT', 'Initiated'),
(12, 'WAIT', 'Waiting List'),
(13, 'DEL', 'Payment Delayed');

--
-- Dumping data for table `types`
--

INSERT INTO `types` (`id`, `name`, `description`) VALUES
(1, 'PLA', 'Player'),
(2, 'COA', 'Coach'),
(3, 'DEL', 'Delegate to congress'),
(4, 'MED', 'Medical Personnel'),
(5, 'ACC', 'Accompanying Person'),
(6, 'UMP', 'Umpire'),
(7, 'REF', 'Referee'),
(8, 'PRE', 'Press'),
(9, 'TV', 'Television'),
(10, 'SUP', 'Supplier'),
(11, 'OFF', 'Official'),
(12, 'ORG', 'Organizer');

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `login_token`, `enabled`, `email`, `add_email`, `group_id`, `nation_id`, `tournament_id`, `language_id`, `prefix_people`, `last_login`, `count_successful`, `count_failed`, `count_failed_since`, `count_requests`, `ticket`, `ticket_expires`, `modified`, `created`) VALUES
(81, 'admin', '8dac7f85e01fbf6bad33c65c7c8dac8a43539d32', NULL, 1, 'theis@gmx.at', '', 1, NULL, NULL, NULL, NULL, '2012-06-10 20:00:54', 0, 0, 0, 0, 'cbb2e4fff365794e382e175f57d213fb', '2015-05-19 17:07:19', '2012-08-06 09:30:12', '2012-04-21 17:22:02'),
(84, 'ettu', '3a7c67b4d4dd554e05efbbb13eca87d02ab060c2', NULL, 1, NULL, '', 1, NULL, NULL, NULL, NULL, '2012-06-22 15:46:35', 0, 0, 0, 0, NULL, NULL, '2012-07-04 10:47:23', '2012-04-23 09:40:56'),
(89, 'theis', '$2y$10$Z5slcGN/q4BAxzPuL2SeH.YieiAeWWtsFGni0cGKxsxxaAC0eVvAa', NULL, 1, 'theis@gmx.at', '', 1, NULL, NULL, NULL, NULL, '2019-06-24 14:33:11', 4, 0, 0, 0, NULL, NULL, '2016-08-10 18:24:13', '2012-06-10 12:44:07'),
(713, 'rpc2', 'ec2eff1d51d70b8def779db2d900197d5d28af56', NULL, 1, NULL, '', 9, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, NULL, '2012-08-16 17:21:30', '2012-08-15 11:46:31'),
(714, 'michael', '$2y$10$olWygkwPBpy7hEzX9oNKWey0k7yq1vXMfA8tJeuAWtsKTl3g45bH6', NULL, 1, 'agatonsax@gmx.de', NULL, 1, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, NULL, '2018-12-12 12:06:51', '2018-12-12 12:06:51'),
(715, 'torben', '$2y$10$OReRSXXQh2J.MSBDFf4YFufwGD1KJI/kGxPDA.qFoz3W5bpF.Klf.', NULL, 1, 'ttg207@gmail.com', NULL, 1, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, NULL, '2018-12-12 12:07:16', '2018-12-12 12:07:16');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
