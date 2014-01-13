<?php
/**
 * Class for profile log.
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
 * @package PSA
 * @version $Id: Psa_Profile_Logger.php 171 2013-12-11 17:43:52Z bmauser $
 * @ignore
 */


/**
 * This class handles profile logging.
 * 
 * @ignore
 */
class Psa_Profile_Logger extends Psa_Logger{


	/**
	 * returns Psa_Profile_Logger instance
	 */
	public static function get_instance($class = null){
		return parent::get_instance(__CLASS__);
	}


	/**
	 * Calls parent log method with 'psa_profile' as default storage
	 */
	public function log($log_data,$log_storage = 'psa_profile'){
		parent::log($log_data,$log_storage);
	}


	/**
	 * returns database query
	 */
	protected function format_database_log_query($log_storage){

		// log query. Should be formated for prepared query.
		return "INSERT INTO " . Psa_Registry::get_instance()->PSA_CFG['logging']['storage'][$log_storage]['target'] . " (method, total_time, method_arguments, client_ip, log_time, request_id) VALUES (?,?,?,?,NOW(),?)";
	}


	/**
	 * returns parameters for prepared query
	 */
	protected function format_database_log_query_params($log_data){

		// parameters for prepared log query
		return array(
			$log_data['method'],
			$log_data['total_time'],
			$log_data['method_arguments'],
			@$_SERVER["REMOTE_ADDR"],
			$log_data["request_id"]
		);
	}
}
