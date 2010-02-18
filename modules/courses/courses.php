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
      $show = smconfig_get("index_show_departments", "all");
      $departments = new department();
      $departments = $departments->Find("", array());
      if($show == "all")
      {
         // show all departments
         $this->args["departments"] = $departments;
      }
      else if($show == "useful")
      {
         // show departments that have courses
         $depts = array();
         foreach($departments as $department)
         {
            if(count($department->courses))
               $depts[] = $department;
         }
         $this->args["departments"] = $depts;
      }
      else
      {
         // show only the specified departments
         $show = explode(",", $show);
         $include = array();
         $depts = array();
         foreach($show as $abbreviation)
         {
            $abbreviation = trim($abbreviation);
            $include[] = $abbreviation;
         }
         foreach($departments as $department)
         {
            if(in_array($department->abbreviation, $include))
               $depts[] = $department;
         }
         $this->args["departments"] = $depts;
      }


      // show buildings
      $show = smconfig_get("index_show_buildings", "all");
      $buildings = new building();
      $buildings = $buildings->Find("", array());
      if($show == "all")
      {
         // show all buildings
         $this->args["buildings"] = $buildings;
      }
      else if($show == "useful")
      {
         // show useful buildings
         $builds = array();
         foreach($buildings as $building)
         {
            if(count($building->class_periods))
               $builds[] = $building;
         }
         $this->args["buildings"] = $builds;
      }
      else
      {
         // shows only the specified buildings
         $show = explode(",", $show);
         $include = array();
         $builds = array();
         foreach($show as $abbreviation)
         {
            $abbreviation = trim($abbreviation);
            $include[] = $abbreviation;
         }
         foreach($buildings as $building)
         {
            if(in_array($building->abbreviation, $include))
               $builds[] = $building;
         }
         $this->args["buildings"] = $builds;
      }

      // show instructors
      $show = smconfig_get("index_show_instructors", "all");
      $instructors = new instructor();
      $instructors = $instructors->Find("", array());
      if($show == "all")
      {
         $this->args["instructors"] = $instructors;
      }
      else if($show == "useful")
      {
         $instructs = array();
         foreach($instructors as $instructor)
         {
            if(count($instructor->course_sections))
               $instructs[] = $instructor;
         }
         $this->args["instructors"] = $instructs;
      }
      else
      {
         // show all instructors if there is a bad value
         warn("Bad value for index_show_instructors: $show");
         $this->args["instructors"] = $instructors;
      }

      // number of columns to display
      $this->args["department_columns"]  = smconfig_get("index_department_columns", 3);
      $this->args["building_columns"]    = smconfig_get("index_building_columns",   5);
      $this->args["instructor_columns"]  = smconfig_get("index_instructor_columns", 5);
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
      else if($SM_ARGS[2] == "building" && isset($SM_ARGS[4]))
      {
         // courses/display/building/building_abbr/room_number
         // display a room in a building
         $this->template_name = "display_building_room";

         $room = $SM_ARGS[4];
         $this->args['room'] = $room;

         $building = new building();
         $results = $building->Find("abbreviation=?", array($SM_ARGS[3]));
         if(count($results))
            $building = $results[0];
         $this->args['building'] = $building;

         // create a list of courses in the room
         $course_sections = array();
         foreach($building->class_periods as $class_period)
         {
            if($class_period->room_number == $room)
               if(!in_array($class_period->course_section, $course_sections))
                  $course_sections[] = $class_period->course_section;
         }
         $this->args['course_sections'] = $course_sections;

         if(!count($course_sections))
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
