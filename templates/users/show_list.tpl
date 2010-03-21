[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

[<*
Display a list of users
*>]

<h1>Users</h1>
<table class="data">
   <thead>
      <th>Username</th>
      <th>Full Name</th>
      <th>Email</th>
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
      </tr>
      [</foreach>]
</table>
<br />
<a href="[<$SM_ROOT>]/users/create">Create a user</a>
