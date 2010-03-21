[<if isset($error)>]
   <h1>[<$error>]</h1>
[<else>]
   <h3>Section Information:</h3>
   <table>
      <tr>
         <td>Name:</td>
         <td>[<$course_section->name>]</td>
      </tr>
      <tr>
         <td>Instructor:</td>
         <td><a href="[<$SM_ROOT>]/courses/display/instructor/[<$course_section->instructor->id>]">[<$course_section->instructor->name>]</a></td>
      </tr>
      <tr>
         <td>CRN:</td>
         <td>[<$course_section->crn>]</td>
      </tr>
      <tr>
         <td>Course:</td>
         <td><a href="[<$SM_ROOT>]/courses/display/[<$course->department->abbreviation>]/[<$course->course_number>]">[<$course->department->abbreviation>] [<$course->course_number>]</a></td>
      </tr>
      [<if $course_section->description>]
         <tr>
            <td>[<$course_section->description>]</td>
         </tr>
      [</if>]
      <tr>
         [<assign var="button_text" value="Add Section to Schedule">]
         [<assign var="exists_text" value="Section is present in active schedule">]
         <td colspan="2">[<include file="_add_course_section.tpl">]</td>
      </tr>
   </table>

   <h3>Class Meeting Times:</h3>
   <table class="data">
      <thead>
         <th>Building</th>
         <th>Room</th>
         <th>Day</th>
         <th>Start Time</th>
         <th>End Time</th>
      </thead>
      [<foreach from=$course_section->class_periods item=class_period>]
      <tr>
         <td><a href="[<$SM_ROOT>]/courses/display/building/[<$class_period->building->abbreviation>]">[<$class_period->building->name>] ([<$class_period->building->abbreviation>])</a></td>
         [<if $class_period->room_number eq -1>]
            <td>N/A</td>
         [<else>]
            <td><a href="[<$SM_ROOT>]/courses/display/building/[<$class_period->building->abbreviation>]/[<$class_period->room_number>]">[<$class_period->room_number>]</a></td>
         [</if>]
         <td>[<$class_period->day>]</td>
         <td>[<$class_period->start_time>]</td>
         <td>[<$class_period->end_time>]</td>
      </tr>
      [</foreach>]
   </table>

   <h3>Section Schedule:</h3>
   [<include file="_schedule_display.tpl">]
[</if>]
