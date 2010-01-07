--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `department_id` mediumint(8) unsigned NOT NULL,
  `course_number` mediumint(8) unsigned NOT NULL,
  `credit_hours` decimal(6,3) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
