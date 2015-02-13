<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2013 Bojan Mauser
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @link http://code.google.com/p/phpstartapp/
 * @author Bojan Mauser <bmauser@gmail.com>
 * @package PSA/more
 * @version $Id: Psa_Smarty.php 142 2013-09-26 17:10:52Z bmauser $
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
		$this->cache_dir    = PSA_BASE_DIR . '/' . Cfg()['folders']['smarty']['cache_dir'];
		$this->config_dir   = PSA_BASE_DIR . '/' . Cfg()['folders']['smarty']['config_dir'];
		$this->compile_dir  = PSA_BASE_DIR . '/' . Cfg()['folders']['smarty']['compile_dir'];
		if(!$template_dir)
			$template_dir = Cfg()['folders']['template_dir'];
		$this->template_dir = PSA_BASE_DIR . '/' . $template_dir;

		$this->use_sub_dirs = true;

		// suppress notices by default like in smarty 2
		$this->error_reporting = error_reporting() & ~E_NOTICE;

		// in develop mode
		if(Cfg()['develop_mode']){
			$this->force_compile = true;
		}
		
		// assign result object to 
		$this->assign('r', Res());
	}
}

