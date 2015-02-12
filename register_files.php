<?php
/**
 * Files registration script.
 * 
 * This script does file registration, it actually calls {@link Psa_Files::register()} 
 * and {@link Psa_Files::save()} methods.
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
 * @link http://code.google.com/p/phpstartapp/
 * @author Bojan Mauser <bmauser@gmail.com>
 * @package PSA
 * @see Psa_Files::register()
 * @see Psa_Files::save()
 * @version $Id: register_files.php 142 2013-09-26 17:10:52Z bmauser $
 */

/**
 *
 */


// allow execution only in cli mode
if(php_sapi_name() !== 'cli'){
	exit('This script can be run only in CLI mode.');
}


// PSA main dir
define('PSA_BASE_DIR', dirname(__FILE__));


// include required files
include PSA_BASE_DIR . '/config.php';
include PSA_BASE_DIR . '/lib/Psa_Singleton.php';
include PSA_BASE_DIR . '/lib/Psa_Files.php';
include PSA_BASE_DIR . '/lib/Psa_Registry.php';
include PSA_BASE_DIR . '/lib/functions.php';


$PSA_CFG['logging']['max_log_level'] = 0; // disable logging

// put PSA config array to registry
Psa_Registry::get_instance()->PSA_CFG = $PSA_CFG;

// register files
$files_data = Psa_Files::get_instance()->register();

// save to file
Psa_Files::get_instance()->save($files_data);

// echo results
print_r($files_data);

echo "\nOK. Autoload data saved to {$PSA_CFG['autoload_data_file']}\n";

