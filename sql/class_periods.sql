--
-- Table structure for table `class_periods`
--

DROP TABLE IF EXISTS `class_periods`;
CREATE TABLE `class_periods` (
  `id` mediumint(9) NOT NULL auto_increment,
  `section_id` mediumint(8) unsigned NOT NULL,
  `building_id` mediumint(8) unsigned NOT NULL,
  `room_number` char(8) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `day` char(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
