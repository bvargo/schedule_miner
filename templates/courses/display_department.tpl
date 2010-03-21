<h3>Department Information:</h3>
<table>
   <tr>
      <td>Department:</td>
      <td>[<$department->name>]</td>
   </tr>
</table>

<h3>Courses:</h3>
[<if isset($empty)>]
   This department does not have any courses.
[<else>]
   <table class="data">
      <thead>
         <th>Section</th>
         <th>CRN</th>
         <th>Name</th>
         <th>Instructor</th>
         <th>Add to active schedule</th>
      </thead>
      [<foreach from=$department->courses|@sortby:"#course_number" item=course>]
         <tr>
            <td colspan="5" class="bold">
               <a href="[<$SM_ROOT>]/courses/display/[<$course->department->abbreviation>]/[<$course->course_number>]">[<$course->department->abbreviation>] [<$course->course_number>]</a>:
            </td>
         </tr>
         [<foreach from=$course->course_sections|@sortby:"section" item=course_section>]
            <tr>
               <td class="center"><a href="[<$SM_ROOT>]/courses/display/[<$course_section->crn>]">[<$course_section->section>]</a></td>
               <td><a href="[<$SM_ROOT>]/courses/display/[<$course_section->crn>]">[<$course_section->crn>]</a></td>
               <td>[<$course_section->name>]</td>
               <td><a href="[<$SM_ROOT>]/courses/display/instructor/[<$course_section->instructor->id>]">[<$course_section->instructor->name>]</a></td>
               <td class="center">[<include file="_add_course_section.tpl">]</td>
            </tr>
         [</foreach>]
      [</foreach>]
   </table>
[</if>]
