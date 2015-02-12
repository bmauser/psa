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
 * @version $Id: Psa_Validator.php 142 2013-09-26 17:10:52Z bmauser $
 */


/**
 * Class for input data validation.
 *
 * You should validate all input data from users. Make each model or view method to
 * validate its parameters if they are input from a user.
 *
 * <br><b>Usage example</b>
 *
 * <code>
 * <?php
 *
 * // model class
 * class search {
 *
 *     // search method
 *     function filter($max_results, $only_pages){
 *
 *         // input validation
 *         $validator = new Psa_Validator();
 *         $validator->required($max_results, 'int');
 *         $validator->optional($only_pages, 'int_array');
 *
 *         // Method logic starts here.
 *         // For example to query the database using $max_results in SQL query
 *         // ...
 * //
 *     }
 * }
 * ?>
 * </code>
 *
 * <br><b>All validation actions</b>
 *
 * Two main methods in this class are {@link required()} and {@link optional()}. They behave the same way except that
 * {@link optional()} will not raise exception if value is empty or <kbd>null</kbd>.
 * Like in the example above, first argument is the value that should be validated and second is the name of the
 * validation action. See {@link required()} method documentation for all action names.
 * Some validation actions require more parameters. Examples:
 *
 * <code>
 *
 * $val = new Psa_Validator();
 *
 * // Finds whether the given $value is between (or equal) 10 and 20.
 * $val->required($value, 'between', 10, 20);
 *
 * // Finds whether the length of the given $value is between (or equal) 10 and 20 characters.
 * $val->required($value, 'lenbetween', 10, 20);
 *
 * // Finds whether the given $value is in array passed as third argument.
 * $val->required($value, 'invalues', array(1, 2, 3, 4));
 *
 * // Checks if the given $value is an integer.
 * $val->required($value, 'int');
 *
 * // Finds whether the given $value is an integer bigger than 0.
 * $val->required($value, 'id');
 *
 * // Checks date if matches given format and validity of the date.
 * // Third argument is combination of 'mm', 'dd' and 'yyyy'
 * $val->required($value, 'date', 'mm.dd.yyyy');
 *
 * //  Finds whether the given $value has valid email format.
 * $val->required($value, 'email');
 *
 * // Checks is the given $value a valid IPv4 address.
 * $val->required($value, 'ip4');
 *
 * // Checks is the given $value a valid floating point number.
 * $val->required($value, 'float');
 *
 * // Checks whether the given $value is a valid string.
 * $val->required($value, 'string');
 *
 * // Checks whether the given $value contains letters only.
 * $val->required($value, 'alpha');
 *
 * // Checks whether the given $value contains numbers only.
 * $val->required($value, 'num');
 *
 * // Checks whether the given $value contains alphanumeric characters only.
 * $val->required($value, 'alphanum');
 *
 * // Checks whether the given $value contains domain name safe characters only.
 * $val->required($value, 'domainsafe');
 *
 * // The same as 'domainsafe' but dot '.' is also included.
 * $val->required($value, 'hostname');
 *
 * // Searches $value for a match to the regular expression given as third argument
 * $val->required($value, 'regex', '/[0-9]/');
 *
 * // Checks if given $value is valid URL.
 * $val->required($value, 'url');
 *
 * // Calls user function or method which has to return true or false.
 * $val->required($value, 'callback', 'user_function_name', 'param1');
 *
 * // Returns true if parameters are equal (==).
 * $val->required($value, 'equal', 'something');
 *
 * // Returns true if parameters are identical (===).
 * $val->required($value, 'identical', 1234);
 *
 * // Validates an IPv6 address.
 * $val->required($value, 'ip6');
 *
 * // Checks with instanceof operator
 * $val->required($value, 'instanceof', 'stdClass');
 * $val->required($value, 'instanceof', new stdClass();
 *
 * </code>
 *
 * <br><b>Validating array of values</b>
 *
 * If you want to validate an array of values, suffix <b><kbd>_array</kbd></b> can be added to any of
 * validation actions to validate each element of array. For example, this is useful
 * when validating input from HTML drop-down menu with multiple selections.
 *
 * <code>
 *
 * $val = new Psa_Validator();
 *
 * // each element of $array should be integger
 * $val->required($array, 'int_array');
 *
 * // each element of $array should be 1, 2, 3 or 4
 * $val->required($array, 'invalues_array', array(1, 2, 3, 4));
 *
 * </code>
 *
 * <br><b>Custom error message per validation</b>
 *
 * If validation fails, by default exception will be raized. Each action has its own default
 * error message, but you can change that message by passing one more argument to {@link required()}
 * or {@link optional()} method. This can be usefull if you want more descriptive error message
 * or message will be shown to the user.
 *
 * <code>
 *
 * $val = new Psa_Validator();
 *
 * $val->required($value, 'id', 'Not valid product ID');
 *
 * </code>
 *
 * <br><b>Adding custom validation</b>
 *
 * You can extend this class and add your custom validation function (action). In the next example '<kbd>5to9</kbd>'
 * action is added. Note that validation method named <var>check_5to9()</var> and error message named
 * <var>$msg_check_5to9</var> are added in the child class. Validation method should return <kbd>true</kbd> or
 * <kbd>false</kbd>. When you add validation action like this you can use it with <kbd>_array</kbd> suffilx like
 * any other built-in action. See the source code of this class for more examples.
 *
 * <code>
 * class MyValidator extends Psa_validator{
 *
 *     // Error message for 5to9 validation
 *     // %v will be replaced with value to validate, and %p1, %p2...
 *     // will be replaced with other parameters to validation method.
 *     $msg_check_5to9 = "%v does not contains only digits from 5 to 9";
 *
 *
 *     // Returns true if $value contains only digits from 5 to 9
 *     public function check_5to9($value){
 *
 *         if(preg_match('/^[5-9]*$/', $value))
 *             return true;
 *         return false;
 *     }
 * }
 * </code>
 *
 *
 */
class Psa_Validator{


	// Default error messages.
	// Each error message corresponds to a specific <kbd>check_</kbd> method.

	/**
	 * @ignore
	 */
	protected $msg_check_int        = "%v is not an int";
	/**
	 * @ignore
	 */
	protected $msg_check_id         = "%v is not database id";
	/**
	 * @ignore
	 */
	protected $msg_check_string     = "%v is not a string";
	/**
	 * @ignore
	 */
	protected $msg_check_date       = "%v is not a valid date in format %p1";
	/**
	 * @ignore
	 */
	protected $msg_check_email      = "%v is not a valid email";
	/**
	 * @ignore
	 */
	protected $msg_check_ip4        = "%v is not a valid IPv4 address";
	/**
	 * @ignore
	 */
	protected $msg_check_ip6        = "%v is not a valid IPv6 address";
	/**
	 * @ignore
	 */
	protected $msg_check_float      = "%v is not a valid float number";
	/**
	 * @ignore
	 */
	protected $msg_check_alpha      = "%v does not contains only alpha characters";
	/**
	 * @ignore
	 */
	protected $msg_check_alphanum   = "%v does not contains only alphanumeric characters";
	/**
	 * @ignore
	 */
	protected $msg_check_num        = "%v does not contains only numeric characters";
	/**
	 * @ignore
	 */
	protected $msg_check_regex      = "%v does not match patern %p1";
	/**
	 * @ignore
	 */
	protected $msg_check_domainsafe = "%v does not contain only domain name safe characters";
	/**
	 * @ignore
	 */
	protected $msg_check_invalues   = "%v is not in valid values"; //implode
	/**
	 * @ignore
	 */
	protected $msg_check_between    = "%v is not in between %p1 and %p2";
	/**
	 * @ignore
	 */
	protected $msg_check_lenbetween = "Length of %v is not in between %p1 and %p2";
	/**
	 * @ignore
	 */
	protected $msg_check_hostname   = "%v is not valid hostname";
	/**
	 * @ignore
	 */
	protected $msg_check_url        = "%v is not valid URL";
	/**
	 * @ignore
	 */
	protected $msg_check_callback   = "%v validation with %p1() failed.";
	/**
	 * @ignore
	 */
	protected $msg_check_equal      = "%v is not equal(==) %p1";
	/**
	 * @ignore
	 */
	protected $msg_check_identical  = "%v is not identical (===) %p1";
	/**
	 * @ignore
	 */
	protected $msg_check_instanceof  = "Value is not an instance of %p1";
	/**
	 * @ignore
	 */
	protected $msg_default          = "Invalid value: %v";
	/**
	 * @ignore
	 */
	protected $msg_required          = "Value for %t is required. "; // Custom message can be concatenated


	/**
	 * Array that holds error messages
	 *
	 * @var array
	 * @ignore
	 */
	protected $errors = array();


	/**
	 * If set to true no exception will be thrown on validation error
	 *
	 * @var bool
	 * @ignore
	 */
	protected $no_exceptions = false;


	/**
	 * Constructor. You can disable throwing exceptions if you pass <kbd>true</kbd> as argument to constructor.
	 *
	 * @param $no_exceptions if set to true no exception will be thrown on validation error
	 * @ignore
	 */
	public function __construct($no_exceptions = false){
		if($no_exceptions)
			$this->no_exceptions = true;
	}


	/**
	 * Validates a required value.
	 *
	 * See examples of ussage above.
	 * @param mixed $value Value to be evaluated.
	 * @param string $type Validation action. Can be:<br> <kbd>int</kbd>, <kbd>id</kbd>, <kbd>date</kbd>,
	 * <kbd>string</kbd>, <kbd>email</kbd>, <kbd>ip4</kbd>, <kbd>ip6</kbd>, <kbd>float</kbd>, <kbd>alpha</kbd>,
	 * <kbd>num</kbd>, <kbd>alphanum</kbd>, <kbd>regex</kbd>, <kbd>domainsafe</kbd>,
	 * <kbd>invalues</kbd>, <kbd>url</kbd>, <kbd>between</kbd>, <kbd>lenbetween</kbd>,
	 * <kbd>equal</kbd>, <kbd>identical</kbd>, <kbd>hostname</kbd>, <kbd>callback</kbd>,
	 * <kbd>instanceof</kbd><br>
	 *
	 * If <var>$value</var> is an array, suffix <b><kbd>_array</kbd></b> can be added to any of these
	 * types to validate each element of the array. For example, this is useful
	 * when validating input from HTML drop-down menu with multiple selections.
	 *
	 * @param mixed $p,... Unlimited optional number of additional parameters that will be passed as
	 * second, third,... parameter to <i>check_</i> methods.
	 * @throws Psa_Validation_Exception
	 * @see optional()
	 * @return bool
	 */
	public function required($value, $type){
		$args = func_get_args();
		return $this->validate(1, $args);
	}


	/**
	 * Validates an optional value.
	 *
	 * This method does the same as {@link required()} method, the only difference is that
	 * evaluated value can be empty string or null.
	 * For example this can be useful for values from optional HTML form elements.
	 * See example in {@link Psa_Validator class description} and for description of parameters
	 * see {@link required()} method.
	 *
	 * @param mixed $value See {@link required()} method.
	 * @param string $type See {@link required()} method.
	 * @param mixed $p,... See {@link required()} method.
	 * @throws Psa_Validation_Exception
	 * @see required()
	 * @return bool
	 */
	public function optional($value, $type){
		$args = func_get_args();
		return $this->validate(0, $args);
	}


	/**
	 * Returns an array with validation errors (messages).
	 *
	 * It will return 0 if there is no validation error messages.
	 *
	 * @return int|array
	 * @see
	 */
	public function get_errors(){
		if(sizeof($this->errors))
			return $this->errors;
		else return 0;
	}


	/**
	 * Resets all errors (messages).
	 */
	public function clean_errors(){
		$this->errors = Array();
	}


	/**
	 * Just throws {@link Psa_Validation_Exception}.
	 *
	 * <br><b>Example:</b><br>
	 *
	 * <br><code>
	 * $val = new Psa_Validator();
	 *
	 * if($password != 'abc123')
	 *     $val->fail('You entered invalid password.');

	 * </code>
	 *
	 * @param string $message Exception message.
	 * @param int $code Exception code.
	 * @throws Psa_Validation_Exception
	 */
	public function fail($message = '', $code = 610){
		throw new Psa_Validation_Exception($message, $code);
	}


	/**
	 * Throws Psa_Validation_Exception and sets $this->errors.
	 *
	 * @ignore
	 */
	protected function fail_local($message, $value, $type, $code){

		$err_index = count($this->errors);

		$this->errors[$err_index]['message'] = $message;
		$this->errors[$err_index]['value'] = $value;
		$this->errors[$err_index]['type'] = $type;

		if($this->no_exceptions)
			return false;

		throw new Psa_Validation_Exception($message, $code);
	}


	/**
	 * Checks if value is empty
	 *
	 * @ignore
	 */
	protected function is_empty($value){

		if($value === '' or $value === null or (is_array($value) && empty($value)))
			return true;

		return false;
	}


	/**
	 * Validates values set by {@link required()} and {@link optional()} methods.
	 * If validation of any value fails, raises exception (or returns false, see {@link __construct()}),
	 * otherwise returns true. See example in {@link Psa_Validator class description}.
	 * It will throw {@link Psa_Validation_Exception} when validation fails. For example, you can
	 * catch this exception in controller and call another action.
	 *
	 * @see required()
	 * @see optional()
	 * @return bool
	 * @throws Psa_Validation_Exception
	 * @ignore
	 */
	protected function validate($reqired, $params){

		$value_to_validate = $params[0];
		$validation_type = $params[1];

		// remove second element because it is validation_type
		array_splice($params, 1, 1);

		// if validation is for each array element
		if(substr($validation_type, -6) == '_array'){
			$validation_type = substr($validation_type, 0, -6);
			$array_validation = 1;
		}

		// check if value can be empty
		if($this->is_empty($value_to_validate)){
			if(!$reqired)
				return true;

			$message = $this->get_required_message($validation_type, $params);

			return $this->fail_local($message, $value_to_validate, $validation_type, 601);
		}

		// if validation has to be run on array of elements
		if(isset($array_validation)){

			if(is_array($value_to_validate)){

				$return = true;
				$arr_params = $params; // copy paremeters

				// validate each element
				foreach ($value_to_validate as $data_key => $data_value){

					$arr_params[0] = $data_value; // replace array with value to validate

					// check if value can be empty
					if($this->is_empty($data_value)){
						// if $data can be empty (optional)
						if(!$reqired)
							continue;
						// data is required
						else{
							$message = $this->get_required_message($validation_type, $params);
							return $this->fail_local($message, $data_value, $validation_type, 602);
						}
					}

					if(!$this->invoke_validation_method($validation_type, $arr_params))
						$return = false;
				}

				return $return;
			}
			else{
				return $this->fail_local("'{$value_to_validate}' is not an array thus cannot validate each element", $value_to_validate, $validation_type, 603);
			}
		}
		// single value
		else{
			return $this->invoke_validation_method($validation_type, $params);
		}
	}


	/**
	 * Invokes validation methods.
	 *
	 * @ignore
	 */
	protected function invoke_validation_method($validation_type, $params){

		// name of the method to invoke
		$validation_method_name = 'check_' . $validation_type;

		// if method exists
		if(method_exists($this, $validation_method_name)){

			// method reflection
			$invoke_method = new ReflectionMethod($this, $validation_method_name);

			// call validating method
			$validation_result = $invoke_method->invokeArgs($this, $params);

			// if invalid
			if(!$validation_result){

				$message = $this->get_validation_message($validation_type, $params, $invoke_method);

				return $this->fail_local($message, $params[0], $validation_type, 604);
			}

			return true;
		}
		else
			return $this->fail_local("Undefined validation method check_{$validation_type}()", $params[0], $validation_type, 605);
	}


	/**
	 * Returns validation error message.
	 *
	 * @ignore
	 */
	protected function get_validation_message($validation_type, $method_params, ReflectionMethod $reflection_method = null, $only_parameter_message = null){

		// default validation message
		$message_var_name = 'msg_check_' . $validation_type;

		if(!$reflection_method){

			// name of the validation method
			$validation_method_name = 'check_' . $validation_type;

			$reflection_method = new ReflectionMethod($this, $validation_method_name);
		}

		// how many arguments has validation_method
		$validation_method_num_args = $reflection_method->getNumberOfParameters();

		// choose error message
		if(isset($method_params[$validation_method_num_args]) && is_string($method_params[$validation_method_num_args]))
			$message = $method_params[$validation_method_num_args];
		else if($only_parameter_message)
			return '';
		else if(isset($this->$message_var_name) && $this->$message_var_name)
			$message = $this->$message_var_name;
		else
			$message = $this->msg_default;

		if(count($method_params) > 1 && substr_count($message, '%p')){
			// replace %p1, %p2, %p3... in message with function params
			for($i=1; $i<$validation_method_num_args; $i++){
				if(is_array($method_params[$i]))
					$val = '[' . implode(', ', $method_params[$i]) . ']';
				if(is_object($method_params[$i]))
					$val = get_class($method_params[$i]);
				else
					$val = $method_params[$i];
				$message = str_replace('%p' . $i, $val, $message);
			}
		}

		if(is_object($method_params[0]))
			$value_str = 'Object';
		else
			$value_str = $method_params[0];

		// replace %v in message with value.
		return $message = str_replace('%v', $value_str, $message);
	}


	/**
	 * Returns validation error message for required values.
	 *
	 * @ignore
	 */
	protected function get_required_message($validation_type, $method_params){

		$message = str_replace('%t', $validation_type, $this->msg_required);
		$message .= $this->get_validation_message($validation_type, $method_params, null, true);

		return trim($message);
	}


	/**
	 * Finds whether the given <var>$value</var> is between (or equal) $min and $max.
	 *
	 * @param float $value the variable being evaluated.
	 * @param float|bool $min if null only <kbd>$max</kbd> argument is evaluated
	 * @param float|bool $max if null only <kbd>$min</kbd> argument is evaluated
	 * @return bool
	 * @ignore
	 */
	public function check_between($value, $min = null, $max = null){

		if($value === '')
			return false;

		if($min !== null && $max !== null && $value >= $min && $value <= $max)
			return true;
		else if($min === null && $max !== null && $value <= $max)
			return true;
		else if($min !== null && $max === null && $value >= $min)
			return true;

		return false;
	}


	/**
	 * Finds whether the length of the given <var>$value</var> is between (or equal) $min and $max.
	 *
	 * @param string $value the variable being evaluated.
	 * @param int|bool $min if null only <kbd>$max</kbd> argument is evaluated
	 * @param int|bool $max if null only <kbd>$min</kbd> argument is evaluated
	 * @return bool
	 * @ignore
	 */
	public function check_lenbetween($value, $min = null, $max = null){

		$length = strlen($value);

		if($min !== null && $max !== null && $length >= $min && $length <= $max)
			return true;
		else if($min === null && $max !== null && $length <= $max)
			return true;
		else if($min !== null && $max === null && $length >= $min)
			return true;

		return false;
	}


	/**
	 * Finds whether the given <var>$value</var> is in array passed as second argument.
	 *
	 * @param $value the variable being evaluated.
	 * @param array $values array of valid values
	 * @return bool
	 * @ignore
	 */
	public function check_invalues($value, $values){

		if(in_array($value,$values))
			return true;
		else
			return false;
	}


	/**
	 * Checks is the given <var>$value</var> an integer.
	 *
	 * @param $value the variable being evaluated.
	 * @return bool
	 * @ignore
	 */
	public function check_int($value){

		if(is_bool($value) or is_array($value))
			return false;
		if(strval(intval($value)) !== (string) $value)
			return false;
		return true;
	}


	/**
	 * Finds whether the given <var>$value</var> is integer bigger than 0.
	 *
	 * Positive integer is often used as primary key in database tables therefore name <i>check_id</i>.
	 *
	 * @param $value the variable being evaluated.
	 * @return bool
	 * @ignore
	 */
	public function check_id($value){
		if(Psa_Validator::check_int($value) && $value > 0)
			return true;
		return false;
	}


	/**
	 * Checks date if matches given format and validity of the date.
	 *
	 * Examples:
	 *
	 * <code>
	 * Psa_Validator::check_date('22.22.2222', 'mm.dd.yyyy'); // returns false
	 * Psa_Validator::check_date('11/30/2008', 'mm/dd/yyyy'); // returns true
	 * Psa_Validator::check_date('30-01-2008', 'dd-mm-yyyy'); // returns true
	 * Psa_Validator::check_date('2008 01 30', 'yyyy mm dd'); // returns true
	 * </code>
	 *
	 * @param string $value the variable being evaluated.
	 * @param string $format Format of the date. Any combination of <i>mm</i>, <i>dd</i>, <i>yyyy</i>
	 * with a single character separator between.
	 * @return bool
	 * @ignore
	 */
	public function check_date($value, $format = 'mm.dd.yyyy'){

		if(strlen($value) == 10 && strlen($format) == 10){

			// find separator. Remove all other characters from $format
			$separator_only = str_replace(array('m','d','y'),'', $format);
			$separator = $separator_only[0]; // separator is first character

			if($separator && strlen($separator_only) == 2){
				// make regex
				$regexp = str_replace($separator, "\\" . $separator, $format);
				$regexp = str_replace('mm', '[0-1][0-9]', $regexp);
				$regexp = str_replace('dd', '[0-3][0-9]', $regexp);
				$regexp = str_replace('yyyy', '[0-9]{4}', $regexp);

				if($regexp != $value && preg_match('/'.$regexp.'/', $value)){

					// check date
					$day   = substr($value, strpos($format, 'd'), 2);
					$month = substr($value, strpos($format, 'm'), 2);
					$year  = substr($value, strpos($format, 'y'), 4);

					if(@checkdate($month, $day, $year))
						return true;
				}
			}
		}
		return false;
	}


	/**
	 * Finds whether the given <var>$value</var> has valid email format.
	 *
	 * @param $value the variable being evaluated.
	 * @return bool
	 * @ignore
	 */
	public function check_email($value){

		if(preg_match('/^[A-Z0-9._%+-]+@(?:[A-Z0-9-]+\.)+[A-Z]{2,64}$/i', $value))
			return true;
		return false;
	}


	/**
	 * Checks is the given <var>$value</var> valid IPv4 address.
	 *
	 * @param string $value the variable being evaluated.
	 * @return bool
	 * @ignore
	 */
	public function check_ip4($value){

		$value = (string) $value;

		$ip2long = ip2long($value);

		if(($ip2long === false) || (long2ip($ip2long) !== $value)){
			return false;
		}

		return true;
	}


	/**
	 * Checks is the given <var>$value</var> valid floating point number.
	 *
	 * @param $value the variable being evaluated.
	 * @return bool
	 * @ignore
	 */
	public function check_float($value){

		if(strval(floatval($value)) != (string) $value){
			return false;
		}

		return true;
	}


	/**
	 * Checks whether the given <var>$value</var> is a valid string.
	 *
	 * @param $value the variable being evaluated.
	 * @return bool
	 * @ignore
	 */
	public function check_string($value){

		if(is_array($value) or is_object($value) or $value === '')
			return false;

		if(strval($value) == $value){
			return true;
		}

		return false;
	}


	/**
	 * Checks whether the given <var>$value</var> contains letters only.
	 *
	 * @param $value the variable being evaluated.
	 * @return bool
	 * @ignore
	 */
	public function check_alpha($value){

		if(!is_string($value))
			return false;

		if(preg_match('/^[a-zA-Z]+$/', $value))
			return true;
		return false;
	}

	/**
	 * Checks whether the given <var>$value</var> contains numbers only.
	 *
	 * @param $value the variable being evaluated.
	 * @return bool
	 * @ignore
	 */
	public function check_num($value){

		if(preg_match('/^[0-9]+$/', $value))
			return true;
		return false;
	}


	/**
	 * Checks whether the given <var>$value</var> contains alphanumeric characters only.
	 *
	 * @param $value the variable being evaluated.
	 * @return bool
	 * @ignore
	 */
	public function check_alphanum($value){

		if(is_array($value) or is_object($value))
			return false;

		if(preg_match('/^[a-zA-Z0-9]+$/', $value))
			return true;
		return false;
	}


	/**
	 * Checks whether the given <var>$value</var> contains domain name safe characters only.
	 *
	 * Those are: letters, numbers, hyphens. Dot '.' is not included because it separates domain
	 * levels thus it is not part of domain name.
	 *
	 * @param $value the variable being evaluated.
	 * @return bool
	 * @see check_hostname()
	 * @ignore
	 */
	public function check_domainsafe($value){

		if(is_array($value) or is_object($value))
			return false;

		if(preg_match('/--|\.|^-|-$|^[0-9]+$/', $value))
			return false;
		if(preg_match('/^[a-zA-Z0-9\-]+$/', $value))
			return true;
		return false;
	}


	/**
	 * The same as {@link check_domainsafe} but dot '.' is also included.
	 *
	 * @param $value the variable being evaluated.
	 * @return bool
	 * @see check_domainsafe()
	 * @ignore
	 */
	public function check_hostname($value){

		if(is_array($value) or is_object($value))
			return false;

		if(preg_match('/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,64}$/', $value))
			return true;
		return false;
	}


	/**
	 * Searches <var>$value</var> for a match to the regular expression given in <var>$pattern</var>.
	 *
	 * @param string $value the variable being evaluated.
	 * @param string $pattern complete pattern for PHP <kbd>preg_match()</kbd> function. For example: <i>/^[1-9]+$/</i>
	 * @return bool
	 * @ignore
	 */
	public function check_regex($value, $pattern){

		if(preg_match($pattern, $value))
			return true;
		return false;
	}


	/**
	 * Checks if given <var>$value</var> is valid URL.
	 *
	 * @param string $value the variable being evaluated.
	 * @return bool
	 * @ignore
	 */
	public function check_url($value){

		// regex taken from: http://geekswithblogs.net/casualjim/archive/2005/12/01/61722.aspx
		$regex = '^(?#Protocol)(?:(?:ht|f)tp(?:s?)\:\/\/|~/|/)?(?#Username:Password)(?:\w+:\w+@)?(?#Subdomains)(?:(?:[-\w]+\.)+(?#TopLevel Domain)(?:[a-z]{2,64}))(?#Port)(?::[\d]{1,5})?(?#Directories)(?:(?:(?:/(?:[-\w~!$+|.,=]|%[a-f\d]{2})+)+|/)+|\?|#)?(?#Query)(?:(?:\?(?:[-\w~!$+|.,*:]|%[a-f\d{2}])+=(?:[-\w~!$+|.,*:=]|%[a-f\d]{2})*)(?:&(?:[-\w~!$+|.,*:]|%[a-f\d{2}])+=(?:[-\w~!$+|.,*:=]|%[a-f\d]{2})*)*)*(?#Anchor)(?:#(?:[-\w~!$+|.,*:=]|%[a-f\d]{2})*)?$';
		if(preg_match(';'.$regex.';i', $value))
			return true;
		return false;
	}


	/**
	 * Calls user function or method which has to return true or false.
	 *
	 * It calls PHP call_user_func() function.
	 *
	 * @param string $param1 user function name
	 * @param mixed $param2 first parameter for user function
	 * @param mixed $param3 second parameter for user function
	 * @return bool
	 * @ignore
	 */
	public function check_callback($value, $callback_function_name){

		return call_user_func($callback_function_name, $value);
	}


	/**
	 * Returns true if parameters are equal.
	 *
	 * Uses '==' operator for comparison.
	 *
	 * @param mixed $param1
	 * @param mixed $param2
	 * @return bool
	 * @ignore
	 */
	public function check_equal($param1, $param2){

		if($param1 == $param2)
			return true;
		return false;
	}


	/**
	 * Returns true if parameters are identical.
	 *
	 * Uses '===' operator for comparison.
	 *
	 * @param mixed $param1
	 * @param mixed $param2
	 * @return bool
	 * @ignore
	 */
	public function check_identical($param1, $param2){

		if($param1 === $param2)
			return true;
		return false;
	}


	/**
	 * Validates an IPv6 address.
	 *
	 * @param  string $value Value to check against
	 * @return bool True when $value is a valid ipv6 address. False otherwise.
	 * @ignore
	 */
	public function check_ip6($value){

		$regex = '^(?:(?:(?:[A-F0-9]{1,4}:){5}[A-F0-9]{1,4}|(?:[A-F0-9]{1,4}:){4}:[A-F0-9]{1,4}|(?:[A-F0-9]{1,4}:){3}(?::[A-F0-9]{1,4}){1,2}|(?:[A-F0-9]{1,4}:){2}(?::[A-F0-9]{1,4}){1,3}|[A-F0-9]{1,4}:(?::[A-F0-9]{1,4}){1,4}|(?:[A-F0-9]{1,4}:){1,5}|:(?::[A-F0-9]{1,4}){1,5}|:):(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])|(?:[A-F0-9]{1,4}:){7}[A-F0-9]{1,4}|(?:[A-F0-9]{1,4}:){6}:[A-F0-9]{1,4}|(?:[A-F0-9]{1,4}:){5}(?::[A-F0-9]{1,4}){1,2}|(?:[A-F0-9]{1,4}:){4}(?::[A-F0-9]{1,4}){1,3}|(?:[A-F0-9]{1,4}:){3}(?::[A-F0-9]{1,4}){1,4}|(?:[A-F0-9]{1,4}:){2}(?::[A-F0-9]{1,4}){1,5}|[A-F0-9]{1,4}:(?::[A-F0-9]{1,4}){1,6}|(?:[A-F0-9]{1,4}:){1,7}:|:(?::[A-F0-9]{1,4}){1,7})$';
		if(preg_match('/'.$regex.'/i', $value))
			return true;
		return false;
	}


	/**
	 * Returns true if $value is instance of $type.
	 *
	 * @param mixed $value
	 * @param object|string $type
	 * @return bool
	 * @ignore
	 */
	public function check_instanceof($value, $type){

		if($value instanceof $type)
			return true;
		return false;
	}

}
