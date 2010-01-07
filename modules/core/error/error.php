<?php

// error handling

class Error
{
   // constructor
   function __construct()
   {
      // make this class the default PHP error handler
      set_error_handler(array(&$this,'default_error_handler'));
      set_exception_handler(array(&$this,'default_exception_handler'));

      // enable strict errors
      error_reporting(E_ALL | E_STRICT);
   }

   // default error handler, used by PHP's trigger_error()
   function default_error_handler($errno, $errstr, $errfile, $errline)
   {
      global $SM_ROOT;

      // ignore HTML parser errors
      if(strpos($errstr, "htmlParseEntityRef"))
         return;

      // ignore ADODB declaration errors
      if(strpos($errstr, "should be compatible with that of ADOConnection"))
         return;

      switch($errno)
      {
         case E_WARNING:
         case E_NOTICE:
         case E_STRICT:
            $this->nonfatal_error("Warning: $errstr\r\nError number: $errno\r\nFile: $errfile#$errline");
            break;
         default:
            $this->fatal_error("Error: $errstr\r\nError number: $errno\r\nFile: $errfile\r\nLine: $errline");
      }
   }

   // failsafe for unhandled exceptions
   function default_exception_handler(Exception $e)
   {
      $this->fatal_error(''.$e->__toString(), FALSE);
   }

   // fatal errors - immediately stops processing of the entire application
   function fatal_error($message)
   {
      global $SM_LOG;

      $out = 'Fatal error: '.$message;
      $out .= "\r\n".'If this problem persists, please contact the administrator.';

      if (!isset($SM_LOG))
      {
         print($out."\n");
         die();
      }

      //TODO: make this go somewhere in the template with buffering instead of 
      //printing
      print($out."\n");
      $SM_LOG->log_error($out);

      die();
   }


   // nonfatal errors - may be fatal for a particular module, but should not 
   // stop processing of the entire application
   function nonfatal_error($msg)
   {
      global $SM_LOG;

      if(isset($SM_LOG))
      {
         $SM_LOG->log_error($msg);
      }
   }

}

?>
