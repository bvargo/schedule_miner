<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

// This module is responsible for scraping data from trailhead, given the
// html source from the 'find class' interface.

// Trailhead's table format; each field represents a cell on a row
// Entries with an asterisk are fields that are currently used in schedule
// miner. Entires that have MULTIPLE attached to them mean that there is
// potentially more than one of this type. For instance, some classes (such as
// EPICS) meet on different days for different periods of times. Accordingly,
// multiple section meeting times are required. On trailhead, these appear in
// different rows following the main entry. When this is translated to an
// array for process_record, these MULTIPLE entires are put, in order, into an
// array. An entry with index 1 in record[8] coresponds to the times of index
// 1 in record[9], etc.
//
// The Format:
// 0: select (check box, full, etc)
// *1: course reference number
// *2: department
// *3: course number
// *4: section letter
// 5: campus
// *6: number of credit hours
// *7: title of course
// *8: meeting days - MULTIPLE
// *9: meeting times - MULTIPLE
// *10: section capacity
// *11: number already enrolled in section
// 12: number of slots left is section
// 13: waitlist capacity
// 14: of people on waitlist
// 15: of slots left on waitlist
// 16: crosslist capacity
// 17: crosslist actual
// 18: crosslist remaining
// *19: instructor (for each meeting time row) - MULTIPLE
// 20: dates of course length - MULTIPLE
// *21: location (e.g. 'BB 125') - MULTIPLE
// 22: attribute ???

class Import extends Module
{

   public function test()
   {
      $department = new department();
      $result = $department->Find("abbreviation='CSCI'");
      $department = $result[0];

      $course = new course();
      $result = $course->Find("course_number=341 and department_id=?", array($department->id));
      $course = $result[0];

      print_r($section = $course->course_sections[0]->instructor->course_sections);
      print("\n");
   }

   // the main index action that makes the import module go vrooom
   public function index()
   {
      // TODO: clean this up and put it somewhere
      // load the file
      $file = "/tmp/courses";
      $file_h = fopen($file, "r");
      $raw_data = fread($file_h, filesize($file));
      fclose($file_h);
      $records = self::create_records($raw_data);
      $this->args['imported_classes'] = self::process_records($records);

//      $building = new building();
//      print_r($building->find("abbreviation=? or abbreviation=?", array('MH', 'BB')));
//      echo "\n";
//      print_r($building);
   }

   // process all of the records
   // records is the big array of data returned by import_data
   private static function process_records($records)
   {
      global $SM_SQL;

      // clean out the courses, course_sections, and class_periods
      // the import process can update existing records that change, but it
      // cannot remove old records that are in the new dataset. rather than
      // try to find these courses, which takes time, it is more efficient to
      // remove everything and reimport. the update code still remains, as it
      // provides warnings in the case of problems
      $SM_SQL->Execute("TRUNCATE TABLE courses");
      $SM_SQL->Execute("TRUNCATE TABLE course_sections");
      $SM_SQL->Execute("TRUNCATE TABLE class_periods");

      $output = "";

      foreach($records as $record)
         $output .= self::process_record($record);

      return $output;
   }

   // processes a single record
   // record is an array of the fields, as described at the top of this file
   private static function process_record($record)
   {

      // 1: course reference number
      $crn = $record[1];

      // 2: department
      $department = $record[2];

      // 3: course number
      $course_number = $record[3];

      // 4: section letter
      $section_letter = $record[4];

      // 6: number of credit hours
      $credit_hours = $record[6];

      // 7: title of course
      $title = $record[7];

      // 8: meeting days - multiple
      // 9: meeting times - multiple
      // 21: location - multiple
      $times = array();
      for($row = 0; $row < count($record[9]); $row++)
      {
         // get the start time and end time, as strings ready for the database
         $time_array = explode("-", $record[9][$row]);
         $start = DATE("H:i:00", STRTOTIME($time_array[0]));
         $end = DATE("H:i:00", STRTOTIME($time_array[1]));

         // get the building and room number
         $room_array = explode(" ", trim($record[21][$row]));
         $building = $room_array[0];
         $room = -1;
         if(count($room_array) >= 2)
            $room = $room_array[1];

         // loop over each of the charactes for the days of the week
         foreach(str_split($record[8][$row]) as $day)
         {
            if(preg_match('/[MTWRFS]/', $day))
               $times[] = array($day, $start, $end, $building, $room);
         }
      }

      // 10: section capacity
      $section_capacity = $record[10];

      // 11: number already enrolled in the section
      $number_enrolled = $record[11];

      // 19: instructor - multiple
      // takes the first instructor, removing the (P) tag from the name, and
      // removing whitespace
      $instructor_array = explode(",", $record[19][0]);
      $instructor = trim(str_replace("(P)", "", $instructor_array[0]));



      // departments - add if it does not exist already
      $dept = new department();
      $result = $dept->Find("abbreviation=?", array($department));
      if(!count($result))
      {
         $dept->abbreviation = $department;
         $result = $dept->save();
      }
      else
      {
         $dept = $result[0];
      }

      // instructor - add if it does not exist already
      $instruct = new instructor();
      $result = $instruct->Find("name=?", array($instructor));
      if(!count($result))
      {
         $instruct->name = $instructor;
         $result = $instruct->save();
      }
      else
      {
         $instruct = $result[0];
      }

      // add the course, if it does not exist already
      $course = new course();
      $result = $course->Find("course_number=? and department_id=?", array($course_number, $dept->id));
      if(count($result))
      {
         $course = $result[0];
         if(count($result) > 1)
            warn("Course search for course_number $course_number and department_id $department_id matches multiple records");
      }
      // add/modify the course
      $course->department_id = $dept->id;
      $course->course_number = $course_number;
      $course->credit_hours = floatval($credit_hours);
      $course->save();

      // add the course section, if it does not exist already - update
      // otherwise
      $course_section = new course_section();
      $result = $course_section->Find("course_id=? and section=?", array($course->id, $section_letter));
      if(count($result))
      {
         $course_section = $result[0];
         if(count($result) > 1)
            warn("Course section search for course_id $course->id matches multiple records");
      }
      // add/modify the course section
      $course_section->crn = $crn;
      $course_section->course_id = $course->id;
      $course_section->instructor_id = $instruct->id;
      $course_section->section = $section_letter;
      $course_section->name = $title;
      $course_section->save();


      // class periods:
      // building, course_section, class_period
      $first_run = 1;
      foreach($times as $time)
      {
         // time = [day, start_time, end_time, building, room]

         // add the building, it does not exist already
         $build = new building();
         $result = $build->Find("abbreviation=?", array($time[3]));
         if(!count($result))
         {
            $build->abbreviation = $time[3];
            $result = $build->save();
         }
         else
         {
            $build = $result[0];
         }

         // add the class period, if it does not exist already - if it does
         // exist, remove all previous class periods for this course and then
         // add the class period
         $class_period = new class_period();
         if($first_run)
         {
            $results = $class_period->Find("section_id=?", array($course_section->id));
            if(count($results))
            {
               // a previous class period was found - remove all the old
               // entries, so that it will appear as if there were never any
               // old entries and we are adding the data for the first time
               foreach($results as $result)
               {
                  $result->Delete();
               }
            }
         }

         $class_period->section_id = $course_section->id;
         $class_period->building_id = $build->id;
         $class_period->room_number = $time[4];
         // TODO: make sure these times work correctly
         $class_period->start_time = $time[1];
         $class_period->end_time = $time[2];
         $class_period->day = $time[0];
         $class_period->save();

         $first_run = 0;
      }

      return "$crn - $department $course_number $section_letter - $credit_hours - $instructor - $title<br />\n";
   }


   // creates records, as described at the top of this file, out of the raw
   // html data from trailhead
   private static function create_records($raw_data)
   {
      // the big array for parsed data
      $data = array();

      // the HTML document, for parsing
      $dom = new domDocument;

      // load the data
      $dom->loadHTML($raw_data);

      // get the tables and rows of the first (and hopefully only) table
      $tables = $dom->getElementsByTagName('table');
      $rows = $tables->item(0)->getElementsByTagName('tr');

      // current record
      // incremented before every new record, so start at -1
      $index = -1;

      // loop over each row, collecting the data as we go
      foreach($rows as $row)
      {
         // get the columns for this row
         $cols = $row->getElementsByTagName('td');

         // make sure we have enough fields; if we don't, skip
         if(!$cols->item(22))
         {
            d("Skipping a row during the import - not enough columns");
            continue;
         }

         // if this course has not been assigned a time yet, skip it
         // TODO: is it worth it to put a course in the database, even if it
         // doesn't have a time yet?
         if($cols->item(8)->nodeValue == "TBA" || $cols->item(9)->nodeValue == "TBA")
         {
            d("Skipping a row during the import - no time given");
            continue;
         }

         // figure out if this row is the start of a new section, or a
         // continuation of the last section
         $select = $cols->item(1)->nodeValue;
         if(!preg_match('/^[ \t]*$/', $select) && ord($select) != 194)
         {
            // this is a new section - start a new record

            // update the index
            $index += 1;

            $data[$index] = array();

            // loop over each column in the table
            for($i = 0; $i <= 22; $i++)
            {
               if($i != 8 && $i != 9 && $i != 19 && $i != 20 && $i != 21)
               {
                  // only one value - no secondary array needed
                  $data[$index][$i] = $cols->item($i)->nodeValue;
               }
               else
               {
                  // multiple values for multiple timeslots - put the value in an
                  // array
                  $data[$index][$i] = array();
                  $data[$index][$i][] = $cols->item($i)->nodeValue;
               }
            }
         }
         else if($index >= 0)
         {
            // continuing the previous record
            // data items that can be added:
            //    date (8)
            //    time (9)
            //    instructor(19)
            //    dates of course length (20)
            //    location (21)

            $data[$index][8][] = $cols->item(8)->nodeValue;
            $data[$index][9][] = $cols->item(9)->nodeValue;
            $data[$index][19][] = $cols->item(19)->nodeValue;
            $data[$index][20][] = $cols->item(20)->nodeValue;
            $data[$index][21][] = $cols->item(21)->nodeValue;
         }
      }

      return $data;
   }
}

?>

