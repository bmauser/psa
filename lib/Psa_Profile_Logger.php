<?php
/**
 * @package PSA
 */


/**
 * This class handles profile logging.
 * 
 * @asFunction ProfileLogger Psa_Profile_Logger getInstance
 * @ignore
 */
class Psa_Profile_Logger extends Psa_Logger{


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
		return "INSERT INTO " . Cfg("logging.storage.{$log_storage}.target") . " (method, total_time, method_arguments, client_ip, log_time, request_id) VALUES (?,?,?,?,NOW(),?)";
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
