<?php
/**
 * Files registration script.
 * 
 * This script does file registration, it actually calls {@link Psa_Files::register()} 
 * and {@link Psa_Files::save()} methods.
 * See their documentation for details about the file registration process.
 * When you add some new classes to your project that you want to autoload
 * you must register files to recreate autoload_data.php.
 * 
 * You don't have to use this script for file registration. It is here for your convenience, 
 * but you can do it in some of your model if you like, by calling these two methods:
 * <code>
 * // register files
 * $files_data = Psa_Files::get_instance()->register();
 * 
 * // save registered data
 * Psa_Files::get_instance()->save($files_data);
 * </code>
 * 
 * If {@tutorial psa_features.pkg#developmode develop mode} is on and 
 * $PSA_CFG['develop_mode_register_files'] config value is set, registration is done on every 
 * request so you don't need to call this script. This script can be useful on production 
 * site when you add some new classes that need to be autoloaded.
 * 
 * <b>Note:</b> This script can be called only in CLI mode.
 *
 * @link http://code.google.com/p/phpstartapp/
 * @author Bojan Mauser <bmauser@gmail.com>
 * @package PSA
 * @see Psa_Files::register()
 * @see Psa_Files::save()
 * @see psa_run_hooks()
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
include PSA_BASE_DIR . '/lib/Psa_Logger.php';
include PSA_BASE_DIR . '/lib/Psa_Files.php';
include PSA_BASE_DIR . '/lib/Psa_Registry.php';
include PSA_BASE_DIR . '/lib/functions.php';


// put PSA config array to registry
Psa_Registry::get_instance()->PSA_CFG = $PSA_CFG;

// register files
$files_data = Psa_Files::get_instance()->register();

// save to file
Psa_Files::get_instance()->save($files_data);

// echo results
echo "files data:";
print_r($files_data);

echo "\nOK. Autoload data saved to {$PSA_CFG['autoload_data_file']}\n";

