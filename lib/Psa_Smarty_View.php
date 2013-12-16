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
 * @version $Id: Psa_Smarty_View.php 142 2013-09-26 17:10:52Z bmauser $
 */


/**
 * Class for views that use {@link http://www.smarty.net Smarty} template engine.
 *
 * Classes that extends {@link Psa_Dully_View} or {@link Psa_Smarty_View} class are place for for methods that will
 * print results from your application. This can be HTML, XML or something else like plain text.
 * View methods, like model methods, are called from controller. All application logic should
 * be in model and in view you should put only part for displaying results (presentational logic) like
 * template rendering code.
 *
 * You can write <i>Psa_Smarty_View</i> views by extending this class. Here is an example:
 *
 * <code>
 * <?php
 * class example_view extends Psa_Smarty_View{
 *
 *     // Render even_numbers.tpl template and put it in "result" smarty value
 *     function even_numbers(){
 *         $rendered_result = $this->psa_smarty->fetch('even_numbers.tpl');
 *         $this->psa_smarty->assign("result", $rendered_result);
 *     }
 *
 *     // Main layout render and display method.
 *     function generate_html(){
 *
 *         $rendered_menu = $this->psa_smarty->fetch('menu.tpl');
 *         $rendered_header = $this->psa_smarty->fetch('header.tpl');
 *
 *         $this->psa_smarty->assign('header', $rendered_header);
 *         $this->psa_smarty->assign('menu', $rendered_menu);
 *
 *         echo $this->psa_smarty->fetch('example_app/main.tpl');
 *     }
 * }
 * ?>
 * </code>
 *
 * @see Psa_Dully_View
 * @see Psa_Smarty
 */
class Psa_Smarty_View extends Psa_View{


	/**
	 * Reference to Smarty template engine object.
	 * @var Psa_Smarty
	 */
	public $psa_smarty;



	/**
	 * Constructor.
	 */
	function __construct(){

		parent::__construct();

		// set reference to $psa_smarty object if exists
		if(!$this->psa_registry->psa_smarty instanceof Psa_Smarty){

			// include smarty class file
			include_once PSA_BASE_DIR . '/lib/Psa_Smarty.php';

			$this->psa_registry->psa_smarty = new Psa_Smarty;

			// assign result object to smarty
			$this->psa_registry->psa_smarty->assign('psa_result', $this->psa_result);
			$this->psa_registry->psa_smarty->assign('r', $this->psa_result);
		}

		$this->psa_smarty = $this->psa_registry->psa_smarty;
	}
}
