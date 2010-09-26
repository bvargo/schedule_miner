[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

[<*
Displays a schedule, with options to edit the schedule if it belongs to the
current user.
*>]

[<if isset($error)>]
   <h1>[<$error>]</h1>
[<else>]

   [<if isset($SM_USER) && $schedule->user->id eq $SM_USER->id>]
      [<if $SM_USER->active_schedule_id eq $schedule->id>]
         <h1>Your Schedule - [<$schedule->name>] (active schedule)</h1>
      [<else>]
         <h1>Your Schedule - [<$schedule->name>]</h1>
      [</if>]
      <form action="[<$SM_ROOT>]/schedules/display/[<$schedule->id>]" method="post">
         <span class="bold">Schedule Name:</span>
         <input type="text" name="name" value="[<$schedule->name>]" />
         <input type="submit" value="Save Name" />
      </form>
      <form action="[<$SM_ROOT>]/schedules/display/[<$schedule->id>]" method="post">
         <span class="bold">Course Sections:</span>
         <input type="text" name="sections" value="[<$course_sections_string>]" size="40" />
         <input type="submit" value="Save Sections" />
      </form>
      <form action="[<$SM_ROOT>]/builder" method="get">
         <input type="hidden" name="from_schedule" value="[<$schedule->id>]" />
         <input type="submit" value="Use in Builder" />
      </form>
      <br />
   [<else>]
      <h1>[<$schedule->user->name>]&#8217;s Schedule - [<$schedule->name>]</h1>
   [</if>]

   [<if count($schedule->course_sections())>]
      [<if $schedule->public >]
         <span class="bold">People sharing at least one class:</span>
         [<foreach from=$schedule->users_sharing_class() item=shared_user name=shared_user>]
            [<if $shared_user.name>]
               <a href="[<$SM_ROOT>]/schedules/display/[<$shared_user.schedule_id>]">[<$shared_user.name>]</a>[<if !$smarty.foreach.shared_user.last>],[</if>]
            [<else>]
               <a href="[<$SM_ROOT>]/schedules/display/[<$shared_user.schedule_id>]">[<$shared_user.username>]</a>[<if !$smarty.foreach.shared_user.last>],[</if>]
            [</if>]
         [</foreach>]
         <br />
      [</if>]
      <br />
      [<include file="_schedule_display.tpl">]
   [<else>]
      <b>The current schedule does not have any course sections. Try <a href="[<$SM_ROOT>]/courses">browsing</a> or <a href="[<$SM_ROOT>]/courses/search">searching</a> for courses. Or, try the <a href="[<$SM_ROOT>]/builder">schedule builder</a>.</b>
   [</if>]

[</if>]
