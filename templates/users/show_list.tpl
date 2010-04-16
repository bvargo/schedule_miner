[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

[<*
Display a list of users
*>]

[<if isset($error)>]
   [<$error>]
[<else>]
   <h1>Users ([<$users|@count>])</h1>
   <form action="[<$SM_ROOT>]/users/show_list" method="post">
      <table class="data user_list">
         <thead>
            <th>Username</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Schedules</th>
            <th>Delete User</th>
         </thead>
         [<foreach from=$users item=user>]
            <tr>
               <td><a href="[<$SM_ROOT>]/users/edit/[<$user->username>]">[<$user->username>]</a></td>
               <td>[<$user->name>]</td>
               <td>
                  [<if isset($user->email)>]
                     <a href="mailto:[<$user->email>]">[<$user->email>]</a>
                  [</if>]
               </td>
               <td>
                  <ul>
                     [<foreach from=$user->schedules item=schedule>]
                        <li><a href="[<$SM_ROOT>]/schedules/display/[<$schedule->id>]">[<$schedule->name>]</a>[<if $schedule->public eq 1>] (Public)[</if>][<if $user->active_schedule_id eq $schedule->id>] (Active)[</if>]</li>
                     [</foreach>]
                  </ul>
               </td>
               [<if $SM_USER->id eq $user->id>]
                  <td class="center">N/A</td>
               [<else>]
                  <td class="center"><input type="submit" name="delete[<$user->id>]" value="Delete" /></td>
               [</if>]
            </tr>
         [</foreach>]
      </table>
   </form>
   <br />
   <a href="[<$SM_ROOT>]/users/create">Create a user</a>
[</if>]
