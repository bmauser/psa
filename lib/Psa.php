<?php
/**
 * @package PSA
 */


// @asFunction Session $_SESSION[] propSelector
/**
 * 
 * @todo
 *
 */
class Psa{

	
	// * @asFunction Res stdClass getInstance
	
	/**
	 * @asFunction Psa Psa::get_new_instance() getInstance
	 * 
	 * @return Psa
	 */
	static function get_new_instance(){
		return new Psa();
	}
	
	
	/**
	 * @asFunction Cfg Psa::get_config_array() propSelector
	 *
	 * @return Array
	 */
	static function &get_config_array(){
	
		static $PSA_CFG = null;
	
		if($PSA_CFG === null){
			include PSA_BASE_DIR . '/config.php';
		}
	
		return $PSA_CFG;
	}
	
}
