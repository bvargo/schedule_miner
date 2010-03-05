--
-- Structure for view `catalog`
-- Used for searching
--

CREATE OR REPLACE VIEW `catalog` AS
SELECT
   `course_sections`.`id` AS `id`,
   `course_sections`.`course_id` AS `course_id`,
   `course_sections`.`instructor_id` AS `instructor_id`,
   `courses`.`department_id` AS `department_id`,
   `course_sections`.`crn` AS `crn`,
   `course_sections`.`section` AS `section`,
   `course_sections`.`name` AS `name`,
   `course_sections`.`description` AS `description`,
   `courses`.`course_number` AS `course_number`,
   `courses`.`credit_hours` AS `credit_hours`,
   `instructors`.`name` AS `instructor_name`,
   `departments`.`abbreviation` AS `department_abbreviation`,
   `departments`.`name` AS `department_name`
FROM
   course_sections,
   courses,
   departments,
   instructors
WHERE
   course_sections.instructor_id=instructors.id
   AND course_sections.course_id=courses.id
   AND courses.department_id=departments.id;
