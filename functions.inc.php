<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Copyright 2004-2008 The Intranet 2 Development Team
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

// Global utility functions

// A quick debugging wrapper for convenience
// level is the debugging level, 9 being the most detailed
function d($text, $level = 9)
{
   global $SM_LOG;
   if (isSet($SM_LOG))
   {
      $SM_LOG->log_debug($text, $level);
   }
}

// Another debugging wrapper - uses recursive print on an object
function d_r($obj, $level = 9)
{
   d(print_r($obj, 1), $level);
}

// Warning wrapper, for non-fatal errors
function warn($msg)
{
   global $SM_ERR;
   $SM_ERR->nonfatal_error($msg);
}

// Another warning wrapper - uses recursive print on an object
function warn_r($obj)
{
   warn(print_r($obj, 1));
}

// Error wrapper, if SM_ERR is defined, else display the error and die
function error($message)
{
   global $SM_ERR;
   if ($SM_ERR)
   {
      $SM_ERR->fatal_error($message);
   }
   else
   {
      echo("$message\n");
      die(1);
   }
}

// autoload function
// used by PHP to search for modules before throwing an error
function __autoload($class_name)
{
   // since the module map does not exist, we need to load the mapper manually
   require_once('modules/core/modulemapper.class.php');

   d("Loading $class_name");
   $class_file = '';

   if (!($class_file=get_sm_module($class_name)))
   {
      // the module cannot be loaded - try regenerating the module map and
      // reloading it
      ModuleMapper::load(1);
   }

   if (!($class_file=get_sm_module($class_name)))
   {
      // the module still cannot be loaded - throw an error
      error('Cannot load module/class \''.$class_name.'\': the file \''.$class_file.'\' is not readable.');
   }
   else
   {
      require_once($class_file);
   }
}

// determines what files holds the specified class
// this is used by __autoload to automatically load modules
// assumes that the module map has already been initialized
function get_sm_module($module_name)
{
   global $SM_MODULE_MAP;

   $key = strtolower($module_name);

   if (!isset($SM_MODULE_MAP[$key]))
   {
      return FALSE;
   }

   $file = $SM_MODULE_MAP[$key];
   if (is_readable($file))
   {
      return $file;
   }

   return FALSE;
}

// gets a configuration variable from the configuration file
// default is the value to be returned if the config entry does not exist
// section is the section in the config file to look - if not specified, the
// calling classname is used or core, depending on the calling location
function smconfig_get($field, $default = NULL, $section = NULL)
{
   static $config = NULL;

   // if the configuration file has not been loaded already, load it
   if ($config === NULL)
   {
      if (!is_readable(CONFIG_FILENAME))
      {
         error('The master configuration file cannot be read.');
      }
      $config = parse_ini_file(CONFIG_FILENAME, TRUE);
   }

   // a section is specified - only use that section
   if ($section != NULL)
   {
      if (isset($config[$section][$field]))
      {
         d("Configuration variable $field in section $section = $config[$section][$field]", 9);
         return $config[$section][$field];
      }
      if($default === NULL)
      {
         // configuration variable does not exist
         d("Configuration variable $field in section $section is invalid", 1);
         return NULL;
      }
      else
      {
         d("Configuration variable $field in section $section not found, returning default $default", 2);
         return $default;
      }
   }

   // a section was not specified - try to guess it from the backtrace

   $trace = debug_backtrace();

   // if called from a class, use that class as the sectionname
   if (isSet($trace[1]['class']))
   {
      $result = smconfig_get($field, $default, strtolower($trace[1]['class']));
      if($result != NULL)
      {
         return $result;
      }
   }

   // if the config value has not been found yet, try to use a value from the
   // core section
   return smconfig_get($field, $default, 'core');
}

// redirects the user to the specified page
// if the headers for the page have already been sent, then this function
// throws an error
function redirect($url = NULL, $absolute_path = 0)
{
   global $SM_ROOT;

   if( headers_sent($file, $line) )
   {
      throw new SMException('A redirect was attempted, but headers have already been sent in file '.$file.' on line '.$line);
   }

   if($absolute_path)
   {
      header('Location: '.$url);
   }
   else
   {
      $url = $SM_ROOT . '/' . $url;
      d('Redirecting to '.$url);

      header('Location: '.$url);
   }

   // stop any rendering that might be happening
   die();
}

// flatten an array (non-recrsive - one level only)
function flatten($arr)
{
   $ret = array();
   foreach($arr as $item)
   {
      if(is_array($item))
      {
         $ret = array_merge($ret,$item);
      }
      else
      {
         $ret[] = $item;
      }
   }
   return $ret;
}

// test if the string ends with the specified suffix
function ends_with($str, $suffix)
{
   return substr($str, strlen($str) - strlen($suffix)) == $suffix;
}

// test if the past string is a safe name for a function, module, etc
function safe_name($string)
{
   // allow alphanumeric characters in addition to the underscore and dash
   return preg_match('/^[a-z0-9_-]+$/i', $string);
}

// determines the most recent modification time of any file or directory in a
// directory tree
function dirmtime($dir)
{
   $time = filemtime($dir);

   $handle = opendir($dir);
   while (($name = readdir($handle)) !== FALSE)
   {
      if ($name != '.' && $name != '..')
      {
         $file = "$dir/$name";
         if(is_dir($file))
         {
            $time = max($time, dirmtime($file));
         }
         else
         {
            $time = max($time, filemtime($file));
         }
      }
   }
   closedir($handle);

   return $time;
}

// creates a temproary filename
// different than tempnam because it allows a suffix and does not actually
// create the file, only the name
function tempname($prefix, $suffix='')
{
   do
   {
      $mtime = microtime();
      srand((float)(substr($mtime, 1+strpos($mtime, ' '))));
      $file = $prefix . substr(md5(''.rand()),0,16) . $suffix;
   } while(file_exists($file));

   return $file;
}

// make a directory
// does not throw an error, like mkdir, if the directory already exists
function makedir($directory)
{
   if(!is_dir($directory))
      mkdir($directory, 0700, true);
}

?>
