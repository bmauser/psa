<?php
/**
 * Front controller script.
 *
 * Your site index.php shoud only include this file, so all requests
 * will go through this script. It includes main PSA files, instances some objects and
 * calls psa_main() method of the Main class.
 *
 * @package PSA
 */






// PSA main directory
define('PSA_BASE_DIR', __DIR__);






// include PSA config file
include PSA_BASE_DIR . '/config.php';


// Error reporting. Enable or disable this through $PSA_CFG['develop_mode'] configuration option
if($PSA_CFG['develop_mode']){
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
}
else{
	ini_set('display_errors', 0);
}


// include required files
include PSA_BASE_DIR . '/wri/asfunctions.php';
include PSA_BASE_DIR . '/lib/functions.php';
include PSA_BASE_DIR . '/lib/Psa_Singleton.php';
include PSA_BASE_DIR . '/lib/Db.php';
include PSA_BASE_DIR . '/lib/Logger.php';
include PSA_BASE_DIR . '/lib/PreInit.php';
include PSA_BASE_DIR . '/lib/Registry.php';



// put $PSA_CFG config array to the registry
//Reg()->PSA_CFG = $PSA_CFG;


// register files on every request
if($PSA_CFG['develop_mode'] && $PSA_CFG['develop_mode_register_files']){
	Files()->save();
}


// register autoloader() function as __autoload() implementation
spl_autoload_register('autoloader');



// if in web mode
if(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST']){
	// get application base URL
	if(isset($PSA_CFG['folders']['basedir_web']))
		Reg()->basedir_web = $PSA_CFG['folders']['basedir_web'];
	else
		Reg()->basedir_web = str_replace('/' . basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
	Reg()->base_url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . Reg()->basedir_web;
}


// include Main.php file
include PSA_BASE_DIR . '/' . $PSA_CFG['folders']['autoload'][0] . '/Main.php';




// call application
$main = new Main;
$main->psa_main();
