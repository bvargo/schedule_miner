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
      global $SM_MODULE;
      $display = new Display();

      // TODO: check for various output formats here (html, json, etc) and
      // modify the template filename accordingly
      $template = $this->template_name.".tpl";

      if(in_array('title', array_keys($this->args)))
      {
         $this->args['title'] .= " - ".smconfig_get('page_title');
      }
      else
      {
         $this->args['title'] = smconfig_get('page_title');
      }
      // display the page template
      $display->smarty_assign('CONTENT', $display->fetch_template($SM_MODULE, $template, $this->args));

      // TODO: theming would be here - just select a different page template
      $display->display_template('core', 'page.tpl');
   }
}

?>
