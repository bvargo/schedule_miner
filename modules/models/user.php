<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

require_once('adodb/adodb-active-record.inc.php');

class user extends ADOdb_Active_Record
{
   public $password;

   // encrypt and save the password when the object is saved, if the password 
   // has been changed
   public function save()
   {
      if($this->password)
      {
         // the password has been changed - regenerated the encrypted form
         $this->epassword = $this->generate_hash($this->password);
      }
      return parent::save();
   }

   public function check_password($password)
   {
      if($this->generate_hash($password) == $this->epassword)
         return 1;
      else
         return 0;
   }

   private function generate_hash($password)
   {
      if(!$this->salt)
         $this->salt = substr(md5(uniqid(rand(), true)), 0, 12);
      return md5(sha1($this->salt.$password));
   }
}

// users have many schedules
ADODB_Active_Record::ClassHasMany('user', 'schedules', 'user_id', 'schedule');

// a user has one active schedule
ADODB_Active_Record::ClassBelongsTo('user', 'schedule', 'active_schedule_id', 'id', 'schedule');

// id - int
// username - varchar (128)
// epassword - varchar (32) (MD5)
// name - varchar(128)
// ename - varchar(128)

?>
