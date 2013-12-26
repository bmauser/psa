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
			$hook_to_run_before = 'Psa_Hook_Before_User_Delete';
			$hook_to_run_after = 'Psa_Hook_After_User_Delete';
		}
		// work with groups
		else if($user_or_group == 2){
			$table = $PSA_CFG['database']['table']['group'];
			$name_column = 'name';
			$hook_to_run_before = 'Psa_Hook_Before_Group_Delete';
			$hook_to_run_after = 'Psa_Hook_After_Group_Delete';
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

			// run Psa_Hook_Before_[user/group]_Delete hooks
			psa_run_hooks(array($hook_to_run_before => array('psa_main' => array($id_value))),'by_type','no_unregistered_warning');

			// run query against the database
			$psa_database->query($sql);

			// if no rows affected
			if($psa_database->affected_rows() <= 0){
				$failed = 1;
				$log_data['message']  = 'Unable to delete ' . (($user_or_group == 1) ? 'user' : 'group') . ". Maybe does not exists.";
			}
			// run Psa_Hook_After_[user/group]_Delete hooks
			else{
				psa_run_hooks(array($hook_to_run_after => array('psa_main' => array($id_value))),'by_type','no_unregistered_warning');
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
 * This function is used for calling hooks.
 * It includes hooks files, makes new instances of hook objects and calls member methods.
 * It can call class methods in two different ways depending on the second argument.
 *
 * If the second argument is true, hooks are called <i>by type</i>. Hook type is the name of the class it extends.
 * In this case the first argument should be an array with elements like in example below:
 * <code>
 * // $run is array that will be argument for psa_run_hooks() function
 * // Structure should be: $run['hook_type']['hook_method'] = array('method_argument1','method_argument2',...);
 * $run['Psa_Hook_Before_User_Delete']['psa_main'] = array(177);
 * psa_run_hooks($run, 'by_type');
 * </code>
 * This example calls method <kbd>psa_main</kbd> with one argument (177) from all registered
 * <kbd>Psa_Hook_Before_User_Delete</kbd> hooks.
 *
 * If the second argument is false (default) hooks are called <i>by name</i> (hook name is the same
 * as its class name) and in this case first argument should be an array with elements
 * like in this example:
 * <code>
 * // $run is array that will be argument for psa_run_hooks() function
 * // Structure should be: $run['class_name']['hook_method'] = array('method_argument1','method_argument2',...);
 * $run['Article']['load'] = array(14);
 * $run['News']['open'] = array();
 * psa_run_hooks($run);
 * </code>
 * This example calls method <kbd>load</kbd> with one argument (14) from the <kbd>Article</kbd> class
 * and method <kbd>open</kbd> with no arguments from the <kbd>News</kbd> class.
 *
 * This function will throw {@link Psa_Exception} on error.
 *
 * @param array $run_data Array with data what to run.
 * @param bool $by_type If true, hooks will be called <i>by type</i>, otherwise they'll be called <i>by name</i>.
 * @param bool $disable_unregistered_exception Exception that hook is not registered won't be thrown.
 * This parameter can be set to true when hook doesn't have to exist.
 * @return int 1 for success (all hooks are executed), -1 for success, but not with all hooks because some are
 * probably not registered or don't exist. -1 can be returned only when third argument is set to true, otherwise
 * exception will be thrown.
 * @see Psa_Files::register()
 * @throws Psa_Exception
 */
function psa_run_hooks($run_data, $by_type = false, $disable_unregistered_exception = false){

	// Disable hooks globally. I use this while testing.
	$psa_registry = Psa_Registry::get_instance();
	if(isset($psa_registry->psa_disable_hooks) && $psa_registry->psa_disable_hooks)
		return;

	// files object
	$psa_files = Psa_Files::get_instance();


	// die if files are not registered or not retrieved from session or database
	if(!$psa_files->files_data or !is_array($psa_files->files_data['class_paths'])){
		$msg = 'No files to run. Files are not registered or data not retrieved from session or database';
		throw new Psa_Fatal_Error_Exception($msg, $msg, 16);
	}


	// exit if $run_data is not array
	if(!is_array($run_data)){
		throw new Psa_Exception('Invalid first argument for psa_run_hooks() function. Not an array.', 11);
	}


	// call hooks
	if($by_type){

		$file_type = key($run_data);

		// if file is registered
		if(isset($psa_files->files_data['hooks'][$file_type]))
			$files_data = &$psa_files->files_data['hooks'][$file_type];
		else{
			if(!$disable_unregistered_exception){
				throw new Psa_Exception("Trying to run unregistered hook $file_type. Try to register files and delete session (cookies) if you store registered files data in session.", 12);
			}
			else
				return 0;
		}
	}
	// call hooks by name (hook class name)
	else{
		$files_data = &$run_data;
	}


	// set default return value
	$return = 1;


	$router = new Psa_Router();


	// $files_data is array with data used to instance classes and run methods (class name, method name, file name)
	foreach ($files_data as $files_data_key => &$files_data_value){

		// hooks
		if($by_type){
			$include_file = $files_data_value;
			$file_class = $files_data_key;
			$file_methods_data = &$run_data[$file_type]; // array of method names and arguments
		}
		// if we call files by name
		else{

			// if file is not registered
			if(!isset($psa_files->files_data['class_paths'][$files_data_key])){

				if(!$disable_unregistered_exception){
					throw new Psa_Exception("Trying to run unregistered class $files_data_key. Try to register files and delete session (cookies) if you store registered file data in session.", 13);
				}
				else{
					$return = -1;

					continue;
				}
			}

			$include_file = $psa_files->files_data['class_paths'][$files_data_key];
			$file_class = $files_data_key;
			$file_methods_data = &$files_data_value; // array of method names and arguments
		}

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
 * You can use this function instead of <kbd>echo</kbd> or <kbd>print_r</kbd> functions.
 *
 * <b>Example:</b>
 *
 * <code>
 * // print $array
 * prs($array);
 * </code>
 *
 * @param mixed $str
 * @param bool $return_only
 */
function prs($str, $return_only = false){
	$return = '<pre>' . htmlspecialchars (print_r($str, TRUE)) . '</pre>';

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

