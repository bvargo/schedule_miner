<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

require_once('adodb/adodb-active-record.inc.php');

class building extends ADOdb_Active_Record
{
   function rooms()
   {
      global $SM_SQL;
      $sqlresults = $SM_SQL->GetAll("select distinct room_number from class_periods where building_id = ?", array($this->id));
      $results = array();
      foreach($sqlresults as $sqlresult)
      {
         if($sqlresult[0] != -1)
         $results[]= $sqlresult[0];
      }

      return $results;
   }
}

// buildings have many class periods
ADODB_Active_Record::ClassHasMany('building', 'class_periods', 'building_id', 'class_period');

// id - int
// abbreviation - char(2)
// name - varchar(128)

?>
