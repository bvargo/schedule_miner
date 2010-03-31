<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

// FIXME: schedule should really be able to handle temporary objects too

class schedule_temp
{
   // schedules have many course_sections, but course_sections also belong to
   // many classes - many-to-many doens't work with ActiveRecord right now, so
   // we have to handle course_sections manually

   private $course_sections = array();

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
      return $this->course_sections;
   }

   // add a course section to a schedule
   // course_section can be an object or a CRN
   public function add_course_section($course_section)
   {
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
         $this->course_sections[] = $course_section;
   }

   // add more than one course section to a schedule
   // course_sections can be an array of objects or CRNs, or a mixture of both
   public function add_course_sections($course_sections)
   {
      foreach($course_sections as $course_section)
         $this->add_course_section($course_section);
   }

   // remove a course section from the schedule
   // course_section can be an object or a CRN
   public function remove_course_section($course_section)
   {
      if($course_section instanceof course_section)
      {
         $course_section = $course_section->crn;
      }
      else if(is_numeric($course_section))
      {
         // course_section is already the crn
      }
      else
      {
         d('Invalid argument given to remove_course_section');
         return -1;
      }

      foreach($this->course_sections() as $id => $section)
      {
         if($course_section == $section->crn)
            unset($this->course_sections[$id]);
      }
   }

   // removes more than one course section from a schedule
   // course_sections can be an array of objects or CRNs, or a mixture of both
   public function remove_course_sections($course_sections)
   {
      foreach($course_sections as $course_section)
         $this->remove_course_section($course_section);
   }

   // removes all course sections from the schedule
   public function remove_all_course_sections()
   {
      $this->course_sections = array();
   }

   // returns whether the schedule contains the indicated course_section
   // course_section can be an object or a CRN
   public function contains_course_section($course_section)
   {
      if($course_section instanceof course_section)
      {
         $course_section = $course_section->crn;
      }
      else if(is_numeric($course_section))
      {
         // course_section is already the crn
      }
      else
      {
         d('Invalid argument given to remove_course_section');
         return -1;
      }

      foreach($this->course_sections() as $section)
      {
         if($section->crn == $course_section)
            return true;
      }

      return false;
   }

   // returns the number of credit hours in a course
   public function credit_hours()
   {
      $credit_hours = 0;
      foreach($this->course_sections() as $course_section)
      {
         $credit_hours += $course_section->course->credit_hours;
      }
      return $credit_hours;
   }

   // returns the number of credit hours in a course, where each course is
   // only counted once if multiple sections are in the same schedule
   public function credit_hours_unique()
   {
      $credit_hours = 0;
      $courses = array();
      foreach($this->course_sections() as $course_section)
      {
         if(!in_array($course_section->course->id, $courses))
         {
            $courses[] = $course_section->course->id;
            $credit_hours += $course_section->course->credit_hours;
         }
      }
      return $credit_hours;
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
}

?>
