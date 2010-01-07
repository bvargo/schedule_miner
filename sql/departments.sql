--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `abbreviation` char(4) NOT NULL,
  `name` varchar(128) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

