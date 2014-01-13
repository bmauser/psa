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
 * @version $Id: Psa_Dully.php 142 2013-09-26 17:10:52Z bmauser $
 */


/**
 * Simple template engine class.
 *
 * This class has few methods that are similar to those in {@link http://www.smarty.net Smarty}
 * template engine.
 *
 * All that Dully does is putting values from an associative array into the local namespace and
 * includes .php (template) file. There are no special template tags, just use PHP code
 * blocks to echo values. Templates are ordinary PHP files and you can write them
 * as any other .php script, but keep in mind that point of the template engine
 * is separating business logic from presentation.
 * So, you should not put any logic (calculations, getting data from a database ...)
 * into the templates. With {@link http://www.smarty.net Smarty} that is easier to achieve,
 * and it has many advanced features so I suggest that you use Smarty as a template engine.
 * If you need a simple and fast template engine or don't want to learn Smarty's tags, you can
 * use Dully.
 * Dully class doesn't include anything from PSA so you can use this class as template engine
 * in any PHP application.
 *
 * I named this class Dully as opposite to Smarty. This class is inspired from 
 * {@link http://www.massassi.com/php/articles/template_engines/ here}
 * and there are some interesting thoughts about template engines.
 *
 * <br><b>Usage examples:</b>
 *
 * <br><b>1)</b> Simple example with one template file:
 *
 * Template file <samp>template.tpl</samp>:
 * <code>
 * This is a text from template file template.php<br/>
 * My car is <?php echo $my_car_color ?>.<br/>
 * Something else: <?php echo $some_name ?>
 * </code>
 *
 * .php file:
 * <code>
 * <?php
 *
 * // make a new Psa_Dully object
 * $Psa_Dully = new Psa_Dully('path/to/dir/with/templates');
 *
 * // assign some data for template
 * $Psa_Dully->assign('my_car_color', 'Black');
 * $Psa_Dully->assign('some_name', 'bla bla bla');
 *
 * // display fetched template
 * $Psa_Dully->display('template.tpl');
 *
 * ?>
 * </code>
 *
 * The printout of the above .php script will be:
 * <pre>
 * This is a text from template file template.php
 * My car is Black.
 * Something else: bla bla bla
 * </pre>
 *
 * <b>Note:</b> you can write <kbd><?= $some_name ?></kbd> instead of <kbd><?php echo $some_name ?></kbd>.
 * Use of this shortcut requires {@link http://www.php.net/manual/en/ini.core.php#ini.short-open-tag short_open_tag}
 * PHP ini option to be on.
 *
 * <br><b>2)</b> You can put one rendered template into another:
 *
 * template file <samp>template1.tpl</samp>:
 * <code>
 * This is a text from template1.php<br/>
 * <?php echo $var1 ?><br/>
 * <?php echo $var2 ?>
 * </code>
 *
 * template file <samp>template2.tpl</samp>:
 * <code>
 * This is a text from template2.php
 * </code>
 *
 * .php file:
 * <code>
 * <?php
 *
 * // make a new Psa_Dully object
 * $Psa_Dully = new Psa_Dully('path/to/dir/with/templates');
 *
 * // put the content of fetched template template2.tpl in the variable $var1
 * $var1 = $Psa_Dully->fetch('template2.tpl');
 *
 * // assign values for var1 and var2
 * $Psa_Dully->assign('var1', $var1);
 * $Psa_Dully->assign('var2', 'bla bla bla');
 *
 * // display template template1.tpl
 * $Psa_Dully->display('template1.tpl');
 *
 * ?>
 * </code>
 *
 * The above example will output:
 * <pre>
 * This is a text from template1.php
 * This is a text from template2.php
 * bla bla bla
 * </pre>
 *
 * <br><b>3)</b> In this kind of templates it's nice to use alternative PHP syntax for control structures.
 * See details in {@link http://www.php.net/manual/en/control-structures.alternative-syntax.php PHP documentation}.
 * <code>
 * <? if ($variable == 'abcd'): ?>
 * 	<h1>this is something</h1>
 * 	<p>
 * 		bla bla
 * 	</p>
 * <? endif ?>
 *
 * <? foreach ($array as $value): ?>
 * 	<div><?= $value['email']) ?></div>
 * 	<div><?= $value['phone_number']) ?></div>
 * <? endforeach ?>
 * </code>
 *
 *
 * @see Psa_Dully_View
 * @see Psa_Smarty
 */
class Psa_Dully{

	/**
	 * Array that holds template values.
	 *
	 * @var array
	 * @ignore
	 */
	protected $template_values = array();


	/**
	 * Path to folder with templates.
	 * Without / at the end. It is set by constructor.
	 *
	 * @var string
	 * @ignore
	 */
	protected $template_dir;


	/**
	 * Constructor.
	 *
	 * @param string $template_dir Path to the the directory with templates.
	 */
	function __construct($template_dir){
		$this->template_dir = $template_dir;
	}


	/**
	 * Assigns values to the templates.
	 * See examples in {@link Psa_Dully} class description.
	 *
	 * @param string $name The name of the variable being assigned.
	 * @param mixed $value The value being assigned.
	 * @see display()
	 * @see fetch()
	 */
	function assign($name, $value){
		$this->template_values[$name] = $value;
	}


	/**
	 * Returns the fetched template as a string.
	 * See examples in {@link Psa_Dully} class description.
	 *
	 * @param string $template_file Template file name or relative path from the path passed to the constructor.
	 * @return string Fetched (rendered) template.
	 * @see display()
	 */
	function fetch($template_file){

		// just to be sure that variable will not be overwritten with extract()
		$sdf33saf2342as8dmm32 = $template_file;

		// extract the template_values to local namespace
		extract($this->template_values);

		// start output buffering
		ob_start();

		// include the file
		include $this->template_dir . '/' . $sdf33saf2342as8dmm32;

		// get the contents and clean the buffer
		return ob_get_clean();
	}


	/**
	 * Outputs the fetched template.
	 *
	 * Just prints output from {@link fetch()} method.
	 *
	 * @param string $template_file Template file name or relative path from path passed to the constructor.
	 * @see fetch()
	 */
	function display($template_file){
		echo $this->fetch($template_file);
	}
}

