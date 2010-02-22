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

   [<include file="_schedule_display.tpl">]

[</if>]
