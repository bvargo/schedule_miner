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
define('CONFIG_FILENAME', 'config.ini');

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
            if (sizeof($element) > 1)
            {
               $SM_QUERY[$element[0]] = $element[1];
            }
            else
            {
               $SM_QUERY[$element[0]] = TRUE;
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
      $results = $user->Find("username=?", array($_SESSION['username']));
      if(count($results))
         $SM_USER = $results[0];
   }
   else
   {
      $SM_USER = null;
   }

   // log the access
   $SM_LOG->log_access();

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
      // TODO: better error message for someone trying to exploit this
      if(!safe_name($SM_ARGS[0]))
         error("Invalid module");
      $SM_ACTION = $SM_ARGS[1];
   }

   // if the user is not logged in, and this isn't the login page, the 
   // registration page, or the homepage, send them to the homepage
   // FIXME: registration is turned off here - uncomment the next line and 
   // comment the second if statement to enable registration
   //if(!($SM_USER || $SM_MODULE == "home" || ($SM_MODULE == "schedules" && $SM_ACTION == "display") || ($SM_MODULE == "users" && ($SM_ACTION == "login" || $SM_ACTION == "create"))))
   if(!($SM_USER || $SM_MODULE == "home" || ($SM_MODULE == "schedules" && $SM_ACTION == "display") || ($SM_MODULE == "users" && ($SM_ACTION == "login"))))
   {
      // show the homepage
      $SM_MODULE = $SM_MODULE = smconfig_get('startmodule', 'home');
      $SM_ACTION = "index";
   }

   // instantiate the module
   $module = new $SM_MODULE();
   if(!$module instanceof Module)
   {
      // module does not exist, or is not complete
      error("Error loading module");
   }

   // invoke the function, if it exists
   // if the action does not exist, call module->no_action() or throw an
   // error
   if(method_exists($module, $SM_ACTION))
   {
      $module->$SM_ACTION();
   }
   else if(method_exists($module, "no_action"))
   {
      $module->unknown_action();
   }
   else
   {
      error("Invalid action");
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
