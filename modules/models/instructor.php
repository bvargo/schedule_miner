<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

require_once('adodb/adodb-active-record.inc.php');

class instructor extends ADOdb_Active_Record {}

// an instructor has many course_sections
aDODB_Active_Record::ClassHasMany('instructor', 'course_sections', 'instructor_id', 'course_section');

// id
// name

?>
