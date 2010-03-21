[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

[<*
Display a room in a building.
*>]

[<if isset($error)>]
   <h1>[<$error>]</h1>
[<else>]
   <h3>Room Information:</h3>
   <table>
      <tr>
         <td>Building:</td>
         <td><a href="[<$SM_ROOT>]/courses/display/building/[<$building->abbreviation>]">[<$building->name>] ([<$building->abbreviation>])</a></td>
      </tr>
      <tr>
         <td>Room number:</td>
         <td>[<$room>]</td>
      </tr>
   </table>

   <h3>Courses Sections:</h3>
   [<if isset($empty)>]
      This room does not have any course sections in it.
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

      <h3>Room Schedule:</h3>
      [<include file="_schedule_display.tpl">]
   [</if>]
[</if>]
