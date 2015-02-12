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
 * @version $Id: Psa_Registry.php 142 2013-09-26 17:10:52Z bmauser $
 */


/**
 * Simple registry class.
 *
 * Registry object can be used to store some values or references to other objects to make
 * them globally accessible. You can use it as storage for data you want make accessible from any scope.
 * In <i>models</i> and <i>views</i> you can access <kbd>Psa_Registry</kbd> object through
 * reference in local scope.
 * For example, you can add property to <kbd>Psa_Registry</kbd> object in your model method
 * by
 * <code>
 * Reg()->my_value = '123';
 * </code>
 * It implements {@link http://en.wikipedia.org/wiki/Singleton_pattern singleton pattern}
 * so you can get reference to registry object from any scope with
 * {@link get_instance()} method. You cannot make instance of Psa_Registry object with
 * the <var>new</var> operator.
 * 
 * @asFunction Reg Psa_Registry propSelector
 */
class Psa_Registry {


	/**
	 * PSA configuration array.
	 * @var array
	 */
	public $PSA_CFG;


	/**
	 * Application configuration array.
	 *
	 * @var array
	 */
	public $CFG;


	/**
	 * Application folder from web root folder.
	 *
	 * @var string
	 */
	public $basedir_web;


	/**
	 * Application base URI.
	 *
	 * @var string
	 */
	public $base_url;


	/**
	 * Reference to Smarty template engine object.
	 *
	 * This instance is used by classes that extend {@link Psa_Smarty_View} class.
	 * @var Psa_Smarty
	 */
	public $psa_smarty = null;


	/**
	 * Reference to Dully template engine object.
	 *
	 * This instance is used by classes that extend {@link Psa_Dully_View} class.
	 * @var Psa_Dully
	 */
	public $psa_dully = null;

}
