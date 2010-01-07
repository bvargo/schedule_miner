--
-- Table structure for table `schedule_course_section_map`
--

DROP TABLE IF EXISTS `schedule_course_section_map`;
CREATE TABLE `schedule_course_section_map` (
  `schedule_id` mediumint(8) NOT NULL,
  `crn` mediumint(8) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
