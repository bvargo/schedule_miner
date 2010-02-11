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
      // show departments
      $departments = new department();
      $departments = $departments->Find("", array());
      $this->args["departments"] = $departments;

      // show instructors
      $instructors = new instructor();
      $instructors = $instructors->Find("", array());
      $this->args["instructors"] = $instructors;

      // show buildings
      $buildings = new building();
      $buildings = $buildings->Find("", array());
      $this->args["buildings"] = $buildings;
   }

   // displays a course or course section
   public function display()
   {
      global $SM_ARGS;

      // if we are not provided an id, redirect to the search page
      // user
      if(!isset($SM_ARGS[2]))
      {
         redirect('courses');
      }

      if($SM_ARGS[2] == "instructor" && isset($SM_ARGS[3]))
      {
         // courses/display/instructor/instructor_id
         // display an instructor
         $this->template_name = "display_instructor";

         $instructor = new instructor();
         $results = $instructor->Find("id=?", array($SM_ARGS[3]));
         if(count($results))
            $instructor = $results[0];
         $this->args['instructor'] = $instructor;

         if(!count($instructor->course_sections))
            $this->args['empty'] = 1;
      }
      else if($SM_ARGS[2] == "department" && isset($SM_ARGS[3]))
      {
         // courses/display/department/department_abbr
         // display a department
         $this->template_name = "display_department";

         $department = new department();
         $results = $department->Find("abbreviation=?", array($SM_ARGS[3]));
         if(count($results))
            $department = $results[0];
         $this->args['department'] = $department;

         if(!count($department->courses))
            $this->args['empty'] = 1;
      }
      else if($SM_ARGS[2] == "building" && isset($SM_ARGS[3]))
      {
         // courses/display/building/building_abbr
         // display a building
         $this->template_name = "display_building";

         $building = new building();
         $results = $building->Find("abbreviation=?", array($SM_ARGS[3]));
         if(count($results))
            $building = $results[0];
         $this->args['building'] = $building;

         // create a list of courses in the building
         $courses = array();
         foreach($building->class_periods as $class_period)
         {
            if(!in_array($class_period->course_section->course, $courses))
               $courses[] = $class_period->course_section->course;
         }
         $this->args['courses'] = $courses;

         if(!count($courses))
            $this->args['empty'] = 1;
      }
      else if(isset($SM_ARGS[4]))
      {
         // courses/display/department_abbr/course_number/course_section_letter
         // display a section
         $this->template_name = "display_section";

         $department = new department();
         $results = $department->Find("abbreviation=?", array($SM_ARGS[2]));
         if(count($results))
            $department = $results[0];
         $course = new course();
         $results = $course->Find("department_id=? and course_number=?", array($department->id, $SM_ARGS[3]));
         if(count($results))
            $course = $results[0];
         $course_section = new course_section();
         $results = $course_section->Find("course_id=? and section=?", array($course->id, $SM_ARGS[4]));
         if(count($results))
            $course_section = $results[0];

         $this->args["course"] = $course;
         $this->args["course_section"] = $course_section;
      }
      else if(isset($SM_ARGS[3]))
      {
         // courses/display/department_abbr/course_number
         // display a course
         $this->template_name = "display_course";

         $department = new department();
         $results = $department->Find("abbreviation=?", array($SM_ARGS[2]));
         if(count($results))
            $department = $results[0];
         $this->args["department"] = $department;
         $course = new course();
         $results = $course->Find("department_id=? and course_number=?", array($department->id, $SM_ARGS[3]));
         if(count($results))
            $course = $results[0];

         $this->args["course"] = $course;
      }
      else
      {
         // courses/display/crn
         // display a section
         $this->template_name = "display_section";

         $course_section = new course_section();
         $results = $course_section->Find("crn=?", array($SM_ARGS[2]));
         if(count($results))
            $course_section = $results[0];
         $course = $course_section->course;

         $this->args["course"] = $course;
         $this->args["course_section"] = $course_section;
      }
   }
}

?>
