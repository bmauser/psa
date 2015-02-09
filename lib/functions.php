<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2013 Bojan Mauser
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @link http://code.google.com/p/phpstartapp/
 * @author Bojan Mauser <bmauser@gmail.com>
 * @package PSA
 * @version $Id: functions.php 142 2013-09-26 17:10:52Z bmauser $
 */


/**
 * Deletes a user or a group. Removes all data about the user or group from the database.
 * Do not use this function directly. Use psa_delete_group() and psa_delete_user() wrapper functions.
 *
 * @param int|array|string $id id of the user|group or array with user|group ids. 'all' for
 * deleting all users|groups.
 * @param int $user_or_group what to delete. 1-user, 2-group
 * @return int 0 for failure, 1 for success, -1 user|group (or more users|groups) not exists
 * @see psa_delete_group()
 * @see psa_delete_user()
 * @ignore
 */
function psa_del_user_group($id, $user_or_group){

	if($id && $user_or_group){

		// config array
		$PSA_CFG = Psa_Registry::get_instance()->PSA_CFG;

		// database object
		$psa_database = Psa_Registry::get_instance()->psa_database;

		// if $id is not array make it array for foreach loop
		if(!is_array($id))
			$id = array($id);

		// flag if some query fail in the foreach loop
		$failed = 0;

		// work with users
		if($user_or_group == 1){
			$table = $PSA_CFG['database']['table']['user'];
			$name_column = 'username';
		}
		// work with groups
		else if($user_or_group == 2){
			$table = $PSA_CFG['database']['table']['group'];
			$name_column = 'name';
		}

		// delete users or groups
		foreach ($id as $id_key => &$id_value){

			// delete all users or groups
			if($id_value == 'all')
				$sql = "DELETE FROM {$table}";
			// delete user or groups by id
			else if(psa_is_int($id_value)){
				$sql = "DELETE FROM {$table} WHERE id = '$id_value'";
			}
			// delete user or groups by name
			else{
				$sql = "DELETE FROM {$table} WHERE $name_column = " . $psa_database->escape($id_value);
			}

			// run query against the database
			$psa_database->query($sql);

			// if no rows affected
			if($psa_database->affected_rows() <= 0){
				$failed = 1;
				$log_data['message']  = 'Unable to delete ' . (($user_or_group == 1) ? 'user' : 'group') . ". Maybe does not exists.";
			}
			else{
				$log_data['message']  = (($user_or_group == 1) ? 'User' : 'Group') . ' deleted';
			}

			// logging
			if($PSA_CFG['logging']['max_log_level'] >= 2){
				// parameters for Psa_Logger::log() method
				$log_data['function'] = __FUNCTION__;
				$log_data['level']    = 2;

				if($user_or_group == 1){
					if(psa_is_int($id_value))
						$log_data['user_id'] = $id_value;
					else
						$log_data['username'] = $id_value;
				}
				else if($user_or_group == 2){
					if(psa_is_int($id_value))
						$log_data['group_id'] = $id_value;
					else
						$log_data['groupname'] = $id_value;
				}

				Psa_Logger::get_instance()->log($log_data);
			}
		}

		if($failed)
			return -1;
		else
			return 1;
	}

	return 0;
}


/**
 * Deletes user from the database table.
 *
 * <b>Example:</b>
 *
 * <code>
 * // delete user with ID 123
 * psa_delete_user(123);
 *
 * // delete user with usename 'my_user'
 * psa_delete_user('my_user');
 *
 * // delete more users
 * psa_delete_user(array(1, 3, 5, 'my_user'))
 * </code>
 *
 * @param int|array|string $user ID or username or array with user
 * IDs or usernames. "<kbd>all</kbd>" to delete all users.
 * @return int 0 for failure, 1 for success, -1 if an user (or more users) don't exist
 */
function psa_delete_user($user){
	return psa_del_user_group($user, 1);
}


/**
 * Deletes group from the database table.
 *
 * <b>Example:</b>
 *
 * <code>
 * // delete group with ID 123
 * psa_delete_group(123);
 *
 * // delete group with name 'my_group'
 * psa_delete_group('my_group');
 *
 * // delete more groups
 * psa_delete_group(array(1, 3, 5, 'my_group'))
 * </code>
 *
 * @param int|array|string $group ID or group name or array with group IDs or group names.
 * "<kbd>all</kbd>" to delete all groups.
 * @return int 0 for failure, 1 for success, -1 if a group (or more groups) don't exist
 */
function psa_delete_group($group){
	return psa_del_user_group($group, 2);
}


/**
 * Executes (runs) hooks.
 *
 * It includes hooks files, makes new instances of hook classes and calls member methods.
 *
 * Hooks are called <i>by type</i>. Hook type is the name of the class it extends.
 * First argument should be an array with elements like in the example below:
 * <code>
 * // $run is array that will be argument for psa_run_hooks() function
 * // Structure should be: $run['hook_type']['hook_method'] = array('method_argument1','method_argument2',...);
 * $run['After_User_Delete']['psa_main'] = array(177);
 * psa_run_hooks($run);
 *
 * // or the same in one line
 * psa_run_hooks(array('After_User_Delete' => array('psa_main' => array(177))));
 * </code>
 * This example calls method <kbd>psa_main</kbd> with one argument (177) from all registered
 * <kbd>After_User_Delete</kbd> hooks.
 *
 * <br><b>How to make hooks</b>
 *
 * <b>1)</b> Put the file with the hook definition class into the folder listed in
 * <var>$PSA_CFG['folders']['hook_def'][]</var>.
 * You can make it abstract class to force method implementation in child classes like this:
 * <code>
 * <?php
 *
 * abstract class After_User_Delete extends Psa_Model{
 *
 *     abstract function main($user_id); // ypu can call this method as you like
 * }
 *
 * ?>
 * </code>
 *
 * <b>2)</b> Put the file with the hook class into the folder listed in <var>$PSA_CFG['folders']['hook_autoload'][]</var>.
 * <code>
 * <?php
 *
 * class MyHook extends After_User_Delete{
 *
 *     function main($user_id){
 *
 *         // Do something here. For example, delete users home folder.
 *     }
 * }
 *
 * ?>
 * </code>
 *
 * <b>3)</b> Put call to <kbd>psa_run_hooks()</kbd> function in your code where is needed.
 * <code>
 * // invoke main() methods in all hooks that extend After_User_Delete class
 * psa_run_hooks(array('After_User_Delete' => array('main' => array($user_id))));
 *
 * </code>
 *
 * <br><b>Note:</b> If there are more hooks of the same type, they are called in no special order.
 *
 * <br><b>Note:</b> When a new hook or a hook definition class is added, you have to register files for autoloading.
 *
 * @param array $run_data Array with data what to run.
 * @param bool $disable_unregistered_exception if true, exception that hook is not registered (
 * or doesn't exists) won't be thrown.
 * @return int 1 for success (all hooks are executed), 0 can be returned when the second
 * argument is set to false and there is no hooks registered.
 * @see Psa_Files::register()
 * @throws Psa_Exception
 */
function psa_run_hooks($run_data, $disable_unregistered_exception = true){

	// return if hooks are disabled
	$psa_registry = Psa_Registry::get_instance();
	if(isset($psa_registry->psa_disable_hooks) && $psa_registry->psa_disable_hooks)
		return;

	// exit if $run_data is not array
	if(!is_array($run_data)){
		throw new Psa_Exception('Invalid first argument for psa_run_hooks() function. Not an array.', 11);
	}

	$hook_type = key($run_data);

	$psa_files = Psa_Files::get_instance();

	// if file is registered
	if(isset($psa_files->files_data['hooks'][$hook_type]))
		$files_data = $psa_files->files_data['hooks'][$hook_type];
	else{
		if(!$disable_unregistered_exception){
			throw new Psa_Exception("Trying to run unregistered hook $hook_type. Try to register files.", 12);
		}
		else
			return 0;
	}


	// set default return value
	$return = 1;

	$router = new Psa_Router();

	// $files_data is array with data used to instance classes and run methods (class name, method name, file name)
	foreach ($files_data as $files_data_key => $files_data_value){

		$include_file = $files_data_value;
		$file_class = $files_data_key;
		$file_methods_data = &$run_data[$hook_type]; // array of method names and arguments

		// call object methods
		if(is_array($file_methods_data)){
			foreach ($file_methods_data as $file_method_name => $file_method_args){
				$router->dispach($file_class, $file_method_name, $file_method_args, $disable_unregistered_exception);
			}
		}
	}

	return $return;
}


/**
 * This function is registered with the PHP <kbd>spl_autoload_register()</kbd> function as <kbd>__autoload()</kbd> implementation
 * used to auto include .php files.
 *
 * Thus, if you want to extend some class you don't have to include its file, it will be included automatically.
 *
 * <b>Note:</b> Class autoloading will be working only in folders specified with
 * <var>$PSA_CFG['folders']['autoload'][]</var> and <var>$PSA_CFG['folders']['hook_autoload'][]</var> configuration
 * options. Also, file registration must be invoked to generate <kbd>autoload_data.php</kbd> file that contains array
 * with all classes and their paths. There is a command line helper script <kbd>register_files.php</kbd> for that.
 *
 * For example, you can just extend <i>Psa_Model</i> class without including its .php file:
 * <code>
 * <?php
 * class My_Model extends Psa_Model{
 * 	function some_function(){
 * 		// ... something here
 * 	}
 * }
 * ?>
 * </code>
 *
 * @see Psa_Files::register()
 */
function psa_autoload($class_name){

	static $psa_files = null;

	// read data from generated autoload_data.php file
	if(!$psa_files){
		$psa_files = Psa_Files::get_instance();
		$psa_files->set_data();
	}

	// try to include registered class
	if($psa_files->files_data && array_key_exists($class_name, $psa_files->files_data['class_paths'])){
		include_once $psa_files->files_data['class_paths'][$class_name];
		return;
	}
}


/**
 * You can use this function instead of <kbd>echo</kbd> or <kbd>print_r</kbd> functions
 * during development for debug output.
 *
 * It will just return if <var>PSA_CFG['develop_mode']</var> is FALSE. This function is
 * wrapper for <kbd>print_r</kbd> function.
 *
 * <b>Example:</b>
 *
 * <code>
 * // print $array
 * prs($array);
 * </code>
 *
 * @param mixed $value The expression to be printed.
 * @param bool $return_only When this parameter is set to TRUE, function will return the
 * information rather than print it just like print_r() function.
 */
function prs($value, $return_only = false){

	if(!Psa_Registry::get_instance()->PSA_CFG['develop_mode'])
		return;

	// web mode
	if(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'])
		$return = '<pre>' . htmlspecialchars (print_r($value, TRUE)) . '</pre>';
	// cli mode
	else
		$return = print_r($value, TRUE);


	if($return_only)
		return $return;

	echo $return;
	flush ();
}


/**
 * Checks if user is a member of the group.
 *
 * It simply tries to find a matching row in <i>psa_user_in_group</i> database table.
 *
 * <b>Example:</b>
 *
 * <code>
 * if(psa_is_user_in_group(2, 55))
 * 	echo 'User with ID 2 is in the group with ID 55';
 * </code>
 *
 * @param int $user user ID
 * @param int $group group ID
 * @return bool 1 if user is a member of the group, otherwise 0
 */
function psa_is_user_in_group($user, $group){

	$PSA_CFG = Psa_Registry::get_instance()->PSA_CFG;

	$user = (int)$user;
	$group = (int)$group;

	if($user && $group){

		$sql = "SELECT * FROM {$PSA_CFG['database']['table']['user_in_group']} WHERE user_id = '$user' AND group_id = '$group'";

		$row = Psa_Registry::get_instance()->psa_database->fetch_row($sql);

		if(isset($row['user_id']) && $row['user_id'])
			return 1;
	}

	return 0;
}


/**
 * Adds a given path to the PHP <var>include_path</var> configuration directive.
 *
 * It can be useful when you work with some externals libraries.
 *
 * <b>Example:</b>
 *
 * <code>
 * psa_add_include_path('/usr/share/somefolder');
 * </code>
 *
 * @param string $path path to add to <var>include_path</var>
 * @return string|bool returns the old include_path on success or FALSE on failure.
 * @see http://www.php.net/manual/en/ini.core.php#ini.include-path
 */
function psa_add_include_path($path){

	if($path){
		return set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	}

	return false;
}


/**
 * Checks is the given <var>$value</var> an integer.
 *
 * @return bool
 * @ignore
 */
function psa_is_int($value){

	if(strval(intval($value)) === (string) $value)
		return true;

	return false;
}


function N($instance_name = null){

	static $first_instance = null;
	static $instances = array();
	
	// work with first instance
	if($instance_name === null){
		if($first_instance === null)
			$first_instance = new stdClass();
		
		return $first_instance;
	}
	else{
		if(!isset($instances['$instance_name']))
			$instances['$instance_name'] = new stdClass();
		
		return $instances[$instance_name];
	}
}

/**
 * @getFunction PSA_CFG psa_get_config() propSelector
 */
function &psa_get_config(){
	
	static $PSA_CFG = null;
	
	if($PSA_CFG === null){
		include PSA_BASE_DIR . '/config.php';
	}
	
	return $PSA_CFG;
}


/**
 * @getFunction PSA_CFG1 $GLOBALS['PSA_CFG1'] propSelector
 */


$PSA_CFG1['aaa'] = 123;


/*
function &PSA_CFG($selector = null){

	static $PSA_CFG = null;

	if($PSA_CFG === null){
		include PSA_BASE_DIR . '/config.php';
	}

	if(!$selector)
		return $PSA_CFG;

	return psa_get_set_property_by_selector($PSA_CFG, $selector, 'PSA_CFG_Exception', 'Config value ' . $selector . ' not set');

}
*/


/**
 *
 * @param unknown_type $selector
 * @return NULL
 */
function &psa_get_set_property_by_selector(&$object, $selector, $exception_class_name = null, $exception_message = null, $use_cache = true){

	static $cache;
	
	if(!$exception_class_name)
		$exception_class_name = 'Psa_Exception';
	
	if(!$exception_message)
		$exception_message = 'Value ' . $selector . ' not set';
	
	if($use_cache && isset($cache[$selector])){
		return $cache[$selector];
	}
	
	$parts1 = explode('->', $selector);
	$ref = &$object;
	
	foreach($parts1 as $k1 => $v1){
		$parts2 = explode('.', $v1);
		foreach($parts2 as $k2 => $v2){
			if($k2 > 0 or ($k1 == 0 && is_array($object))){
				if(isset($ref[$v2])){
					$ref = &$ref[$v2];
					continue;
				}
			}
			else if(isset($ref->$v2)){
				$ref = &$ref->$v2;
				continue;
			}
			
			$not_isset = 1;
			break 2;
		}
	}

	// if not set
	if(isset($not_isset)){
		if($exception_class_name)
			throw new $exception_class_name($exception_message);
		else{
			$return = null; 
			return $return;
		}
	}
	// save to cache
	else if($use_cache)
		$cache[$selector] = &$ref;
	
	return $ref;
}
