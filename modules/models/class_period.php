<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

require_once('adodb/adodb-active-record.inc.php');

class class_period extends ADOdb_Active_Record
{
   // returns if this class period conflicts with the other period
   public function conflicts($other_period)
   {
      // if the two periods aren't on the same day, they cannot conflict
      if($this->day != $other_period->day)
         return false;

      // this period is before the other one
      if(class_period::to_minutes($this->end_time) < class_period::to_minutes($other_period->start_time))
         return false;

      // this period is after the other one
      if(class_period::to_minutes($this->start_time) > class_period::to_minutes($other_period->end_time))
         return false;

      // if the period isn't after and isn't before, then it must conflict
      return true;
   }

   // returns the time in minutes since midnight
   public static function to_minutes($time)
   {
      $t = explode(":", $time);
      $hour = intval($t[0]);
      $minute = intval($t[1]);
      $minutes = $hour * 60 + $minute;
      return $minutes;
   }

   // returns an array of distinct start times
   public static function start_times()
   {
      global $SM_SQL;

      $query = "SELECT DISTINCT start_time FROM class_periods;";
      $results = $SM_SQL->GetAll($query);

      $return_results = array();
      foreach($results as $result)
      {
         $return_results[] = $result['start_time'];
      }

      return $return_results;
   }

   // returns an array of distinct end times
   public static function end_times()
   {
      global $SM_SQL;

      $query = "SELECT DISTINCT end_time FROM class_periods;";
      $results = $SM_SQL->GetAll($query);

      $return_results = array();
      foreach($results as $result)
      {
         $return_results[] = $result['end_time'];
      }

      return $return_results;
   }

   // finds all sections that have at least one class period that takes place
   // between start_time and end_time and falls on the day provided
   // days should contains strings of M, T, W, R, F and is assumed to be sql-safe
   // returns an array of section ids
   public static function time_search($start_time, $end_time, $days)
   {
      global $SM_SQL;

      $query = "SELECT DISTINCT section_id FROM class_periods WHERE start_time >= ? AND end_time <= ? AND day IN (";
      foreach($days as $day)
      {
         $query .= "'$day'";
         if($day != end($days))
            $query .= ',';
      }
      $query .= ");";
      $results = $SM_SQL->GetAll($query, array($start_time, $end_time, $days));

      $section_ids = array();
      foreach($results as $result)
      {
         $section_ids[] = $result['section_id'];
      }

      return $section_ids;
   }
}

// a class period has one building
ADODB_Active_Record::ClassBelongsTo('class_period', 'building', 'building_id', 'id', 'building');

// class periods have one course_section
ADODB_Active_Record::ClassBelongsTo('class_period', 'course_section', 'section_id', 'id', 'course_section');

// id
// section
// building
// room_number - smallint(5)
// start_time - time
// end_time - time
// day - tinyint(3)

?>
