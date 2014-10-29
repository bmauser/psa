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
 * @version $Id: Psa_Logger.php 171 2013-12-11 17:43:52Z bmauser $
 */


/**
 * This class handles logging.
 *
 * Logs are by default written to database into <i>psa_log</i> table, but can be also
 * written to a file depending on the settings in {@link config.php} file.
 *
 * This class implements {@link http://en.wikipedia.org/wiki/Singleton_pattern singleton pattern}
 * to ensure that there is only one instance of the {@link Psa_Logger} object and
 * to allow that this single instance is easily accessible from any scope.
 * You cannot not make an instance of {@link Psa_Logger} object with the <kbd>new</kbd> operator,
 * call static method {@link get_instance()} instead.
 *
 * <br><b>Examples:</b>
 *
 * <br><b>1)</b> Log a single message.
 * <code>
 * Psa_Logger::get_instance()->log("Log message");
 * </code>
 *
 * <br><b>2)</b> If you want to extend this class you can write your child class like this:
 * <code>
 * <?php
 * class My_Logger extends Psa_Logger
 * {
 *    // returns My_Logger instance
 *    public static function get_instance()
 *    {
 *       return parent::get_instance(__CLASS__);
 *    }
 *
 *    // your method for database query
 *    function format_database_log_query($log_storage){
 *    		//...
 *    }
 *
 *    // your method for parameters for prepared query
 *    function format_database_log_query_params($log_data){
 *    		//...
 *    }
 * }
 *
 * // call log method
 * My_Logger::get_instance()->log("log message");
 * ?>
 * </code>
 */
class Psa_Logger extends Psa_Singleton{

	/**
	 * Prepared query.
	 *
	 * @var PDOStatement
	 * @ignore
	 */
	protected $prepared_query = null;


	/**
	 * Database connection.
	 *
	 * @var Psa_PDO
	 * @ignore
	 */
	protected $database = null;


	/**
	 * Returns the instance of the {@link Psa_Logger} object.
	 *
	 * You should statically call this method with the scope resolution operator (::) which gives you
	 * the access to the logging object from anywhere in your application, whether it is from a function,
	 * a method, or the global scope.
	 * Example:
	 * <code>
	 * Psa_Logger::get_instance()->log("Log message");
	 * </code>
	 *
	 * @return object Psa_Logger
	 */
	public static function get_instance($class = null){
		// this is needed that this class can be extended
		if($class)
			return parent::get_instance($class);
		else
			return parent::get_instance(__CLASS__);
	}


	/**
	 * Writes logs.
	 *
	 * Writes a log record to the database or a file. This depends on the settings (<var>$PSA_CFG['logging']</var>)
	 * in {@link config.php}. First argument can be an array or a string. If it's a string, it is written to the log.
	 * If it's an array, some other values in the log record can be set. Here is an example array with all
	 * the available elements and values that can be used as the first argument to this method.
	 * <pre>
	 * Array
	 * (
	 *     [user_id] => 5                // user ID
	 *     [username] => john            // username
	 *     [group_id] => 3               // group ID
	 *     [groupname] => test           // group name
	 *     [message] => new user created // log message
	 *     [function] => Psa_User::save  // method or function name that created the log record
	 *     [level] => 2                  // Log level, default 1. Integer. If greater than <var>$PSA_CFG['logging']['level']</var> log will not be saved.
	 *     [type] => general             // Log message type. Class name for exceptions or custom string value.
	 * )
	 * </pre>
	 * All array elements are optional.
	 *
	 * <b>Example:</b>
	 *
	 * <code>
	 * <?php
	 *
	 * // set data to log
	 * $log_data['user_id']  = 123;
	 * $log_data['username'] = 'abc';
	 * $log_data['message']  = 'test message';
	 * $log_data['function'] = 'some_method';
	 * $log_data['level']    = 1;
	 * $log_data['type']     = 'some_type';
	 *
	 * // write log
	 * Psa_Logger::get_instance()->log($log_data);
	 *
	 * ?>
	 * </code>
	 *
	 * @param array|string $log_data Array with data to be logged or log message only.
	 * @param int|string $log_storage Storage for the log. This is the index of <var>$PSA_CFG['logging']['storage']</var>
	 * array in {@link config.php}. Default is 'psa_default'.
	 * @return int 0 for failure, 1 for success, -1 logging is disabled or the log level for the message is set too low
	 * (bigger number than <var>$PSA_CFG['logging']['max_log_level'] </var>).
	 * @see get_instance()
	 * @see format_file_log()
	 * @see format_database_log()
	 * @see config.php
	 */
	public function log($log_data, $log_storage = 'psa_default'){

		// config array
		$PSA_CFG = Psa_Registry::get_instance()->PSA_CFG;

		// if $log_data is not array
		if(!is_array($log_data)){
			$msg = (string)$log_data;
			$log_data = array();
			$log_data['message'] = $msg;
		}

		// default log level
		if(!isset($log_data['level']) or !$log_data['level'])
			$log_data['level'] = 1;

		// default log type
		if(!isset($log_data['type']) or !$log_data['type'])
			$log_data['type'] = null;

		// if not set user data, use data from $_SESSION['psa_current_user_data']
		if(!@$log_data['user_id'] && !@$log_data['username'] && isset($_SESSION['psa_current_user_data']) && $_SESSION['psa_current_user_data'] instanceof Psa_User){
			$log_data['user_id'] = $_SESSION['psa_current_user_data']['id'];
			$log_data['username'] = $_SESSION['psa_current_user_data']['username'];
		}


		// if logging is enabled
		if($PSA_CFG['logging']['max_log_level'] >= $log_data['level']){

			// write log to file
			if($PSA_CFG['logging']['storage'][$log_storage]['type'] == 'file'){

				// format log message
				$message = $this->format_file_log($log_data);

				// open log file
				if (!$handle = fopen($PSA_CFG['logging']['storage'][$log_storage]['target'], 'a')){
					trigger_error("Cannot open log file: {$PSA_CFG['logging']['storage'][$log_storage]['target']}");
					return 0;
				}

				// write to file
				else{
					// if error writing to file
					if (!fwrite($handle, $message)){
						trigger_error("Cannot write to log file: {$PSA_CFG['logging']['storage'][$log_storage]['target']}");
						fclose($handle);
						return 0;
					}

					// close file
					fclose($handle);
					return 1;
				}
			}
			// write log to database
			else if($PSA_CFG['logging']['storage'][$log_storage]['type'] == 'database'){

				if(!($this->database instanceof Psa_PDO)){

					// should new database connection be opened
					if(@$PSA_CFG['logging']['new_database_connection']){
						$psa_registry = Psa_Registry::get_instance();
						if(!(@$psa_registry->psa_log_database_connection instanceof Psa_PDO)){
							$psa_registry->psa_log_database_connection = new Psa_PDO();
						}
						$this->database = $psa_registry->psa_log_database_connection;
					}
					else{
						// existing database connection
						$this->database = Psa_Registry::get_instance()->psa_database;
					}
				}

				// prepare database query if not already prepared
				if(!$this->prepared_query){
					$this->prepared_query = $this->database->prepare($this->format_database_log_query($log_storage));
				}

				// run query against the database
				try{
					$this->database->execute($this->format_database_log_query_params($log_data), $this->prepared_query, 1);
				}
				catch(PDOException $e){

					$message = "Unable to write log message to database table '{$PSA_CFG['logging']['storage'][$log_storage]['target']}' " . $e->getMessage();

					// log message cannot be written into the database if there is problem with database connection
					if($PSA_CFG['logging']['storage']['psa_default']['type'] == 'database'){
						trigger_error($message);
						throw new Psa_Logger_Exception($message, $e->getCode(), false);
					}
					else
						throw new Psa_Logger_Exception($message, $e->getCode());
				}

				return 1;
			}
		}
		else
			return -1;
	}


	/**
	 * Returns SQL insert query that inserts row to the <i>psa_log</i> database table.
	 *
	 * Override this method if you want to change the log format in the database. You will have
	 * to manually alter the log database table if you wish to add some new columns into it.
	 * This method has to have the matching {@link format_database_log_query_params()} method.
	 *
	 * @param int $log_storage see {@link log()} method
	 * @see format_database_log_query_params()
	 * @see format_file_log()
	 * @see log()
	 * @return string
	 */
	protected function format_database_log_query($log_storage){

		// log query. Should be formated for prepared query.
		return "INSERT INTO " . Psa_Registry::get_instance()->PSA_CFG['logging']['storage'][$log_storage]['target'] . " (client_ip, log_time, request_uri, user_agent, referer, type, username, user_id, message, function, group_id, groupname) VALUES (?,NOW(),?,?,?,?,?,?,?,?,?,?)";
	}


	/**
	 * Returns array of parameters for prepared query from {@link format_database_log_query()} method.
	 *
	 * @param array $log_data see {@link log()} method
	 * @param int $log_storage see {@link log()} method
	 * @see format_database_log_query()
	 * @see format_file_log()
	 * @see log()
	 * @return array
	 */
	protected function format_database_log_query_params($log_data){

		// parameters for prepared log query
		return array(@$_SERVER["REMOTE_ADDR"],
			@$_SERVER["REQUEST_URI"],
			@$_SERVER["HTTP_USER_AGENT"],
			@$_SERVER['HTTP_REFERER'],
			@$log_data['type'],
			@$log_data['username'],
			@$log_data['user_id'],
			@$log_data['message'],
			@$log_data['function'],
			@$log_data['group_id'],
			@$log_data['groupname']);
	}


	/**
	 * Formats a log message that will be written into the log file.
	 *
	 * Override this method if you want to change the log format.
	 *
	 * @param array $log_data see {@link log()} method
	 * @see format_database_log()
	 * @see log()
	 * @return string
	 */
	protected function format_file_log($log_data){

		// config array
		$PSA_CFG = Psa_Registry::get_instance()->PSA_CFG;

		// format log message
		if(@$PSA_CFG['logging']['more_lines']){
			$new_line = "\r\n";
			$message = "\r\n" . '[' . date($PSA_CFG['logging']['time_format']) . "] " . $new_line . '=====================' . $new_line;
		}
		else{
			$new_line = '';
			$message = '[' . date($PSA_CFG['logging']['time_format']) . "] " . $new_line;
		}

		if(@$log_data['message'])
			$message .= $log_data['message'] . $new_line;
		if(@$_SERVER['REMOTE_ADDR'])
			$message .= " IP={$_SERVER['REMOTE_ADDR']}" . $new_line;
		if(@$log_data['username'])
			$message .= " USER={$log_data['username']}" . $new_line;
		if(@$log_data['user_id'])
			$message .= " UID={$log_data['user_id']}" . $new_line;
		if(@$log_data['grouname'])
			$message .= " GROUP={$log_data['grouname']}" . $new_line;
		if(@$log_data['group_id'])
			$message .= " GID={$log_data['group_id']}" . $new_line;
		if(@$log_data['function']){
			if(!$new_line)
				$message .= ' ' . trim('FUNCTION=' . str_replace(array("\r\n","\n"), ' ', $log_data['function'])) . $new_line;
			else
				$message .= ' ' . trim('FUNCTION=' .  $log_data['function']) . $new_line;
		}
		if(@$log_data['type'])
			$message .= " TYPE={$log_data['type']}" . $new_line;
		if(@$_SERVER['REQUEST_URI'])
			$message .= " REQUEST_URI={$_SERVER['REQUEST_URI']}" . $new_line;
		if(@$_SERVER['HTTP_USER_AGENT'])
			$message .= " USER_AGENT={$_SERVER['HTTP_USER_AGENT']}" . $new_line;
		if(@$_SERVER['HTTP_REFERER'])
			$message .= " REFERER={$_SERVER['HTTP_REFERER']}" . $new_line;

		return $message . "\n";
	}
}
