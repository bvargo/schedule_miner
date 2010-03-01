<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

// sorts an array of objects by a field
// in order, tries sorting by:
//    object->field()
//    object->field
//    object['field']
// if a field is not given, then the data is sorted directly
// usage (in smarty template):
//    {foreach item=item from=$arr|@sortby:"-name, #age"}
// modifiers:
//    prefix '-' to do a reverse sort
//    prefix '#' to sort numerically / direct comparison
//    prefix '-#' to sort numerically / direct comparison in reverse

// smarty modifier: sortby
// allows arrays of named arrays, objects with functions, or objects with
// fields to be sorted by a given field or fields
function smarty_modifier_sortby($arr_data, $sortfields)
{
   uasort($arr_data, sortby($sortfields));
   return $arr_data;
}

// uncomment this to use the modifier directly, instead of including this file
// in a plugin directory
//$smarty->register_modifier("sortby", "smarty_modifier_sortby" );
?>
