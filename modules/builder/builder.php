<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

// automatic schedule builder
// generates a number of schedules given criteria

class Builder extends Module
{
   function index()
   {
      global $SM_QUERY, $SM_USER;

      $this->args['max_schedules'] = smconfig_get('max_schedules', 20);

      // FIXME - the CSS should be specified in the template, not here
      global $SM_RR;
      $this->args['css'][] = $SM_RR . "/css/partials/_schedule_display.css";

      if(isset($SM_QUERY['from_schedule']))
      {
         $id = $SM_QUERY['from_schedule'];
         $schedule = new schedule();
         if($schedule->load("id=?", array($id)) && $schedule->user_id == $SM_USER->id)
         {
            // the schedule exists and it is our schedule; overwrite any other
            // parameters passed
            $SM_QUERY['department'] = array();
            $SM_QUERY['course_number'] = array();
            $SM_QUERY['course_section'] = array();
            $SM_QUERY['priority'] = array();
            $i = 1;
            $sections = $schedule->course_sections();
            uasort($sections, sortby("course->department->abbreviation,#course->course_number,section"));
            foreach($sections as $section)
            {
               $SM_QUERY['department'][] = $section->course->department->abbreviation;
               $SM_QUERY['course_number'][] = $section->course->course_number;
               $SM_QUERY['course_section'][] = $section->section;
               $SM_QUERY['priority'][] = $i++;
            }
         }
      }

      if(isset($SM_QUERY['department']) && isset($SM_QUERY['course_number']))
      {
         // generate the entry_rows array(department, course_number, course_section, priority)
         // get rid of rows without enough data

         // create optional arrays if not given
         if(!isset($SM_QUERY['course_section']))
            $SM_QUERY['course_section'] = array();
         if(!isset($SM_QUERY['priority']))
            $SM_QUERY['priority'] = array();

         // create the entry_rows
         $entry_rows = array();
         for($i = 0; $i < count($SM_QUERY['department']); $i++)
         {
            if(isset($SM_QUERY['department'][$i]) &&
               isset($SM_QUERY['course_number'][$i]) &&
               trim($SM_QUERY['department'][$i]) != "" &&
               trim($SM_QUERY['course_number'][$i]) != "")
            {
               // blank course_section if not given
               if(!isset($SM_QUERY['course_section'][$i]))
                  $SM_QUERY['course_section'][$i] = "";

               // default priority of 1 if not given
               if(!isset($SM_QUERY['priority'][$i]) || trim($SM_QUERY['priority'][$i]) == "")
                  $SM_QUERY['priority'][$i] = 1;

               $entry_rows[] = array($SM_QUERY['department'][$i], $SM_QUERY['course_number'][$i], $SM_QUERY['course_section'][$i], $SM_QUERY['priority'][$i]);
            }
         }

         // generate the schedules, after validating entry_rows
         Builder::validate_entry_rows($entry_rows);
         $generated = Builder::get_schedules($entry_rows);
         if($generated == null)
         {
            // the memory limit was hit
            $this->args['hit_limit'] = 1;
         }
         else
         {
            $this->args['schedules'] = $generated;
            $this->args['schedule_count'] = count($generated);
            $this->args['number_schedules_display'] = min($this->args['max_schedules'], count($generated));
         }

         // set entry_rows in template
         $this->args['entry_rows'] = $entry_rows;
      }
   }

   // validates entry rows
   // makes sure all courses / course sections are valid
   // makes sure all group numbers are actually numbers
   // entry_rows =  array(department, course_number, course_section, priority)
   private static function validate_entry_rows(&$entry_rows)
   {
      $contents = array();
      foreach($entry_rows as $id => &$row)
      {
         // make sure this row doesn't already exist
         if(in_array(array($row[0], $row[1], $row[2]), $contents))
            unset($entry_rows[$id]);
         else
            $contents[] = array($row[0], $row[1], $row[2]);

         // try to find the department
         $department = new department();
         $department->load('abbreviation=?', array($row[0]));
         if(!$department->id)
         {
            unset($entry_rows[$id]);
            continue;
         }

         // try to find the course
         $course = new course();
         $course->load('department_id=? and course_number=?', array($department->id, $row[1]));
         if(!$course->id)
         {
            unset($entry_rows[$id]);
            continue;
         }

         // try to find the course section, if given
         if(trim($row[2]) != "")
         {
            $section = new course_section();
            $section->load('course_id=? and section=?', array($course->id, $row[2]));
            if(!$section->id)
            {
               unset($entry_rows[$id]);
               continue;
            }
         }

         // make sure the group is numeric
         if(!is_numeric($row[3]))
         {
            $row[3] = -1;
         }
      }
   }

   // return an array of schedules found for the given entry rows
   // if no schedules are found that match the criteria, an empty array is
   // returned
   // if the memory/time limit is hit, null is returned
   // entry_rows =  array(department, course_number, course_section, priority)
   private static function get_schedules($entry_rows)
   {
      // final schedule results
      $schedules = array();
      // BFS search to get schedules
      $queue = array();

      if(empty($entry_rows))
      {
         return $schedules;
      }

      // push empty schedule and entry_rows into the queue
      array_push($queue, array(new schedule_temp(), $entry_rows));

      // get the memory limit
      $memory_limit = trim(ini_get('memory_limit'));
      $last = strtolower($memory_limit[strlen($memory_limit) - 1]);
      switch($last)
      {
         case 'g':
            $memory_limit *= 1024;
         case 'm':
            $memory_limit *= 1024;
         case 'k':
            $memory_limit *= 1024;
      }

      while(!empty($queue))
      {
         // make sure we are not near the memory limit
         // if we come within 2MB of the memory limit, return null
         $memory_current = memory_get_usage();
         if($memory_limit - $memory_current < 2097152)
         {
            warn('Schedule builder hit memory limit');
            return null;
         }

         // get element at the front of the queue
         $current = array_shift($queue);
         // split it up
         $schedule = $current[0];
         $rows = $current[1];
         if(empty($rows))
         {
            // no more selections to add - add this schedule to the list of
            // schedules and process the next entry
            $schedules[] = $schedule;
            continue;
         }

         // get the smallest priority of the selections remaining
         $min_priority = null;
         foreach($rows as $row)
         {
            if($min_priority == null || $row[3] < $min_priority)
               $min_priority = $row[3];
         }

         // get the selections with the min priority, removing these rows from
         // $rows
         $current_selections = array();
         foreach($rows as $id => $row)
         {
            if($row[3] == $min_priority)
            {
               $current_selections[] = $row;
               unset($entry_rows[$id]);
            }
         }

         // process the current_selections
         foreach($current_selections as $row)
         {
            // try to find the department
            $department = new department();
            $department->load('abbreviation=?', array($row[0]));
            if(!$department->id)
               continue;
            // try to find the course
            $course = new course();
            $course->load('department_id=? and course_number=?', array($department->id, $row[1]));
            if(!$course->id)
               continue;
            if(trim($row[2]) != "")
            {
               // try to find the course secection
               $section = new course_section();
               $section->load('course_id=? and section=?', array($course->id, $row[2]));
               if(!$section->id)
                  continue;
               if(!$schedule->conflicts($section))
               {
                  $new_schedule = clone $schedule;
                  $new_schedule->add_course_section($section);
                  array_push($queue, array($new_schedule, $entry_rows));
               }
            }
            else
            {
               // get all the course sections
               $section = new course_section();
               $sections =  $section->find('course_id=?', array($course->id));
               foreach($sections as $section)
               {
                  if(!$schedule->conflicts($section))
                  {
                     $new_schedule = clone $schedule;
                     $new_schedule->add_course_section($section);
                     array_push($queue, array($new_schedule, $entry_rows));
                  }
               }
            }
         }
      }

      return $schedules;
   }
}

?>

