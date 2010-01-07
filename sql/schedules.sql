--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
CREATE TABLE `schedules` (
  `id` mediumint(8) NOT NULL auto_increment,
  `user_id` mediumint(8) NOT NULL,
  `name` varchar(128) NOT NULL,
  `public` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
