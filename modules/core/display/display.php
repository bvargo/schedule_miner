<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Copyright 2004-2008 The Intranet 2 Development Team
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

// display

class Display
{
   // the smarty object for this display
   private $smarty = NULL;

   // the root directory for templates
   private static $tpl_root = NULL;

   // constructor - setup the smarty object and the template path
   public function __construct()
   {
      global $SM_FS_ROOT, $SM_LOG;

      // initialize smarty
      require_once('smarty/Smarty.class.php');
      $this->smarty                  = new Smarty();
      $this->smarty->left_delimiter  = '[<';
      $this->smarty->right_delimiter = '>]';
      $this->smarty->compile_dir     = smconfig_get('cache_dir') . 'smarty/';
      $this->smarty->cache_dir       = $this->smarty->compile_dir.'cache';
      $this->smarty->plugins_dir = array('plugins', $SM_FS_ROOT . 'smarty');
      $this->smarty->default_template_handler_func = array('Display', 'smarty_get_template');

      // create the smarty cache directory if it doesn't exist
      makedir($this->smarty->compile_dir);

      // turn off caching
      $this->smarty->caching = false;

      // TODO: turn this off for production?
      // checks all template files for modification time to see if
      // recompilation of the template is necessary
      $this->smarty->compile_check = true;

      // root directory for templates
      self::$tpl_root = $SM_FS_ROOT . 'templates/';
   }

   // displays a template for the given module
   public function display_template($module, $template, $args=array())
   {
      // ensure each module gets its own compiled template file, so two
      // different modules using the same filename for an included template do
      // not get the wrong compiled template file.
      // for example, if modules a and b both have a partial template _foo.tpl
      // under templates/a and templates/b, respectfully, then this will
      // produce compiled templates in the cache directory for each _foo.tpl,
      // instead of just one for the name _foo.tpl.
      $this->smarty->compile_id = $module;

      $tpl = $this->prepare_template($module, $template, $args);
      $this->smarty->display($tpl);
	}

   // fetches a template for the given module
   public function fetch_template($module, $template, $args=array())
   {
      // ensure each module gets its own compiled template file, so two
      // different modules using the same filename for an included template do
      // not get the wrong compiled template file.
      // for example, if modules a and b both have a partial template _foo.tpl
      // under templates/a and templates/b, respectfully, then this will
      // produce compiled templates in the cache directory for each _foo.tpl,
      // instead of just one for the name _foo.tpl.
      $this->smarty->compile_id = $module;

      $tpl = $this->prepare_template($module, $template, $args);
      return $this->smarty->fetch($tpl);
	}

   // assign a particular value or an array of values
   public function smarty_assign($var, $value=null)
   {
      if ($value === null)
      {
         // assign the key,value pairs that are in the array
			$this->smarty->assign($var);
		}
      else
      {
         // assign the single variable,value pair passed to this function
			$this->smarty->assign($var, $value);
		}
	}

   // prepare a template - returns the path to the template
   private function prepare_template($module, $template, $args=array())
   {
      global $SM_USER,$SM_ROOT,$SM_SELF,$SM_ARGS,$SM_RR,$SM_URL;

      // assign some global values
		$this->smarty_assign('SM_ROOT', $SM_ROOT);
		$this->smarty_assign('SM_SELF', $SM_SELF);
		$this->smarty_assign('SM_ARGSTRING', implode('/', $SM_ARGS));
		$this->smarty_assign('SM_RR', $SM_RR);
		$this->smarty_assign('SM_URL', $SM_URL);
		$this->smarty_assign('SM_MODULE', $module);
      if($SM_USER)
         $this->smarty_assign('SM_USER', $SM_USER);

      // assign the arguments
      $this->smarty_assign($args);

      // validate that the template exists and is readable
      // locate a template specific to this module
      $tpl = self::get_template($template, $module);
      // if both have failed, throw an error
      if($tpl === NULL)
         error("Invalid template `$template` passed to Display");

      return $tpl;
   }

   // smarty get template
   // provides the template source to smarty if smarty cannot locate it
   // called when [<include>] is used in a template
   static function smarty_get_template($resource_type, $resource_name, &$template_source, &$template_timestamp, &$smarty_obj)
   {
      if($resource_type == 'file')
      {
         $module = $smarty_obj->get_template_vars('SM_MODULE');
         $tpl = self::get_template($resource_name, $module);

         if($tpl == NULL)
            return false;

         $fh = fopen($tpl, 'r');
         $template_source = fread($fh, filesize($tpl));

         return true;
      }
   }

   // returns the full path to the specified template, if the template can be
   // found
   static function get_template($template, $module = NULL)
   {
      // look first for a template under the module's template directory
      $tpl = $module . '/' . $template;
      $path = self::$tpl_root . $tpl;

      // if a template is not found, search for a partial template
      if(!self::check_template($path))
      {
         $tpl = 'partials/' . $template;
         $path = self::$tpl_root . $tpl;
      }

      if(self::check_template($path))
			return $path;
      return NULL;
   }

   // checks if a template is valid
   private static function check_template($template_path)
   {
      if(is_readable($template_path))
         return true;
      return false;
   }

}

?>
