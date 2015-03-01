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
$PSA_CFG['pdo']['username'] = 'databaseUser';
$PSA_CFG['pdo']['password'] = 'databasePass';

// for MySQL:
//$PSA_CFG['pdo']['dsn']      = "mysql:host=localhost;port=3306;dbname=databaseName";
//$PSA_CFG['pdo']['driver_options'] = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);

// for PostgreSQL:
//$PSA_CFG['pdo']['dsn']      = "pgsql:host=localhost;port=5432;dbname=databaseName";
//$PSA_CFG['pdo']['driver_options'] = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);


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
$PSA_CFG['develop_mode_register_files'] = false;


/**
 * File with data about registered files for autoloading (full filesystem path).
 */
$PSA_CFG['autoload_data_file'] = PSA_BASE_DIR . '/wri/autoload_data.php';


/**
 * File with data about registered files for autoloading (full filesystem path).
 */
$PSA_CFG['@asFunction_file'] = PSA_BASE_DIR . '/wri/asfunctions.php';


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
$PSA_CFG['folders']['autoload'][] = 'lib'; // psa/lib/
$PSA_CFG['folders']['autoload'][] = 'lib/exceptions'; // psa/exceptions/


/**
 * Folders for Smarty templates. Relative from PSA_BASE_DIR folder. See http://smarty.php.net/ for details.
 */
$PSA_CFG['folders']['template_dir'] = '../templates';
$PSA_CFG['folders']['smarty']['cache_dir']    = '../templates/smarty/cache';       // must be writable to web server
$PSA_CFG['folders']['smarty']['config_dir']   = '../templates/smarty/configs';
$PSA_CFG['folders']['smarty']['compile_dir']  = '../templates/smarty/templates_c'; // must be writable to web server


/**
 * Folders for @getFunction phpdoc tag templates. Relative from PSA_BASE_DIR folder.
 */
$PSA_CFG['folders']['@asFunction']['template_dir'] = 'lib/tpl';


/**
 * If true, profile log will be enabled.
 * If you enable this, profile log will be written into psa_profile_log database table by default
 * (see settings below). Enabling this will increase number of queries against the database
 * on every request. You should enable this in testing and profiling process.
 * When you collect appropriate amount of data in profile log you can run various sql queries to get
 * interesting data about your application like which methods are mostly invoked, which takes the most
 * execution time and are candidates for optimization.
 * Logging must be enabled for this to work (see ['logging']['max_log_level'] option below).
 *
 * NOTE: Only methods invoked through Router::dispatch() method will be listed in
 *       profile logs.
 *
 * NOTE: If property $psa_no_profile_log is set in object (class), profile log will be disabled for
 *       all methods in that class.
 */
$PSA_CFG['profile_log'] = 0;


/**
 * Logging. If enabled (see max_log_level value below) some PSA activity will be logged.
 * This values are used by Logger class which you can extend for your customized logging.
 * You can add more storages to $PSA_CFG['logging']['storage'] array for your custom logs.
 */

// Log level:
//  0 - logging disabled,
//  1 - only exceptions, errors, warnings
//  2 - all in level 1 and some activities like authorizations, user/group data saving, password changing
$PSA_CFG['logging']['max_log_level'] = 2;

// Log storage:
// - type can be 'database' or 'file'. PSA writes log messages in storage named 'psa_default'.
// - target can be database table name or or full filesystem path for the log file.
//   If it's a file, the web server must have write permission on the specified file.
// NOTE: If you want to write logs to different database than one mentioned in $PSA_CFG['pdo']['dsn']
//       write target as 'database.psa_log'.
$PSA_CFG['logging']['storage']['psa_default']['type']   = 'database';
$PSA_CFG['logging']['storage']['psa_default']['target'] = 'psa_log'; // for PSA default log
$PSA_CFG['logging']['storage']['psa_profile']['type']   = 'database';
$PSA_CFG['logging']['storage']['psa_profile']['target'] = 'psa_profile_log'; // for PSA profile log.
//$PSA_CFG['logging']['storage']['my_storage']['type']   = 'database';
//$PSA_CFG['logging']['storage']['my_storage']['target'] = 'my_log_table';

// Single or multiple lines in log messages.
// If logging storage type is 'file' should log messages be written with new lines (more lines per
// one log message which is more readable) or one log message per line.
$PSA_CFG['logging']['more_lines'] = true;

// Time format for log message.
// This is used only when logging to file. See date() function in PHP manual for details.
$PSA_CFG['logging']['time_format'] = 'd.m.Y H:i:s';

// Should a new database connection be opened for writing logs to the database.
// Set this to 1 if you use database transactions and you want to write logs to database from inside
// transactions that can be rollbacked.
// NOTE: If you enable this option and use logging and profile logging,
//       on every request 3 database connections will be opened.
$PSA_CFG['logging']['new_database_connection'] = 0;


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

