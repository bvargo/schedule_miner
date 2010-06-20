[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]
[<*
Main page layout
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
      <script src="[<$SM_RR>]/js/jquery.js" type="text/javascript"></script>
      [<if isset($js)>]
         [<foreach from=$js item=js_file>]
            <script src="[<$js_file>]" type="text/javascript"></script>
         [</foreach>]
      [</if>]
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   </head>
   <body>
      <div id="page-background"></div>
      <div id="page-container">

         <div id="header">Schedule Miner</div>

         [<if isset($SM_USER)>]
            [<* display this menu if a user is logged in*>]
            <div class="menu-div">
               <ul class="menu menu-left">
                  <li><a href="[<$SM_ROOT>]">Home</a></li>
                  <li><a href="[<$SM_ROOT>]/courses">Browse</a></li>
                  <li><a href="[<$SM_ROOT>]/builder">Builder</a></li>
                  <li><a href="[<$SM_ROOT>]/courses/search" id="search_link">Search</a></li>
                  <li><a href="[<$SM_ROOT>]/suggestions">Suggestions</a></li>
               </ul>
               <ul class="menu menu-right">
                  <li><a href="[<$SM_ROOT>]/users/logout">Logout</a></li>
                  <li><a href="[<$SM_ROOT>]/users/edit">Preferences</a></li>
                  <li><a href="[<$SM_ROOT>]/schedules">Schedules</a></li>
                  [<if count($SM_USER->schedules)>]
                     <li><a href="[<$SM_ROOT>]/schedules/display">Active Schedule</a></li>
                  [</if>]
               </ul>
            </div>
            <div id="search_bar">
               <form action="[<$SM_ROOT>]/courses/search" method="get">
                  [<if isset($search_query)>]
                     <input type="text" name="q" id="search_field" size="35" value="[<$search_query>]" />
                  [<else>]
                     <input type="text" name="q" id="search_field" size="35" />
                  [</if>]
                  <input type="submit" value="Search" />
               </form>
            </div>
         [</if>]

         <div id="content">
            [<$CONTENT>]
         </div>

      </div>

      <div id="search_open_close" style="display:none"></div>
      <script type="text/javascript">
         $(document).ready(function()
         {
            //$("#search_bar").width($("#search_bar").width() + 105);
            $("#search_link").click(function(e)
            {
               e.preventDefault();
               if($("#search_open_close").is(":hidden"))
               {
                  $("#search_bar").animate({marginTop:"0px"}, 300);
                  $("#search_field").focus();
               }
               else
               {
                  $("#search_bar").animate({marginTop:"-33px"}, 300);
               }
               $("#search_open_close").toggle();
            });

            $("#search_bar").mouseup(function()
            {
               return false;
            });

            $(document).mouseup(function(e)
            {
               if($(e.target).attr("id") != "search_link")
               {
                  //$("#search_bar").hide();
                  if($("#search_open_close").is(":visible"))
                  {
                     $("#search_bar").animate({marginTop:"-33px"}, 500);
                     $("#search_open_close").hide();
                  }
               }
            });
         });
      </script>
      <script type="text/javascript" src="/awstatsjs/awstats_misc_tracker.js"></script>
      <noscript><img src="/awstatsjs/awstats_misc_tracker.js?nojs=y" height=0 width=0 border=0 style="display: none"></noscript>
   </body>
</html>

