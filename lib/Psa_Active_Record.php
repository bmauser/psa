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
 * @package PSA/more
 * @version $Id: Psa_Active_Record.php 144 2013-09-30 23:12:52Z bmauser $
 */


/**
 * Implements a simple {@link https://en.wikipedia.org/wiki/Active_record_pattern Active Record} pattern.
 *
 * Active record is an approach to accessing data in a database. An object instance
 * is tied to a single row in the database table. After creation of an object, a new
 * row is added to the table upon save. Any object restored gets its information from
 * the existing row in the database.
 *
 * By extending this class you can make an object which has member variables that correspond
 * to the fields in the database table. When you call {@link save_to_database()} method, values assigned
 * to instance variables will be saved to the database. When you call {@link restore_from_database()} method
 * member variables named the same as database columns will be populated with values from the
 * database.
 *
 * <b>Example:</b>
 *
 * <code>
 * <?php
 *
 * class MyUserGroup extends Psa_Active_Record{
 *
 *     public function __construct($group_id){
 *
 *         // columns in the 'group_table' database table
 *         $table_columns = array('group_id', 'group_name', 'group_status');
 *
 *         // call constructor form Psa_Active_Record class
 *         parent::__construct('group_table', 'id', $group_id, $table_columns);
 *     }
 * }
 *
 * // change the value of the group_status column for group with ID 123
 * $group = new MyUserGroup(123);
 * $group->group_status = 'Not active';
 * $group->save_to_database();
 *
 * </code>
 *
 * <br>For example of usage, check source code of {@link Psa_Group} and {@link Psa_User} classes which extend this class.
 */
class Psa_Active_Record{


	/**
	 * New record flag.
	 *
	 * @var int
	 * @ignore
	 */
	protected $psa_new_record = false;


	/**
	 * Database connection object
	 *
	 * @var Psa_PDO
	 * @ignore
	 */
	protected $psa_database;


	/**
	 * Array that holds names of table columns.
	 *
	 * @var array
	 * @ignore
	 */
	protected $psa_column_names = array();


	/**
	 * Database table name.
	 *
	 * @var string
	 * @ignore
	 */
	protected $psa_table_name = null;


	/**
	 * Primary key field name.
	 *
	 * @var string
	 * @ignore
	 */
	protected $psa_primary_key_field_name = null;


	/**
	 * Reference to Psa_Registry object.
	 *
	 * @var string
	 * @ignore
	 */
	protected $psa_registry = null;


	/**
	 * Name of the sequence for primary key.
	 *
	 * @var string
	 * @ignore
	 */
	protected $psa_sequence_name = null;


	/**
	 * Constructor.
	 *
	 * @param string $table_name Name of the database table.
	 * @param string $primary_key_field_name Name of the primary key field in the database table.
	 * @param string $primary_key_value Value of the primary key field. If not set, a new row
	 * to the table will be added when {@link save_to_database()} method is called.
	 * @param array $column_names Array with column names from the database table to work with.
	 * Not all columns need to be included. If you skip to set this argument, column names will be
	 * set automatically. That is one query more to the database on the object initialization to get
	 * a table schema, so it's better to provide column names by setting this argument.
	 * @param string $primary_key_sequence_name Name of the primary key field sequence.
	 * @param Psa_PDO $database_connection Database connection to use.
	 */
	protected function __construct($table_name, $primary_key_field_name, $primary_key_value = null, array $column_names = array(), $primary_key_sequence_name = null, Psa_PDO $database_connection = null){

		$this->psa_registry = Psa_Registry::get_instance();

		// reference to database object
		if(!$database_connection)
			$this->psa_database = $this->psa_registry->psa_database;
		else
			$this->psa_database = $database_connection;

		$this->psa_primary_key_field_name = $primary_key_field_name;
		$this->psa_table_name = $table_name;
		$this->psa_sequence_name = $primary_key_sequence_name;
		$this->$primary_key_field_name = $primary_key_value;

		if($primary_key_value === null){
			// Set the flag that a new database row should be inserted.
			$this->psa_new_record = true;
		}

		if(!$column_names){
			// find all table columns
			$this->auto_set_column_names();
		}
		else
			$this->psa_column_names = $column_names;
	}


	/**
	 * Fetches data from the database and populates objects member variables that correspond to columns
	 * in the database table.
	 *
	 * @param array $only_columns Array with column names to restore from the database. If not set,
	 * column names set by the constructor are used.
	 * @param string $custom_query Query to fetch data from the database. '<kbd><COLUMNS></kbd>' will
	 * be replaced with comma delimited column names.
	 * @param array $custom_query_params Array with query parameters for <var>$custom_query</var> argument.
	 * @throws Psa_Active_Record_Exception
	 * @return array
	 */
	protected function restore_from_database(array $only_columns = array(), $custom_query = null, $custom_query_params = array()){

		if($only_columns)
			$use_columns = $only_columns;
		else
			$use_columns = $this->psa_column_names;

		if(!$use_columns){
			throw new Psa_Active_Record_Exception("Table column names not set", 701);
		}

		if($custom_query){
			$custom_query = str_replace('<COLUMNS>', implode(', ', $use_columns), $custom_query);
			$this->psa_database->execute($custom_query_params, $custom_query);
			$data = $this->psa_database->fetch_row();
		}
		else{
			// construct query
			$sql = 'SELECT ' . implode(', ', $use_columns)  . ' FROM ' . $this->psa_table_name . ' WHERE ' . $this->psa_primary_key_field_name . ' = ?';
			$this->psa_database->execute(array( $this->{$this->psa_primary_key_field_name}), $sql);
			$data = $this->psa_database->fetch_row();
		}

		if(!$data){
			throw new Psa_Active_Record_Exception("No data for {$this->psa_primary_key_field_name} with value '{$this->{$this->psa_primary_key_field_name}}' in table {$this->psa_table_name}", 702);
		}

		// set local member variables
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}

		return $data;
	}


	/**
	 * Gets all column names from the database.
	 *
	 * @throws Psa_Active_Record_Exception
	 * @ignore
	 */
	protected function auto_set_column_names(){

		if(!isset($this->psa_registry->PSA_CFG['pdo']['database'])){
			throw new Psa_Active_Record_Exception('Please set the database name to $PSA_CFG[\'pdo\'][\'database\'] configuration value or better pass the column names to constructor.', 703);
		}

		$sql = 'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = \'' . $this->psa_table_name . '\' AND table_schema = \'' . $this->psa_registry->PSA_CFG['pdo']['database'] . '\'';
		$this->psa_column_names = $this->psa_database->fetch_column(0, $sql);
	}


	/**
	 * Saves values from the object's member variables to the database.
	 *
	 * Unset member variables or those with NULL value will be excluded from saving. If you want to save
	 * NULL value to the database, assign the string "<kbd>NULL</kbd>" to the member variable.
	 *
	 * @param array $only_columns Array with column names to save to the database. If not set,
	 * column names set by the constructor are used.
	 * @param array $and_columns Array with column names to be saved together with default columns set
	 * by the constructor or <var>$only_columns</var> argument.
	 * @throws Psa_Active_Record_Exception
	 * @return int Returns the value of the primary key column.
	 */
	protected function save_to_database(array $only_columns = array(), array $and_columns = array()){

		$columns = $this->columns_for_save($only_columns, $and_columns);

		if($this->psa_new_record){

			$col_count = count($columns);

			$col_names = implode(', ', array_keys($columns));
			$t = array_fill(0, $col_count, '?');
			$marks = implode(',', $t);

			$sql = 'INSERT INTO ' . $this->psa_table_name . ' (' . $col_names . ') VALUES (' . $marks . ')';
			$this->psa_database->execute(array_values($columns), $this->psa_database->prepare($sql));

			$this->psa_new_record = 0;

			// get the last insert id
			return $this->{$this->psa_primary_key_field_name} = (int) $this->psa_database->last_insert_id($this->psa_sequence_name);
		}
		else{
			// remove  primary key field from $columns
			if(array_key_exists($this->psa_primary_key_field_name, $columns))
				unset($columns[$this->psa_primary_key_field_name]);

			$col_names = implode('=?, ', array_keys($columns)) . '=?';

			// put primary key field in the last place
			$columns[] = $this->{$this->psa_primary_key_field_name};

			$sql = 'UPDATE ' . $this->psa_table_name . ' SET ' . $col_names . ' WHERE ' . $this->psa_primary_key_field_name . '=?';
			$this->psa_database->execute(array_values($columns), $this->psa_database->prepare($sql));

			return $this->{$this->psa_primary_key_field_name};
		}
	}


	/**
	 * Saves values from the object's member variables to the session.
	 *
	 * This method will save values from object's member variables mentioned in <var>$column_names</var>
	 * parameter in constructor method to session.
	 *
	 * @param array $only_columns Array with column names to save to the database. If not set,
	 * column names set by the constructor are used.
	 * @param array $and_columns Array with column names to be saved together with default columns set
	 * by the constructor or <var>$only_columns</var> argument.
	 * @param string $save_key Database table name will be used by default.
	 * @see restore_from_session()
	 * @throws Psa_Active_Record_Exception
	 * @return array
	 */
	protected function save_to_session(array $only_columns = array(), array $and_columns = array(), $save_key = null){

		// check if session is started
		if(!session_id())
			throw new Psa_Active_Record_Exception('Session is not started. Cannot save data into the session.', 705);

		// if no value for primary key
		if(!isset($this->{$this->psa_primary_key_field_name}) or !$this->{$this->psa_primary_key_field_name})
			throw new Psa_Active_Record_Exception("Value for primary key not set.", 706);

		if(!$save_key)
			$save_key = $this->psa_table_name;

		return $_SESSION['psa_active_record_data'][$save_key][$this->{$this->psa_primary_key_field_name}] = $this->columns_for_save($only_columns, $and_columns);
	}


	/**
	 * Restores data from the session saved with the {@link save_to_session()} method and populates
	 * objects member variables.
	 *
	 * @param string $save_key Database table name will be used by default.
	 * @see restore_from_session()
	 * @throws Psa_Active_Record_Exception
	 * @return array
	 */
	protected function restore_from_session($save_key = null){

		// check if session is started
		if(!session_id())
			throw new Psa_Active_Record_Exception('Session is not started. Cannot restore data from the session.', 707);

		// if no value for primary key
		if(!isset($this->{$this->psa_primary_key_field_name}) or !$this->{$this->psa_primary_key_field_name})
			throw new Psa_Active_Record_Exception('Value for primary key not set.', 708);

		if(!$save_key)
			$save_key = $this->psa_table_name;

		// get data from session
		if(isset($_SESSION['psa_active_record_data'][$save_key][$this->{$this->psa_primary_key_field_name}])){

			$data = $_SESSION['psa_active_record_data'][$save_key][$this->{$this->psa_primary_key_field_name}];

			if(is_array($data)){
				foreach ($data as $key => $value) {
					$this->$key = $value;
				}

				return $data;
			}
		}

		throw new Psa_Active_Record_Exception('No data in session', 709);
	}


	/**
	 * Returns names and values for data to save.
	 *
	 * See {@link save_to_database()} method for description of arguments and NULL values.
	 *
	 * @param array $only_columns
	 * @param array $and_columns
	 * @return array Associative array with names and values.
	 * @see save_to_database()
	 * @ignore
	 */
	protected function columns_for_save(array $only_columns = array(), array $and_columns = array()){

		$return = array();

		if($only_columns)
			$use_columns = $only_columns;
		else
			$use_columns = $this->psa_column_names;

		if($and_columns)
			$use_columns = array_merge($use_columns, $and_columns);

		if($use_columns){
			foreach ($use_columns as $col_name){

				if(isset($this->$col_name) && $this->$col_name !== null){

					$return[$col_name] = $this->$col_name !== 'NULL' ? $this->$col_name : null;
				}
			}

			return $return;
		}

		throw new Psa_Active_Record_Exception('No values set to save.', 704);
	}
}
