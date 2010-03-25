[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

[<*
Edit a user
*>]

[<if isset($error)>]
   [<$error>]
[<else>]
   [<if isset($user)>]
      <h1>User Preferences</h1>
      [<if isset($update_success) and $update_success eq 1>]
         <h3>User updated successfully</h3>
      [</if>]
      <form action="[<$SM_ROOT>]/users/edit/[<$user->username>]" method="post">
         <table>
            <tr>
               <td>Username:</td>
               <td>[<$user->username>]</td>
            </tr>
            <tr>
               <td>Name:</td>
               <td><input type="text" name="name" value="[<$user->name>]" /></td>
            </tr>
            <tr>
               <td>Email:</td>
               <td><input type="text" name="email" value="[<$user->email>]" /></td>
            </tr>
            <tr>
               <td>Password:</td>
               <td><a href="[<$SM_ROOT>]/users/change_password/[<$user->username>]">Change password</a></td>
            </tr>
            <tr>
               <td colspan="2"><input type="submit" value="Save" /></td>
            </tr>
         </table>
      </form>
   [<else>]
      <h1>User [<$username>] not found!</h1>
      [<if $SM_USER->admin>]
         Create user <a href="[<$SM_ROOT>]/users/create/[<$username>]">[<$username>]</a>. <br />
      [</if>]
   [</if>]
   [<if $SM_USER->admin>]
      <br />
      <a href="[<$SM_ROOT>]/users/show_list">List of users</a>
   [</if>]
[</if>]
