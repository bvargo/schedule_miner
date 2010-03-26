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

   // make sure the module map is loaded
   if(!ModuleMapper::loaded())
      ModuleMapper::load();

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
      // DISABLED: errors thrown here cannot be caught in < PHP 5.3, so just
      // log the error
      //error('Cannot load module/class \''.$class_name.'\': the file \''.$class_file.'\' is not readable.');
      warn('Cannot load module/class \''.$class_name.'\': the file \''.$class_file.'\' is not readable.');
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
function smconfig_get($field, $default = NULL, $section = NULL, $recurse = false)
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
         if($recurse == false)
            d("Configuration variable $field in section $section is invalid", 1);
         return NULL;
      }
      else
      {
         if($recurse == false)
         {
            // original call specified the section, return the default
            d("Configuration variable $field in section $section not found, returning default $default", 2);
            return $default;
         }
         else
         {
            // not the original call, return NULL
            return NULL;
         }
      }
   }

   // a section was not specified - try to guess it from the backtrace

   $trace = debug_backtrace();

   // if called from a class, use that class as the sectionname
   if (isSet($trace[1]['class']))
   {
      $result = smconfig_get($field, $default, strtolower($trace[1]['class']), true);
      if($result != NULL)
      {
         return $result;
      }
   }

   // if the config value has not been found yet, try to use a value from the
   // core section - this will return the default if not found
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


// validate an email address
// given an email address, returns true if the emal address is in the correct
// format, the domain exists, and the domain has an MX record.
// Source: http://www.linuxjournal.com/article/9585
function valid_email($email)
{
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if(preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if(!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if(preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if
         (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
         str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless
         // local part is quoted
         if(!preg_match('/^"(\\\\"|[^"])+"$/',
            str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      if($isValid && !(checkdnsrr($domain,"MX") ||
         checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}


// generates a function that can be used for comparisions while sorting
// in order, compares by:
//    object->field()
//    object->field
//    object['field']
// if a field is not given, then the data is compared directly
// modifiers:
//    prefix '-' to do a reverse sort
//    prefix '#' to sort numerically / direct comparison
//    prefix '-#' to sort numerically / direct comparison in reverse
// example:
//    sortby("-name, #age") returns a function that first compares name in
//    reverse and, if those are equal, then compares by age numerically
function sortby($sortby)
{
   // caches generated functions
   static $sort_funcs = array();

   if(empty($sort_funcs[$sortby]))
   {
      $code = "\$compare = 0;";
      foreach(split(',', $sortby) as $key)
      {
         $direction = '1';
         $number = 0;
         if(substr($key, 0, 1) == '-')
         {
            $direction = '-1';
            $key = substr($key, 1);
         }
         if(substr($key, 0, 1) == '#')
         {
            $key = substr($key, 1);
            $number = 1;
         }
         if($key == "")
         {
            // assume a direct sort of data, since no fields were given
            $code .= "
            \$keya = \$a;
            \$keyb = \$b;
            ";
         }
         else
         {
            $code .= "
            if(is_numeric(\$a))
            {
               \$keya = \$a;
               \$keyb = \$b;
            }
            else if(method_exists(\$a, '$key') && method_exists(\$b, '$key'))
            {
               \$keya = \$a->$key();
               \$keyb = \$b->$key();
            }
            else if(isset(\$a->$key) && isset(\$b->$key))
            {
               \$keya = \$a->$key;
               \$keyb = \$b->$key;
            }
            else if(is_array(\$a) && is_array(\$b))
            {
               \$keya = \$a['$key'];
               \$keyb = \$b['$key'];
            }
            else
            {
               \$keya = 0;
               \$keyb = 0;
            }
            ";
         }
         if($number)
         {
            $code .= "if(\$keya > \$keyb) return $direction * 1;\n";
            $code .= "if(\$keya < \$keyb) return $direction * -1;\n";
         }
         else
         {
            $code .= "if ( (\$compare = strcasecmp(\$keya, \$keyb)) != 0 ) return $direction * \$compare;\n";
         }
      }
      $code .= 'return $compare;';
      $sort_func = $sort_funcs[$sortby] = create_function('$a, $b', $code);
   }
   else
   {
      $sort_func = $sort_funcs[$sortby];
   }
   return $sort_func;
}

?>
