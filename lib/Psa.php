<?php
/**
 * @package PSA
 */


// @asFunction Session $_SESSION propSelector
/**
 * 
 * @todo
 *
 */
class Psa{

	protected $asFunction_instances;
	
	
	
	// * @asFunction Res stdClass getInstance
	
	// * @asFunction Psa Psa getInstance
	
	
	/**
	 * @asFunction Cfg Psa::getConfig() propSelector
	 * @asFunction Cfgn Psa::getConfig() propSelector exception=no
	 *
	 * @return Array
	 */
	static function &getConfig(){
	
		static $PSA_CFG = null;
	
		if($PSA_CFG === null){
			include PSA_BASE_DIR . '/config.php';
		}
	
		return $PSA_CFG;
	}
	
}
