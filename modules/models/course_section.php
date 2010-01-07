<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

require_once('adodb/adodb-active-record.inc.php');

class course_section extends ADOdb_Active_Record
{
   // used for assigning colors when displaying a schedule
   public $color;
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
