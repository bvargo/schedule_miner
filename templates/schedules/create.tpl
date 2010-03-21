[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

[<*
Interface to create a schedule
*>]

<h1>Create a Schedule</h1>
<form action="[<$SM_ROOT>]/schedules/create" method="post">
   <table>
      <tr>
         <td>Schedule Name:</td>
         <td><input type="text" name="schedule_name" value="" /></td>
      </tr>
      <tr>
         <td>Publically viewable?</td>
         <td><input type="checkbox" name="public" checked="checked" /></td>
      </tr>
      [<if count($SM_USER->schedules) gte 1>]
         <tr>
            <td>Active Schedule?</td>
            <td><input type="checkbox" name="active" checked="checked" /></td>
         </tr>
      [</if>]
      <tr>
         <td colspan="2"><input type="submit" value="Create Schedule" /></td>
      </tr>
   </table>
</form>
