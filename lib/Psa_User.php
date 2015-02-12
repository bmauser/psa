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
 * @version $Id: Psa_User.php 150 2013-10-24 16:32:54Z bmauser $
 */


/**
 * User object class.
 *
 * <br><b>Examples:</b>
 *
 * <br><b>1)</b> Create a new user:
 * <code>
 * // user object
 * $user = new Psa_User('new');
 *
 * // set username and password
 * $user->username = 'my_user';
 * $user->password = 'user_password';
 *
 * // save user to the database
 * $user->save();
 * </code>
 *
 * <br><b>2)</b> Authorize user by username and password:
 * <code>
 * $user = new Psa_User('my_user');
 * $user->authorize('user_password');
 * </code>
 *
 * <br><b>3)</b> Extend <kbd>Psa_User</kbd> class. You'll probably want to extend <kbd>Psa_User</kbd> class
 * and to add more columns to <i>psa_user</i> database table. In this example <kbd>user_level</kbd>,
 * <kbd>photo</kbd> and <kbd>email</kbd> columns are added to <i>psa_user</i> table.
 *
 * <code>
 * class MyUser extends Psa_User{
 *
 *     public function __construct($user_id_or_username){
 *         parent::__construct($user_id_or_username, array('id', 'username', 'user_level', 'photo', 'email'));
 *     }
 * }
 * </code>
 *
 * Code in a model method:
 * <code>
 * // create a new user
 * $user = new MyUser('new');
 * $user->username = 'my_user';
 * $user->user_level = 'admin';
 * $user->email = 'admin@host';
 * $user->save();
 * </code>
 *
 * <br><b>4)</b> Change user's group membership:
 * <code>
 * // user object
 * $user = new MyUser(10); // MyUser extends Psa_User
 *
 * // remove the user from all groups and put it in groups with ID 3,5 and 7
 * $user->remove_group('all');
 * $user->add_group(array(3,5,7));
 * </code>
 */
class Psa_User extends Psa_Active_Record{


	/**
	 * User ID.
	 *
	 * Value of the primary key column from <i>psa_user</i> database table.
	 *
	 * @var int
	 */
	public $id;


	/**
	 * Username.
	 *
	 * @var string
	 */
	public $username;


	/**
	 * Password.
	 *
	 * You will need to set this variable when you creating a new user.
	 *
	 * @var string
	 */
	public $password;


	/**
	 * UNIX timestamp of last login.
	 *
	 * @var int
	 * @see save_last_login_time()
	 */
	protected $last_login = null;




	/**
	 * Constructor method.
	 *
	 * - If called with '<kbd>new</kbd>' as the first argument, new user will be created when {@link save()}
	 *   method is called.
	 * - If called with user ID as the first argument, {@link $id} value is set. In this case, the
	 *   first argument must be an integer value.
	 * - If the first parameter is a string (different from '<kbd>new</kbd>'), the {@link $username} value is set.
	 *
	 * @param int|string $user_id_or_username '<kbd>new</kbd>' if new user should be created, or ID or username of the existing user.
	 * @param array $table_columns Array with column names in <i>psa_user</i> database table. See example 3
	 * above on how to extend this class and add more columns to the database table.
	 * @see restore()
	 */
	public function __construct($user_id_or_username, array $table_columns = array('id', 'username')){

		if(!$user_id_or_username)
			throw new Psa_User_Exception('Invalid user ID or username.', 201);

		// new user
		if($user_id_or_username === 'new'){
			parent::__construct('psa_user', 'id', null, $table_columns, 'psa_user_id_seq');
		}
		// existing user
		else{

			// is int
			if(psa_is_int($user_id_or_username)){
				$this->id = (int) $user_id_or_username;
				parent::__construct('psa_user', 'id', $this->id, $table_columns, 'psa_user_id_seq');
			}
			else{

				if(!is_string($user_id_or_username))
					throw new Psa_User_Exception('Invalid username.', 202);

				$this->username = $user_id_or_username;
				parent::__construct('psa_user', 'username', $this->username, $table_columns, 'psa_user_id_seq');
			}
		}
	}


	/**
	 * Does the same as {@link restore()} method, but also writes a log message and calls
	 * {@link session_save()} method.
	 *
	 * See examples in {@link Psa_User} class description.
	 *
	 * This function calls {@link restore()} method with <kbd>'username_password'</kbd> as the first argument
	 * and writes a log message that user is authorized.
	 *
	 * @param string $password User password.
	 * @param bool $write_success_login_logs If false, log message about successful authorization will not be written.
	 * @param bool $save_to_session If true, {@link session_save()} will be called to write user ID and username to
	 * the session if session is started before.
	 * @return array User data restored from the database.
	 * @see restore()
	 * @throws Psa_User_Exception
	 */
	public function authorize($password = null, $write_success_login_logs = true, $save_to_session = true){

		if($password){
			$this->password = $this->password_hash($password);
			$return = $this->restore('username_password');
		}
		else{
			$return = $this->restore();
		}

		// write log
		if($write_success_login_logs)
			$this->log('User authorized', __METHOD__, 2);

		if($save_to_session)
			$this->session_save(false);

		return $return;
	}


	/**
	 * Restores member variables from the database previously saved with {@link save()} method.
	 *
	 * Reads values from the database and populates corresponding object member variables. For example,
	 * if you have added to <i>psa_user</i> table column named <kbd>access_level</kbd> and you passed
	 * the name of the column to the constructor with <var>$table_columns</var> parameter, then when
	 * this method is called, member variable with name <kbd>access_level</kbd> will be
	 * populated with the value from the database.
	 *
	 * @return array Restored user data.
	 * @see authorize()
	 * @throws Psa_User_Exception
	 */
	public function restore($restore_by = null, $try_from_session = false){

		try{
			if($restore_by == 'username_password'){
				if(!$this->username or !$this->password)
					throw new Psa_User_Exception("Username and password not set.", 203);

				$sql = "SELECT <COLUMNS> FROM {$this->psa_registry->PSA_CFG['database']['table']['user']} WHERE username=? AND password=?";
				$q_params = array($this->username, $this->password);
				return $this->restore_from_database(array(), $sql, $q_params);
			}
			else if($this->id or $this->username){

				if($try_from_session){
					try{
						return $this->restore_from_session();
					}
					catch (Psa_Active_Record_Exception $e){
						return $this->restore_from_database();
					}
				}
				else
					return $this->restore_from_database();
			}
			else
				throw new Psa_User_Exception('Cannot restore user data. User id or username not set', 204);
		}
		catch (Psa_Active_Record_Exception $e){
			throw new Psa_User_Exception('Cannot restore user data. ' . $e->getMessage(), 205);
		}
	}


	/**
	 * Saves values from the member variables to the database.
	 *
	 * There must be a column in the database named the same as the member variable. It works only for
	 * variables mentioned in <var>$table_columns</var> argument to the constructor or by <var>$only_columns</var>
	 * argument to this method.
	 * All saved values will be restored when the {@link authorize()} or {@link restore()}
	 * methods are called.
	 * See examples in {@link Psa_User} class description.
	 *
	 * @see authorize()
	 * @param array $only_columns Array with column names in <i>psa_user</i> database table to work with.
	 * If not set, column names set by the constructor are used.
	 * @return int User ID.
	 * @throws Psa_User_Exception
	 */
	public function save(array $only_columns = array()){

		if($this->psa_new_record && !($this->username && $this->password))
			throw new Psa_User_Exception("Error creating a new user. Username & password not set.", 206);


		if($this->psa_new_record){

			if(!isset($this->password) or !$this->password)
				throw new Psa_User_Exception("Password for new user not set.", 214);

			// hash new password
			$this->password = $this->password_hash($this->password);

			parent::save_to_database($only_columns, array('password'));

			// write log
			$this->log('New user created', __METHOD__, 2);
		}
		else{
			parent::save_to_database($only_columns);
		}

		return $this->id;
	}


	/**
	 * Puts the user in a group or more groups if the <var>$group_id</var> argument is an array.
	 *
	 * Group membership changes are immediately stored in the database and you don't have to
	 * call {@link save()} method after.
	 * If you are creating a new user, this method must be called after {@link save()} method.
	 * {@link $id} member variable must be set before calling this method. If you pass username
	 * to the constructor, call {@link restore()} or {@link authorize()} method before to get the
	 * user ID from the database.
	 * Throws {@link Psa_User_Exception} on error.
	 *
	 * Example:
	 * <code>
	 * $user = new Psa_User(123);
	 * $user->add_group(5);
	 * </code>
	 *
	 * @param int|array $group_id ID or array with groups IDs.
	 * @return int 1 for success, -1 user already was in the group (or more groups)
	 * or group does not exist.
	 * @see remove_group()
	 * @throws Psa_User_Exception
	 */
	public function add_group($group_id){
		return $this->add_remove_group($group_id,1);
	}


	/**
	 * Remove the user from a group or more groups if $group_id argument is an array.
	 *
	 * Group membership changes are immediately stored in the database and you don't have to
	 * call {@link save()} method after.
	 * {@link $id} member variable must be set before calling this method. If you pass username
	 * to the constructor, call {@link restore()} or {@link authorize()} method before to get the
	 * user ID from the database.
	 * Throws {@link Psa_User_Exception} on error.
	 *
	 * Example:
	 * <code>
	 * $user = new Psa_User('some_username');
	 * $user->restore();
	 * $user->remove_group(4);
	 * </code>
	 *
	 * @param int|array|string $group_id ID or array with group IDs. '<kbd>all</kbd>'
	 * to remove user from all groups.
	 * @return int 1 for success, -1 user was not in the group (or more groups).
	 * or group does not exist.
	 * @see add_group()
	 * @throws Psa_User_Exception
	 */
	public function remove_group($group_id){
		return $this->add_remove_group($group_id,0);
	}


	/**
	 * Puts user in the group or removes user from group (or more groups if $group_id argument is array).
	 *
	 * This method is called from add_group() and remove_group() methods.
	 *
	 * @param int|array|string $group_id id of the group or array with group ids.
	 * @param int $action 1 add, 0 remove
	 * @return int 1 for success, -1
	 * @see add_group()
	 * @see remove_group()
	 * @throws Psa_User_Exception
	 * @ignore
	 */
	protected function add_remove_group($group_id, $action){

		if(!$this->id)
			throw new Psa_User_Exception('Error changing user group. User ID not set.', 207);

		if(!$group_id)
			throw new Psa_User_Exception('Error changing user group. Invalid group ID.', 208);


		// if $group_id is not array make it array for foreach loop
		if(!is_array($group_id))
			$group_id = array($group_id);

		// flag if some query failed in the foreach loop
		$success = $failed = 0;

		// add user to one or more groups or remove from group(s)
		foreach ($group_id as $group_id_key => $group_id_value){

			// add to group
			if($action == 1){
				$sql = "INSERT INTO {$this->psa_registry->PSA_CFG['database']['table']['user_in_group']} (user_id, group_id) VALUES (?, ?)";
				$q_params = array($this->id, $group_id_value);
			}
			// remove from group
			else if($action == 0){

				// remove all groups
				if($group_id_value == 'all'){
					$sql = "DELETE FROM {$this->psa_registry->PSA_CFG['database']['table']['user_in_group']} WHERE user_id=?";
					$q_params = array($this->id);
				}
				// remove specific group
				else{
					$sql = "DELETE FROM {$this->psa_registry->PSA_CFG['database']['table']['user_in_group']} WHERE user_id=? AND group_id=?";
					$q_params = array($this->id, $group_id_value);
				}
			}

			// run query against the database
			$this->psa_database->execute($q_params, $sql);

			// if no rows affected user already wasn't or was in the group depending on $action
			if($this->psa_database->affected_rows() <= 0)
				$failed = 1;
			else
				$success = 1;
		}

		if($success){

			// write log
			if($this->psa_registry->PSA_CFG['logging']['max_log_level'] >= 2)
				$this->log("Group membership changed: " . implode(',',$group_id) . " action=" . ($action ? 'add' : 'remove'),__METHOD__,2);

			if(!$failed)
				return 1;
		}

		return -1;
	}


	/**
	 * Changes user password.
	 *
	 * New password will be hashed with {@link password_hash()} method and stored in the database.
	 * You need to call this method only when you want to change the password for the existing user.
	 * Throws {@link Psa_User_Exception} on error.
	 *
	 * @param string $new_password New password.
	 * @return int 1 for success.
	 * @throws Psa_User_Exception
	 * @see password_hash()
	 */
	public function password_change($new_password){

		if((string)$new_password !== $new_password)
			throw new Psa_User_Exception('Error changing password. Invalid password.', 209);

		if($new_password){

			// encrypt new password
			$new_password = $this->password_hash($new_password);

			// update user in the database
			if($this->id){
				$sql = "UPDATE {$this->psa_registry->PSA_CFG['database']['table']['user']} SET password = ? WHERE id = ?";
				$q_params = array($new_password, $this->id);
			}
			else{
				$sql = "UPDATE {$this->psa_registry->PSA_CFG['database']['table']['user']} SET password = ? WHERE username = ?";
				$q_params = array($new_password, $this->username);
			}

			// run query against the database
			try{
				$this->psa_database->execute($q_params, $sql);

				// write log
				$this->log("Password changed",__METHOD__,2);

				return 1;
			}
			catch (Psa_Db_Exception $e){
				throw new Psa_User_Exception('Error changing password', 210);
			}
		}
	}


	/**
	 * Verifies user password.
	 *
	 * Checks if given string is the user's password. This method can be used in process of changing user password to
	 * verify the old password.
	 *
	 * @param string $password Password to check.
	 * @return int 1 given password is valid, 0 given password is invalid
	 * @see password_hash()
	 * @see password_change()
	 * @throws Psa_User_Exception
	 */
	public function password_verify($password){

		if(!$password)
			throw new Psa_User_Exception('Error verifying password.', 211);


		// serialize object
		$database_password = $this->password_hash($password);

		// update user in the database
		if($this->id){
			$sql = "SELECT id FROM {$this->psa_registry->PSA_CFG['database']['table']['user']} WHERE password = ? AND id = ?";
			$q_params = array($database_password, $this->id);
		}
		else{
			$sql = "SELECT id FROM {$this->psa_registry->PSA_CFG['database']['table']['user']} WHERE password = ? AND username = ?";
			$q_params = array($database_password, $this->username);
		}

		$this->psa_database->execute($q_params, $sql);

		$row = $this->psa_database->fetch_row();

		if($row['id'])
			return 1;
		else
			return 0;
	}


	/**
	 * Returns hashed password.
	 *
	 * It uses {@link http://www.php.net/manual/en/function.hash.php hash()} PHP function. The hash type is set by <var>$PSA_CFG['password_hash']</var>
	 * value in <kbd>config.php</kbd> file. By default is sha256.
	 *
	 * @param string $password String to be hashed.
	 * @see config.php
	 * @see password_change()
	 * @return string hashed password
	 */
	public function password_hash($password){

		// return hash
		return hash($this->psa_registry->PSA_CFG['password_hash'], $password);
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
		if($this->psa_registry->PSA_CFG['logging']['max_log_level'] >= $level){

			$log_data['user_id']  = $this->id;
			$log_data['username'] = $this->username;
			$log_data['message']  = $message;
			$log_data['function'] = $method;
			$log_data['level']    = $level;
			$log_data['type']     = $type;
			Logger()->log($log_data);
		}
	}


	/**
	 * Returns an array with all user's groups.
	 *
	 * Example of returning array:
	 * <pre>
	 * Array
	 * (
	 *     [11] => 'admins'  // array index is group id and value is group name
	 *     [14] => 'users'
	 *     [33] => 'other'
	 * )
	 * </pre>
	 *
	 * {@link $id} member variable must be set before calling this method. If you pass username
	 * to the constructor, call {@link restore()} or {@link authorize()} method before to get the
	 * user ID from the database.
	 *
	 * @return array
	 */
	public function get_groups(){

		if(!$this->id)
			throw new Psa_User_Exception('Error with setting user groups. User ID not set.', 212);


		// get data from all groups user is in
		$sql = "SELECT psa_user_in_group.group_id, psa_group.name FROM psa_user_in_group JOIN psa_group ON psa_user_in_group.group_id = psa_group.id WHERE psa_user_in_group.user_id = ?";
		$q_params = array($this->id);

		$this->psa_database->execute($q_params, $sql);

		$groups = array();

		// for each fetched row
		while($row = $this->psa_database->fetch_row()){
			$groups[$row['group_id']] = $row['name'];
		}

		return $groups;
	}


	/**
	 * Saves current timestamp to the database into the <kbd>last_login</kbd> column of the <kbd>psa_user</kbd> table.
	 *
	 * You can call this method when you want to set the last login time for the user. It
	 * will also set value of the {@link $last_login} member variable.
	 * Throws {@link Psa_User_Exception} on error.
	 *
	 * @return int 1 for success.
	 * @throws Psa_User_Exception
	 */
	public function save_last_login_time(){

		$this->last_login = time();

		$this->save(array('last_login'));

		return 1;
	}


	/**
	 * Stores user ID and username into the session.
	 *
	 * It will store an array named <kbd>psa_current_user_data</kbd> with user ID and username into the session.
	 * All log messages will contain user ID and username from this session
	 * variable if not explicitly set otherwise. See {@link Psa_Logger::log()} method for details.
	 *
	 * @param bool $throw_exception If true, Psa_User_Exception will be thrown if PHP session is not started.
	 * @throws Psa_User_Exception
	 */
	protected function session_save($throw_exception = true){

		// check if session is started
		if(!session_id()){
			if($throw_exception)
				throw new Psa_User_Exception('Session is not started. Cannot store user data into the session.', 213);
			else
				return false;
		}

		if($this->id)
			$_SESSION['psa_current_user_data']['id'] = $this->id;

		if($this->username)
			$_SESSION['psa_current_user_data']['username'] = $this->username;

		$this->save_to_session();

		return true;
	}
}
