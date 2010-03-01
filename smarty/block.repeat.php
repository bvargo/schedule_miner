<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

// repeats a template block multiple times
// note that the contents of the block are only parsed once
//
// parameters:
// count (required) - the number of times to repeat the block
// assign (optional) - the variable to which the results should be assigned,
//    instead of printing the results to the page
function smarty_block_repeat($parameters, $content, &$smarty)
{
   if(!empty($content))
   {
      $count = intval($parameters['count']);
      $str = str_repeat($content, $count);
      if(!empty($parameters['assign']))
         $smarty->assign($parameters['assign'], $str);
      else
         echo $str;
   }
}
?>
