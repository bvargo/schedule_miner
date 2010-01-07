<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

require_once('adodb/adodb-active-record.inc.php');

class course extends ADOdb_Active_Record {}

// a course has many course sections
ADODB_Active_Record::ClassHasMany('course', 'course_sections', 'course_id', 'course_section');

// a course has one department
ADODB_Active_Record::ClassBelongsTo('course', 'department', 'department_id', 'id', 'department');

// id
// department
// course_number
// credit_hours

?>
