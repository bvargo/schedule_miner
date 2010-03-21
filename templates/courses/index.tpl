[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

[<*
Display the homepage. Show departments, buildings, and instructors.
*>]

<h3>Departments:</h3>
[<assign var="columns" value=$department_columns>]
[<assign var="current_column" value="0">]
<table class="browse_list">
   [<foreach from=$departments|@sortby:"name" item=department>]
      [<if $current_column eq 0>]
         <tr>
      [</if>]
      <td><a href="[<$SM_ROOT>]/courses/display/department/[<$department->abbreviation>]">[<$department->name>] ([<$department->abbreviation>])</a></td>
      [<if $current_column % $columns eq $columns-1>]
         </tr>
      [</if>]
      [<math assign="current_column" equation="(cc + 1) % c" cc=$current_column c=$columns>]
   [</foreach>]
   [<math assign="finish_td" equation="x - y" x=$columns y=$current_column>]
   [<if $current_column neq 0 and $current_column neq $columns>]
      [<repeat count=$finish_td>]
         <td></td>
      [</repeat>]
      </tr>
   [</if>]
</table>

<h3>Buildings:</h3>
[<assign var="columns" value=$building_columns>]
[<assign var="current_column" value="0">]
<table class="browse_list">
   [<foreach from=$buildings|@sortby:"name" item=building>]
      [<if $current_column eq 0>]
         <tr>
      [</if>]
      <td><a href="[<$SM_ROOT>]/courses/display/building/[<$building->abbreviation>]">[<$building->name>] ([<$building->abbreviation>])</a></td>
      [<if $current_column % $columns eq $columns-1>]
         </tr>
      [</if>]
      [<math assign="current_column" equation="(cc + 1) % c" cc=$current_column c=$columns>]
   [</foreach>]
   [<math assign="finish_td" equation="x - y" x=$columns y=$current_column>]
   [<if $current_column neq 0 and $current_column neq $columns>]
      [<repeat count=$finish_td>]
         <td></td>
      [</repeat>]
      </tr>
   [</if>]
</table>

<h3>Instructors:</h3>
[<assign var="columns" value=$instructor_columns>]
[<assign var="current_column" value="0">]
<table class="browse_list">
   [<foreach from=$instructors|@sortby:"name" item=instructor>]
      [<if $current_column eq 0>]
         <tr>
      [</if>]
      <td><a href="[<$SM_ROOT>]/courses/display/instructor/[<$instructor->id>]">[<$instructor->name>]</a></td>
      [<if $current_column % $columns eq $columns-1>]
         </tr>
      [</if>]
      [<math assign="current_column" equation="(cc + 1) % c" cc=$current_column c=$columns>]
   [</foreach>]
   [<math assign="finish_td" equation="x - y" x=$columns y=$current_column>]
   [<if $current_column neq 0 and $current_column neq $columns>]
      [<repeat count=$finish_td>]
         <td></td>
      [</repeat>]
      </tr>
   [</if>]
</table>

