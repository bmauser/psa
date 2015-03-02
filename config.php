<?php
/**
 * PSA configuration file.
 * 
 * @package PSA
 */


/**
 * Define PSA_BASE_DIR constant if not defined.
 */
if(!defined('PSA_BASE_DIR'))
	define('PSA_BASE_DIR', __DIR__); // PSA main directory


/**
 * PDO settings - database connection settings
 */
$PSA_CFG['db']['username'] = 'databaseUser';
$PSA_CFG['db']['password'] = 'databasePass';

// for MySQL:
//$PSA_CFG['db']['dsn']      = "mysql:host=localhost;port=3306;dbname=databaseName";
//$PSA_CFG['db']['driver_options'] = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);

// for PostgreSQL:
//$PSA_CFG['db']['dsn']      = "pgsql:host=localhost;port=5432;dbname=databaseName";
//$PSA_CFG['db']['driver_options'] = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);


/**
 * Develop mode.
 * If true, some settings optimized for developing will be set. On production site you
 * should set this to false.
 * - Smarty's force_compile option set to true
 *   (see {@link http://www.smarty.net/manual/en/variable.force.compile.php})
 * - logs turned on
 * - error reporting set to E_ALL
 */
$PSA_CFG['develop_mode'] = true;


/**
 * Should files be registered for autoloader on every request if develop_mode is on.
 * Note that te web server must have write permission to file set by 
 * $PSA_CFG['autoload_data_file'] option.
 */
$PSA_CFG['asFunction']['develop_mode_check'] = false;



/**
 * File with data about registered files for autoloading (full filesystem path).
 */
$PSA_CFG['asFunction']['file_path'] = PSA_BASE_DIR . '/wri/asfunctions.php';


/**
 * Web path to the folder where index.php is if application is not in web server root directory.
 * Set to empty ('') if your application is in the web server root folder.
 * Example: '/webroot/myapp'
 * Leave this value commented if you want to auto discover application root folder (in init.php).
 */
//$PSA_CFG['folders']['basedir_web'] = '';


/**
 * Autoload locations.
 * Folders in $PSA_CFG['folders']['autoload'] array will be checked (non recursively) for files
 * on file registration. You can add more locations by adding elements
 * to this array. Paths in this array must be relative to PSA_BASE_DIR folder.
 */
$PSA_CFG['asFunction']['check_dir'][] = 'lib'; // psa/lib/
$PSA_CFG['asFunction']['check_dir'][] = 'lib/exceptions'; // psa/exceptions/


/**
 * Folders for Smarty templates. Relative from PSA_BASE_DIR folder. See http://smarty.php.net/ for details.
 */
$PSA_CFG['smarty']['template_dir'] = '../templates';
$PSA_CFG['smarty']['cache_dir']    = '../templates/smarty/cache';       // must be writable to web server
$PSA_CFG['smarty']['config_dir']   = '../templates/smarty/configs';
$PSA_CFG['smarty']['compile_dir']  = '../templates/smarty/templates_c'; // must be writable to web server


/**
 * Folders for @getFunction phpdoc tag templates. Relative from PSA_BASE_DIR folder.
 */
$PSA_CFG['asFunction']['template_dir'] = 'lib/tpl';


/**
 * Should a new database connection be opened for writing logs to the database.
 * Set this to 1 if you use database transactions and you want to write logs to database from inside
 * transactions that can be rollbacked.
 */
$PSA_CFG['logging']['new_database_connection'] = 0;



$PSA_CFG['logging']['enabled'] = 1;


/**
 * User passwords hashing method. This value must be valid argument for hash() PHP function.
 */
$PSA_CFG['password_hash'] = 'sha256';


/**
 * Database tables names.
 */
$PSA_CFG['database']['table']['user'] = 'psa_user';
$PSA_CFG['database']['table']['group'] = 'psa_group';
$PSA_CFG['database']['table']['user_in_group'] = 'psa_user_in_group';
$PSA_CFG['database']['table']['log'] = 'psa_log';


/**
 * Default controller and action names.
 * These values are used by Router::getDispatchData() method.
 */
$PSA_CFG['mvc']['default_controller_name'] = 'Default';
$PSA_CFG['mvc']['default_action_name'] = 'default';
$PSA_CFG['mvc']['default_controller_suffix'] = '_Controller';
$PSA_CFG['mvc']['default_action_suffix'] = '_action';


/**
 * Config file that will override settings in this file.
 */
if(defined('PSA_CONFIG_OVERRIDE') && PSA_CONFIG_OVERRIDE)
	include PSA_CONFIG_OVERRIDE;
else if(file_exists(PSA_BASE_DIR . '/config_override.php'))
	include PSA_BASE_DIR . '/config_override.php';

