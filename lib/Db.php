<?php
/**
 * @package PSA
 */


/**
 * Simple database connection class and database access wrapper class.
 *
 * Handles database connection and executes queries against the database.
 * PSA uses {@link http://www.php.net/pdo PHP Data Objects (PDO)} extension for
 * database access. Methods in this class are wrappers for corresponding PDO methods.
 *
 * If you don't want to use methods from this class in your models, you can use
 * same database connection that can be accessed through <var>Db()->pdo</var>
 * which is PDO object.
 *
 * See in examples how to interact with database in model methods.
 *
 * <br><b>Examples:</b>
 *
 * <br><code>
 * class my_model{
 *
 *     // returns all records from a database table
 *     function get_all_records(){
 *
 *         Db()->query("SELECT * FROM table");
 *         return Db()->fetchAll();
 *     }
 *
 *
 *     // or the same as above
 *     function get_all_records1(){
 *         return Db()->fetchAll("SELECT * FROM table");
 *     }
 *
 *
 *     // returns one row from the table
 *     function get_one_row(){
 *         return Db()->fetchRow("SELECT * FROM table WHERE id = 123");
 *     }
 *
 *
 *     // iterates through each row
 *     function sum_all(){
 *
 *         $sum = 0;
 *
 *         Db()->query("SELECT * FROM table");
 *         while($row = $database->fetchRow()){
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
 *         Db()->execute($qparams, $sql);
 *
 *         return Db()->last_insert_id();
 *     }
 *
 *
 *     // use transactions
 *     function save_values2($value1, $value2){
 *
 *         // start transaction
 *         // be sure to have database connection before beginTransaction()
 *         Db()->connect();
 *         Db()->pdo->beginTransaction();
 *
 *         try{
 *             Db()->execute(array($value1), "INSERT INTO table1 (column1) VALUES (?)");
 *
 *             Db()->execute(array($value2), "INSERT INTO table2 (column2) VALUES (?)");
 *         }
 *         catch (DbException $e){
 *             // rollback
 *             Db()->pdo->rollback();
 *             throw $e;
 *         }
 *
 *         // commit
 *         Db()->pdo->commit();
 *     }
 * }
 * </code>
 *
 * @see config.php
 * @asFunction Db Db getInstance
 */


class Db{

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


	/**
	 * Performs a query against the database.
	 *
	 * Throws a {@link DbException} if query fails.
	 *
	 * @param string $sql SQL query.
	 * @return boolean True on success.
	 * @throws DbException
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

			throw new DbException('SQL error: ' . $e->getMessage() . '. In query: ' . $sql, $e->getCode());
		}

		// if success
		return true;
	}


	/**
	 * Returns the number of rows affected by a DELETE, INSERT, or UPDATE statement.
	 *
	 * @return int|boolean Number of rows affected, false if there is no result.
	 */
	public function affectedRows(){

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
	public function lastInsertId($sequence = null){

		return $this->pdo->lastInsertId($sequence);
	}


	/**
	 * Connects to the database.
	 *
	 * If called without arguments, it takes connection parameters from <var>$PSA_CFG</var> config array
	 * (set in {@link config.php} or config_override.php).
	 * Throws a {@link DbException} if the attempt to connect to the requested database fails.
	 *
	 * @param string $dsn PDO data source name.
	 * @param string $username Database user.
	 * @param string $password Database user's password.
	 * @param string $driver_options PDO driver-specific connection options.
	 * @param bool $throw_PDOException If true PDOException will be thrown instead DbException.
	 * @return boolean
	 * @throws DbException
	 * @see config.php
	 */
	public function connect($dsn = null, $username = null, $password = null, $driver_options = null, $throw_PDOException = 0){

		// if connected to the database
		if(!$this->pdo){

			// Connect to database server
			try{
				if($dsn)
					$this->pdo = new PDO($dsn, $username, $password, $driver_options);
				else
					$this->pdo = new PDO(Cfg('db.dsn'), Cfg('db.username'), Cfg('db.password'), Cfgn('db.driver_options'));
			}
			catch(PDOException $e){

				if($throw_PDOException)
					throw $e;

				if(!$dsn)
					$dsn = Cfg('db.dsn');
				
				// check if PDO extension is enabled
				if(substr($dsn, 0, 6) == 'mysql:' && !extension_loaded('pdo_mysql'))
					$msg1 = "pdo_mysql extension not enabled.";
				else if(substr($dsn, 0, 6) == 'pgsql:' && !extension_loaded('pdo_pgsql'))
					$msg1 = "pdo_pgsql extension not enabled.";
				else
					$msg1 = '';

				$message = "Unable to connect to database. $msg1 " . $e->getMessage();

				// log message cannot be written into the database if there is problem with database connection
				if(Cfg('logging.storage.psa_default.type') == 'database')
					throw new DbException($message, $e->getCode(), false);
				else
					throw new DbException($message, $e->getCode());
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
	public function fetchAll($sql = null){

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
	public function fetchRow($sql = null){

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
	public function fetchColumn($column = 0, $sql = null) {

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
	 * @return PDOStatement
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
	 * Throws a {@link DbException} if statement fails.
	 *
	 * @param array $params An array of values with as many elements as there are bound
	 * parameters in the SQL statement being executed.
	 * @param string|PDOStatement $statement SQL query or prepared PDO statement.
	 * @param bool $throw_PDOException If true PDOException will be thrown instead DbException
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
			return $statement->execute($params);
		}
		catch(PDOException $e){

			if($throw_PDOException)
				throw $e;

			$error = $this->pdo->errorInfo();

			if(is_array($params))
				$query_params_str = ' Query input parameters: ' . implode(',',$params);
			else
				$query_params_str = '';

			throw new DbException('SQL error: ' . $e->getMessage() . '. In query: ' . $statement->queryString . $query_params_str, $e->getCode());
		}
	}
}
