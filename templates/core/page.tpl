[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
         "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
      <title>[<$title>]</title>
      <link rel="stylesheet" href="[<$SM_RR>]/page.css" />
      [<if isset($css)>]
         [<foreach from=$css item=css_sheet>]
            <link rel="stylesheet" href="[<$css_sheet>]" />
         [</foreach>]
      [</if>]
      <meta http-equiv="Content-TYpe" content="text/html; charset=utf-8" />
   </head>
   <body>
      <div id="page-background"></div>
      <div id="page-container">

         <div id="header">Schedule Miner</div>

         [<if isset($SM_USER)>]
            [<* display this menu if a user is logged in*>]
            <div id="menu-div">
               <ul class="menu menu-left">
                  <li><a href="[<$SM_ROOT>]">Home</a></li>
                  <li><a href="[<$SM_ROOT>]/courses">Browse Courses</a></li>
                  [<*<li><a href="[<$SM_ROOT>]/builder">Automated Scheduler</a></li>*>]
                  <li><a href="[<$SM_ROOT>]/schedules">Saved Schedules</a></li>
               </ul>
               <ul class="menu menu-right">
                  <li><a href="[<$SM_ROOT>]/users/logout">Logout</a></li>
                  <li><a href="[<$SM_ROOT>]/users/edit">Preferences</a></li>
               </ul>
            </div>
         [</if>]

         <div id="content">
            [<$CONTENT>]
         </div>

      </div>
   </body>
</html>

