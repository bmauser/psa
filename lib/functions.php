<?php
/**
 * @package PSA
 */


/**
 * Deletes a user or a group. Removes all data about the user or group from the database.
 * Do not use this function directly. Use deleteGroup() and deleteUser() wrapper functions.
 *
 * @param int|array|string $id id of the user|group or array with user|group ids. 'all' for
 * deleting all users|groups.
 * @param int $user_or_group what to delete. 1-user, 2-group
 * @return int 0 for failure, 1 for success, -1 user|group (or more users|groups) not exists
 * @see deleteGroup()
 * @see deleteUser()
 * @ignore
 */
function deleteUserGroup($id, $user_or_group){

	if($id && $user_or_group){;
		
		// if $id is not array make it array for foreach loop
		if(!is_array($id))
			$id = array($id);

		// flag if some query fail in the foreach loop
		$failed = 0;

		// work with users
		if($user_or_group == 1){
			$table = Cfg('database.table.user');
			$name_column = 'username';
		}
		// work with groups
		else if($user_or_group == 2){
			$table = Cfg('database.table.group');
			$name_column = 'name';
		}

		// delete users or groups
		foreach ($id as $id_key => &$id_value){

			// delete all users or groups
			if($id_value == 'all')
				$sql = "DELETE FROM {$table}";
			// delete user or groups by id
			else if(isInt($id_value)){
				$sql = "DELETE FROM {$table} WHERE id = '$id_value'";
			}
			// delete user or groups by name
			else{
				$sql = "DELETE FROM {$table} WHERE $name_column = " . Db()->escape($id_value);
			}

			// run query against the database
			Db()->query($sql);

			// if no rows affected
			if(Db()->affectedRows() <= 0){
				$failed = 1;
				$log_message  = 'Unable to delete ' . (($user_or_group == 1) ? 'user' : 'group') . ". Maybe does not exists.";
			}
			else{
				$log_message  = (($user_or_group == 1) ? 'User' : 'Group') . ' deleted';
			}

			// logging
			if(Cfgn('logging.enabled')){
				// parameters for Logger
				$log_data['function'] = __FUNCTION__;
				$log_data['level']    = 2;

				if($user_or_group == 1){
					if(isInt($id_value))
						$log_data['user_id'] = $id_value;
					else
						$log_data['username'] = $id_value;
				}
				else if($user_or_group == 2){
					if(isInt($id_value))
						$log_data['group_id'] = $id_value;
					else
						$log_data['groupname'] = $id_value;
				}

				Logger()->info($log_message, $log_data);
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
 * deleteUser(123);
 *
 * // delete user with usename 'my_user'
 * deleteUser('my_user');
 *
 * // delete more users
 * deleteUser(array(1, 3, 5, 'my_user'))
 * </code>
 *
 * @param int|array|string $user ID or username or array with user
 * IDs or usernames. "<kbd>all</kbd>" to delete all users.
 * @return int 0 for failure, 1 for success, -1 if an user (or more users) don't exist
 */
function deleteUser($user){
	return deleteUserGroup($user, 1);
}


/**
 * Deletes group from the database table.
 *
 * <b>Example:</b>
 *
 * <code>
 * // delete group with ID 123
 * deleteGroup(123);
 *
 * // delete group with name 'my_group'
 * deleteGroup('my_group');
 *
 * // delete more groups
 * deleteGroup(array(1, 3, 5, 'my_group'))
 * </code>
 *
 * @param int|array|string $group ID or group name or array with group IDs or group names.
 * "<kbd>all</kbd>" to delete all groups.
 * @return int 0 for failure, 1 for success, -1 if a group (or more groups) don't exist
 */
function deleteGroup($group){
	return deleteUserGroup($group, 2);
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
 * if(isUserInGroup(2, 55))
 * 	echo 'User with ID 2 is in the group with ID 55';
 * </code>
 *
 * @param int $user user ID
 * @param int $group group ID
 * @return bool 1 if user is a member of the group, otherwise 0
 */
function isUserInGroup($user, $group){

	$user = (int)$user;
	$group = (int)$group;

	if($user && $group){

		$sql = "SELECT * FROM " . Cfg('database.table.user_in_group') . " WHERE user_id = '$user' AND group_id = '$group'";

		$row = Db()->fetchRow($sql);

		if(isset($row['user_id']) && $row['user_id'])
			return 1;
	}

	return 0;
}


/**
 * Checks is the given <var>$value</var> an integer.
 *
 * @return bool
 * @ignore
 */
function isInt($value){

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
 * @throws PsaException
 * @return Ambigous <unknown, NULL>
 */
function getInstance($class_name, $instance_name = null, array $constructor_args = array(), $is_function = false, $only_first_instance = false){

	// instance store
	static $first_instance = array();
	static $instances = array();	

	// just return a new instance
	if($instance_name === '_new'){
		$instance = null;
	}
	// select an instance
	else{
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
	}
	
	// make a new instance
	if($instance === null){
		
		// if function name is passed
		if($is_function){
			$instance = call_user_func_array($class_name, $constructor_args);
		}
		
		else if($constructor_args){
			$ref = new ReflectionClass($class_name);
			$instance = $ref->newInstanceArgs($constructor_args);
		}
		else
			$instance = new $class_name();
	}
	else if($constructor_args){
		throw new PsaException("Trying to call constructor for existing instance of $class_name.");
	}
	
	return $instance;
}


/**
 *
 * @param unknown_type $selector
 * @return NULL
 */
function &getPropertyBySelector(&$object, $selector, $exception_class_name = 'PsaException', $exception_message = null, $use_cache = true){

	static $cache;
	
	if(!$exception_message)
		$exception_message = 'Value ' . $selector . ' not set';
	
	if($use_cache && isset($cache[$selector])){
		return $cache[$selector];
	}
	
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
		if($exception_class_name){
			throw new $exception_class_name($exception_message);
		}
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
