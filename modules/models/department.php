<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

require_once('adodb/adodb-active-record.inc.php');

class department extends ADOdb_Active_Record {}

// a department has many courses
ADODB_Active_Record::ClassHasMany('department', 'courses', 'department_id', 'course');

// id
// abbreviation
// name

?>
