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
   <h1>Users</h1>
   <form action="[<$SM_ROOT>]/users/show_list" method="post">
      <table class="data">
         <thead>
            <th>Username</th>
            <th>Full Name</th>
            <th>Email</th>
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
