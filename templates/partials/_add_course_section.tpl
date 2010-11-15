[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

[<*
Display a button to add a course section to the schedule.
If the course ection is alrady in the schedule, display a message.

Arguments:
   $course_section - course section to add - mandatory
   $button_text - text to put in the button - optional
   $exists_text - text to display if course is already in schedule
*>]

[<if !($SM_USER->active_schedule_id eq null)>]
   [<if $SM_USER->schedule->contains_course_section($course_section)>]
      [<if isset($exists_text)>]
         [<$exists_text>]
      [<else>]
         Already in schedule
      [</if>]
   [<else>]
   <form action="[<$SM_ROOT>]/schedules/display/[<$SM_USER->active_schedule_id>]" method="post">
         <input type="hidden" name="add" value="[<$course_section->crn>]" />
         [<if isset($button_text)>]
            <input type="submit" value="[<$button_text>]" />
         [<else>]
            <input type="submit" value="Add Section" />
         [</if>]
      </form>
   [</if>]
[<else>]
   <a href="[<$SM_ROOT>]/schedules/create">Create a schedule</a>.
[</if>]
