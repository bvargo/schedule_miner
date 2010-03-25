<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

// suggestions module

class Suggestions extends Module
{
   function index()
   {
      global $SM_USER;

      if(isset($_POST["suggestion_box"]))
      {
         // get the message
         $message = $_POST["suggestion_box"];
         $message = trim($message);

         // don't send an empty message
         if($message != "")
         {
            $to = smconfig_get("send_to");
            if(!$to)
               return;
            $subject = "[Schedule Miner] Suggestion from {$SM_USER->name} ({$SM_USER->username})";
            $browser = $_SERVER["HTTP_USER_AGENT"];
            $message .= "\r\n\r\n$browser";

            $headers = "From: {$SM_USER->email}\r\n";
            $headers .= "Reply-To: {$SM_USER->email}\r\n";
            $headers .= "Return-Path: $to\r\n";
            $this->args["mailed"] = mail($to, $subject, $message, $headers);
         }
      }
   }
}

?>
