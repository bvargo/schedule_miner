[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

[<*
Time search interface
*>]

<form action="[<$SM_ROOT>]/courses/time_search" method="get">
   <label>Days:</label>
   [<if isset($monday)>]
      <input type="checkbox" name="monday" id="monday" checked="1"><label for="monday">Monday</label>
   [<else>]
      <input type="checkbox" name="monday" id="monday"><label for="monday">Monday</label>
   [</if>]

   [<if isset($tuesday)>]
      <input type="checkbox" name="tuesday" id="tuesday" checked="1"><label for="tuesday">Tuesday</label>
   [<else>]
      <input type="checkbox" name="tuesday" id="tuesday"><label for="tuesday">Tuesday</label>
   [</if>]

   [<if isset($wednesday)>]
      <input type="checkbox" name="wednesday" id="wednesday" checked="1"><label for="wednesday">Wednesday</label>
   [<else>]
      <input type="checkbox" name="wednesday" id="wednesday"><label for="wednesday">Wednesday</label>
   [</if>]

   [<if isset($thursday)>]
      <input type="checkbox" name="thursday" id="thursday" checked="1"><label for="thursday">Thursday</label>
   [<else>]
      <input type="checkbox" name="thursday" id="thursday"><label for="thursday">Thursday</label>
   [</if>]

   [<if isset($friday)>]
      <input type="checkbox" name="friday" id="friday" checked="1"><label for="friday">Friday</label>
   [<else>]
      <input type="checkbox" name="friday" id="friday"><label for="friday">Friday</label>
   [</if>]

   <br />

   <label>Start time:</label>
   <select name="start_time">
      [<foreach from=$start_times|@sortby:"#" item=time>]
         [<if isset($start_time) && $start_time == $time>]
            <option value="[<$time>]" selected="1">[<$time>]</option>
         [<else>]
            <option value="[<$time>]">[<$time>]</option>
         [</if>]
      [</foreach>]
   </select>

   <label>End time:</label>
   <select name="end_time">
      [<foreach from=$end_times|@sortby:"#" item=time>]
         [<if isset($end_time) && $end_time == $time>]
            <option value="[<$time>]" selected="1">[<$time>]</option>
         [<else>]
            <option value="[<$time>]">[<$time>]</option>
         [</if>]
      [</foreach>]
   </select>

   <br />

   <input type="submit" value="Search" />
</form>

[<if isset($course_sections)>]
   [<if empty($course_sections)>]
      There are no results to display. <br />
      Note: A section will only be displayed if all of it&#8217;s class periods
      fall during the selected time on the selected days.
   [<else>]
      <h3>Search Results ([<$course_sections|@count>]):</h3>
      [<include file="_search_results.tpl">]
   [</if>]
[</if>]
