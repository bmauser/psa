<?php
/**
 * @package PSA
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
 * to the fields in the database table. When you call {@link saveToDatabase()} method, values assigned
 * to instance variables will be saved to the database. When you call {@link restoreFromDatabase()} method
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
 * $group->saveToDatabase();
 *
 * </code>
 *
 * <br>For example of usage, check source code of {@link Group} and {@link User} classes which extend this class.
 */
class ActiveRecord{


	/**
	 * New record flag.
	 *
	 * @var int
	 * @ignore
	 */
	protected $psa_new_record = false;


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
	 * Name of the sequence for primary key.
	 *
	 * @var string
	 * @ignore
	 */
	protected $psa_sequence_name = null;


	/**
	 * Array for modifier definitions.
	 *
	 * @var array
	 * @ignore
	 */
	protected $psa_modifiers = array();


	/**
	 * Array for SQL functions in SELECT, INSERT or UPDATE query per column.
	 *
	 * Example:
	 *
	 * <code>
	 * $psa_column_settings['col_name1']['select_sql'] = "RPAD(data, 20, '*') AS data";
	 * $psa_column_settings['col_name2']['select_sql'] = "DATE_FORMAT(event_time, '%d.%m.%Y') AS event_time";
	 * $psa_column_settings['col_name2']['insert_sql'] = "NOW()";
	 * $psa_column_settings['col_name2']['no_update'] = true;
	 * $psa_column_settings['col_name3']['insert_sql'] = "RPAD(?, 20, '*')";
	 * $psa_column_settings['col_name4']['insert_sql'] = "FUNC()";
	 * $psa_column_settings['col_name4']['insert_no_params'] = true;
	 * </code>
	 *
	 * @var array
	 * @see setColumnSettings()
	 * @ignore
	 */
	protected $psa_column_settings = array();



	/**
	 * Constructor.
	 *
	 * @param string $table_name Name of the database table.
	 * @param string $primary_key_field_name Name of the primary key field in the database table.
	 * @param string $primary_key_value Value of the primary key field. If not set, a new row
	 * to the table will be added when {@link saveToDatabase()} method is called.
	 * @param array $column_names Array with column names from the database table to work with.
	 * Not all columns need to be included. If you skip to set this argument, column names will be
	 * set automatically. That is one query more to the database on the object initialization to get
	 * a table schema, so it's better to provide column names by setting this argument.
	 * @param string $primary_key_sequence_name Name of the primary key field sequence.
	 * @param Db $database_connection Database connection to use.
	 */
	protected function __construct($table_name, $primary_key_field_name, $primary_key_value = null, array $column_names = array(), $primary_key_sequence_name = null, $database_connection = null){

		// reference to database object
		if(!$database_connection)
			$this->psa_database = Reg()->psa_database;
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
			$this->autoSetColumnNames();
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
	 * @throws ActiveRecordException
	 * @return array
	 */
	protected function restoreFromDatabase(array $only_columns = array(), $custom_query = null, $custom_query_params = array()){

		if($only_columns)
			$use_columns = $only_columns;
		else
			$use_columns = $this->psa_column_names;

		if(!$use_columns){
			throw new ActiveRecordException("Table column names not set", 701);
		}

		// select sql per column
		if($this->psa_column_settings){
			foreach ($this->psa_column_settings as $column_name => $settings){
				if(isset($settings['select_sql'])){
					$use_columns[$column_name] = $settings['select_sql'];
				}
			}
		}

		if($custom_query){
			$custom_query = str_replace('<COLUMNS>', implode(', ', $use_columns), $custom_query);
			$this->psa_database->execute($custom_query_params, $custom_query);
			$data = $this->psa_database->fetchRow();
		}
		else{
			// construct default query
			$sql = 'SELECT ' . implode(', ', $use_columns)  . ' FROM ' . $this->psa_table_name . ' WHERE ' . $this->psa_primary_key_field_name . ' = ?';
			$this->psa_database->execute(array( $this->{$this->psa_primary_key_field_name}), $sql);
			$data = $this->psa_database->fetchRow();
		}

		if(!$data){
			throw new ActiveRecordException("No data for {$this->psa_primary_key_field_name} with value '{$this->{$this->psa_primary_key_field_name}}' in table {$this->psa_table_name}", 702);
		}

		$modifier_count = $this->countModifiers('after_restore_from_database');

		// set local member variables
		foreach ($data as $key => $value) {

			// call modifier
			if($modifier_count)
				$this->$key = $this->callModifier('after_restore_from_database', $key, $value);
			else
				$this->$key = $value;
		}

		return $data;
	}


	/**
	 * Gets all column names from the database.
	 *
	 * @throws ActiveRecordException
	 * @ignore
	 */
	protected function autoSetColumnNames(){

		if(!isset(Reg()->PSA_CFG['pdo']['database'])){
			throw new ActiveRecordException('Please set the database name to $PSA_CFG[\'pdo\'][\'database\'] configuration value or better pass the column names to constructor.', 703);
		}

		$sql = 'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = \'' . $this->psa_table_name . '\' AND table_schema = \'' . Reg()->PSA_CFG['pdo']['database'] . '\'';
		$this->psa_column_names = $this->psa_database->fetchColumn(0, $sql);
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
	 * @throws ActiveRecordException
	 * @return int Returns the value of the primary key column.
	 */
	protected function saveToDatabase(array $only_columns = array(), array $and_columns = array()){

		$query_data = $this->getQueryData($only_columns, $and_columns);

		//prs($query_data);

		// execute query
		$this->psa_database->execute($query_data['params'], $query_data['sql']);

		if($query_data['operation'] == 'insert'){

			$this->psa_new_record = 0;

			// get the last insert id
			return $this->{$this->psa_primary_key_field_name} = (int) $this->psa_database->lastInsertId($this->psa_sequence_name);
		}
		else{
			return $this->{$this->psa_primary_key_field_name};
		}
	}


	/**
	 * Returns database query and params for binding.
	 *
	 * @see saveToDatabase()
	 * @ignore
	 */
	protected function getQueryData(array $only_columns = array(), array $and_columns = array()){

		$return = array();

		// if is insert or update
		if($this->psa_new_record)
			$operation = 'insert';
		else
			$operation = 'update';


		// array with columns involved in the query
		$columns = $this->columnsForSave($only_columns, $and_columns, 'before_save_to_database', $operation);

		if($operation == 'insert'){

			// names of columns in query
			$col_names = implode(', ', array_keys($columns));

			// insert query
			$return['sql'] = 'INSERT INTO ' . $this->psa_table_name . ' (' . $col_names . ') VALUES (' . implode(',', $this->sqlValuesForSave($columns, $operation)) . ')';
		}
		else{
			// remove  primary key field from $columns
			if(array_key_exists($this->psa_primary_key_field_name, $columns))
				unset($columns[$this->psa_primary_key_field_name]);

			// parts of sql with values
			$marks = $this->sqlValuesForSave($columns, $operation);

			// put primary key field in the last place
			$columns[] = $this->{$this->psa_primary_key_field_name};

			// update query
			$return['sql'] = 'UPDATE ' . $this->psa_table_name . ' SET ' . implode(', ', $marks) . ' WHERE ' . $this->psa_primary_key_field_name . '=?';
		}

		// values for query params
		$return['params'] = $this->prepareValuesForQueryParams($columns, $operation);
		$return['operation'] = $operation;

		return $return;
	}


	/**
	 * Saves values from the object's member variables to the session.
	 *
	 * This method will save values from object's member variables mentioned in <var>$column_names</var>
	 * parameter in the constructor method to the session.
	 *
	 * @param array $only_columns Array with column names to save to the database. If not set,
	 * column names set by the constructor are used.
	 * @param array $and_columns Array with column names to be saved together with default columns set
	 * by the constructor or <var>$only_columns</var> argument.
	 * @param string $save_key Database table name will be used by default.
	 * @see restoreFromSession()
	 * @throws ActiveRecordException
	 * @return array
	 */
	protected function saveToSession(array $only_columns = array(), array $and_columns = array(), $save_key = null){

		// check if session is started
		if(!session_id() && !defined('PSA_TEST'))
			throw new ActiveRecordException('Session is not started. Cannot save data into the session.', 705);

		// if no value for primary key
		if(!isset($this->{$this->psa_primary_key_field_name}) or !$this->{$this->psa_primary_key_field_name})
			throw new ActiveRecordException("Value for primary key not set.", 706);

		if(!$save_key)
			$save_key = $this->psa_table_name;

		return Session()['psa_active_record_data'][$save_key][$this->{$this->psa_primary_key_field_name}] = $this->columnsForSave($only_columns, $and_columns);
	}


	/**
	 * Restores data from the session saved with the {@link saveToSession()} method and populates
	 * objects member variables.
	 *
	 * @param string $save_key Database table name will be used by default.
	 * @see restoreFromSession()
	 * @throws ActiveRecordException
	 * @return array
	 */
	protected function restoreFromSession($save_key = null){

		// check if session is started
		if(!session_id() && !defined('PSA_TEST'))
			throw new ActiveRecordException('Session is not started. Cannot restore data from the session.', 707);

		// if no value for primary key
		if(!isset($this->{$this->psa_primary_key_field_name}) or !$this->{$this->psa_primary_key_field_name})
			throw new ActiveRecordException('Value for primary key not set.', 708);

		if(!$save_key)
			$save_key = $this->psa_table_name;

		// get data from session
		if(isset(Session()['psa_active_record_data'][$save_key][$this->{$this->psa_primary_key_field_name}])){

			$data = Session()['psa_active_record_data'][$save_key][$this->{$this->psa_primary_key_field_name}];

			if(is_array($data)){
				foreach($data as $key => $value){
					$this->$key = $value;
				}

				return $data;
			}
		}

		throw new ActiveRecordException('No data in session', 709);
	}


	/**
	 * Returns column names and values for query parameters.
	 *
	 * See {@link saveToDatabase()} method for description of arguments and NULL values.
	 *
	 * @param array $only_columns
	 * @param array $and_columns
	 * @param string $call_modifier
	 * @param string $sql_operation 'insert' or 'update'
	 * @return array Associative array with names and values.
	 * @see saveToDatabase()
	 * @ignore
	 */
	protected function columnsForSave(array $only_columns = array(), array $and_columns = array(), $call_modifier = null, $sql_operation = null){

		$return = array();
		$modifier_count = 0;

		if($only_columns)
			$use_columns = $only_columns;
		else
			$use_columns = $this->psa_column_names;

		if($and_columns)
			$use_columns = array_merge($use_columns, $and_columns);

		if($use_columns){

			if($call_modifier)
				$modifier_count = $this->countModifiers($call_modifier);

			foreach ($use_columns as $col_name){

				// skip columns with no_insert or no_update setting
				if(isset($this->psa_column_settings[$col_name]['no_' . $sql_operation]))
					continue;

				// include columns with no needed binded value
				if(isset($this->psa_column_settings[$col_name][$sql_operation . '_no_params'])){
					$return[$col_name] = 'psa_no_param';
				}
				// if member var is set and is not NULL
				else if(isset($this->$col_name) && $this->$col_name !== null){

					$return[$col_name] = $this->$col_name !== 'NULL' ? $this->$col_name : null;

					// call modifier
					if($modifier_count)
						$return[$col_name] = $this->callModifier($call_modifier, $col_name);
				}
			}

			return $return;

		}

		throw new ActiveRecordException('No values set to save.', 704);
	}


	/**
	 * Registers data modifier.
	 *
	 * This method is useful when data stored in the database needs to be formatted.
	 * For example an array can be serialized before saving it to the database and unserialized
	 * when is restored from the database.
	 *
	 * <b>Example:</b>
	 *
	 * <code>
	 * <?php
	 *
	 * class MyClass extends Psa_Active_Record{
	 *
	 *     public function __construct($record_id){
	 *
	 *         // call parent constructor
	 *         parent::__construct('my_table', 'id', $record_id, array('column1', 'column2', 'column3'));
	 *
	 *         // register modifier for column1
	 *         $this->registerDataModifier('before_save_to_database', 'column1',  function($value){
	 *             return serialize($value);
	 *         });
	 *
	 *         $this->registerDataModifier('after_restore_from_database', 'column1',  function($value){
	 *             return unserialize($value);
	 *         });
	 * }
	 * </code>
	 *
	 * @param string $modifier_type <kbd>before_save_to_database</kbd> or <kbd>after_restore_from_database</kbd>
	 * @param string $property_name Name of the property on which modifier is bound.
	 * @param function $function Callback function.
	 * @throws ActiveRecordException
	 */
	protected function registerDataModifier($modifier_type, $property_name, $function){

		$types = array(
				'before_save_to_database',
				//'before_save_to_session',
				'after_restore_from_database',
				//'after_restore_from_session',
				);

		if(!in_array($modifier_type, $types))
			throw new ActiveRecordException("Invalid modifier type: $modifier_type", 710);

		$this->psa_modifiers[$modifier_type][$property_name] = $function;
	}


	/**
	 * Registers modifier action.
	 *
	 * @param string $modifier_type
	 * @param string $function
	 * @param string|function $function
	 * @throws ActiveRecordException
	 * @return multitype
	 * @ignore
	 */
	protected function callModifier($modifier_type, $property_name, $value = null){

		if($value === null && isset($this->$property_name))
			$value = $this->$property_name;

		if(isset($this->psa_modifiers[$modifier_type][$property_name]) && is_callable($this->psa_modifiers[$modifier_type][$property_name])){

			// invoke modifier
			return $this->psa_modifiers[$modifier_type][$property_name]($value);
		}

		return $value;
	}


	/**
	 * Counts elements in $psa_modifiers member array;
	 *
	 * @param string $modifier_type
	 * @ignore
	 */
	protected function countModifiers($modifier_type){

		if($modifier_type && isset($this->psa_modifiers[$modifier_type])){
			return count($this->psa_modifiers[$modifier_type]);
		}

		return 0;
	}


	/**
	 * Returns array with values for query parameters.
	 *
	 * @param array $columns
	 * @param string $sql_operation 'insert' or 'update'
	 * @ignore
	 */
	protected function prepareValuesForQueryParams($columns, $sql_operation = null){

		// columns with no ? in query
		if($sql_operation){
			foreach ($columns as $column_name => $column_value) {
				if(isset($this->psa_column_settings[$column_name][$sql_operation . '_no_params']))// or if($column_value == 'psa_no_param')
					unset($columns[$column_name]);
			}
		}

		return array_values($columns);
	}


	/**
	 * Returns part of SQL query with values.
	 *
	 * @param array $columns
	 * @param string $operation 'insert' or 'update'
	 * @return array
	 * @ignore
	 */
	protected function sqlValuesForSave($columns, $sql_operation){

		$marks = array();

		foreach ($columns as $column_name => $column_value) {

			if(isset($this->psa_column_settings[$column_name][$sql_operation . '_sql']))
				$val = $this->psa_column_settings[$column_name][$sql_operation . '_sql'];
			else
				$val = '?';

			if($sql_operation == 'update')
				$marks[] = $column_name . '=' . $val;
			else
				$marks[] = $val;

		}

		return $marks;
	}


	/**
	 * Function for setting custom SQL per column in select, insert or update queries.
	 *
	 * Example:
	 *
	 * <code>
	 * $options['select_sql'] = "DATE_FORMAT(date_created, '%d.%m.%Y') AS date_created";
	 * $options['insert_sql'] = "NOW()";
	 * $options['no_update'] = true; // skip column in update query
	 * $options['insert_no_params'] = true; // no bound query parameters in insert_sql
	 *
	 * setColumnSettings('date_created', $options);
	 * </code>
	 *
	 * @param string $column_name Name of the column.
	 * @param array $options Array with option values. Array keys can be: select_sql, insert_sql, update_sql, insert_update_sql, insert_no_params, update_no_params, no_insert, no_update
	 */
	protected function setColumnSettings($column_name, Array $options){

		// set insert_sql and update_sql by insert_update_sql
		if(isset($options['insert_update_sql']) && $options['insert_update_sql'])
			$options['insert_sql'] = $options['update_sql'] = $options['insert_update_sql'];

		foreach ($options as $option_name => $option) {
			if($option)
				$this->psa_column_settings[$column_name][$option_name] = $option;
			else if(isset($this->psa_column_settings[$column_name]) && array_key_exists($option_name, $this->psa_column_settings[$column_name]))
				unset($this->psa_column_settings[$column_name][$option_name]);
		}
	}

}
