<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Copyright 2004-2008 The Intranet 2 Development Team
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

// generate the module map

class ModuleMapper
{

   static $map = array();

   static $loaded = false;

   public static function loaded()
   {
      return ModuleMapper::$loaded;
   }

   private static function process_dir($dir)
   {
      if ($handle = opendir($dir))
      {
         while (FALSE !== ($file = readdir($handle)))
         {
            if ($file == '.' || $file == '..')
            {
               continue;
            }

            if (is_dir($dir . $file))
            {
               if (!self::process_dir("$dir$file/"))
               {
                  return FALSE;
               }
            }
            else if (ends_with($file, '.php'))
            {
               $arr = preg_split("/\./", $file);
               self::$map[strtolower($arr[0])] = "$dir$file";
            }
            else
            {
               d("Ignoring $file");
            }
         }
      }
      else
      {
         return FALSE;
      }
      return TRUE;
   }

   private static function generate()
   {
      global $SM_FS_ROOT;
      $module_path = $SM_FS_ROOT . 'modules/';
      $cache_dir = smconfig_get('cache_dir');
      makedir($cache_dir);
      $map_file = $cache_dir . 'module.map';

      // generate the map hash
      if (!self::process_dir($module_path))
         error("Could not process modules directory $module_path");

      // try to delete the old map file if it exists
      if (file_exists($map_file) && !unlink($map_file))
         error("Could not delete $map_file");

      // write the map out to the file
      if (!file_put_contents($map_file, serialize(self::$map)))
         error("Could not write contents to $map_file");
   }

   // loads the module map
   public static function load($generate=false)
   {
      global $SM_MODULE_MAP;

      $cache_dir = smconfig_get('cache_dir');
      makedir($cache_dir);
      $filename = $cache_dir . 'module.map';

      if (!file_exists($filename) || $generate)
      {
         // the module map does not exist, or the user requests it to be
         // regenerated, generate it
         d('Generating module map at ' . $filename , 4);

         // generate the module map
         ModuleMapper::generate();
      }

      $contents = file_get_contents($filename);
      if ($contents === FALSE)
      {
         error('Could not load module map: could not read file ' . $filename);
      }

      $SM_MODULE_MAP = unserialize($contents);
      if ($SM_MODULE_MAP === FALSE)
      {
         error('Could not load module map: could not unserialize contents of file ' . $filename);
      }

      ModuleMapper::$loaded = 1;

      d('Module map successfully loaded', 8);
   }

}
?>
