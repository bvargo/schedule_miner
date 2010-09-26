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
