<?php

class SessionManager
{
   // starts a session
   public static function session_start()
   {
      session_start();

      // if we wanted to have custom handlelers, but php does everything we 
      // need
      //session_set_save_handler("open", "close", "read", "gc");
   }

   public static function session_destroy()
   {
      session_destroy();
   }
}

?>
