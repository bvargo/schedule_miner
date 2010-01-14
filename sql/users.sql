--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `username` varchar(128) NOT NULL,
  `epassword` varchar(32) NOT NULL,
  `salt` varchar(12) NOT NULL,
  `name` varchar(128) NOT NULL,
  `email` varchar(128) default NULL,
  `admin` tinyint(1) NOT NULL default '0',
  `active_schedule_id` mediumint(8) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
