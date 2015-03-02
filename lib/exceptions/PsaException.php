<?php
/**
 * @package PSA/exceptions
 */


/**
 * This class extends PHP Exception and adds method for logging.
 * 
 * Other PSA exception classes extend this class. You can also extend this class 
 * for your custom exceptions. If logs are enabled in {@link config.php} file 
 * exception messages will be logged.
 */
class PsaException extends Exception{


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
	 * array with values for {@link Logger::log()} method. If you pass
	 * array some elements, if not set, will get default values.
	 * Default values are:
	 * - for <kbd>'function'</kbd> element is return from <kbd>getTraceAsString()</kbd>
	 *   exception method
	 * - for <kbd>'level'</kbd> element is 1
	 * - for <kbd>'type'</kbd> element is <kbd>'exception'</kbd>.
	 *
	 * @see Logger::log()
	 */
	protected function log($log_message){

		// if logging is enabled
		if(Cfgn('logging.enabled')){
			
			if(is_array($log_message))
				$log_data = &$log_message;
			else
				$log_data['message'] = $log_message;

			if(!@$log_data['function'])
				$log_data['function'] = $this->getTraceAsString();

			if(!@$log_data['type'])
				$log_data['type'] = get_class($this);

			Logger()->info($log_data['message'], $log_data);
		}
	}
}
