[<*
Copyright 2009-2010 The Schedule Miner Authors
Use of this source code is governed by an MIT-style license that can be
found in the LICENSE file.
*>]

[<*
Suggestion input form.
*>]

[<if isset($mailed)>]
   [<if $mailed>]
      Your suggestion/question has been submitted. Thank you.
   [<else>]
      There was a problem submitting your suggestion.
   [</if>]
[<else>]
   <h1>Suggestions/Questions</h1>
   <p>We will contact you at <strong>[<$SM_USER->email>]</strong> if appropriate. If this email address is incorrect, please update your <a href="[<$SM_ROOT>]/users/edit">preferences</a>.</p>
   <form method="post" action="[<$SM_ROOT>]/suggestions">
      <div id="suggestion_div">
         <textarea name="suggestion_box" id="suggestion_box"></textarea>
      </div>
      <br />
      <input type="submit" value="Submit" name="submit_form" />
   </form>
[</if>]
