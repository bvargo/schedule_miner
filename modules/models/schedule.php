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
         if($course_section->load("crn=?", array($crn)))
            $course_sections[] = $course_section;
      }

      return $course_sections;
   }

   // add a course section to a schedule
   // course_section can be an object or a CRN
   public function add_course_section($course_section)
   {
      global $SM_SQL;

      // if this is only a temporary object, and is not from the database, do
      // not allow course_sections
      // FIXME
      if(!$this->id)
         return null;

      if($course_section instanceof course_section)
      {
         // given a course_section object
         $crn = $course_section->crn;
      }
      else if(is_numeric($course_section))
      {
         // make sure the crn is valid
         $section = new course_section();
         if(!$section->load('crn=?', array($course_section)))
         {
            // crn not found - error
            d('Invalid CRN given to add_course_section');
            return -1;
         }
         else
         {
            $course_section = $section;
         }
      }
      else
      {
         // not given a course_section or a number, error
         d('Invalid argument given to add_course_section');
         return -1;
      }

      // make sure the CRN isn't already in the schedule
      if(!$this->contains_course_section($course_section))
         $SM_SQL->Execute("INSERT INTO schedule_course_section_map (schedule_id,crn) VALUES (?,?)", array($this->id, $course_section->crn));

      // TODO some kind of return code to make sure this worked, error out,
      // etc
   }

   // add more than one course section to a schedule
   // course_sections can be an array of objects or CRNs, or a mixture of both
   public function add_course_sections($course_sections)
   {
      global $SM_SQL;
      foreach($course_sections as $course_section)
         $this->add_course_section($course_section);

      // TODO some kind of return code, as above
   }

   // remove a course section from the schedule
   // course_section can be an object or a CRN
   public function remove_course_section($course_section)
   {
      global $SM_SQL;

      // if this is only a temporary object, and is not from the database, do
      // not allow course_sections
      // FIXME
      if(!$this->id)
         return null;

      if($course_section instanceof course_section)
      {
         $SM_SQL->Execute("DELETE FROM schedule_course_section_map WHERE schedule_id=? AND crn=?", array($this->id, $course_section->crn));
      }
      else if(is_numeric($course_section))
      {
         $SM_SQL->Execute("DELETE FROM schedule_course_section_map WHERE schedule_id=? AND crn=?", array($this->id, $course_section));
      }
      else
      {
         d('Invalid argument given to remove_course_section');
         return -1;
      }

      // TODO some kind of return code to make sure this worked, error out,
      // etc
   }

   // removes more than one course section from a schedule
   // course_sections can be an array of objects or CRNs, or a mixture of both
   public function remove_course_sections($course_sections)
   {
      foreach($course_sections as $course_section)
         $this->remove_course_section($course_section);

      // TODO some kind of return code, as above
   }

   // removes all course sections from the schedule
   public function remove_all_course_sections()
   {
      global $SM_SQL;

      $SM_SQL->Execute("DELETE FROM schedule_course_section_map WHERE schedule_id=?", array($this->id));

      // TODO some kind of return code, as above
   }

   // returns whether the schedule contains the indicated course_section
   // course_section can be an object or a CRN
   public function contains_course_section($course_section)
   {
      global $SM_SQL;

      if(is_numeric($course_section))
         $results = $SM_SQL->GetAll("SELECT crn FROM schedule_course_section_map where schedule_id=? and crn=?", array($this->id, $course_section));
      else
         $results = $SM_SQL->GetAll("SELECT crn FROM schedule_course_section_map where schedule_id=? and crn=?", array($this->id, $course_section->crn));

      // TODO: can we optimize this?
      return count($results);
   }

   // returns the number of credit hours in the schedule
   public function credit_hours()
   {
      $credit_hours = 0;
      foreach($this->course_sections() as $course_section)
      {
         $credit_hours += $course_section->credit_hours;
      }
      return $credit_hours;
   }

   // returns the number of credit hours in the schedule, where each course
   // is only counted once if multiple sections are in the same schedule
   public function credit_hours_unique()
   {
      $credit_hours = 0;
      $courses = array();
      foreach($this->course_sections() as $course_section)
      {
         // skip sections that do not have any credit hours
         // this makes sure that if there is a lab with 0 credits and the
         // course with x credits, then the x is added; if a course actually
         // has 0 credit hours, it won't affect the total count, so it doesn't
         // matter
         if($course_section->credit_hours != 0)
         {
            if(!in_array($course_section->course->id, $courses))
            {
               $courses[] = $course_section->course->id;
               $credit_hours += $course_section->credit_hours;
            }
         }
      }
      return $credit_hours;
   }

   // delete the schedule
   public function delete()
   {
      // remove all course sections for this object
      $this->remove_all_course_sections();

      // remove the object from the database
      parent::delete();
   }

   // returns if adding this course section cause a conflict in the schedule
   public function conflicts($course_section)
   {
      foreach($this->course_sections() as $section)
      {
         if($course_section->conflicts($section))
            return true;
      }

      return false;
   }

   // returns an array of associative arrays containing users' schedules that
   // share a course section of their active schedule with at least one class
   // of this schedule
   // only public schedules are searched
   // each array element contains an array with keys:
   // - user id (id)
   // - user's full name (name)
   // - schedule id (schedule_id)
   public function users_sharing_class()
   {
      global $SM_SQL;

      // a is schedule_course_section_map where the schedule_id is this
      // schedule
      // b is schedule_course_section_map where the schedule_id is what we are
      // trying to find
      // schedules is selecting the schedule we are trying to find to see if
      // it is public
      $query = "SELECT DISTINCT users.id, users.username, users.name, b.schedule_id FROM users, schedules, schedule_course_section_map AS a JOIN schedule_course_section_map AS b ON a.crn = b.crn WHERE a.schedule_id = ? AND b.schedule_id = users.active_schedule_id AND b.schedule_id = schedules.id AND schedules.public = 1 AND a.schedule_id != b.schedule_id ORDER BY users.name;";
      $results = $SM_SQL->GetAll($query, array($this->id));
      return $results;
   }
}

// a schedule has one user
ADODB_Active_Record::ClassBelongsTo('schedule', 'user', 'user_id', 'id', 'user');

?>
