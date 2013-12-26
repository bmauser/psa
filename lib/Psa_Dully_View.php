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
 * @package PSA
 * @version $Id: Psa_Dully_View.php 142 2013-09-26 17:10:52Z bmauser $
 */


/**
 * Class for views that use {@link Psa_Dully} template engine.
 *
 * Classes that extend {@link Psa_Dully_View} or {@link Psa_Smarty_View} class are the place for methods that will
 * print results from your application. This can be HTML, XML or something else like a plain text.
 * View methods, like model methods, are called from controller. All application logic should
 * be in model and in view you should put only part of the logic for displaying results (presentation logic) like
 * template rendering code.
 *
 * You can write <i>Psa_Dully_View</i> views by extending this class. Here is an example:
 *
 * <code>
 * <?php
 * class example_view extends Psa_Dully_View{
 *
 * 	// Render even_numbers.tpl template
 * 	function even_numbers(){
 * 		$rendered_result = $this->psa_dully->fetch("example_app/even_numbers.tpl");
 * 		$this->psa_dully->assign("result", $rendered_result);
 * 	}
 *
 * 	// Main layout render and display method.
 * 	function generate_html(){
 *
 * 		$rendered_menu = $this->psa_dully->fetch("example_app/menu.tpl");
 * 		$rendered_header = $this->psa_dully->fetch("example_app/header.tpl");
 *
 * 		$this->psa_dully->assign("header", $rendered_header);
 * 		$this->psa_dully->assign("menu", $rendered_menu);
 *
 * 		$this->psa_dully->display("example_app/main.tpl");
 * 	}
 * }
 * ?>
 * </code>
 *
 * @see Psa_Dully
 * @see Psa_Smarty_View
 */
class Psa_Dully_View extends Psa_View{


	/**
	 * Reference to {@link Psa_Dully} template engine object.
	 *
	 * @var Psa_Dully
	 */
	public $psa_dully;



	/**
	 * Constructor.
	 */
	function __construct(){

		parent::__construct();

		// set reference to $Psa_Dully object if exists
		if(!$this->psa_registry->psa_dully instanceof Psa_Dully){

			// include dully class file
			include_once PSA_BASE_DIR . '/lib/Psa_Dully.php';

			$this->psa_registry->psa_dully = new Psa_Dully;

			// folder with templates
			$this->psa_dully->template_dir = $this->psa_registry->PSA_CFG['folders']['dully']['template_dir'];

			// assign result object to dully
			$this->psa_registry->psa_dully->assign('psa_result', $this->psa_result);
			$this->psa_registry->psa_dully->assign('r', $this->psa_result);
		}

		$this->psa_dully = $this->psa_registry->psa_dully;
	}
}
