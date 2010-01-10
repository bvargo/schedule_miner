<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Copyright 2004-2008 The Intranet 2 Development Team
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

// logging

class Logging
{
   private $access_log;
   private $auth_log;
   private $debug_log;
   private $error_log;

   private $default_debug_level;
   private $debug_threshold;

   public function __construct()
   {
      global $SM_ERR;

      $log_dir = smconfig_get('log_dir');
      $access_filename = smconfig_get('access_log', 'access.log');
      $auth_filename = smconfig_get('auth_log', 'auth.log');
      $debug_filename = smconfig_get('debug_log', 'debug.log');
      $error_filename = smconfig_get('error_log', 'error.log');

      if(!$log_dir)
      {
         d("Logging directory not specified: all file logging is disabled", 1);
      }
      else
      {
         // append a / to the directory if it is not supplied
         if($log_dir[strlen($log_dir)-1] != "/")
            $log_dir .= "/";

         // create the logging directory if it does not exist
         makedir($log_dir);

         // make full paths for each string
         if($access_filename)
         {
            $access_filename = $log_dir.$access_filename;
            $this->access_log = fopen($access_filename, 'a');
         }
         if($auth_filename)
         {
            $auth_filename = $log_dir.$auth_filename;
            $this->auth_log = fopen($auth_filename, 'a');
         }
         if($debug_filename)
         {
            $debug_filename = $log_dir.$debug_filename;
            $this->debug_log = fopen($debug_filename, 'a');
         }
         if($error_filename)
         {
            $error_filename = $log_dir.$error_filename;
            $this->error_log = fopen($error_filename, 'a');
         }
      }

      
      $this->default_debug_level = smconfig_get("default_debug_level", 9);
      $this->debug_threshold = smconfig_get('debug_threshold', 0);

      // for when we write screen errors/debugging
      //register_shutdown_function(array($this, 'flush_debug_output'));
   }

   public function __destruct()
   {
      if($this->access_log)
         fclose($this->access_log);
      if($this->auth_log)
         fclose($this->auth_log);
      if($this->debug_log)
         fclose($this->debug_log);
      if($this->error_log)
         fclose($this->error_log);
   }

   // log every access
   // format: 'IP - username - Apache-style date format - "Request" - "Referrer" - "User-Agent"'
   public function log_access()
   {
      $message = "";
      $message .= $_SERVER["REMOTE_ADDR"];
      $message .= " - ";
      if(isset($_SESSION['username']))
         $message .= $_SESSION['username'];
      else
         $message .= "not_logged_in";
      $message .= " - ";
      $message .= date('d/M/Y:H:i:s O');
      if(isset($_SERVER['REQUEST_URI']))
      {
         $message .= " - ";
         $message .= $_SERVER['REQUEST_URI'];
      }
      if(isset($_SERVER['HTTP_REFERER']))
      {
         $message .= " - ";
         $message .= $_SERVER['HTTP_REFERER'];
      }
      if(isset($_SERVER['HTTP_USER_AGENT']))
      {
         $message .= " - ";
         $message .= $_SERVER['HTTP_USER_AGENT'];
      }
      $message .= "\n";
      
      if($this->access_log)
      {
         fwrite($this->access_log, $message);
         fflush($this->access_log);
      }
   }

   // log authentication
   public function log_auth($username, $success)
   {
      $message = "";
      $message .= $_SERVER['REMOTE_ADDR'];
      $message .=  ' - ';
      $message .= date('d/M/Y:H:i:s O');
      $message .=  ' - ';
      if($success)
         $message .= 'success';
      else
         $message .= 'FAILURE';
      $message .= ' - ';
      $message .= $username;
      $message .= "\n";

      if($this->auth_log)
      {
         fwrite($this->auth_log, $message);
         fflush($this->auth_log);
      }
   }

   // record an error
   // format: 'IP - Apache-style date format - [Mini-backtrace] - "Request" - "Error"'.
   public function log_error($error_message)
   {
      $trace_arr = array();
      foreach(array_slice(debug_backtrace(),1) as $trace)
      {
         if (isSet($trace['file']) && isSet($trace['line']))
         {
            $trace_arr[] = $trace['file'] .':'. $trace['line'];
         } 
         else if (isSet($trace['line']))
         {
            $trace_arr[] = 'Unknown file:'. $trace['line'];	
         } 
         else 
         {
            $trace_arr[] = 'Unknown file: Unknown line';
         }
      }

      $message =  $_SERVER['REMOTE_ADDR'];
      $message .= " - ";
      $message .= date('d/M/Y:H:i:s O');
      $message .= " - ";
      $message .= implode($trace_arr, ',');
      $message .= " - ";
      $message .= $_SERVER['REQUEST_URI'];
      $message .= " - ";
      $message .= $error_message;
      $message .= "\n";

      if($this->error_log)
      {
         fwrite($this->error_log, $message);
         fflush($this->error_log);
      }
   }

   // debug logging
   function log_debug($message, $level = NULL)
   {
      if ($level === NULL)
      {
         // if the level is not set, get the default debug level
         $level = $this->default_debug_level;
      }
      if ($level > $this->debug_threshold)
         return;

      if($this->debug_log)
      {
         fwrite($this->debug_log, $message."\n");
         fflush($this->debug_log);
      }
   }
}

?>
