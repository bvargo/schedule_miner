--
-- Table structure for table `buildings`
--


DROP TABLE IF EXISTS `buildings`;
CREATE TABLE `buildings` (
  `id` mediumint(8) NOT NULL auto_increment,
  `abbreviation` char(12) NOT NULL,
  `name` varchar(128) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
