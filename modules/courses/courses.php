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
      $departments = $departments->find("", array());
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
      $buildings = $buildings->find("", array());
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
      $instructors = $instructors->find("", array());
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

   // serach module
   public function search()
   {
      global $SM_QUERY, $SM_ARGS;

      if(isset($SM_ARGS[2]) && $SM_ARGS[2] == "help")
      {
         $this->args['display_help'] = 1;
         return;
      }

      // see if there is a GET request with a search
      if(array_key_exists("q", $SM_QUERY))
      {
         $search_query = $SM_QUERY["q"];
         $this->args["search_query"] = $search_query;

         $search = new search();
         $results = $search->course_sections($search_query);
         if($results === false)
            $this->args["error"] = $search->error;
         else if(empty($results))
            $this->args["no_results"] = true;
         else
            $this->args["course_sections"] = $results;
      }
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
         if(!$instructor->load("id=?", array($SM_ARGS[3])))
         {
            $this->args['error'] = "Requested instructor not found.";
            return;
         }
         $this->args['instructor'] = $instructor;

         if(!count($instructor->course_sections))
            $this->args['empty'] = 1;

         // FIXME - the CSS should be specified in the template, not here
         global $SM_RR;
         $this->args['css'][] = $SM_RR . "/css/partials/_schedule_display.css";
      }
      else if($SM_ARGS[2] == "department" && isset($SM_ARGS[3]))
      {
         // courses/display/department/department_abbr
         // display a department
         $this->template_name = "display_department";

         $department = new department();
         if(!$department->load("abbreviation=?", array($SM_ARGS[3])))
         {
            $this->args['error'] = "Requested department not found.";
            return;
         }
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
         if(!$building->load("abbreviation=?", array($SM_ARGS[3])))
         {
            $this->args['error'] = "Requested room not found.";
            return;
         }
         $this->args['building'] = $building;

         // create a list of courses in the room and class periods in the room
         $course_sections = array();
         $class_periods = array();
         foreach($building->class_periods as $class_period)
         {
            if($class_period->room_number == $room)
            {
               if(!in_array($class_period->course_section, $course_sections))
                  $course_sections[] = $class_period->course_section;
               $class_periods[] = $class_period;
            }
         }
         $this->args['course_sections'] = $course_sections;
         $this->args['class_periods'] = $class_periods;

         if(!count($course_sections))
            $this->args['empty'] = 1;

         // FIXME - the CSS should be specified in the template, not here
         global $SM_RR;
         $this->args['css'][] = $SM_RR . "/css/partials/_schedule_display.css";
      }
      else if($SM_ARGS[2] == "building" && isset($SM_ARGS[3]))
      {
         // courses/display/building/building_abbr
         // display a building
         $this->template_name = "display_building";

         $building = new building();
         if(!$building->load("abbreviation=?", array($SM_ARGS[3])))
         {
            $this->args['error'] = "Requested building not found.";
            return;
         }
         $this->args['building'] = $building;

         // create a list of courses in the room
         $course_sections = array();
         foreach($building->class_periods as $class_period)
         {
            if(!in_array($class_period->course_section, $course_sections))
               $course_sections[] = $class_period->course_section;
         }
         $this->args['course_sections'] = $course_sections;

         if(!count($course_sections))
            $this->args['empty'] = 1;
      }
      else if(isset($SM_ARGS[4]))
      {
         // courses/display/department_abbr/course_number/course_section_letter
         // display a section
         $this->template_name = "display_section";

         $department = new department();
         if(!$department->load("abbreviation=?", array($SM_ARGS[2])))
         {
            $this->args['error'] = "Requested department not found.";
            return;
         }

         $course = new course();
         if(!$course->load("department_id=? and course_number=?", array($department->id, $SM_ARGS[3])))
         {
            $this->args['error'] = "Requested course not found.";
            return;
         }

         $course_section = new course_section();
         if(!$course_section->load("course_id=? and section=?", array($course->id, $SM_ARGS[4])))
         {
            $this->args['error'] = "Requested course section not found.";
            return;
         }

         $this->args["course"] = $course;
         $this->args["course_section"] = $course_section;

         // FIXME - the CSS should be specified in the template, not here
         global $SM_RR;
         $this->args['css'][] = $SM_RR . "/css/partials/_schedule_display.css";
      }
      else if(isset($SM_ARGS[3]))
      {
         // courses/display/department_abbr/course_number
         // display a course
         $this->template_name = "display_course";

         $department = new department();
         ;
         if(!$department->load("abbreviation=?", array($SM_ARGS[2])))
         {
            $this->args['error'] = "Requested department not found.";
            return;
         }
         $this->args["department"] = $department;

         $course = new course();
         if(!$course->load("department_id=? and course_number=?", array($department->id, $SM_ARGS[3])))
         {
            $this->args['error'] = "Requested course not found.";
            return;
         }

         $this->args["course"] = $course;

         // FIXME - the CSS should be specified in the template, not here
         global $SM_RR;
         $this->args['css'][] = $SM_RR . "/css/partials/_schedule_display.css";
      }
      else
      {
         // courses/display/crn
         // display a section
         $this->template_name = "display_section";

         $course_section = new course_section();
         if(!$course_section->load("crn=?", array($SM_ARGS[2])))
         {
            $this->args['error'] = "Requested course section not found.";
            return;
         }
         $course = $course_section->course;

         $this->args["course"] = $course;
         $this->args["course_section"] = $course_section;

         // FIXME - the CSS should be specified in the template, not here
         global $SM_RR;
         $this->args['css'][] = $SM_RR . "/css/partials/_schedule_display.css";
      }
   }
}

?>
