<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

// session management

class SessionManager
{
   // starts a session
   public static function session_start()
   {
      $session_name = smconfig_get("session_name", "schedule_miner");
      session_name($session_name);
      session_start();

      // if we wanted to have custom handlelers, but php does everything we
      // need
      //session_set_save_handler("open", "close", "read", "gc");
   }

   public static function session_destroy()
   {
      session_destroy();
   }

   public static function session_regenerate_id()
   {
      session_regenerate_id();
   }
}

?>
