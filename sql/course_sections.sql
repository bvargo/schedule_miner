--
-- Table structure for table `course_sections`
--


DROP TABLE IF EXISTS `course_sections`;
CREATE TABLE `course_sections` (
  `id` mediumint(8) NOT NULL auto_increment,
  `crn` mediumint(8) NOT NULL,
  `course_id` mediumint(8) unsigned NOT NULL,
  `instructor_id` mediumint(8) unsigned default NULL,
  `section` text NOT NULL,
  `name` varchar(128) NOT NULL,
  `description` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
