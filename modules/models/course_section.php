<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

require_once('adodb/adodb-active-record.inc.php');

class course_section extends ADOdb_Active_Record
{
   // returns if this section conflicts with the other section
   function conflicts($other_section)
   {
      // loop throw all class_period paris
      foreach($this->class_periods as $this_class_period)
      {
         foreach($other_section->class_periods as $other_class_period)
         {
            if($this_class_period->conflicts($other_class_period))
               return true;
         }
      }
      return false;
   }
}

// a course section has many class periods
ADODB_Active_Record::ClassHasMany('course_section', 'class_periods', 'section_id', 'class_period');

// a course section has one course
ADODB_Active_Record::ClassBelongsTo('course_section', 'course', 'course_id', 'id', 'course');

// a course section has one instructor
ADODB_Active_Record::ClassBelongsTo('course_section', 'instructor', 'instructor_id', 'id', 'instructor');

// id
// course
// instructor
// section
// name
// description

?>
