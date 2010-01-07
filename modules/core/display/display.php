<?php

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

      // create the smarty cache directory if it doesn't exist
      makedir($this->smarty->compile_dir);

      // turn off caching
      $this->smarty->caching = false;

      // TODO: turn this off for production?
      $this->smarty->compile_check = true;

      // root directory for templates
      self::$tpl_root = $SM_FS_ROOT . 'templates/';
   }
   
   // displays a template for the given module
   public function display_template($module, $template, $args=array())
   {
      $tpl = $this->prepare_template($module, $template, $args);
      $this->smarty->display($tpl);
	}
   
   // fetches a template for the given module
   public function fetch_template($module, $template, $args=array())
   {
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
      global $SM_USER,$SM_ROOT,$SM_SELF,$SM_ARGS, $SM_RR;

      // assign some global values
		$this->smarty_assign('SM_ROOT', $SM_ROOT);
		$this->smarty_assign('SM_SELF', $SM_SELF);
		$this->smarty_assign('SM_ARGSTRING', implode('/', $SM_ARGS));
		$this->smarty_assign('SM_RR', $SM_RR);
      if($SM_USER)
         $this->smarty_assign('SM_USER', $SM_USER);

      // assign the arguments
      $this->smarty_assign($args);

      // validate that the template exists and is readable
      $tpl = self::get_template(strtolower($module).'/'.strtolower($template));

      if($tpl === NULL)
      {
			error("Invalid template `$tpl` passed to Display");
      }

      return $tpl;
   }

   // returns the full path to a given relative template path, if it exists
   private static function get_template($tpl) 
   {
		$path = self::$tpl_root . $tpl;
		
      if (is_readable($path))
      {
			return $path;
		}
		return NULL;
	}

}

?>
