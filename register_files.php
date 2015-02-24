<?php
/**
 * Files registration script.
 * 
 * This script does file registration, it actually calls {@link PreInit::register()} 
 * and {@link PreInit::save()} methods.
 * See their documentation for details about the file registration process.
 * When you add some new classes to your project that you want to autoload
 * you must register files to recreate autoload_data.php file.
 * 
 * If options <var>$PSA_CFG['develop_mode']</var> and <var>$PSA_CFG['develop_mode_register_files']</var> 
 * are true, registration is done on every request so you don't need to call this script.
 * This script is also useful on production site when you pull some new classes that need to be 
 * autoloaded.
 * 
 * <b>Note:</b> This script can be called only in CLI mode.
 *
 * @package PSA
 */

/**
 *
 */


// allow execution only in cli mode
if(php_sapi_name() !== 'cli'){
	exit('This script can be run only in CLI mode.');
}


// PSA main dir
define('PSA_BASE_DIR', __DIR__);


// include required files
include PSA_BASE_DIR . '/lib/Psa.php';
include PSA_BASE_DIR . '/config.php';
include PSA_BASE_DIR . '/lib/PreInit.php';
include PSA_BASE_DIR . '/lib/Registry.php';
include PSA_BASE_DIR . '/lib/functions.php';
include PSA_BASE_DIR . '/wri/asfunctions.php';


$PSA_CFG['logging']['max_log_level'] = 0; // disable logging

// put PSA config array to registry
//Reg()->PSA_CFG = $PSA_CFG;

// register files
$files_data = Files()->register();

// save to file
Files()->save($files_data);

// echo results
print_r($files_data);

echo "\nOK. Autoload data saved to {$PSA_CFG['autoload_data_file']}\n";

