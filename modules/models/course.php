<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

require_once('adodb/adodb-active-record.inc.php');

class course extends ADOdb_Active_Record
{
   // returns the "most popular" course name out of all the course sections
   // returns null if no dominant name
   public function name()
   {
      $names = array();
      foreach($this->course_sections as $section)
      {
         if(!isset($names[$section->name]))
            $names[$section->name] = 1;
         else
            $names[$section->name] += 1;
      }

      $max_count = max($names);
      if(($max_count == 0) or $max_count == 1 and count($this->course_sections) != 1)
         return null;

      foreach($names as $name => $count)
      {
         if($count == $max_count)
            return $name;
      }

      // should never reach here
      return null;
   }

   // returns an array of instructors that teach this course
   public function instructors()
   {
      $instructors = array();
      foreach($this->course_sections as $section)
      {
         $instructors[] = $section->instructor;
      }
      return array_unique($instructors);
   }

   // returns an array of associative arrays containing users' schedules that
   // contain this course in their active schedule
   // only public schedules are searched
   // each array element contains an array with keys:
   // - user id (id)
   // - user's full name (name)
   // - schedule id (schedule_id)
   public function users()
   {
      global $SM_SQL;

      $query = "SELECT DISTINCT users.id, users.name, schedule_course_section_map.schedule_id FROM users JOIN schedule_course_section_map ON users.active_schedule_id = schedule_course_section_map.schedule_id JOIN schedules ON schedule_course_section_map.schedule_id = schedules.id JOIN course_sections ON course_sections.crn = schedule_course_section_map.crn JOIN courses ON courses.id = course_sections.course_id WHERE schedules.public = 1 AND courses.id = ? ORDER BY users.name;";
      $results = $SM_SQL->GetAll($query, array($this->id));
      return $results;
   }
}

// a course has many course sections
ADODB_Active_Record::ClassHasMany('course', 'course_sections', 'course_id', 'course_section');

// a course has one department
ADODB_Active_Record::ClassBelongsTo('course', 'department', 'department_id', 'id', 'department');

// id
// department
// course_number

?>
