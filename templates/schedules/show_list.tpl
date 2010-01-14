[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

[<if count($SM_USER->schedules) eq 0>]
   <h1>You do not have any schedules yet!</h1>
   <h3>Please <a href="[<$SM_ROOT>]/schedules/create">create a schedule</a>.</h3>
[<else>]
   <form action="[<$SM_ROOT>]/schedules/show_list" method="post">
      <table class="schedule_list">
         <thead>
            <th>Schedule</th>
            <th>Course Sections</th>
            <th>Credit Hours</th>
            <th>Publically Visible?</th>
            [<if count($SM_USER->schedules) gt 1>]
               <th>Active Schedule</th>
            [</if>]
            <th>Delete Schedule</th>
         </thead>
         <tbody>
            [<foreach from=$SM_USER->schedules|@sortby:"name" item=schedule>]
               <tr>
                  <td class="center bold"><a href="[<$SM_ROOT>]/schedules/display/[<$schedule->id>]">[<$schedule->name>]</a></td>
                  <td>
                     [<assign var=course_sections value=$schedule->course_sections()>]
                     [<foreach from=$course_sections|@sortby:"name,#crn" item=section>]
                        [<$section->name>] ([<$section->crn>])<br />
                     [</foreach>]
                  </td>
                  <td class="center">[<$schedule->credit_hours()>]</td>
                  [<if $schedule->public eq 1>]
                     <td class="center"><input type="checkbox" name="public[<$schedule->id>]" checked="checked" /></td>
                  [<else>]
                     <td class="center"><input type="checkbox" name="public[<$schedule->id>]" /></td>
                  [</if>]
                  [<if count($SM_USER->schedules) gt 1>]
                     [<if $schedule->id eq $SM_USER->active_schedule_id>]
                        <td class="center"><input type="radio" name="active_schedule" value="[<$schedule->id>]" checked="checked"/></td>
                     [<else>]
                        <td class="center"><input type="radio" name="active_schedule" value="[<$schedule->id>]" /></td>
                     [</if>]
                  [</if>]
                  <td class="center"><input type="submit" name="delete[<$schedule->id>]" value="Delete" /></td>
               </tr>
            [</foreach>]
         </tbody>
      </table>
      <div class="center"><input type="submit" name="save" value="Save preferences" /></div>
   </form>
   <br />
   <div class="center"><a href="[<$SM_ROOT>]/schedules/create">Create a new schedule</a></div>
[</if>]
