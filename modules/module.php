<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

// module superclass

class Module
{
   protected $template_name = NULL;
   protected $args = array();

   function __construct()
   {
      global $SM_ACTION;
      $this->template_name = $SM_ACTION;
      $this->args['css'] = array();
      $this->args['js'] = array();
   }

   function __destruct()
   {
      global $SM_MODULE, $SM_ACTION, $SM_FS_ROOT, $SM_RR;

      // only try to display a template if the action is valid
      if((is_callable(array($this, $SM_ACTION)) && substr($SM_ACTION, 0, 1) != "_") || is_callable(array($this, "unknown_action")))
      {
         $display = new Display();

         // TODO: check for various output formats here (html, json, etc) and
         // modify the template filename accordingly
         $template = $this->template_name.".tpl";

         // set the title
         if(array_key_exists('title', $this->args))
            $this->args['title'] .= " - ".smconfig_get('page_title');
         else
            $this->args['title'] = smconfig_get('page_title');

         // setup additional css and js arrays

         // TODO: the module has set additional css or js files already
         // if it's a full path, include it in addition to the found css and 
         // js files
         // if it isn't a full path, find the requested file and make the 
         // reference a full path

         // look for module-specific css
         if(file_exists($SM_FS_ROOT . "/www/css/$SM_MODULE.css"))
            $this->args['css'][] = $SM_RR . "/css/$SM_MODULE.css";

         // look for action-specific css
         if(file_exists($SM_FS_ROOT . "/www/css/$SM_MODULE/$SM_ACTION.css"))
            $this->args['css'][] = $SM_RR . "/css/$SM_MODULE/$SM_ACTION.css";
         
         // look for module-specific js
         if(file_exists($SM_FS_ROOT . "/www/js/$SM_MODULE.js"))
            $this->args['js'][] = $SM_RR . "/js/$SM_MODULE.js";

         // look for action-specific js
         if(file_exists($SM_FS_ROOT . "/www/js/$SM_MODULE/$SM_ACTION.js"))
            $this->args['js'][] = $SM_RR . "/js/$SM_MODULE/$SM_ACTION.js";

         // display the page template
         $display->smarty_assign('CONTENT', $display->fetch_template($SM_MODULE, $template, $this->args));

         // TODO: theming would be here - just select a different page template
         $display->display_template('core', 'page.tpl', $this->args);
      }
   }
}

?>
