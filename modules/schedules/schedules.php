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

         redirect("schedules/display/".$schedule->id);
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

      // get the specified schedule
      $schedule = new schedule();
      $results = $schedule->Find("id=?", array($id));
      if(!count($results))
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
      if(!$schedule->public && isset($SM_USER) && $schedule->user_id != $SM_USER->id)
      {
         $this->args['error'] = "The requested schedule is private.";
         return;
      }

      // check for updated schedule name
      if(array_key_exists('name', $_POST) && $schedule->user_id == $SM_USER->id)
      {
         $schedule->name = $_POST['name'];
         $schedule->save();
      }

      // check for the modification of any CRNs
      if(array_key_exists('sections', $_POST) && $schedule->user_id == $SM_USER->id)
      {
         // create an array of CRNs
         // split on any number of spaces or commas
         $crns = preg_split('/[\s,]+/', $_POST['sections']);

         // add the course section - if it isn't valid, nothing will change
         $schedule->remove_all_course_sections();
         $schedule->add_course_sections($crns);
         $schedule->save();
      }

      // create a list of course sections, comma separated
      $string = "";
      $sections = $schedule->course_sections();
      uasort($sections, sortby("course->department->abbreviation,#course->course_number,section"));
      foreach($sections as $course_section)
      {
         $string .= $course_section->crn . ",";
      }
      if(strlen($string) > 0)
         $string = substr($string, 0, -1);
      $this->args['course_sections_string'] = $string;

      // assign the schedule variable
      $this->args['schedule'] = $schedule;
      // FIXME - the CSS should be specified in the template, not here
      global $SM_RR;
      $this->args['css'][] = $SM_RR . "/css/partials/_schedule_display.css";
   }
}
?>
