[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

[<if isset($user)>]
   <h1>Change password for [<$user->username>]</h1>
   [<if isset($update_success) and $update_success eq 1>]
      <h3>User updated successfully</h3>
   [</if>]
   <table>
      <form action="[<$SM_ROOT>]/users/change_password/[<$user->username>]" method="post">
         <tr>
            <td>Password:</td>
            <td><input type="password" name="password" value="" /></td>
         </tr>
         <tr>
            <td>Verify Password:</td>
            <td><input type="password" name="password_verify" value="" /></td>
         </tr>
         <tr>
            <td colspan="2"><input type="submit" value="Update password" /></td>
         </tr>
      </form>
   </table>
[<else>]
   <h1>User [<$username>] not found!</h1>
[</if>]
[<if $SM_USER->admin>]
   <br />
   <a href="[<$SM_ROOT>]/users/show_list">List of users</a>
[</if>]
