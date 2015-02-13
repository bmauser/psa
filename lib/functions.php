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

	if($id && $user_or_group){;
		
		// if $id is not array make it array for foreach loop
		if(!is_array($id))
			$id = array($id);

		// flag if some query fail in the foreach loop
		$failed = 0;

		// work with users
		if($user_or_group == 1){
			$table = Cfg()['database']['table']['user'];
			$name_column = 'username';
		}
		// work with groups
		else if($user_or_group == 2){
			$table = Cfg()['database']['table']['group'];
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
				$sql = "DELETE FROM {$table} WHERE $name_column = " . Db()->escape($id_value);
			}

			// run query against the database
			Db()->query($sql);

			// if no rows affected
			if(Db()->affected_rows() <= 0){
				$failed = 1;
				$log_data['message']  = 'Unable to delete ' . (($user_or_group == 1) ? 'user' : 'group') . ". Maybe does not exists.";
			}
			else{
				$log_data['message']  = (($user_or_group == 1) ? 'User' : 'Group') . ' deleted';
			}

			// logging
			if(Cfg()['logging']['max_log_level'] >= 2){
				// parameters for Logger
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

				Logger()->log($log_data);
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
 * This function is registered with the PHP <kbd>spl_autoload_register()</kbd> function as <kbd>__autoload()</kbd> implementation
 * used to auto include .php files.
 *
 * Thus, if you want to extend some class you don't have to include its file, it will be included automatically.
 *
 * <b>Note:</b> Class autoloading will be working only in folders specified with
 * <var>$PSA_CFG['folders']['autoload'][]</var>  configuration
 * options. Also, file registration must be invoked to generate <kbd>autoload_data.php</kbd> file that contains array
 * with all classes and their paths. There is a command line helper script <kbd>register_files.php</kbd> for that.
 *
 * @see Psa_Files::register()
 */
function psa_autoload($class_name){

	static $psa_files = null;

	// read data from generated autoload_data.php file
	if(!$psa_files){
		$psa_files = Files();
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

	if(!Cfg()['develop_mode'])
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

	$user = (int)$user;
	$group = (int)$group;

	if($user && $group){

		$sql = "SELECT * FROM " . Cfg()['database']['table']['user_in_group'] . " WHERE user_id = '$user' AND group_id = '$group'";

		$row = Reg()->psa_database->fetch_row($sql);

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


/**
 * @todo
 * 
 * @param unknown $class_name
 * @param string $instance_name
 * @param array $constructor_args
 * @throws Psa_Exception
 * @return Ambigous <unknown, NULL>
 */
function psa_get_instance($class_name, $instance_name = null, array $constructor_args = null, $is_function = false, $only_first_instance = false){

	// instance store
	static $first_instance = array();
	static $instances = array();	

	
	// select instance
	if($instance_name === null or $only_first_instance){ // first instance
		if(!isset($first_instance[$class_name])){
			$first_instance[$class_name] = null;
			$instance = &$first_instance[$class_name];
		}
		else
			$instance = $first_instance[$class_name];
	}
	else{ // named instance
		if(!isset($instances[$class_name][$instance_name])){
			$instances[$class_name][$instance_name] = null;
			$instance = &$instances[$class_name][$instance_name];
		}
		else
			$instance = $instances[$class_name][$instance_name];
	}
	
	// make new instance
	if($instance === null){
		
		// if function name is passed
		if($is_function){
			if($constructor_args)
				$instance = call_user_func_array($class_name, $constructor_args);
			else
				$instance = $class_name();
		}
		
		else if($constructor_args){
			$ref = new ReflectionClass($class_name);
			$instance = $ref->newInstanceArgs($constructor_args);
		}
		else
			$instance = new $class_name();
	}
	else if($constructor_args){
		throw new Psa_Exception("Trying to call constructor for existing instance of $class_name.");
	}
	
	return $instance;
}


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
	
	// @todo check if is array or object
	
	$parts1 = explode('->', $selector);
	$ref = &$object;
	
	// find reference by selector
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
