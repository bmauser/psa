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
 * @version $Id: Psa_PDO.php 147 2013-10-04 16:38:50Z bmauser $
 */


/**
 * Simple database connection class and database access wrapper class.
 *
 * Handles database connection and executes queries against the database.
 * PSA uses {@link http://www.php.net/pdo PHP Data Objects (PDO)} extension for
 * database access. Methods in this class are wrappers for corresponding PDO methods.
 *
 * If you don't want to use methods from this class in your models, you can use
 * same database connection that can be accessed through <var>$this->psa_database->pdo</var>
 * which is PDO object.
 *
 * See in examples how to interact with database in model methods.
 *
 * <br><b>Examples:</b>
 *
 * <br><code>
 * class my_model extends Psa_Model {
 *
 *     // returns all records from a database table
 *     function get_all_records(){
 *
 *         $this->psa_database->query("SELECT * FROM table");
 *         return $this->psa_database->fetch_all();
 *     }
 *
 *
 *     // or the same as above
 *     function get_all_records1(){
 *         return $this->psa_database->fetch_all("SELECT * FROM table");
 *     }
 *
 *
 *     // returns one row from the table
 *     function get_one_row(){
 *         return $this->psa_database->fetch_row("SELECT * FROM table WHERE id = 123");
 *     }
 *
 *
 *     // iterates through each row
 *     function sum_all(){
 *
 *         $sum = 0;
 *
 *         $this->psa_database->query("SELECT * FROM table");
 *         while($row = $database->fetch_row()){
 *             $sum += $row['column1'] + $row['column2'] + $row['column3'];
 *         }
 *
 *         return $sum;
 *     }
 *
 *
 *     // executes prepared statement
 *     function save_values($value1, $value2, $value3){
 *
 *         // query
 *         $sql = "INSERT INTO table (column1, column2, column3) VALUES (?,?,?)";
 *
 *         // argument for execute() method
 *         $qparams = array($value1, $value2, $value3);
 *
 *         // execute query
 *         $this->psa_database->execute($qparams, $sql);
 *
 *         return $this->psa_database->last_insert_id();
 *     }
 *
 *
 *     // use transactions
 *     function save_values2($value1, $value2){
 *
 *         // start transaction
 *         // be sure to have database connection before beginTransaction()
 *         $this->psa_database->connect();
 *         $this->psa_database->pdo->beginTransaction();
 *
 *         try{
 *             $this->psa_database->execute(array($value1), "INSERT INTO table1 (column1) VALUES (?)");
 *
 *             $this->psa_database->execute(array($value2), "INSERT INTO table2 (column2) VALUES (?)");
 *         }
 *         catch (Psa_Db_Exception $e){
 *             // rollback
 *             $this->psa_database->pdo->rollback();
 *         }
 *
 *         // commit
 *         $this->psa_database->pdo->commit();
 *     }
 * }
 * </code>
 *
 * @see config.php
 */


class Psa_PDO{

	/**
	 * PHP PDO object.
	 *
	 * @var PDO
	 * @see connect()
	 * @see http://www.php.net/manual/en/book.pdo.php
	 */
	public $pdo = null;


	/**
	 * Query result.
	 *
	 * Result from {@link query()} or {@link execute()} method.
	 *
	 * @var PDOStatement
	 * @see query()
	 * @see execute()
	 */
	public $result;


	// Database connection parameters. If are set, will be used by connect() function.

	/**
	 * PDO data source name.
	 * @var string
	 */
	public $dsn = null;
	/**
	 * Database username.
	 * @var string
	 */
	public $username = null;
	/**
	 * Database password.
	 * @var string
	 */
	public $password = null;
	/**
	 * PDO driver options
	 * @var array
	 */
	public $driver_options = array();



	/**
	 * Performs a query against the database.
	 *
	 * Throws a {@link Psa_Db_Exception} if query fails.
	 *
	 * @param string $sql SQL query.
	 * @return boolean True on success.
	 * @throws Psa_Db_Exception
	 */
	public function query($sql){

		// connect to database if connection don't exists
		if (!$this->pdo){
			$this->connect();
		}

		// destroy previous result
		$this->result = null;

		// run query against the database
		try{
			$this->result = $this->pdo->query($sql);
		}
		catch(PDOException $e){

			$error = $this->pdo->errorInfo();

			throw new Psa_Db_Exception('SQL error: ' . $e->getMessage() . '. In query: ' . $sql, $e->getCode());
		}

		// if success
		return true;
	}


	/**
	 * Returns the number of rows affected by a DELETE, INSERT, or UPDATE statement.
	 *
	 * @return int|boolean Number of rows affected, false if there is no result.
	 */
	public function affected_rows(){

		if ($this->result)
			return $this->result->rowCount();
		else
			return false;
	}


	/**
	 * Returns the ID of the last inserted row.
	 *
	 * @param string $sequence Sequence name.
	 * @return string
	 */
	public function last_insert_id($sequence = null){

		return $this->pdo->lastInsertId($sequence);
	}


	/**
	 * Connects to the database.
	 *
	 * If called without arguments, it first uses connection parameters from object properties ({@link $dsn},
	 * {@link $username}, {@link $password}, {@link $driver_options}) and if
	 * {@link $dsn} value is not set, it takes connection parameters from <var>$PSA_CFG</var> config array
	 * (set in {@link config.php} or config_override.php).
	 * Throws a {@link Psa_Db_Exception} if the attempt to connect to the requested database fails.
	 *
	 * @param string $dsn PDO data source name.
	 * @param string $username Database user.
	 * @param string $password Database user's password.
	 * @param string $driver_options PDO driver-specific connection options.
	 * @param bool $throw_PDOException If true PDOException will be thrown instead Psa_Db_Exception.
	 * @return boolean
	 * @throws Psa_Db_Exception
	 * @see config.php
	 */
	public function connect($dsn = null, $username = null, $password = null, $driver_options = null, $throw_PDOException = 0){

		// if connected to the database
		if(!$this->pdo){

			$PSA_CFG = Psa_Registry::get_instance()->PSA_CFG;

			// if connection parameters are passed as method arguments
			if($dsn){
				$this->dsn = $dsn;
				$this->username = $username;
				$this->password = $password;
				$this->driver_options = $driver_options;
			}

			// if connection parameters are not set as object properties use default
			else if(!$this->dsn){
				$this->dsn = $PSA_CFG['pdo']['dsn'];
				$this->username = $PSA_CFG['pdo']['username'];
				$this->password = $PSA_CFG['pdo']['password'];
				if(isset($PSA_CFG['pdo']['driver_options']))
					$this->driver_options = $PSA_CFG['pdo']['driver_options'];
			}

			// Connect to database server
			try{
				$this->pdo = new PDO($this->dsn, $this->username, $this->password, $this->driver_options);
			}
			catch(PDOException $e){

				if($throw_PDOException)
					throw $e;

				// check if PDO extension is enabled
				if(substr($this->dsn,0,6) == 'mysql:' && !extension_loaded('pdo_mysql'))
					$msg1 = "pdo_mysql extension not enabled.";
				else if(substr($this->dsn,0,6) == 'pgsql:' && !extension_loaded('pdo_pgsql'))
					$msg1 = "pdo_pgsql extension not enabled.";
				else
					$msg1 = '';

				$message = "Unable to connect to database. $msg1 " . $e->getMessage();

				// log message cannot be written into the database if there is problem with database connection
				if($PSA_CFG['logging']['storage']['psa_default']['type'] == 'database')
					throw new Psa_Db_Exception($message, $e->getCode(), false);
				else
					throw new Psa_Db_Exception($message, $e->getCode());
			}
		}

		// return true on success or already connected
		return true;
	}


	/**
	 * Returns all rows from the result.
	 *
	 * @param string $sql Database query.
	 * @return array see http://php.net/manual/en/pdostatement.fetchall.php
	 */
	public function fetch_all($sql = null){

		if($sql)
			$this->query($sql);

		return $this->result->fetchAll(PDO::FETCH_ASSOC);
	}


	/**
	 * Fetch next row from result.
	 *
	 * @param string $sql Database query.
	 * @return array see http://www.php.net/manual/en/pdostatement.fetch.php
	 */
	public function fetch_row($sql = null){

		if($sql)
			$this->query($sql);

		return $this->result->fetch(PDO::FETCH_ASSOC);

	}


	/**
	 * Returns only one column from the result as one dimensional array.
	 *
	 * @param int $column Column index. 0 for the first column.
	 * @param string $sql Database query.
	 * @return array see http://www.php.net/manual/en/pdostatement.fetchall.php
	 */
	public function fetch_column($column = 0, $sql = null) {

		if($sql)
			$this->query($sql);

		return $this->result->fetchAll(PDO::FETCH_COLUMN, $column);
	}


	/**
	 * Escapes special characters in a string for use in a SQL statement.
	 *
	 * @param string $value String to escape.
	 * @return string Escaped string.
	 */
	public function escape($value) {

		// connect to database if connection don't exists
		if (!$this->pdo){
			$this->connect();
		}

		return $this->pdo->quote($value);
	}


	/**
	 * Prepares a statement for execution.
	 *
	 * @param string $sql
	 * @return object PDOStatement
	 * @see execute()
	 */
	public function prepare($sql) {

		// connect to database if connection don't exists
		if (!$this->pdo){
			$this->connect();
		}

		// prepare query
		$this->result = $this->pdo->prepare($sql);
		return $this->result;
	}


	/**
	 * Executes a prepared statement.
	 *
	 * Throws a {@link Psa_Db_Exception} if statement fails.
	 *
	 * @param array $params An array of values with as many elements as there are bound
	 * parameters in the SQL statement being executed.
	 * @param string|PDOStatement $statement SQL query or prepared PDO statement.
	 * @param bool $throw_PDOException If true PDOException will be thrown instead Psa_Db_Exception
	 * @return bool
	 * @see prepare()
	 */
	public function execute($params = null, $statement = null, $throw_PDOException = 0) {

		// if statement is not set
		if(!$statement)
			$statement = $this->result;
		else if($statement instanceof PDOStatement)
			$this->result = $statement;
		// if $statement is SQL query
		else
			$this->result = $statement = $this->prepare($statement);

		// execute prepared query
		try{
			if(is_array($params))
				$statement->execute($params);
			else
				$statement->execute();
		}
		catch(PDOException $e){

			if($throw_PDOException)
				throw $e;

			$error = $this->pdo->errorInfo();

			if(is_array($params))
				$query_params_str = ' Query input parameters: ' . implode(',',$params);
			else
				$query_params_str = '';

			throw new Psa_Db_Exception('SQL error: ' . $e->getMessage() . '. In query: ' . $statement->queryString . $query_params_str, $e->getCode());
		}

		// if success
		return true;
	}
}
