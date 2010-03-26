<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

// the homepage

class ErrorPage extends Module
{
   function error404()
   {
      // send a 404 header, then render the 404 page
      header("HTTP/1.0 404 Not Found");
   }
}

?>
