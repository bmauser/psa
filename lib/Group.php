<?php
/**
 * @package PSA
 */

/**
 * User group class.
 *
 * <br><b>Examples:</b>
 *
 * <br><b>1)</b> Create a new user group:
 * <code>
 * // if you want to create a new user group use 'new' as argument to the constructor
 * $group = new Group('new');
 *
 * // set name of the new grop
 * $group->name = 'my_new_user_group';
 *
 * // save the group to the database and get ID of the newly created group
 * $new_group_id = $group->save();
 * </code>
 *
 * <br><b>2)</b>
 * If you want to add more columns to <i>psa_group</i> database table, extend <kbd>Group</kbd> class:
 * <code>
 * class MyGroup extends Group{
 *
 *     public function __construct($group_id_or_groupname){
 *         // Call Group class constructor and pass names of the columns in the
 *         // psa_group database table to work with.
 *         parent::__construct($group_id_or_groupname, array('id', 'name', 'custom_col1', 'custom_col2', 'custom_col3'));
 *     }
 * }
 *
 * $group = new MyGroup('GroupName');
 * $group->custom_col2 = 'something';
 * $group->custom_col2 = 1234;
 * $group->save();
 * </code>
 *
 * Because this class extends {@link ActiveRecord} you can save data in the database table
 * by setting corresponding object member variables and call {@link save()} method. After calling
 * {@link restore()} method, corresponding object member variables will have values fetched from the
 * database.
 *
 * @see ActiveRecord
 */
class Group extends ActiveRecord{

	/**
	 * Group ID.
	 * Primary key from <i>psa_group</i> database table.
	 *
	 * @var int
	 */
	public $id;


	/**
	 * Name of the group.
	 * There cannot be two groups with the same name.
	 * You will need to set this value when creating a new group or renaming a group.
	 *
	 * @var string
	 */
	public $name;




	/**
	 * Constructor.
	 *
	 * Must be called with the group ID or group name as argument.
	 * Pass <kbd>'new'</kbd> as argument if you want to create a new group.
	 * See examples in {@link Group} class description.
	 *
	 * @param int|string $group_id_or_name Group ID or group name or 'new' if a new group should be created.
	 * @param array $table_columns Columns in the <i>psa_group</i> database table. If you add more
	 * columns to the database table, pass column names with this argument.
	 * @see save()
	 */
	public function __construct($group_id_or_name, array $table_columns = array('id', 'name')){

		if(!$group_id_or_name)
			throw new GroupException('Invalid group ID not or group name.', 301);

		// new group
		if($group_id_or_name === 'new'){
			parent::__construct('psa_group', 'id', null, $table_columns, 'psa_group_id_seq');
		}
		// existing group
		else{
			// is int
			if(isInt($group_id_or_name)){
				$this->id = (int) $group_id_or_name;
				parent::__construct('psa_group', 'id', $this->id, $table_columns, 'psa_group_id_seq');
			}
			else{
				if(!is_string($group_id_or_name))
					throw new UserException('Invalid group name.', 302);

				$this->name = $group_id_or_name;
				parent::__construct('psa_group', 'name', $this->name, $table_columns, 'psa_group_id_seq');
			}
		}
	}


	/**
	 * Restores member variables from the database previously saved with {@link save()} method.
	 *
	 * Reads values from the database and populates corresponding object member variables. For example,
	 * if you have added to <i>psa_group</i> table column named <kbd>access_level</kbd>, and you passed
	 * the name of the column to the constructor with <var>$table_columns</var> parameter. Then, when
	 * this method is called, member variable named <kbd>access_level</kbd> will be populated with
	 * a value from the database.
	 *
	 * @param array $only_columns Array with column names to restore from the database. If not set,
	 * all columns which names are passed to the constructor are restored.
	 * @return array Restored data from database.
	 * @see save()
	 * @throws GroupException
	 */
	public function restore(array $only_columns = array()){

		if(!$this->id && !$this->name){
			throw new GroupException("Group ID or group name are not set. Cannot load data about the group", 303);
		}

		try{
			return $this->restoreFromDatabase($only_columns);
		}
		catch (ActiveRecordException $e){
			throw new GroupException("Group does not exists. Cannot restore data.", 304);
		}
	}


	/**
	 * Saves values from the member variables to the database.
	 *
	 * It also sets {@link $id} member variable if a new group is created.
	 * See examples in {@link Group} class description. This method will throw
	 * {@link GroupException} on error.
	 *
	 * @param array $only_columns
	 * @return int ID of the saved group for success.
	 * @throws GroupException
	 */
	public function save(array $only_columns = array()){

		if($this->psa_new_record && $this->name){

			$this->saveToDatabase($only_columns);

			$log_message = "New group created";
		}
		else{
			$this->saveToDatabase();
			$log_message = "Group data saved";
		}

		// write log
		$this->log($log_message, __METHOD__, 2);

		return $this->id;
	}


	/**
	 * Puts an user or more users in the group if the <var>$user_id</var> argument is an array.
	 *
	 * Group membership changes are immediately stored in the database, and you don't have to
	 * call {@link save()} method after.
	 * If you are creating a new group, this method must be called after {@link save()} method.
	 * Throws {@link GroupException} on error.
	 *
	 * Example:
	 * <code>
	 * // group object
	 * $group = new Group('my_user_group');
	 * // put users with IDs 1,3 and 6 into the group
	 * $group->addUser(array(1,3,6));
	 * </code>
	 *
	 * @param int|array $user_id ID of the user. Or array with user IDs.
	 * @return int 1 for success, -1 user was already in the group (or more groups)
	 * or user doesn't exist
	 * @see removeUser()
	 * @throws GroupException
	 */
	public function addUser($user_id){
		return $this->addRemoveUser($user_id, 1);
	}


	/**
	 * Removes user from the group or removes more users if the <var>$user_id</var> argument is an array.
	 *
	 * Group membership changes are immediately stored in the database and you don't have to
	 * call {@link save()} method after.
	 * If you are creating a new group, this method must be called after {@link save()} method.
	 * Throws {@link GroupException} on error.
	 *
	 * Example:
	 * <code>
	 * // group object
	 * $group = new Group('my_user_group');
	 * // remove user with ID 6 from the group
	 * $group->removeUser(6);
	 * </code>
	 *
	 * @param int|array $user_id ID of the user. Or array with user IDs.
	 * @return int 1 for success, -1 user was not a member of the group (or more groups)
	 * or user doesn't exist
	 * @see removeUser()
	 * @throws GroupException
	 */
	public function removeUser($user_id){
		return $this->addRemoveUser($user_id, 0);
	}


	/**
	 * Puts user in the group or removes user from group (or more users if $user_id argument is array).
	 *
	 * This method is called from addUser() and removeUser() methods.
	 *
	 * @param int|array|string $user_id id of the user or array with user ids.
	 * @param int $action 1 add, 0 remove
	 * @return int 1 for success, -1
	 * @see addUser()
	 * @see removeUser()
	 * @throws GroupException
	 * @ignore
	 */
	protected function addRemoveUser($user_id, $action){

		if(!$this->id)
			throw new GroupException("Group ID not set.", 305);


		if(!$user_id)
			throw new GroupException('Invalid $user_id method parameter.', 306);


		// if $user_id is not array make it array for foreach loop
		if(!is_array($user_id))
			$user_id = array($user_id);

		// flag if some query failed in the foreach loop
		$success = $failed = 0;

		// add one or more users to the group or remove from group
		foreach ($user_id as $user_id_key => $user_id_value){

			$user_id_value = (int) $user_id_value;

			// add to group
			if($action == 1){
				$sql = 'INSERT INTO ' . Reg()->PSA_CFG['database']['table']['user_in_group'] . ' (group_id, user_id) VALUES (?, ?)';
				$q_params = array($this->id, $user_id_value);
			}
			// remove from group
			else if($action == 0){

				// remove all users from the group
				if($user_id_value == 'all'){
					$sql = 'DELETE FROM ' . Reg()->PSA_CFG['database']['table']['user_in_group'] . ' WHERE group_id=?';
					$q_params = array($this->id);
				}
				// remove specific user from the group
				else{
					$sql = 'DELETE FROM ' . Reg()->PSA_CFG['database']['table']['user_in_group'] . ' WHERE group_id=? AND user_id =?';
					$q_params = array($this->id, $user_id_value);
				}
			}

			try{
				// run query against the database
				Db()->execute($q_params, $sql);
			}
			catch(DbException $e){
				$failed = 1;
			}

			// if no rows affected user already wasn't or was in the group depending on $action
			if(Db()->affectedRows() <= 0)
				$failed = 1;
			else
				$success = 1;
		}

		if($success){

			// write log
			if(Reg()->PSA_CFG['logging']['max_log_level'] >= 2)
				$this->log("Members of group changed: " . implode(',', $user_id) . " action=" . ($action ? 'add' : 'remove'), __METHOD__, 2);

			if(!$failed)
				return 1;
		}

		return -1;
	}


	/**
	 * Handles logging for this class.
	 *
	 * @param string $message log message
	 * @param string $method class method which writes log message
	 * @param int $level log level
	 * @param string $type 'general', 'error', 'warning' ...
	 * @ignore
	 */
	protected function log($message, $method = '', $level = 1, $type = ''){

		// if logging is enabled
		if(Reg()->PSA_CFG['logging']['max_log_level'] >= $level){

			$log_data['group_id'] = $this->id;
			$log_data['groupname'] = $this->name;
			$log_data['message'] = $message;
			$log_data['function'] = $method;
			$log_data['level'] = $level;
			$log_data['type'] = $type;
			Logger()->log($log_data);
		}
	}
}
