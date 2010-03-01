[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

[<*
Display a schedule, given a schedule object or a list of course sections.
*>]

[<php>]
   function date_to_minutes($date)
   {
      $t = explode(":", $date);
      $hour = intval($t[0]);
      $minute = intval($t[1]);
      $minutes = $hour * 60 + $minute;
      return $minutes;
   }

   if(array_key_exists('class_periods', $this->_tpl_vars))
   {
      $class_periods = $this->_tpl_vars['class_periods'];
      if(array_key_exists('course_sections', $this->_tpl_vars))
         $course_sections = $this->_tpl_vars['course_sections'];
   }
   else if(array_key_exists('course_sections', $this->_tpl_vars))
   {
      $course_sections = $this->_tpl_vars['course_sections'];
      $class_periods = array();
      foreach($course_sections as $course_section)
         $class_periods = array_merge($class_periods, $course_section->class_periods);
   }
   else if(array_key_exists('course_section', $this->_tpl_vars))
   {
      $course_section = $this->_tpl_vars['course_section'];
      $course_sections = array();
      $course_sections[] = $course_section;
      $class_periods = $course_section->class_periods;
   }
   else
   {
      // FIXME: more checks here - don't just assume a schedule exists
      $course_sections = $this->_tpl_vars['schedule']->course_sections();
      $class_periods = array();
      foreach($course_sections as $course_section)
         $class_periods = array_merge($class_periods, $course_section->class_periods);
   }

   // construct an array of meeting times containing:
   // 0 - name
   // 1 - section
   // 2 - department abbreviation
   // 3 - course number
   // 4 - crn
   // 5 - building abbreviation
   // 6 - room number
   // 7 - day
   // 8 - start time
   // 9 - end time
   // 10 - slot
   // 11 - column height (for rowspan)
   // 12 - building name
   // 13 - color
   $meeting_times = array();

   // find meeting times
   foreach($class_periods as $class_period)
   {
      // round down to the closest five minute interval for the start time
      // use fixed width strings so that string comparisons of times can be
      // used without errors
      $start_minutes = intval(date_to_minutes($class_period->start_time) / 5) * 5;
      $start_hour = intval($start_minutes / 60);
      $start_minute = $start_minutes % 60;
      $start_time = sprintf("%02d:%0.2d:00", $start_hour, $start_minute);

      // round up to the closest five minute interval for the end time
      // use fixed width strings so that string comparisons of times can be
      // used without errors
      $end_minutes = intval(date_to_minutes($class_period->end_time));
      if($end_minutes % 5 != 0)
         $end_minutes = (intval($end_minutes / 5) + 1) * 5;
      else
         $end_minutes = intval($end_minutes / 5) * 5;
      $end_hour = intval($end_minutes / 60);
      $end_minute = $end_minutes % 60;
      $end_time = sprintf("%02d:%0.2d:00", $end_hour, $end_minute);

      $meeting_times[] = array($class_period->course_section->name,
                                 $class_period->course_section->section,
                                 $class_period->course_section->course->department->abbreviation,
                                 $class_period->course_section->course->course_number,
                                 $class_period->course_section->crn,
                                 $class_period->building->abbreviation,
                                 $class_period->room_number,
                                 $class_period->day,
                                 $start_time,
                                 $end_time,
                                 -1,
                                 1,
                                 $class_period->building->name);
   }

   // the following should really go in the template, but smarty is really
   // stupid

   // transform days "M, T, W, etc" into numbers (0 = Sunday)
   for($i = 0; $i < count($meeting_times); $i++)
   {
      switch($meeting_times[$i][7])
      {
      case "M":
         $meeting_times[$i][7] = 1;
         break;
      case "T":
         $meeting_times[$i][7] = 2;
         break;
      case "W":
         $meeting_times[$i][7] = 3;
         break;
      case "R":
         $meeting_times[$i][7] = 4;
         break;
      case "F":
         $meeting_times[$i][7] = 5;
         break;
      default:
         // should never get here
         $meeting_times[$i][7] = 0;
      }
   }

   // determine the first and last class times
   $first_class_hour = $meeting_times[0][8];
   $last_class_hour = $meeting_times[0][9];
   foreach($meeting_times as $meeting_time)
   {
      if($meeting_time[8] < $first_class_hour)
         $first_class_hour = $meeting_time[8];
      if($meeting_time[9] > $last_class_hour)
         $last_class_hour = $meeting_time[9];
   }
   // first_class_hour - always start on an hour
   $t = explode(":", $first_class_hour);
   $first_class_hour = intval($t[0]);
   // last_class_hour - always end on an hour
   $t = explode(":", $last_class_hour);
   if(!$t[0])
      $last_class_hour = intval($t[0]);
   else
      $last_class_hour = intval($t[0]) + 1;
   // first and last class_hour are now ints containing the start and end hours

   // an array of days, each containing an entry for each slot
   // calendar[day][minute][slot] = [meeting_time, state]
   // slot is the class (usually 0), but is incremented if two classes
   // overlap - see below
   // state is 0 if start of block, 1 if in the middle, 2 if at the end
   $calendar = array();
   for($day = 1; $day < 6; $day++)
      $calendar[$day] = array();

   // just add the classes to the calendar array - no slots yet
   for($day = 0; $day < 7; $day++)
   {
      foreach($meeting_times as &$meeting_time)
      {
         if($meeting_time[7] == $day)
         {
            for($time = date_to_minutes($meeting_time[8]); $time < date_to_minutes($meeting_time[9]); $time += 5)
            {
               // make sure the arrays exist
               if(!array_key_exists($time, $calendar[$day]))
                  $calendar[$day][$time] = array();

               // set the state
               $state = 1;
               if($time == date_to_minutes($meeting_time[8]))
                  $state = 0;
               else if($time >= date_to_minutes($meeting_time[9]))
                  $state = 2;

               // set the number of rows to span
               $meeting_time[11] = (date_to_minutes($meeting_time[9]) - date_to_minutes($meeting_time[8])) / 5;

               // add it
               $calendar[$day][$time][] = array(&$meeting_time, $state);
            }
         }
      }
      unset($meeting_time);
   }

   // figure out how many columns each day needs
   $colspan = array(0,1,1,1,1,1,0);
   for($day = 1; $day < 6; $day++)
   {
      for($time = $first_class_hour * 60; $time < $last_class_hour * 60; $time += 5)
      {
         if(array_key_exists($time, $calendar[$day]))
            if(count($calendar[$day][$time]) > $colspan[$day])
               $colspan[$day] = count($calendar[$day][$time]);
      }
   }

   // now assign slots
   for($day = 1; $day < 6; $day++)
   {
      if($colspan[$day] > 1)
      {
         for($time = $first_class_hour * 60; $time < $last_class_hour * 60; $time += 5)
         {
            if(array_key_exists($time, $calendar[$day]))
            {
               if(count($calendar[$day][$time]) > 1)
               {
                  // we have found a time that has multiple classes
                  // this needs to be resolved

                  // as temporary place to keep track of block assignments for
                  // this timeperiod
                  $slot_assignments = array();

                  //find if something already has a slot assigned
                  //if so, keep that assignment
                  foreach($calendar[$day][$time] as $block)
                  {
                     if($block[0][10] != -1)
                        $slot_assignments[$block[0][10]] = $block[0];
                  }

                  // now assign the rest
                  foreach($calendar[$day][$time] as &$block)
                  {
                     if($block[0][10] == -1)
                     {
                        // need to assign a block
                        $i = 0;
                        while(1)
                        {
                           if(!array_key_exists($i, $slot_assignments))
                           {
                              $slot_assignments[$i] = $block[0];
                              $block[0][10] = $i;
                              break;
                           }
                           else
                           {
                              $i++;
                           }
                        }
                     }
                  }
                  unset($block);
               }
            }
         }
      }
      else if($colspan[$day] == 1)
      {
         // no conflicts here - just set all the slots to 0
         for($time = $first_class_hour * 60; $time < $last_class_hour * 60; $time += 5)
         {
            if(array_key_exists($time, $calendar[$day]))
            {
               foreach($calendar[$day][$time] as &$block)
               {
                  $block[0][10] = 0;
               }
               unset($block);
            }
         }
      }
   }

   // figure out the number of credit hours
   $total_credit_hours = 0;
   foreach($course_sections as $course_section)
   {
      $total_credit_hours += $course_section->course->credit_hours;
   }

   // assign colors to the courses
   $color_assign = array();
   $colors = array(
                  '#ff0000',   // red
                  '#ff8100',   // orange
                  '#ffff00',   // yellow
                  '#00ff00',   // lime green
                  '#107000',   // darker green
                  '#9999ff',   // lilac
                  '#00aaff',   // light blue
                  '#0f94a9',   // teal blue
                  '#990099',   // purple/pink
                  '#AAAAAA',   // lightgrey
                  '#cc6600',   // brown / dark orange
                  '#45c300',   // bright green
                  '#3366ff',   // dark blue
                  '#f2679a',   // pink
                  '#666666'    // darkgrey
               );
   $color_index = 0;

   // sort the course_sections list and then actually assign the colors
   uasort($course_sections, sortby("course->department->abbreviation,#course->course_number,section"));
   foreach($course_sections as $course_section)
   {
      if(!array_key_exists($course_section->crn, $color_assign))
      {
         $course_section->color = $colors[$color_index];;
         $color_assign[$course_section->crn] = $colors[$color_index];
         $color_index += 1;
         $color_index %= count($colors);
      }
   }

   // now calendar contains all of the data, including slot information
   // pass this off to the template to display
   $this->assign('first_class_hour', $first_class_hour);
   $this->assign('last_class_hour', $last_class_hour);
   $this->assign('colspan', $colspan);
   $this->assign('calendar', $calendar);
   $this->assign('course_sections', $course_sections); // needed, since we add colors above;
   $this->assign('total_credit_hours', $total_credit_hours);
   $this->assign('color_assign', $color_assign);
[</php>]

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
            [<*timecounter controls when the time on the left-hand side of the schedule is displayed*>]
            [<assign var=timecounter value=-7>]
            [<section name=minute start=$first_class_hour*60-60 loop=$last_class_hour*60+60 step=5>]
               [<assign var=spacer value=-1>]
               <tr class="[<cycle name=block values="first_block, middle_block_bottom, middle_block_top, middle_block_bottom, middle_block_top, middle_block_bottom, middle_block_top, middle_block_bottom, middle_block_top, middle_block_bottom, middle_block_top, last_block">] [<cycle name=color values=",,,,,,,,,,,,colorhour,colorhour,colorhour,colorhour,colorhour,colorhour,colorhour,colorhour,colorhour,colorhour,colorhour,colorhour">]">
                  [<math equation="(timecounter + 1) % 12" timecounter=$timecounter assign=timecounter>]
                  [<if $timecounter lt 0 or $smarty.section.minute.index - $last_class_hour*60 gte 30>]
                     <td class="time"></td>
                  [<elseif $timecounter eq 0>]
                     [<math equation="floor((minutes + 30) / 60)" minutes=$smarty.section.minute.index assign=hour>]
                     [<math equation="(minutes + 30) % 60" minutes=$smarty.section.minute.index assign=minute>]
                     [<*the rowspan should be the number of blocks in an hour; here, there are 12 blocks in 5 minute incremenets in an hour*>]
                     <td class="time" rowspan="12">[<$hour>]:[<$minute|string_format:"%02d">]</td>
                  [</if>]
                  <td class="vspacer"></td>
                  [<section name=day start=1 loop=6>]
                     [<section name=slot start=0 loop=$colspan[$smarty.section.day.index]>]
                        [<if isset($calendar[$smarty.section.day.index][$smarty.section.minute.index][$smarty.section.slot.index][1])>]
                           [<if $calendar[$smarty.section.day.index][$smarty.section.minute.index][$smarty.section.slot.index][1] eq 0>]
                              [<*math equation="colspan - count" day=$smarty.section.day.index minute=$smarty.section.minute.index count=$calendar[day][minute]|@count colspan=$colspan[day] assign=columns*>]
                              [<math equation="1" assign=columns>]
                              [<* start of course block *>]
                              [<assign var=crn value=$calendar[$smarty.section.day.index][$smarty.section.minute.index][$smarty.section.slot.index][0][4]>]
                              <td class="class" rowspan="[<$calendar[$smarty.section.day.index][$smarty.section.minute.index][$smarty.section.slot.index][0][11]>]" colspan="[<$columns>]" style="background-color: [<$color_assign[$crn]>];">
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

   [<if isset($course_sections)>]
      <div class="sidebar">
         <div id="courses">
            <ul id="course-list">
               [<*course_sections is already sorted above*>]
               [<foreach from=$course_sections item=course_section>]
               [<*TODO: make editable follow if there is more than one section, permissions, etc*>]
                  [<assign var=crn value=$course_section->crn>]
                  <li style="background-color: [<$color_assign[$crn]>]" class="editable">
                     <div class="course">
                        <h3 class="number">
                           <a href="[<$SM_ROOT>]/courses/display/[<$course_section->course->department->abbreviation>]/[<$course_section->course->course_number>]">[<$course_section->course->department->abbreviation>]-[<$course_section->course->course_number>]</a> <a href="[<$SM_ROOT>]/courses/display/[<$course_section->crn>]">[<$course_section->section>]</a> <a href="[<$SM_ROOT>]/courses/display/[<$course_section->crn>]">([<$course_section->crn>])</a>
                        </h3>
                        <h4 class="name"><a href="[<$SM_ROOT>]/courses/display/[<$course_section->course->department->abbreviation>]/[<$course_section->course->course_number>]">[<$course_section->name>]</a></h4>
                        <h4 class="name"><a href="[<$SM_ROOT>]/courses/display/instructor/[<$course_section->instructor->id>]">[<$course_section->instructor->name>]</a></h4>
                        [<math equation="credit_hours" credit_hours=$course_section->course->credit_hours format="%.1f" assign=credit_hours>]

                        <div class="credit-hours">[<$credit_hours>] <abbr title="Credit Hours">CR</abbr></div>
                     </div>
                  </li>
               [</foreach>]


            </ul>
            <div id="total-credit-hours">
               Total: [<$total_credit_hours>] credit hours
            </div>
         </div>
      </div>
   [</if>]

   <div style="clear: both;"></div>
</div>
