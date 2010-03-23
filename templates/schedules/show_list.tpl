[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

[<*
Displays a list of schedules
*>]

[<if count($SM_USER->schedules) eq 0>]
   <h1>You do not have any schedules yet!</h1>
   <h3>Please <a href="[<$SM_ROOT>]/schedules/create">create a schedule</a>.</h3>
[<else>]
   <h1>Saved Schedules</h1>
   <form action="[<$SM_ROOT>]/schedules/show_list" method="post">
      <table class="schedule_list data">
         <thead>
            <th>Name</th>
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
                     [<foreach from=$course_sections|@sortby:"name,#crn" item=course_section>]
                        <a href="[<$SM_ROOT>]/courses/display/[<$course_section->course->department->abbreviation>]/[<$course_section->course->course_number>]">[<$course_section->name>]</a> - <a href="[<$SM_ROOT>]/courses/display/[<$course_section->crn>]">[<$course_section->crn>]</a><br />
                     [</foreach>]
                  </td>
                  [<assign var="credit_hours" value=$schedule->credit_hours()>]
                  [<assign var="credit_hours_unique" value=$schedule->credit_hours_unique()>]
                  [<if $credit_hours eq $credit_hours_unique>]
                     <td class="center">[<$credit_hours>]</td>
                  [<else>]
                     <td class="center">[<$credit_hours>] ([<$credit_hours_unique>])</td>
                  [</if>]
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
