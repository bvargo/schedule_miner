[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

[<*
Create a user.
*>]

<h1>Create an Account</h1>
[<if isset($error)>]
   <h3>[<$error>]</h3>
[</if>]
<form action="[<$SM_ROOT>]/users/create" method="post">
   <table>
      <tr>
         <td>Username:</td>
         [<if isset($username)>]
            <td><input type="text" name="username" value="[<$username>]" /></td>
         [<else>]
            <td><input type="text" name="username" value="" /></td>
         [</if>]
      </tr>
      <tr>
         <td>Name:</td>
         <td><input type="text" name="name" value="" /></td>
      </tr>
      <tr>
         <td>Email:</td>
         <td><input type="text" name="email" value="" /></td>
      </tr>
      <tr>
         <td>Password:</td>
         <td><input type="password" name="password" value="" /></td>
      </tr>
      <tr>
         <td>Verify Password:</td>
         <td><input type="password" name="password_verify" value="" /></td>
      </tr>
   </table>
   <input type="submit" value="Create Account" />
</form>
[<if isset($SM_USER) && $SM_USER->admin>]
   <br />
   <a href="[<$SM_ROOT>]/users/show_list">List of users</a>
[</if>]
