<h3>Departments</h3>
<ul>
   [<foreach from=$departments|@sortby:"name" item=department>]
   <li><a href="[<$SM_ROOT>]/courses/display/department/[<$department->abbreviation>]">[<$department->name>] ([<$department->abbreviation>])</a></li>
   [</foreach>]
</ul>

<h3>Instructors</h3>
<ul>
   [<foreach from=$instructors|@sortby:"name" item=instructor>]
      <li><a href="[<$SM_ROOT>]/courses/display/instructor/[<$instructor->id>]">[<$instructor->name>]</a></li>
   [</foreach>]
</ul>

<h3>Buildings</h3>
<ul>
   [<foreach from=$buildings|@sortby:"name" item=building>]
   <li><a href="[<$SM_ROOT>]/courses/display/building/[<$building->abbreviation>]">[<$building->name>] ([<$building->abbreviation>])</a></li>
   [</foreach>]
</ul>
