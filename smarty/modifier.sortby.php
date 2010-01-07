<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

// sorts an array of objects by a field
// in order, tries sorting by:
//    object->field()
//    object->field
//    object['field']
// usage (in smarty template):
//    {foreach item=item from=$arr|@sortby:"-name, #age"}
// modifiers:
//    prefix '-' to do a reverse sort
//    prefix '#' to sort numerically
//    prefix '-#' to sort numerically in reverse
//

function array_sort_by(&$data, $sortby)
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
         $code .= "
         if(method_exists(\$a, '$key'))
         {
            \$keya = \$a->$key();
            \$keyb = \$b->$key();
         }
         else if(isset(\$a->$key))
         {
            \$keya = \$a->$key;
            \$keyb = \$b->$key;
         }
         else
         {
            \$keya = \$a['$key'];
            \$keyb = \$b['$key'];
         }";
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
   uasort($data, $sort_func);
}

// smarty modifier: sortby
// allows arrays of named arrays, objects with functions, or objects with
// fields to be sorted by a given field or fields
function smarty_modifier_sortby($arr_data, $sortfields)
{
   array_sort_by($arr_data, $sortfields);
   return $arr_data;
}

// uncomment this to use the modifier directly, instead of including this file
// in a plugin directory
//$smarty->register_modifier("sortby", "smarty_modifier_sortby" );
?>
