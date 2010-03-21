[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
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
      <br />
   [<else>]
      <h1>[<$schedule->user->name>]&#8217;s Schedule - [<$schedule->name>]</h1>
      <br />
   [</if>]

   [<if count($schedule->course_sections())>]
      [<include file="_schedule_display.tpl">]
   [<else>]
      The current schedule does not have any course sections.
   [</if>]

[</if>]
