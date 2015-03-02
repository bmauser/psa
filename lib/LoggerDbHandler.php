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
 * Logger()->info("Log message");
 * </code>
 * 
 * @asFunction Logger LoggerDbHandler::asFunction() getInstance
 */
class LoggerDbHandler extends Monolog\Handler\AbstractProcessingHandler{

	
	/**
	 * Database connection.
	 *
	 * @var Db
	 * @ignore
	 */
	protected $db = null;
	
	
	public function __construct($level = Monolog\Logger::DEBUG, $bubble = true, Db $db = null)
	{
		if($db)
			$this->db = $db;
		// should new database connection be opened
		else if(Cfgn('logging.new_database_connection'))
			$this->db = Db('_new');
		else
			$this->db = Db(); // default database connection
		
		parent::__construct($level, $bubble);
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
	 * $monolog_record['user_id']  = 123;
	 * $monolog_record['username'] = 'abc';
	 * $monolog_record['message']  = 'test message';
	 * $monolog_record['function'] = 'some_method';
	 * $monolog_record['level']    = 1;
	 * $monolog_record['type']     = 'some_type';
	 *
	 * // write log
	 * Logger()->warning($monolog_record);
	 *
	 * ?>
	 * </code>
	 *
	 * @param array|string $monolog_record Array with data to be logged or log message only.
	 * @param int|string $log_storage Storage for the log. This is the index of <var>$PSA_CFG['logging']['storage']</var>
	 * array in {@link config.php}. Default is 'psa_default'.
	 * @return int 0 for failure, 1 for success, -1 logging is disabled or the log level for the message is set too low.
	 * @see get_instance()
	 * @see formatFileLog()
	 * @see format_database_log()
	 * @see config.php
	 */
	protected function write(array $monolog_record){
		
		static $prepared_query = null;
		
		// prepare database query if not prepared
		if(!$prepared_query){
			$prepared_query = $this->db->prepare($this->getQuery());
		}
		
		// run query against the database
		try{
			return $this->db->execute($this->getQueryParams($monolog_record), $prepared_query, 1);
		}
		catch(PDOException $e){
		
			$message = 'Unable to write log message to database table ' . Cfg('database.table.log') . ' ' . $e->getMessage();
			
			trigger_error($message);
			
			throw new LoggerException($message, $e->getCode(), false);
		}
	}
	
	
	protected function getQueryParams(array $monolog_record){
		
		isset($monolog_record['extra']['ip']) ? $qparams[] = $monolog_record['extra']['ip'] : $qparams[] = null;
		isset($monolog_record['extra']['url']) ? $qparams[] = $monolog_record['extra']['url'] : $qparams[] = null;
		isset($monolog_record['extra']['user_agent']) ? $qparams[] = $monolog_record['extra']['user_agent'] : $qparams[] = null;
		isset($monolog_record['extra']['referrer']) ? $qparams[] = $monolog_record['extra']['referrer'] : $qparams[] = null;
		isset($monolog_record['extra']['type']) ? $qparams[] = $monolog_record['extra']['type'] : $qparams[] = null;
		isset($monolog_record['extra']['username']) ? $qparams[] = $monolog_record['extra']['username'] : $qparams[] = null;
		isset($monolog_record['extra']['user_id']) ? $qparams[] = $monolog_record['extra']['user_id'] : $qparams[] = null;
		isset($monolog_record['message']) ? $qparams[] = $monolog_record['message'] : $qparams[] = null;
		isset($monolog_record['function']) ? $qparams[] = $monolog_record['function'] : $qparams[] = null;
		isset($monolog_record['group_id']) ? $qparams[] = $monolog_record['group_id'] : $qparams[] = null;
		isset($monolog_record['groupname']) ? $qparams[] = $monolog_record['groupname'] : $qparams[] = null;
		$qparams[] = $monolog_record['level_name'];
		
		return $qparams;
	}
	
	
	protected function getQuery(){
		return "INSERT INTO " . Cfg('database.table.log') . " (client_ip, log_time, request_uri, user_agent, referer, type, username, user_id, message, function, group_id, groupname, level) VALUES (?,NOW(),?,?,?,?,?,?,?,?,?,?,?)";
	}
	
	
	public static function asFunction(){
	
		// create a log channel
		$logger = new Monolog\Logger('name');
		
		$wp = new Monolog\Processor\WebProcessor();
		$wp->addExtraField('user_agent', 'HTTP_USER_AGENT');
		$logger->pushProcessor($wp);
		
		$logger->pushProcessor(function ($monolog_record) {
			
			// if not set user data, use data from $_SESSION['psa_current_user_data']
			if((!isset($monolog_record['user_id']) or !$monolog_record['user_id']) && isset(Session()['psa_current_user_data']['id']))
				$monolog_record['extra']['user_id'] = Session()['psa_current_user_data']['id'];
			
			if((!isset($monolog_record['username']) or !$monolog_record['username']) && isset(Session()['psa_current_user_data']['username']))
				$monolog_record['extra']['username'] = Session()['psa_current_user_data']['username'];
			
			return $monolog_record;
		});
		
		$logger->pushHandler(new LoggerDbHandler());
		
		return $logger;
	}
}
