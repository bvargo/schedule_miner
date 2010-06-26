[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

[<*
Display a department.
*>]

[<if isset($error)>]
   <h1>[<$error>]</h1>
[<else>]
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
            <th>Course</th>
            <th># Sections</th>
            <th>Name</th>
            <th>Instructor(s)</th>
         </thead>
         [<foreach from=$department->courses|@sortby:"#course_number" item=course>]
            <tr>
               <td class="bold">
                  <a href="[<$SM_ROOT>]/courses/display/[<$course->department->abbreviation>]/[<$course->course_number>]">[<$course->department->abbreviation>] [<$course->course_number>]</a>
               </td>
               <td class="center">[<$course->course_sections|@count>]</td>
               <td>
                  [<if $course->name()>]
                     [<$course->name()>]
                  [<else>]
                     <i>(Various)</i>
                  [</if>]
               </td>
               <td>
                  [<foreach from=$course->instructors()|@sortby:"name" item=instructor name=instructor>]
                     <a href="[<$SM_ROOT>]/courses/display/instructor/[<$instructor->id>]">[<$instructor->name>]</a>[<if !$smarty.foreach.instructor.last>],[</if>]
                  [</foreach>]
               </td>
            </tr>
         [</foreach>]
      </table>
   [</if>]
[</if>]
