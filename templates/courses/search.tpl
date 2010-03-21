[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

[<*
Search interface
*>]

<form action="[<$SM_ROOT>]/courses/search" method="get">
   [<if isset($search_query)>]
      <input type="text" name="q" size="55" value="[<$search_query>]" />
   [<else>]
      <input type="text" name="q" size="55" />
   [</if>]
   <input type="submit" value="Search" />
</form>

[<if isset($error)>]
   <span class="red">Error: [<$error>]</span>
[<elseif isset($no_results)>]
      There are no results to display. Try looking at the <a href="[<$SM_ROOT>]/courses/search/help">search help</a>.
[<elseif isset($course_sections)>]
   <h3>Search Results:</h3>
   [<assign var=description value=0>]
   [<foreach from=$course_sections item=course_section>]
      [<if $course_section->description>]
         [<assign var=description value=1>]
      [</if>]
   [</foreach>]

   <table class="data">
      <thead>
         <th>Course</th>
         <th>Section</th>
         <th>CRN</th>
         <th>Name</th>
         <th>Instructor</th>
         [<if $description eq 1>]
            <th>Description</th>
         [</if>]
         <th>Add to active schedule</th>
      </thead>
      [<foreach from=$course_sections|@sortby:"-#weight,course->department->abbreviation,#course->course_number,section" item=course_section>]
         <tr>
            <td><a href="[<$SM_ROOT>]/courses/display/[<$course_section->course->department->abbreviation>]/[<$course_section->course->course_number>]">[<$course_section->course->department->abbreviation>] [<$course_section->course->course_number>]</a></td>
            <td class="center"><a href="[<$SM_ROOT>]/courses/display/[<$course_section->crn>]">[<$course_section->section>]</a></td>
            <td><a href="[<$SM_ROOT>]/courses/display/[<$course_section->crn>]">[<$course_section->crn>]</a></td>
            <td>[<$course_section->name>]</td>
            <td><a href="[<$SM_ROOT>]/courses/display/instructor/[<$course_section->instructor->id>]">[<$course_section->instructor->name>]</a></td>
            [<if $description eq 1>]
               <td>
                  [<if $course_section->description>]
                     [<$course_section->description>]
                  [<else>]
                     No Description.
                  [</if>]
               </td>
            [</if>]
            <td class="center">[<include file="_add_course_section.tpl">]</td>
         </tr>
      [</foreach>]
   </table>
[</if>]

