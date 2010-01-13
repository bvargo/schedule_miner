<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

require_once('adodb/adodb-active-record.inc.php');

class schedule extends ADOdb_Active_Record
{
   // schedules have many course_sections, but course_sections also belong to
   // many classes - many-to-many doens't work with ActiveRecord right now, so
   // we have to handle course_sections manually

   // return courses as objects
   public function courses()
   {
      $courses = array();

      $course_sections = $this->course_sections();
      foreach($course_sections as $course_section)
      {
         $course = $course_section->course;
         if(!in_array($course, $courses))
            $courses[] = $course;
      }
      return $courses;
   }

   // return course_sections as objects
   public function course_sections()
   {
      global $SM_SQL;

      // if this is only a temporary object, and is not from the database, do
      // not allow course_sections
      // FIXME
      if(!$this->id)
         return null;

      $course_sections = array();

      $results = $SM_SQL->GetAll("SELECT crn FROM schedule_course_section_map where schedule_id=?", array($this->id));
      foreach($results as $result)
      {
         // crn for this particular section
         $crn = $result['crn'];

         // get the section object
         $course_section = new course_section();
         $result2 = $course_section->Find("crn=?", array($crn));

         // FIXME: this should not return more than 1
         if(count($result2) >= 1)
            $course_sections[] = $result2[0];
      }
 
      return $course_sections;
   }

   // add a course section to a schedule
   public function add_course_section($crn)
   {
      global $SM_SQL;

      // if this is only a temporary object, and is not from the database, do
      // not allow course_sections
      // FIXME
      if(!$this->id)
         return null;

      // make sure the crn is valid
      $course_section = new course_section();
      $results = $course_section->Find('crn=?', array($crn));
      if(!count($results))
      {
         // crn not found - error
         d('Invalid CRN added to schedule');
         return -1;
      }

      // make sure the CRN isn't already in the schedule
      // TODO: can we optimize this?
      $results = $SM_SQL->GetAll("SELECT crn FROM schedule_course_section_map where schedule_id=? and crn=?", array($this->id, $crn));
      if(!count($results))
         $SM_SQL->Execute("INSERT INTO schedule_course_section_map (schedule_id,crn) VALUES (?,?)", array($this->id, $crn));

      // TODO some kind of return code to make sure this worked, error out,
      // etc
   }

   // add more than one course section to a schedule
   public function add_course_sections($crns)
   {
      global $SM_SQL;
      foreach($crns as $crn)
         $this->add_course_section($crn);

      // TODO some kind of return code, as above
   }

   public function credit_hours()
   {
      $credit_hours = 0;
      foreach($this->course_sections() as $course_section)
      {
         $credit_hours += $course_section->course->credit_hours;
      }
      return $credit_hours;
   }

}

// a schedule has one user
ADODB_Active_Record::ClassBelongsTo('schedule', 'user', 'user_id', 'id', 'user');

?>