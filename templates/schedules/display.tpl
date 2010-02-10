[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

[<if isset($error)>]
   <h1>[<$error>]</h1>
[<else>]
   [<if isset($SM_USER) && $schedule->user eq $SM_USER>]
      <h1>Your Schedule - [<$schedule->name>]</h1>
      <form>
         <span class="bold">Schedule Name:</span>
         <input type="text" value="[<$schedule->name>]" />
         <input type="submit" value="Save" />
         <br />
         <br />
      </form>
   [<else>]
      <h1>[<$schedule->user->name>]&#8217;s Schedule - [<$schedule->name>]</h1>
      <br />
      <br />
   [</if>]


   <div class="schedule">
      <div class="calendar">
         <table>
            <thead>
               <tr>
                  <th class="time"></th>
                  <th class="vspacer"></th>
                  <th colspan="[<$colspan[1]>]">Monday</th>
                  <th colspan="[<$colspan[2]>]">Tuesday</th>
                  <th colspan="[<$colspan[3]>]">Wednesday</th>
                  <th colspan="[<$colspan[4]>]">Thursday</th>
                  <th colspan="[<$colspan[5]>]">Friday</th>
               </tr>
            </thead>
            <tbody>
               [<assign var=timecounter value=-4>]
               [<section name=minute start=$first_class_hour*60-60 loop=$last_class_hour*60+60 step=10>]
                  [<assign var=spacer value=-1>]
                  <tr class="[<cycle name=block values="first_block, middle_block, middle_block, middle_block, middle_block, last_block">] [<cycle name=color values=",,,,,,colorhour,colorhour,colorhour,colorhour,colorhour,colorhour">]">
                     [<math equation="(timecounter + 1) % 6" timecounter=$timecounter assign=timecounter>]
                     [<if $timecounter lt 0 or $smarty.section.minute.index - $last_class_hour*60 gte 30>]
                        <td class="time"></td>
                     [<elseif $timecounter eq 0>]
                        [<math equation="floor((minutes + 30) / 60)" minutes=$smarty.section.minute.index assign=hour>]
                        [<math equation="(minutes + 30) % 60" minutes=$smarty.section.minute.index assign=minute>]
                        <td class="time" rowspan="6">[<$hour>]:[<$minute|string_format:"%02d">]</td>
                     [</if>]
                     <td class="vspacer"></td>
                     [<section name=day start=1 loop=6>]
                        [<section name=slot start=0 loop=$colspan[$smarty.section.day.index]>]
                           [<if isset($calendar[$smarty.section.day.index][$smarty.section.minute.index][$smarty.section.slot.index][1])>]
                              [<if $calendar[$smarty.section.day.index][$smarty.section.minute.index][$smarty.section.slot.index][1] eq 0>]
                                 [<*math equation="colspan - count" day=$smarty.section.day.index minute=$smarty.section.minute.index count=$calendar[day][minute]|@count colspan=$colspan[day] assign=columns*>]
                                 [<math equation="1" assign=columns>]
                                 <!-- colspan is [<$colspan[day]>], count is [<$calendar[day][minute]|@count>]-->
                                 [<* start of course block *>]
                                 <td class="class" rowspan="[<$calendar[$smarty.section.day.index][$smarty.section.minute.index][$smarty.section.slot.index][0][11]>]" colspan="[<$columns>]" style="background-color: [<$calendar[$smarty.section.day.index][$smarty.section.minute.index][$smarty.section.slot.index][0][13]>];">
                                    <div class="number" title="[<$calendar[$smarty.section.day.index][$smarty.section.minute.index][$smarty.section.slot.index][0][0]>]">
                                       [<$calendar[$smarty.section.day.index][$smarty.section.minute.index][$smarty.section.slot.index][0][2]>]-[<$calendar[$smarty.section.day.index][$smarty.section.minute.index][$smarty.section.slot.index][0][3]>] [<$calendar[$smarty.section.day.index][$smarty.section.minute.index][$smarty.section.slot.index][0][1]>] ([<$calendar[$smarty.section.day.index][$smarty.section.minute.index][$smarty.section.slot.index][0][4]>])
                                    </div>
                                    [<if $calendar[$smarty.section.day.index][$smarty.section.minute.index][$smarty.section.slot.index][0][6] neq -1>]
                                       <div class="room" title="[<$calendar[$smarty.section.day.index][$smarty.section.minute.index][$smarty.section.slot.index][0][12]>] - [<$calendar[$smarty.section.day.index][$smarty.section.minute.index][$smarty.section.slot.index][0][6]>]">
                                          [<$calendar[$smarty.section.day.index][$smarty.section.minute.index][$smarty.section.slot.index][0][5]>]
                                          [<$calendar[$smarty.section.day.index][$smarty.section.minute.index][$smarty.section.slot.index][0][6]>]
                                       </div>
                                    [</if>]
                                 </td>
                              [</if>]
                           [<else>]
                              [<*ensures that we only have one spacer per day*>]
                              [<if $spacer lt $smarty.section.day.index>]
                                 [<if isset($calendar[day][minute])>]
                                    [<math equation="colspan - count" count=$calendar[day][minute]|@count day=$smarty.section.day.index minute=$smarty.section.minute.index colspan=$colspan[day] assign=columns>]
                                 [<else>]
                                    [<assign var=columns value=$colspan[$smarty.section.day.index]>]
                                 [</if>]
                                 [<if $columns gt 0>]
                                    <td colspan="[<$columns>]"></td>
                                 [</if>]
                              [</if>]
                              [<assign var=spacer value=$smarty.section.day.index>]
                           [</if>]
                        [</section>]
                     [</section>]
                  </tr>
               [</section>]
            </tbody>
         </table>
      </div>

      <div class="sidebar">
         <div id="courses">
            <ul id="course-list">
               [<foreach from=$course_sections item=course_section>]
               [<*TODO: make editable follow if there is more than one section, permissions, etc*>]
                  <li style="background-color: [<$course_section->color>]" class="editable">
                     <div class="course">
                        <h3 class="number">
                           <a href="[<$SM_ROOT>]/courses/display/[<$course_section->course->department->abbreviation>]/[<$course_section->course->course_number>]">[<$course_section->course->department->abbreviation>]-[<$course_section->course->course_number>]</a> <a href="[<$SM_ROOT>]/courses/display/[<$course_section->crn>]">[<$course_section->section>]</a> <a href="[<$SM_ROOT>]/courses/display/[<$course_section->crn>]">([<$course_section->crn>])</a>
                        </h3>
                        <h4 class="name"><a href="[<$SM_ROOT>]/courses/display/[<$course_section->course->department->abbreviation>]/[<$course_section->course->course_number>]">[<$course_section->name>]</a></h4>
                        <h4 class="name"><a href="[<$SM_ROOT>]/browse/instructor/[<$course_section->instructor->id>]">[<$course_section->instructor->name>]</a></h4>
                        [<math equation="credit_hours" credit_hours=$course_section->course->credit_hours format="%.1f" assign=credit_hours>]

                        <div class="credit-hours">[<$credit_hours>] <abbr title="Credit Hours">CR</abbr></div>
                     </div>
                  </li>
               [</foreach>]


            </ul>
            <div id="total-credit-hours">
               Total: [<$schedule->credit_hours()>] credit hours
            </div>
         </div>
      </div>

      <div style="clear: both;"></div>
   </div>
[</if>]
