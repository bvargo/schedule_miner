[<*
Arguments:
   $course_section - course section to add - mandatory
   $button_text - text to put in the button - optional
   $exists_text - text to display if course is already in schedule
*>]

[<if $SM_USER->schedule->contains_course_section($course_section)>]
   [<if isset($exists_text)>]
      [<$exists_text>]
   [<else>]
      Already in schedule
   [</if>]
[<else>]
   <form action="[<$SM_ROOT>]/schedules/display" method="post">
      <input type="hidden" name="add" value="[<$course_section->crn>]" />
      [<if isset($button_text)>]
         <input type="submit" value="[<$button_text>]" />
      [<else>]
         <input type="submit" value="Add Section" />
      [</if>]
   </form>
[</if>]
