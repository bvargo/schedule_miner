[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

[<php>]
// meeting_times:
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

$meeting_times = $this->_tpl_vars['meeting_times'];
print_r($meting_times);

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

$this->assign('first_class_hour', $first_class_hour);
$this->assign('last_class_hour', $last_class_hour);

[</php>]


<h1>Your Schedule</h1>

<div id="calendar">
   <table>
      <thead>
         <tr>
            <th class="time"></th>
            <th colspan="1">Monday</th>
            <th colspan="1">Tuesday</th>
            <th colspan="1">Wednesday</th>
            <th colspan="1">Thursday</th>
            <th colspan="1">Friday</th>
         </tr>
      </thead>
      <tbody>
      [<php>]
      // used for setting the color/controlling times on left - modulo 4
      $repetition = 0;

      // loop over every time period for the schedule
      for($hour = $first_class_hour; $hour < $last_class_hour; $hour++)
      {
         for($minute = 0; $minute <= 30; $minute += 30)
         {
            $blockclass = "";
            if($repetition % 2 == 0)
               $blockclass = "first_block";
            else
               $blockclass="second_block";
            if($repetition % 4 == 2 || $repetition % 4 == 3)
               $blockclass .= " colorhour";
            $this->assign('blockclass', $blockclass);
            $this->assign('repetition', $repetition);
            $repetition += 1;
            $repetition %= 4;

            $this->assign('hour', $hour);
            $this->assign('minute', $minute);
            [</php>]

            <tr class="[<$blockclass>]">
               [<if $hour eq $first_class_hour and $minute == 0 or ($hour+1) eq $last_class_hour and $minute != 0>]
                  <td class="time"></td>
               [</if>]
               [<if ($repetition eq 1 or $repetition eq 3) and ($hour+1) neq $last_class_hour>]
                  <td class="time" rowspan="2">[<$hour>]:00</td>
               [</if>]
               <td colspan="1">M</td>
               <td colspan="1">T</td>
               <td colspan="1">W</td>
               <td colspan="1">R</td>
               <td colspan="1">F</td>
            </tr>

            [<php>]
         }
      }
      [</php>]
      </tbody>
   </table>
</div>
