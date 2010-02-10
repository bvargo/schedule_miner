<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

// displays a schedule

class Schedules extends Module
{

   // the main show schedule interface
   public function index()
   {
      redirect('schedules/show_list');
   }

   private static function byname($a, $b)
   {
      return strcasecmp($a->name, $b->name);
   }

   public function show_list()
   {
      global $SM_USER;

      // set the active schedule if the active schedule id is not set or the 
      // id references a schedule does not exist
      // if the user does not have any schedules, set the id to -1 
      $schedule_ids = array();
      $schedules = $SM_USER->schedules;
      usort($schedules, array("Schedules", "byname"));
      foreach($schedules as $schedule)
      {
         $schedule_ids[] = $schedule->id;
      }
      if(count($schedule_ids) < 1)
      {
         $SM_USER->active_schedule_id = -1;
         $SM_USER->save();
      } 
      else if(!in_array($SM_USER->active_schedule_id, $schedule_ids))
      {
         $SM_USER->active_schedule_id = $schedule_ids[0];
         $SM_USER->save();
      }

      // check for the saving of preferences
      if(!empty($_POST))
      {
         // check for updated visible values
         foreach($SM_USER->schedules as $schedule)
         {
            if(isset($_POST["public" . $schedule->id]))
               $schedule->public = 1;
            else
               $schedule->public = 0;
            $schedule->save();
         }

         // set the active schedule
         if(isset($_POST['active_schedule']) && in_array($_POST['active_schedule'], $schedule_ids))
         {
            $SM_USER->active_schedule_id = $_POST['active_schedule'];
            $SM_USER->save();
         }

         // check if a schedule as deleted
         if(!isset($_POST['save']))
         {
            // the save button wasn't used, so a delete button was probably 
            // hit (these buttons are also submit buttons)
            foreach($SM_USER->schedules as &$schedule)
            {
               if(isset($_POST["delete" . $schedule->id]))
                  $schedule->delete();
            }
         }

         redirect('schedules/show_list');
      }
   }

   public function create()
   {
      global $SM_USER;

      // see if there is a POST request to create a user
      if(!empty($_POST) && isset($_POST['schedule_name']) && $_POST['schedule_name'] != "")
      {
         // create a schedule
         $schedule = new schedule();
         $schedule->user_id = $SM_USER->id;
         $schedule->name = $_POST['schedule_name'];

         // public schedule?
         if(isset($_POST["public"]))
            $schedule->public = 1;
         else
            $schedule->public = 0;

         $schedule->save();

         // make this the default schedule, if selected, or if this is the 
         // user's only schedule
         if(isset($_POST["active"]) || count($SM_USER->schedules) == 1)
         {
            $SM_USER->active_schedule_id = $schedule->id;
            $SM_USER->save();
         }

         redirect('schedules/show_list');
      }
   }

   public function display()
   {
      global $SM_USER, $SM_ARGS;

      // if we are not provided an id, show a list of all schedules for this 
      // user
      if(!isset($SM_ARGS[2]))
      {
         redirect('schedules/show_list');
      }

      // we were provided an id; show that schedule
      $id = $SM_ARGS[2];

      // get the course sections for the specified schedule
      $schedule = new schedule();
      $results = $schedule->Find("id=?", array($id));
      if(empty($results))
      {
         $this->args['error'] = "The requested schedule could not be found.";
         return;
      }
      else
      {
         $schedule = $results[0];
      }

      // if the schedule is our schedule, it can be displayed
      // or, if the schedule is public, it can be displayed
      // otherwise, don't display the schedule
      if(!$schedule->public && $SM_USER && $schedule->user_id != $SM_USER->id)
      {
         $this->args['error'] = "The requested schedule is private.";
         return;
      }

      $course_sections = $schedule->course_sections();

      // assign colors to the courses
      $color_assign = array();
      $colors = array(
                     '#ff0000',   // red
                     '#ff8100',   // orange
                     '#ffff00',   // yellow
                     '#00ff00',   // lime green
                     '#107000',   // darker green
                     '#9999ff',   // lilac
                     '#00aaff',   // light blue
                     '#0f94a9',   // teal blue
                     '#990099',   // purple/pink
                     '#AAAAAA',   // lightgrey
                     '#cc6600',   // brown / dark orange
                     '#45c300',   // bright green
                     '#3366ff',   // dark blue
                     '#f2679a',   // pink
                     '#666666'    // darkgrey
                  );
      $color_index = 0;
      foreach($course_sections as $course_section)
      {
         if(!array_key_exists($course_section->id, $color_assign))
         {
            $course_section->color = $colors[$color_index];;
            $color_index += 1;
            $color_index %= count($colors);
            $courses[$course_section->id] = 1;
         }
      }

      // construct an array of meeting times containing:
      // 0 - name
      // 1 - section
      // 2 - department abbreviation
      // 3 - course number
      // 4 - crn
      // 5 - building abbreviation
      // 6 - room number
      // 7 - day
      // 8 - start time
      // 9 - end time
      // 10 - slot
      // 11 - column height (for rowspan)
      // 12 - building name
      // 13 - color
      $meeting_times = array();

      // find meeting times
      foreach($course_sections as $course_section)
      {
         $class_periods = $course_section->class_periods;
         foreach($class_periods as $class_period)
         {
            $meeting_times[] = array($course_section->name,
                                     $course_section->section,
                                     $course_section->course->department->abbreviation,
                                     $course_section->course->course_number,
                                     $course_section->crn,
                                     $class_period->building->abbreviation,
                                     $class_period->room_number,
                                     $class_period->day,
                                     $class_period->start_time,
                                     $class_period->end_time,
                                     -1,
                                     1,
                                     $class_period->building->name,
                                     $course_section->color);
         }
      }


      // the following should really go in the template, but smarty is really
      // stupid

      // transform days "M, T, W, etc" into numbers (0 = Sunday)
      for($i = 0; $i < count($meeting_times); $i++)
      {
         switch($meeting_times[$i][7])
         {
         case "M":
            $meeting_times[$i][7] = 1;
            break;
         case "T":
            $meeting_times[$i][7] = 2;
            break;
         case "W":
            $meeting_times[$i][7] = 3;
            break;
         case "R":
            $meeting_times[$i][7] = 4;
            break;
         case "F":
            $meeting_times[$i][7] = 5;
            break;
         default:
            // should never get here
            $meeting_times[$i][7] = 0;
         }
      }

      // determine the first and last class times
      $first_class_hour = $meeting_times[0][8];
      $last_class_hour = $meeting_times[0][9];
      foreach($meeting_times as $meeting_time)
      {
         if($meeting_time[8] < $first_class_hour)
            $first_class_hour = $meeting_time[8];
         if($meeting_time[9] > $last_class_hour)
            $last_class_hour = $meeting_time[9];
      }
      // first_class_hour - always start on an hour
      $t = explode(":", $first_class_hour);
      $first_class_hour = intval($t[0]);
      // last_class_hour - always end on an hour
      $t = explode(":", $last_class_hour);
      if(!$t[0])
         $last_class_hour = intval($t[0]);
      else
         $last_class_hour = intval($t[0]) + 1;
      // first and last class_hour are now ints containing the start and end hours

      // an array of days, each containing an entry for each slot
      // calendar[day][minute][slot] = [meeting_time, state]
      // slot is the class (usually 0), but is incremented if two classes 
      // overlap - see below
      // state is 0 if start of block, 1 if in the middle, 2 if at the end
      $calendar = array();
      for($day = 1; $day < 6; $day++)
         $calendar[$day] = array();

      // just add the classes to the calendar array - no slots yet
      for($day = 0; $day < 7; $day++)
      {
         foreach($meeting_times as &$meeting_time)
         {
            if($meeting_time[7] == $day)
            {
               for($time = self::date_to_minutes($meeting_time[8]); $time < self::date_to_minutes($meeting_time[9]); $time += 10)
               {
                  // make sure the arrays exist
                  if(!array_key_exists($time, $calendar[$day]))
                     $calendar[$day][$time] = array();

                  // set the state
                  $state = 1;
                  if($time == self::date_to_minutes($meeting_time[8]))
                     $state = 0;
                  else if($time >= self::date_to_minutes($meeting_time[9]))
                     $state = 2;

                  // set the number of rows to span
                  $meeting_time[11] = self::date_to_minutes($meeting_time[9]) - self::date_to_minutes($meeting_time[8]);
                  if($meeting_time[11] % 10 != 0)
                     $meeting_time[11] = intval($meeting_time[11] / 10) + 1;
                  else
                     $meeting_time[11] = intval($meeting_time[11] / 10);


                  // add it
                  $calendar[$day][$time][] = array(&$meeting_time, $state);
               }
            }
         }
         unset($meeting_time);
      }

      // figure out how many columns each day needs
      $colspan = array(0,1,1,1,1,1,0);
      for($day = 1; $day < 6; $day++)
      {
         for($time = $first_class_hour * 60; $time < $last_class_hour * 60; $time += 10)
         {
            if(array_key_exists($time, $calendar[$day]))
               if(count($calendar[$day][$time]) > $colspan[$day])
                  $colspan[$day] = count($calendar[$day][$time]);
         }
      }

      // now assign slots
      for($day = 1; $day < 6; $day++)
      {
         if($colspan[$day] > 1)
         {
            for($time = $first_class_hour * 60; $time < $last_class_hour * 60; $time += 10)
            {
               if(array_key_exists($time, $calendar[$day]))
               {
                  if(count($calendar[$day][$time]) > 1)
                  {
                     // we have found a time that has multiple classes
                     // this needs to be resolved

                     // as temporary place to keep track of block assignments for 
                     // this timeperiod
                     $slot_assignments = array();

                     //find if something already has a slot assigned
                     //if so, keep that assignment
                     foreach($calendar[$day][$time] as $block)
                     {
                        if($block[0][10] != -1)
                           $slot_assignments[$block[0][10]] = $block[0];
                     }

                     // now assign the rest
                     foreach($calendar[$day][$time] as &$block)
                     {
                        if($block[0][10] == -1)
                        {
                           // need to assign a block
                           $i = 0;
                           while(1)
                           {
                              if(!array_key_exists($i, $slot_assignments))
                              {
                                 $slot_assignments[$i] = $block[0];
                                 $block[0][10] = $i;
                                 break;
                              }
                              else
                              {
                                 $i++;
                              }
                           }
                        }
                     }
                     unset($block);
                  }
               }
            }
         }
         else if($colspan[$day] == 1)
         {
            // no conflicts here - just set all the slots to 0
            for($time = $first_class_hour * 60; $time < $last_class_hour * 60; $time += 10)
            {
               if(array_key_exists($time, $calendar[$day]))
               {
                  foreach($calendar[$day][$time] as &$block)
                  {
                     $block[0][10] = 0;
                  }
                  unset($block);
               }
            }
         }
      }

      // figure out the number of credit hours
      $total_credit_hours = 0;
      foreach($course_sections as $course_section)
      {
         $total_credit_hours += $course_section->course->credit_hours;
      }

      // now calendar contains all of the data, including slot information
      // pass this off to the template to display
      $this->args['schedule'] = $schedule;
      $this->args['first_class_hour'] = $first_class_hour;
      $this->args['last_class_hour'] = $last_class_hour;
      $this->args['colspan'] = $colspan;
      $this->args['calendar'] = $calendar;
      $this->args['course_sections'] = $course_sections; // needed, since we add colors above
   }

   private static function date_to_minutes($date)
   {
      $t = explode(":", $date);
      $hour = intval($t[0]);
      $minute = intval($t[1]);
      $minutes = $hour * 60 + $minute;
      return $minutes;
   }

}
?>
