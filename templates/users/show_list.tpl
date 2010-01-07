[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

<h1>Users</h1>
[<foreach from=$users item=user>]
   <a href="[<$SM_ROOT>]/users/edit/[<$user->username>]">[<$user->username>]</a> - [<$user->name>]
   [<if isset($user->email)>]
   - <a href="mailto:[<$user->email>]">[<$user->email>]</a>
   [</if>]
   <br />
[</foreach>]
<br />
<a href="[<$SM_ROOT>]/users/create">Create a user</a>
