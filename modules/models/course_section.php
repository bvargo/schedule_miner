<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

require_once('adodb/adodb-active-record.inc.php');

class course_section extends ADOdb_Active_Record
{
   // returns if this section conflicts with the other section
   function conflicts($other_section)
   {
      // loop throw all class_period paris
      foreach($this->class_periods as $this_class_period)
      {
         foreach($other_section->class_periods as $other_class_period)
         {
            if($this_class_period->conflicts($other_class_period))
               return true;
         }
      }
      return false;
   }

   // returns an array of associative arrays containing users' schedules that
   // contain this course section in their active schedule
   // only public schedules are searched
   // each array element contains an array with keys:
   // - user id (id)
   // - user's full name (name)
   // - schedule id (schedule_id)
   public function users()
   {
      global $SM_SQL;

      $query = "SELECT DISTINCT users.id, users.username, users.name, schedule_course_section_map.schedule_id FROM users JOIN schedule_course_section_map ON users.active_schedule_id = schedule_course_section_map.schedule_id JOIN schedules ON schedule_course_section_map.schedule_id = schedules.id WHERE schedules.public = 1 AND  schedule_course_section_map.crn = ? ORDER BY users.name;";
      $results = $SM_SQL->GetAll($query, array($this->crn));
      return $results;
   }

   // finds all course sections that take place between start_time and
   // end_time and fall on the days provided
   // days should contains strings of M, T, W, R, F and is assumed to be sql-safe
   // returns an array of sections
   public static function time_search($start_time, $end_time, $days)
   {
      $section_ids = class_period::time_search($start_time, $end_time, $days);

      $sections = array();
      foreach($section_ids as $id)
      {
         $section = new course_section();
         $section->load("id=?", $id);
         $sections[] = $section;
      }

      foreach($sections as $section_id => &$section)
      {
         foreach($section->class_periods as &$period)
         {
            if($period->start_time < $start_time || $period->end_time > $end_time || !in_array($period->day, $days))
            {
               // the section does not match - remove the course
               unset($sections[$section_id]);

               // go on to the next course
               break;
            }
         }
      }

      return $sections;
   }
}

// a course section has many class periods
ADODB_Active_Record::ClassHasMany('course_section', 'class_periods', 'section_id', 'class_period');

// a course section has one course
ADODB_Active_Record::ClassBelongsTo('course_section', 'course', 'course_id', 'id', 'course');

// a course section has one instructor
ADODB_Active_Record::ClassBelongsTo('course_section', 'instructor', 'instructor_id', 'id', 'instructor');

// id
// course
// instructor
// section
// credit_hours
// name
// description

?>
