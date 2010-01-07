<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

require_once('adodb/adodb-active-record.inc.php');

class class_period extends ADOdb_Active_Record {}

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
