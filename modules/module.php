<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

class Module
{
   protected $template_name = NULL;
   protected $args = array();

   function __construct()
   {
      global $SM_ACTION;
      $this->template_name = $SM_ACTION;
      $this->args['css'] = array();
   }

   function __destruct()
   {
      global $SM_MODULE, $SM_ACTION, $SM_FS_ROOT, $SM_RR;
      $display = new Display();

      // TODO: check for various output formats here (html, json, etc) and
      // modify the template filename accordingly
      $template = $this->template_name.".tpl";

      // set the title
      if(array_key_exists('title', $this->args))
         $this->args['title'] .= " - ".smconfig_get('page_title');
      else
         $this->args['title'] = smconfig_get('page_title');

      // setup additional css array

      // TODO: the module has set additional css files already
      // if it's a full path, include it in addition to the found css
      // files
      // if it isn't a full path, find the requested file and make the
      // reference a full path

      // look for module-specific css
      if(file_exists($SM_FS_ROOT . "/www/css/$SM_MODULE.css"))
         $this->args['css'][] = $SM_RR . "/css/$SM_MODULE.css";

      // look for action-specific css
      if(file_exists($SM_FS_ROOT . "/www/css/$SM_MODULE/$SM_ACTION.css"))
         $this->args['css'][] = $SM_RR . "/css/$SM_MODULE/$SM_ACTION.css";

      // display the page template
      $display->smarty_assign('CONTENT', $display->fetch_template($SM_MODULE, $template, $this->args));

      // TODO: theming would be here - just select a different page template
      $display->display_template('core', 'page.tpl', $this->args);
   }
}

?>
