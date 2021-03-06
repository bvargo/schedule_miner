[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

[<*
Login form.
*>]

<h1>Login</h1>
[<if isset($username)>]
   You are currently logged in as [<$username|escape>]
[</if>]
<h3>Type your Schedule Miner username/password to login.</h3>
<b>This website is not associated with Mines.</b>
[<if isset($error)>]
   <h3 class="error">Error: username/password unknown</h3>
[</if>]
<table>
   <form action="[<$SM_ROOT>]/users/login/" method="post">
      <tr>
         <td>Username:</td>
         <td><input type="text" name="username" value="" /></td>
      </tr>
      <tr>
         <td>Password:</td>
         <td><input type="password" name="password" value="" /></td>
      </tr>
      <tr>
         <td colspan="2"><input type="submit" value="Login" /></td>
      </tr>
   </form>
</table>

