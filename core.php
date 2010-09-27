<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Copyright 2004-2008 The Intranet 2 Development Team
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

// This is the core kernel of the schedule miner

// misc functions.
require_once('functions.inc.php');

// sql and activerecord
require_once("adodb/adodb.inc.php");
require_once('adodb/adodb-active-record.inc.php');

// path to the main configuration file
define('CONFIG_FILENAME', 'config/config.ini');

// helpful global variables
$SM_SELF = $_SERVER['REDIRECT_URL'];
$SM_DOMAIN = $_SERVER['HTTP_HOST'];

// define globals for the URL root and the filesystem root
// used to strip off the current filename for paths
$url_parts = Explode('/', $_SERVER['SCRIPT_FILENAME']);
$len = strlen($url_parts[count($url_parts) - 1]);
// URL root (e.g. https://example.foo/bar)
$SM_ROOT = (isSet($_SERVER['HTTPS'])?'https://':'http://') . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'],0,-1 * $len - 1);
// URL for resources (e.g. https://example.foo/bar/www) - RR = resource root
$SM_RR = $SM_ROOT.'/www';
// filesystem root (e.g. /var/www/bar/)
$SM_FS_ROOT = substr($_SERVER['SCRIPT_FILENAME'],0,-1 * $len);
// clean up variables
unset($url_parts);
unset($len);

// set the timezone - PHP generates errors if this is not set in recent
// versions
if(version_compare(PHP_VERSION, '5.1.0', '>'))
{
   date_default_timezone_set(smconfig_get('timezone','America/New_York'));
}

// load basic modules, parse the query string, start the session, and transfer
// control to the module
try {

   // start the session
   SessionManager::session_start();

   // global array of arguments in the style of sysv
   // e.g. https://example.foo/module/arg1/arg2 will give
   // [0] => "module" [1] => "arg1" [2] => "arg2"
   $SM_ARGS = array();

   // global array of query arguments
   // e.g. https://example.foo/module/?a&b=c&d will give
   // ['a'] = TRUE, ['b'] = 'c', ['d'] = TRUE
   $SM_QUERY = array();

   // generate SM_ARGS and SM_QUERY - automatically eliminates excess /s
   if(isset($_SERVER['REDIRECT_QUERY_STRING']))
   {
      $index = strpos($_SERVER['REDIRECT_QUERY_STRING'], '?');
      $args = substr($_SERVER['REDIRECT_QUERY_STRING'], 0, $index);
      foreach(explode('/', $args) as $arg)
      {
         if(strlen($arg) != 0)
         {
            $SM_ARGS[] = $arg;
         }
      }
      $queries = substr($_SERVER['REDIRECT_QUERY_STRING'], $index+1);
      foreach(explode('&', $queries) as $query)
      {
         if ($query)
         {
            $element = explode('=', urldecode($query));

            // check to see if query is of the form query[]
            // if it is, then make SM_QUERY['query'] an array of the values
            // given
            if(strpos($element[0], "[]") == strlen($element[0]) - 2)
            {
               $array = 1;
               $element[0] = substr($element[0], 0, strlen($element[0]) - 2);
            }
            else
            {
               $array = 0;
            }

            // assign the values
            if(sizeof($element) > 1)
            {
               // key-value pair
               if($array)
               {
                  if(!isset($SM_QUERY[$element[0]]))
                     $SM_QUERY[$element[0]] = array();
                  $SM_QUERY[$element[0]][] = $element[1];
               }
               else
               {
                  $SM_QUERY[$element[0]] = $element[1];
               }
            }
            else
            {
               // just the key is given
               if($array)
               {
                  if(!isset($SM_QUERY[$element[0]]))
                     $SM_QUERY[$element[0]] = array();
                  $SM_QUERY[$element[0]][] = TRUE;
               }
               else
               {
                  $SM_QUERY[$element[0]] = TRUE;
               }
            }
         }
      }
   }

   // global error handler
   $SM_ERR = new Error();

   // global logging
   $SM_LOG = new Logging();

   // global sql
   // TODO: make this work for sqlite, etc
   $sql_type = smconfig_get('type', NULL, 'database');
   $sql_server = smconfig_get('server', NULL, 'database');
   $sql_username = smconfig_get('username', NULL, 'database');
   $sql_password = smconfig_get('password', NULL, 'database');
   $sql_database = smconfig_get('database', NULL, 'database');
   if(!$sql_type || !$sql_server || !$sql_username || !$sql_password || !$sql_database)
      error("Cannot read database settings.");

   // make all field names be lowercase
   $ADODB_ASSOC_CASE = 0;

   $SM_SQL = NewADOConnection($sql_type);
   // TODO: better handling of this
   if(!$SM_SQL->Connect($sql_server, $sql_username, $sql_password, $sql_database))
      error("Could not connect to database.");
   ADOdb_Active_Record::SetDatabaseAdapter($SM_SQL);
   unset($sql_type);
   unset($sql_server);
   unset($sql_username);
   unset($sql_password);
   unset($sql_database);

   // global user
   if(isset($_SESSION['username']))
   {
      $user = new user();
      if($user->load("username=?", array($_SESSION['username'])))
         $SM_USER = $user;
   }
   else
   {
      $SM_USER = null;
   }

   // log the access
   $SM_LOG->log_access();

   // check to make sure the user's session has not timed out
   // only logout if a user is logged in
   if(isset($_SESSION['last_access_time']) && isset($SM_USER))
   {
      if(time() - $_SESSION['last_access_time'] > smconfig_get('autologout_period', 1800, 'core'))
      {
         // have the user logout
         $SM_ARGS[0] = 'users';
         $SM_ARGS[1] = 'logout';
      }
   }
   $_SESSION['last_access_time'] = time();

   // pass control to the specified module
   // if no module is specified, default to the startpage

   // figure out the module
   $SM_MODULE = "";
   if(isSet($SM_ARGS[0]))
   {
      // TODO: better error message for someone trying to exploit this
      if(!safe_name($SM_ARGS[0]))
         error("Invalid module");
      $SM_MODULE = $SM_ARGS[0];
   }
   else
   {
      $SM_MODULE = smconfig_get('startmodule', 'home');
   }

   // figure out the action
   $SM_ACTION = "";
   if(count($SM_ARGS) < 2)
   {
      $SM_ACTION = "index";
   }
   else
   {
      $SM_ACTION = $SM_ARGS[1];
      // TODO: better error message for someone trying to exploit this
      if(!safe_name($SM_ARGS[0]))
      {
         warn("Unsafe URL: " . $_SERVER['REDIRECT_QUERY_STRING']);
         $SM_MODULE = "errorpage";
         $SM_ACTION = "error404";
      }
   }

   // if the user is not logged in, and this isn't the login page, the
   // registration page, or the homepage, send them to the homepage
   // FIXME: registration is turned on/off here
   if(!($SM_USER || $SM_MODULE == "home" || ($SM_MODULE == "schedules" && $SM_ACTION == "display") || ($SM_MODULE == "users" && ($SM_ACTION == "login" || $SM_ACTION == "create"))))
   //if(!($SM_USER || $SM_MODULE == "home" || ($SM_MODULE == "schedules" && $SM_ACTION == "display") || ($SM_MODULE == "users" && ($SM_ACTION == "login"))))
   {
      // access denied - show the homepage
      header("HTTP/1.0 401 Unauthorized");
      if(isset($_SERVER['HTTP_REFERER']))
         warn("401: " . $_SERVER['REDIRECT_QUERY_STRING'] . " from " . $_SERVER['HTTP_REFERER']);
      else
         warn("401: " . $_SERVER['REDIRECT_QUERY_STRING']);
      $SM_MODULE = smconfig_get('startmodule', 'home');
      $SM_ACTION = "index";
   }

   // instantiate the module
   if(class_exists($SM_MODULE))
   {
      $module = new $SM_MODULE();
   }
   else
   {
      $module = null;
   }

   // invoke the function, if it exists and is callable and is not an internal
   // function (begins with _)
   // if the action does not exist, call module->unknown_action() or throw an
   // error
   if($module instanceof Module && is_callable(array($module, $SM_ACTION)) && substr($SM_ACTION, 0, 1) != "_")
   {
      $module->$SM_ACTION();
   }
   else if($module instanceof Module && is_callable(array($module, "unknown_action")))
   {
      $module->unknown_action();
   }
   else
   {
      // 404 - page does not exist
      unset($module);
      if(isset($_SERVER['HTTP_REFERER']))
         warn("404: " . $_SERVER['REDIRECT_QUERY_STRING'] . " from " . $_SERVER['HTTP_REFERER']);
      else
         warn("404: " . $_SERVER['REDIRECT_QUERY_STRING']);
      $SM_MODULE = "errorpage";
      $SM_ACTION = "error404";
      $module = new $SM_MODULE();
      $module->$SM_ACTION();
   }

   /*$reflector = new ReflectionClass($module_name);
   $methods = $reflector->getMethods();
   foreach($methods as $method)
   {
      if($SM_ARGS[1] == $method->getName())
      {
         $method->invoke($module);
      }
   }*/


} // end of try block
catch (Exception $e)
{
   if(isset($SM_ERR))
   {
      $SM_ERR->default_exception_handler($e);
   }
   else {
      die('There is a critical error such that this application is unable to handle the error. Please contact the schedule master immediately. Error: '.$e->__toString());
   }
}

?>
