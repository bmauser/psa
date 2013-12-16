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
 * @package PSA\exceptions
 * @version $Id: Psa_Fatal_Error_Exception.php 38 2011-10-18 00:18:43Z bmauser $
 */


include_once 'Psa_Exception.php';


/**
 * Psa_Fatal_Error_Exception class.
 *
 * If you raise <kbd>Psa_Fatal_Error_Exception</kbd> exception anywhere in your application,
 * execution of the script will be terminated and message you provide as first argument
 * to constructor will be logged.
 *
 * <b>Example:</b>
 * <code>
 * class example_model extends Psa_Model {
 *
 *     function example_method(){
 *
 *         // some code here
 *
 *         // die and write message to log
 *         if($fatal_error)
 *             throw new Psa_Fatal_Error_Exception('This message will be in logs');
 * 	}
 * }
 * </code>
 */
class Psa_Fatal_Error_Exception extends Psa_Exception{

	/**
	 * Constructor
	 *
	 * @param string $log_message message to be logged
	 * @param string $echo_message message to be sent to output
	 * @param string $code user defined exception code
	 * @param bool $write_log set to false if you don't want to write exception to log
	 */
	public function __construct($log_message, $echo_message = null, $code = 0, $write_log = true){

		// make sure everything is assigned properly
		parent::__construct($log_message, $code, $write_log);

		exit($echo_message);
	}
}
