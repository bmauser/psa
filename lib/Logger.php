<?php
/**
 * @package PSA
 */


/**
 * This class handles logging.
 *
 * Logs are by default written to database into <i>psa_log</i> table, but can be also
 * written to a file depending on the settings in {@link config.php} file.
 *
 * <br><b>Example:</b>
 *
 * <br><b>1)</b> Log a single message.
 * <code>
 * Logger()->log("Log message");
 * </code>
 * 
 * @asFunction Logger Logger getInstance
 */
class Logger{

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
	 * @var Db
	 * @ignore
	 */
	protected $db = null;


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
	 *     [function] => User::save  // method or function name that created the log record
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
	 * Logger()->log($log_data);
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
	 * @see formatFileLog()
	 * @see format_database_log()
	 * @see config.php
	 */
	public function log($log_data, $log_storage = 'psa_default'){

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
		if((!isset($log_data['user_id']) or !$log_data['user_id']) && isset(Session()['psa_current_user_data']['id'])){
			$log_data['user_id'] = Session()['psa_current_user_data']['id'];
		}
		if((!isset($log_data['username']) or !$log_data['username']) && isset(Session()['psa_current_user_data']['username'])){
			$log_data['username'] = Session()['psa_current_user_data']['username'];
		}


		// if logging is enabled
		if(Cfg('logging.max_log_level') >= $log_data['level']){

			// write log to file
			if(Cfg("logging.storage.{$log_storage}.type") == 'file'){

				// format log message
				$message = $this->formatFileLog($log_data);

				// open log file
				if (!$handle = fopen(Cfg("logging.storage.{$log_storage}.target"), 'a')){
					trigger_error('Cannot open log file: ' . Cfg()['logging']['storage'][$log_storage]['target']);
					return 0;
				}

				// write to file
				else{
					// if error writing to file
					if (!fwrite($handle, $message)){
						trigger_error('Cannot write to log file: ' . Cfg()['logging']['storage'][$log_storage]['target']);
						fclose($handle);
						return 0;
					}

					// close file
					fclose($handle);
					return 1;
				}
			}
			// write log to database
			else if(Cfg()['logging']['storage'][$log_storage]['type'] == 'database'){

				if(!($this->db instanceof Db)){

					// should new database connection be opened
					if(isset(Cfg()['logging']['new_database_connection'])){
						
						if(!(@Reg()->psa_log_database_connection instanceof Db)){
							Reg()->psa_log_database_connection = Db();
						}
						$this->db = Reg()->psa_log_database_connection;
					}
					else{
						// existing database connection
						$this->db = Reg()->psa_database;
					}
				}

				// prepare database query if not already prepared
				if(!$this->prepared_query){
					$this->prepared_query = $this->db->prepare($this->formatDatabaseLogQuery($log_storage));
				}

				// run query against the database
				try{
					$this->db->execute($this->formatDatabaseLogQueryParams($log_data), $this->prepared_query, 1);
				}
				catch(PDOException $e){

					$message = 'Unable to write log message to database table ' . Cfg()['logging']['storage'][$log_storage]['target'] . ' ' . $e->getMessage();

					// log message cannot be written into the database if there is problem with database connection
					if(Cfg()['logging']['storage']['psa_default']['type'] == 'database'){
						trigger_error($message);
						throw new LoggerException($message, $e->getCode(), false);
					}
					else
						throw new LoggerException($message, $e->getCode());
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
	 * This method has to have the matching {@link formatDatabaseLogQueryParams()} method.
	 *
	 * @param int $log_storage see {@link log()} method
	 * @see formatDatabaseLogQueryParams()
	 * @see formatFileLog()
	 * @see log()
	 * @return string
	 */
	protected function formatDatabaseLogQuery($log_storage){

		// log query. Should be formated for prepared query.
		return "INSERT INTO " . Cfg("logging.storage.{$log_storage}.target") . " (client_ip, log_time, request_uri, user_agent, referer, type, username, user_id, message, function, group_id, groupname) VALUES (?,NOW(),?,?,?,?,?,?,?,?,?,?)";
	}


	/**
	 * Returns array of parameters for prepared query from {@link formatDatabaseLogQuery()} method.
	 *
	 * @param array $log_data see {@link log()} method
	 * @param int $log_storage see {@link log()} method
	 * @see formatDatabaseLogQuery()
	 * @see formatFileLog()
	 * @see log()
	 * @return array
	 */
	protected function formatDatabaseLogQueryParams($log_data){

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
	protected function formatFileLog($log_data){

		// format log message
		if(isset(Cfg()['logging']['more_lines'])){
			$new_line = "\r\n";
			$message = "\r\n" . '[' . date(Cfg('logging.time_format')) . "] " . $new_line . '=====================' . $new_line;
		}
		else{
			$new_line = '';
			$message = '[' . date(Cfg('logging.time_format')) . "] " . $new_line;
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
