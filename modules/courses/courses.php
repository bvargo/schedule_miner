<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

// course schedules

class Courses extends Module
{
   // the main show schedule interface
   public function index()
   {
      redirect('courses/search');
   }

   public function search()
   {
   }

   // displays a course
   public function display()
   {
      global $SM_ARGS;

      // if we are not provided an id, redirect to the search page
      // user
      if(!isset($SM_ARGS[2]))
      {
         redirect('courses/search');
      }

      if(isset($SM_ARGS[4]))
      {
         // courses/display/department/course/course_section
         $department = new department();
         $results = $department->Find("abbreviation=?", array($SM_ARGS[2]));
         if(!empty($results))
            $department = $results[0];
         $course = new course();
         $results = $course->Find("department_id=? and course_number=?", array($department->id, $SM_ARGS[3]));
         if(!empty($results))
            $course = $results[0];
         $course_section = new course_section();
         $results = $course_section->Find("course_id=? and section=?", array($course->id, $SM_ARGS[4]));
         if(!empty($results))
            $course_section = $results[0];
         $this->template_name = "display_section";

         $this->args["course"] = $course;
         $this->args["course_section"] = $course_section;
      }
      else if(isset($SM_ARGS[3]))
      {
         // courses/display/department/course
         $department = new department();
         $results = $department->Find("abbreviation=?", array($SM_ARGS[2]));
         if(!empty($results))
            $department = $results[0];
         $this->args["department"] = $department;
         $course = new course();
         $results = $course->Find("department_id=? and course_number=?", array($department->id, $SM_ARGS[3]));
         if(!empty($results))
            $course = $results[0];
         $this->template_name = "display_course";

         $this->args["course"] = $course;
      }
      else
      {
         // courses/display/crn
         $course_section = new course_section();
         $results = $course_section->Find("crn=?", array($SM_ARGS[2]));
         if(!empty($results))
            $course_section = $results[0];
         $course = $course_section->course;
         $this->template_name = "display_section";

         $this->args["course"] = $course;
         $this->args["course_section"] = $course_section;
      }
   }
}

?>
