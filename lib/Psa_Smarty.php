<?php
/**
 * @package PSA/more
 */


/**
 * Smarty main class.
 */
include_once PSA_BASE_DIR . '/lib/smarty/libs/Smarty.class.php';


/**
 * {@link http://www.smarty.net Smarty} template engine class.
 *
 * This class only extends Smarty's main class and sets some properties specific to PSA.
 * See {@link http://www.smarty.net} for more details about Smarty.
 *
 * If you need a new Smarty object you can instance this class.
 *
 * @see Psa_Dully
 * @see Psa_Smarty_View
 * @asFunction Smarty Psa_Smarty getInstance
 */
class Psa_Smarty extends Smarty{


	function __construct($template_dir = null){

		// call parent constructor
		parent::__construct();

		// Smarty folder configuration
		$this->cache_dir    = PSA_BASE_DIR . '/' . Cfg('folders.smarty.cache_dir');
		$this->config_dir   = PSA_BASE_DIR . '/' . Cfg('folders.smarty.config_dir');
		$this->compile_dir  = PSA_BASE_DIR . '/' . Cfg('folders.smarty.compile_dir');
		if(!$template_dir)
			$template_dir = Cfg('folders.template_dir');
		$this->template_dir = PSA_BASE_DIR . '/' . $template_dir;

		$this->use_sub_dirs = true;

		// suppress notices by default like in smarty 2
		$this->error_reporting = error_reporting() & ~E_NOTICE;

		// in develop mode
		if(isset(Cfg()['develop_mode'])){
			$this->force_compile = true;
		}
		
		// assign result object to 
		$this->assign('r', Res());
	}
}

