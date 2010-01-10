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
   }

   function __destruct()
   {
      global $SM_MODULE, $SM_ACTION, $SM_FS_ROOT, $SM_RR;
      $display = new Display();

      // TODO: check for various output formats here (html, json, etc) and
      // modify the template filename accordingly
      $template = $this->template_name.".tpl";

      // set the title
      if(in_array('title', array_keys($this->args)))
         $this->args['title'] .= " - ".smconfig_get('page_title');
      else
         $this->args['title'] = smconfig_get('page_title');

      // look for additional css
      if(file_exists($SM_FS_ROOT . "/www/css/$SM_MODULE/$SM_ACTION.css"))
      {
         if(!array_key_exists('css', $this->args))
         {
            $this->args['css'] = array();
            $this->args['css'][] = $SM_RR . "/css/$SM_MODULE/$SM_ACTION.css";
         }
         else
         {
            // TODO: the module has set additional css files
            // if it's a full path, include it in addition to the found css 
            // files
            // if it isn't a full path, find the requested file and make the
            // reference a full path
         }
      }

      // display the page template
      $display->smarty_assign('CONTENT', $display->fetch_template($SM_MODULE, $template, $this->args));

      // TODO: theming would be here - just select a different page template
      $display->display_template('core', 'page.tpl');
   }
}

?>
