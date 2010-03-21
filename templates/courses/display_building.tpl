[<if isset($error)>]
   <h1>[<$error>]</h1>
[<else>]
   <h3>Building Information:</h3>
   <table>
      <tr>
         <td>Building:</td>
         <td>[<$building->name>] ([<$building->abbreviation>])</td>
      </tr>
   </table>

   [<assign var="rooms" value=$building->rooms()>]
   [<if !empty($rooms)>]
      <h3>Rooms:</h3>
      [<foreach from=$rooms|@sortby:"#" item=room>]
         <a href="[<$SM_ROOT>]/courses/display/building/[<$building->abbreviation>]/[<$room>]">[<$room>]</a>
      [</foreach>]
   [</if>]

   <h3>Courses sections:</h3>
   [<if isset($empty)>]
      This building does not have any course sections in it.
   [<else>]
      <table class="data">
         <thead>
            <th>Course</th>
            <th>Section</th>
            <th>CRN</th>
            <th>Name</th>
            <th>Instructor</th>
            <th>Add to active schedule</th>
         </thead>
         [<foreach from=$course_sections|@sortby:"course->department->abbreviation,#course->course_number,section" item=course_section>]
            <tr>
               <td><a href="[<$SM_ROOT>]/courses/display/[<$course_section->course->department->abbreviation>]/[<$course_section->course->course_number>]">[<$course_section->course->department->abbreviation>] [<$course_section->course->course_number>]</a></td>
               <td class="center"><a href="[<$SM_ROOT>]/courses/display/[<$course_section->crn>]">[<$course_section->section>]</a></td>
               <td><a href="[<$SM_ROOT>]/courses/display/[<$course_section->crn>]">[<$course_section->crn>]</a></td>
               <td>[<$course_section->name>]</td>
               <td><a href="[<$SM_ROOT>]/courses/display/instructor/[<$course_section->instructor->id>]">[<$course_section->instructor->name>]</a></td>
               <td class="center">[<include file="_add_course_section.tpl">]</td>
            </tr>
         [</foreach>]
      </table>
   [</if>]
[</if>]
