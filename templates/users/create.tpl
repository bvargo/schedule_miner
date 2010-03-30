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
   <h3>Terms of Service:</h3>
   By using this website, you understand and consent to the following terms:
   <ul>
      <li>This website and affiliated software ("the website", "the service", "the system") is not run by or affiliated with the Colorado School of Mines (CSM). At no point will this website interact with school systems on your behalf.</li>
      <li>This website is provided "as is" and "as available", without warranty of any kind, express or implied, including but not limited to the warranties of merchantability, fitness for a particular purpose and non-infringement. In no event shall the authors, maintainers, or copyright holders of this website be liable for any claim, damages, or other liability, whether in an action of contract, tort or otherwise, arising from, out of or in connection with the website or the use or other dealings with the website.</li>
      <li>This website is designed for the purpose of making scheduling easier. It does not replace Trailhead. Trailhead and the Registrar&#8217;s office is the sole source of information. You must still register for classes through Trailhead. This website is not responsible for any incorrect information presented.</li>
      <li>This website is made available for personal, academic use only. You must provide accurate, current information during the registration process and/or use of the system. You warrant that you have the necessary power and authority to enter into this agreement and follow the terms contained within.</li>
      <li>The maintainers of the website hold the ability to, at any time, discontinue the availability or deny the use of the service to anyone, for any reason.</li>
      <li>You agree that you are responsible for the content you submit to this website. This content will not be illegal, will not be objectionable as reasonably determined by the maintainers of the website. Content may be removed by the maintainers of the website for any reason without warning.</li>
      <li>You agree that you will not knowingly prevent others or attempt to prevent others from using the service.</li>
      <li>The maintainers of this website take no responsibility for any third party content that is made available through the system.</li>
      <li>You hold harmless the authors, maintainers, and copyright holders of the website against any third party claim that may arise in any way related to your use of the system.</li>
      <li>These terms of service may be updated at any time. A future login affirms your agreement to any updated terms. This policy will always be available before logging into the system.</li>
   </ul>
   <h3>Privacy Policy:</h3>
   By using this website, you also understand and consent to the following terms:
   <ul>
      <li>When registering for use of the website, and throughout the use of the website, you provide information that is stored as a part of the website. This includes, but is not limited to, account information, schedule contents, and usage information.</li>
      <li>The maintainers of this website will not transfer any private information to third parties, though general statistics that do not personally identify you by name may be made available. Information that is not explicitly marked as public in the website is considered private data. Any information that you make public can be made private again at any time.</li>
      <li>The maintainers of this website reserve the right to transfer your personal information submitted through the website in the event of a transfer of ownership of the website. If this is the case, the website will notify you before this transfer of ownership takes place.</li>
      <li>If you decide to delete your account and end the use of the service, residual copies of information may still be stored in the system even after the deletion of your account.</li>
      <li>This privacy policy may be updated at any time. A future login affirms your agreement to any updated terms. This policy will always be available before logging into the system.</li>
   </ul>

   <input type="submit" value="Create Account" />
</form>
[<if isset($SM_USER) && $SM_USER->admin>]
   <br />
   <a href="[<$SM_ROOT>]/users/show_list">List of users</a>
[</if>]
