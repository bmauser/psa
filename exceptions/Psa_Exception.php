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
 * @version $Id: Psa_Exception.php 71 2013-04-03 00:05:35Z bmauser $
 */


/**
 * Psa_Exception class.
 *
 * This class extends PHP Exception and adds {@link log()} method
 * for writing PSA log. Other PSA exception classes extend this class.
 * You can also extend this class for your custom exceptions. If logs are enabled
 * in {@link config.php} file every exception message will be logged.
 */
class Psa_Exception extends Exception{


	/**
	 * Constructor.
	 *
	 * Calls {@link log()} method which writes message to log.
	 *
	 * @param string|array $message exception message or associative
	 * array with values for {@link log()} method.
	 * @param string $code user defined exception code
	 * @param bool $write_log set to false if you don't want to write exception to log
	 */
	public function __construct($message = '', $code = 0, $write_log = true){

		if(is_array($message))
			$exception_message = $message['message'];
		else
			$exception_message = $message;

		// make sure everything is assigned properly
		parent::__construct($exception_message, (int)$code); //$code can be string here

		// write log
		if($write_log)
			$this->log($message);
	}


	/**
	 * Writes a log message.
	 *
	 * @param string|array $log_message message to be logged or associative
	 * array with values for {@link Psa_Logger::log()} method. If you pass
	 * array some elements, if not set, will get default values.
	 * Default values are:
	 * - for <kbd>'function'</kbd> element is return from <kbd>getTraceAsString()</kbd>
	 *   exception method
	 * - for <kbd>'level'</kbd> element is 1
	 * - for <kbd>'type'</kbd> element is <kbd>'exception'</kbd>.
	 *
	 * @see Psa_Logger::log()
	 */
	protected function log($log_message){

		// if logging is enabled
		if(Psa_Registry::get_instance()->PSA_CFG['logging']['max_log_level'] >= 1){
			// parameters for Psa_Logger::log() method
			if(is_array($log_message)){
				$log_data = &$log_message;
			}
			else
				$log_data['message'] = $log_message;

			if(!@$log_data['function'])
				$log_data['function'] = $this->getTraceAsString();

			if(!@$log_data['level'])
				$log_data['level'] = 1;

			if(!@$log_data['type'])
				$log_data['type'] = get_class($this);

			Psa_Logger::get_instance()->log($log_data);
		}
	}
}
