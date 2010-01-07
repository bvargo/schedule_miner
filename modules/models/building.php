<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

require_once('adodb/adodb-active-record.inc.php');

class building extends ADOdb_Active_Record {}

// buildings have many class periods
ADODB_Active_Record::ClassHasMany('building', 'class_periods', 'building_id', 'class_period');

// id - int
// abbreviation - char(2)
// name - varchar(128)

?>
