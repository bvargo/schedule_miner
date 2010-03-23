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
[<elseif isset($display_help)>]
   <h1>Search Help:</h1>
   <span>The search system uses a boolean query system that makes searching both easy and powerful.</span>
   <dl>
      <dt>Operator AND (&)</dt>
      <dd>Example: "csci & 261"</dd>
      <dd>Returns results matching "csci" and "261".</dd>
      <dd>Note: The AND operator is implicit, so "csci 261" is the same as "csci & 261".</dd>

      <dt>Operator OR (|)</dt>
      <dd>Example: "csci | 261"</dd>
      <dd>Returns results matching "csci" or "261".</dd>

      <dt>Operator NOT (! or -)</dt>
      <dd>Example: "csci -261" or "csci !261"</dd>
      <dd>Returns results matching "csci" but not "261". Note the use of implicit AND.</dd>

      <dt>Grouping</dt>
      <dd>Group multiple terms with parenthesis.</dd>
      <dd>Example: "(csci 261) | (csci 262)"</dd>

      <dt>Phrases</dt>
      <dd>Surround a phrase with quotations. It will then be considered just like any single word.</dd>
      <dd>Example: "Programming Concepts"</dd>

      <dt>Specifying Fields</dt>
      <dd>Fields can be specified using the @ symbol.</dd>
      <dd>Example: "@department_name: Mathematics @credit_hours: 3"</dd>
      <dd>Returns courses from the Mathematics department with three credit hours.</dd>
      <dd>List of fields:
         <ul>
            <li>crn</li>
            <li>section</li>
            <li>name</li>
            <li>description</li>
            <li>course_number</li>
            <li>credit_hours</li>
            <li>instructor_name</li>
            <li>department_abbreviation</li>
            <li>department_name</li>
         </ul>
      </dd>
   </dl>
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

