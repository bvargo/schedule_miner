<h1>Schedule Builder</h1>
Want the perfect schedule? Let the schedule builder show you your options.
<h3>How it works:</h3>
<ul>
   <li>Input the courses (and optionally course sections) you want to take this semester.</li>
   <li>Give each course a group number.</li>
   <li>Click the generate button to see your options. Don&#8217;t like what you see? Tweak the options and try again.</li>
</ul>
<h3>Notes:</h3>
<ul>
   <li><b>Only one course section per group is put in a schedule</b>. This is handy if you want to take either class A or class B, but do not want to take both in the same semester, or if you want section A or B, but obviously do not want to be in two sections of the same course at once.</li>
   <li><b>A schedule option is only shown if all of your course selections are in the schedule</b>. If you do not see any results, try reducing the number of criteria or adding multiple course to the same group.</li>
   <li><b>A maximum of [<$max_schedules>] schedule options are displayed</b>, due to limited computing resources. Try putting in just a few courses and deciding which sections you like. After you decide, add these course sections to your selections to narrow the search space. Remember, if you want either section A or section B, for example, add both options to the course selections table and put both options to the same group. This way only one of the sections will be added to a schedule.</li>
   <li><b>Labs, recitations, etc are currently not supported explicitly</b>. Sorry, it is being worked on for the future. As a workaround, try to figure out which lecture sections and which lab sections interest you. Add all of the lecture sections to one group, and add all of the lab sections to another group. This will ensure you have a lab section and a lecture section in your schedule.</li>
   <li><b>Courses with a smaller group number are given priority</b> when making schedules. For example, if you have class A in group 10 and class B in group 2, then class A will never appear in a schedule unless class B is also in that schedule. Be sure to change the group numbers if you do not like the options you see!</li>
</ul>
<h3>Course selections:</h3>
<form method="get">
   <table class="data" id="schedule_input">
      <thead>
         <th>Department abbr.</th>
         <th>Course number</th>
         <th>Section (optional)</th>
         <th>Group number</th>
      </thead>
      [<assign var=last_group value=0>]
      [<if isset($entry_rows)>]
         [<foreach from=$entry_rows|@sortby:"#3,0,#1,2" item="row">]
            <tr>
               <td><input name="department[]" value="[<$row[0]>]" /></td>
               <td><input name="course_number[]" value="[<$row[1]>]" /></td>
               <td><input name="course_section[]" value="[<$row[2]>]" /></td>
               <td><input name="priority[]" value="[<$row[3]>]" /></td>
            </tr>
            [<assign var=last_group value=$row[3]>]
         [</foreach>]
      [</if>]
      [<math equation="x+1" x=$last_group assign=last_group>]
      <tr>
         <td><input name="department[]" value="" /></td>
         <td><input name="course_number[]" value="" /></td>
         <td><input name="course_section[]" value="" /></td>
         <td><input name="priority[]" value="[<$last_group>]" /></td>
      </tr>
   </table>
   <input type="submit" value="Generate" /> (This may take a few seconds)
</form>

[<if isset($schedule_count)>]
   [<if $schedule_count gt 0>]
      [<if $schedule_count gt $max_schedules>]
         <h3>Generated Schedules ([<$schedule_count>] found, showing [<$max_schedules>]):</h3>
      [<else>]
         <h3>Generated Schedules ([<$schedule_count>]):</h3>
      [</if>]
      [<*[<foreach from=$schedules item=schedule>]
         [<include file="_schedule_display.tpl">]
      [</foreach>]*>]
      [<section name=schedule_number loop=$number_schedules_display>]
         [<if isset($schedules[schedule_number])>]
            [<assign var=schedule value=$schedules[schedule_number]>]
            [<include file="_schedule_display.tpl">]
            <form method="post" action="[<$SM_ROOT>]/schedules/create">
               <input type="hidden" name="schedule_name" value="Generated" />
               <input type="hidden" name="public" value="1" />
               <input type="hidden" name="active" value="1" />
               [<foreach from=$schedule->course_sections() item=course_section>]
                  <input type="hidden" name="crn[]" value="[<$course_section->crn>]" />
               [</foreach>]
               <p><input type="submit" value="Save Schedule" /></p>
            </form>
            [<if !$smarty.section.schedule_number.last>]
               <hr />
            [</if>]
         [</if>]
      [</section>]
   [<else>]
      <h1>There are no schedules that match your query.</h1>
   [</if>]
[<elseif isset($hit_limit)>]
   <h1>Too many schedules were found. Please reduce your search criteria.</h1>
   <p>You can do this by adding course sections where appropriate, if you know
   you want to be in a particular section, or removing courses from your
   selections. Remember, you can add multiple course sections to the same
   group, and only one section will be added to each schedule. This is
   especially helpful for courses that have many sections.</p>
[</if>]
