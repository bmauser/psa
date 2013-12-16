<?php
/**
 * Singleton class.
 *
 *
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
 * @version $Id: Psa_Singleton.php 142 2013-09-26 17:10:52Z bmauser $
 * @ignore
 */


/**
 * Singleton class.
 * Classes that needs to implement singleton pattern can extend this class.
 *
 * @see http://en.wikipedia.org/wiki/Singleton_pattern
 * @ignore
 */
class Psa_Singleton{

	/**
	 * Array with instances of objects. This object and child objects.
	 *
	 * @var array
	 * @ignore
	 */
	static private $instances = array();


	/**
	 * Constructor.
	 * The constructor is defined as protected that a instance of the this class
	 * cannot be created using the new operator from outside the class.
	 */
	protected function __construct(){}


	/**
	 * Returns object's instance.
	 * You should statically call this method with scope resolution operator (::) which gives you
	 * instance to the object from any scope in your application.
	 * Example:
	 * <code>
	 * $instance = Psa_Singleton::get_instance();
	 * </code>
	 *
	 * @return object
	 */
	static function get_instance($classname = null)
	{
		// determine object name
		if(!$classname)
			$classname = __CLASS__;

		// make new instance of object if not exists
		if (!isset(self::$instances[$classname])){
			self::$instances[$classname] = new $classname();
		}
		// return existing instance
		return self::$instances[$classname];
	}


	/**
	 * Clone method.
	 * Defined as private that a second instance of the {@link Psa_Singleton} class
	 * cannot be created using the clone operator.
	 * @ignore
	 */
	protected function __clone(){}
}
