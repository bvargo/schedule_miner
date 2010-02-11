<h3>Building Information:</h3>
<table>
   <tr>
      <td>Building:</td>
      <td>[<$building->name>] ([<$building->abbreviation>])</td>
   </tr>
</table>

<h3>Courses:</h3>
[<if isset($empty)>]
   This building does not have any courses in it.
[<else>]
   <table class="data">
      <thead>
         <th>Section</th>
         <th>CRN</th>
         <th>Name</th>
         <th>Instructor</th>
      </thead>
      [<foreach from=$courses|@sortby:"department->abbreviation,#course_number" item=course>]
         <tr>
            <td colspan="4" class="bold">
               <a href="[<$SM_ROOT>]/courses/display/[<$course->department->abbreviation>]/[<$course->course_number>]">[<$course->department->abbreviation>] [<$course->course_number>]</a>:
            </td>
         </tr>
         [<foreach from=$course->course_sections|@sortby:"section" item=course_section>]
            <tr>
               <td class="center"><a href="[<$SM_ROOT>]/courses/display/[<$course_section->crn>]">[<$course_section->section>]</a></td>
               <td><a href="[<$SM_ROOT>]/courses/display/[<$course_section->crn>]">[<$course_section->crn>]</a></td>
               <td>[<$course_section->name>]</td>
               <td><a href="[<$SM_ROOT>]/courses/display/instructor/[<$course_section->instructor->id>]">[<$course_section->instructor->name>]</a></td>
            </tr>
         [</foreach>]
      [</foreach>]
   </table>
[</if>]
