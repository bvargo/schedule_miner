[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

[<*
Change password.
*>]

[<if isset($error)>]
   [<$error>]
[<else>]
   [<if isset($user)>]
      <h1>Change password for [<$user->username|escape>]</h1>
      [<if isset($update_success) and $update_success eq 1>]
         <h3>Password updated successfully</h3>
      [</if>]
      [<if isset($no_match)>]
         <h3>The passwords entered do not match</h3>
      [</if>]
      [<if isset($bad_current_password)>]
         <h3>The current password you entered is incorrect</h3>
      [</if>]
      <table>
         <form action="[<$SM_ROOT>]/users/change_password/[<$user->username|escape>]" method="post">
            <tr>
               <td>Current Password:</td>
               <td><input type="password" name="current_password" value="" /></td>
            </tr>
            <tr>
               <td>New Password:</td>
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
      <h1>User [<$username|escape>] not found!</h1>
   [</if>]
   [<if $SM_USER->admin>]
      <br />
      <a href="[<$SM_ROOT>]/users/show_list">List of users</a>
   [</if>]
[</if>]
