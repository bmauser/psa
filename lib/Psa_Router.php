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
 * @version $Id: Psa_Router.php 159 2013-12-06 01:08:51Z bmauser $
 */


/**
 * Class with methods for parsing the request URL into controller, action, and parameters
 * and invoking action methods.
 *
 * Router should be used to decide which controller to call. This class has methods for that task.
 * That decision can be made from given URL path if your web server is set to use rewrite rules,
 * which is common practice, but can also be made from get or post values.
 *
 * If you want to use rewrite rules you must set up your web server for this, with Apache web server
 * you can do it by enabling {@link http://httpd.apache.org/docs/2.0/mod/mod_rewrite.html mod_rewrite}
 * by adding line like this into the configuration file:
 *
 * <kbd>LoadModule rewrite_module modules/mod_rewrite.so</kbd>
 *
 * Make sure that you can override (AllowOverride directive) rewrite settings with .htaccess file.
 * Edit .htaccess file to match your configuration.
 *
 * You can write your Router by extending this class. Here is an example:
 *
 * <code>
 * <?php
 * class Main extends Psa_Router{
 *
 *     // you have to define psa_main() method in your child class
 *     function psa_main(){
 *
 *         // put basedir_web into psa_result object to be available in all templates
 *         $this->psa_result->basedir_web = $this->psa_registry->basedir_web;
 *
 *         // start session
 *         session_start();
 *
 *         // call action method
 *         try{
 *             $this->dispach();
 *         }
 *         // redirect to login screen if Unauthorized_Exception is raised
 *         catch(Unauthorized_Exception $e){
 *             $main_view = new Main_View();
 *             $main_view->redirect('default/login');
 *         }
 *     }
 * }
 * ?>
 * </code>
 *
 * <br><b>Note:</b> PSA will firstly try to call <kbd>psa_main()</kbd> method from the <i>Main</i> class. You can make
 * your main class extends Psa_Router class. <i>Main</i> class should be defined in Main.php file placed
 * in the first autoload directory (see settings in {@link config.php}). So you can use Main.php file as bootstrap
 * file for your application.
 */
class Psa_Router extends Psa_Model{


	/**
	 * Returns array of URL path parts after application base directory exploded by '/'.
	 *
	 * For example, for URL <kbd>http://server/mycontroller/mymethod/abc/123</kbd> returning array
	 * is:
	 *
	 * <pre>
	 * Array
	 * (
	 *     [0] => mycontroller
	 *     [1] => mymethod
	 *     [2] => abc
	 *     [3] => 123
	 * )
	 * </pre>
	 *
	 * @param string $request_uri $_SERVER["REQUEST_URI"] by default
	 * @return array
	 */
	public function explode_url($request_uri = null){

		if($request_uri === null){
			if(isset($_SERVER["REQUEST_URI"]) && $_SERVER["REQUEST_URI"])
				$request_uri = $_SERVER["REQUEST_URI"];
			else
				throw new Psa_Router_Exception("Unknown REQUEST_URI.", 103);
		}

		// remove query string from url
		$request_uri_arr = explode('?', $request_uri);

		// If application root folder is in subfolder from server root like: www.example.com/my/application/
		// remove unnecessary part of the path. In case of url above that would be '/my/application'.
		if($this->psa_registry->basedir_web)
			$request_uri_arr[0] = str_replace($this->psa_registry->basedir_web, '', $request_uri_arr[0]);

		$request_uri_arr[0] = trim($request_uri_arr[0], "/ \t\n\r\0\x0B");

		if($request_uri_arr[0])
			return explode('/', $request_uri_arr[0]);

		return array();
	}


	/**
	 * Returns array with the names of the controller and action and array of arguments for
	 * action method.
	 *
	 * For example, for URL <kbd>http://server/mycontroller/mymethod/abc/123</kbd> returning array
	 * is:
	 *
	 * <pre>
	 * Array
	 * (
	 *     [controller] => Mycontroller_Controller
	 *     [action] => mymethod_action
	 *     [arguments] => Array
	 *         (
	 *             [0] => abc
	 *             [1] => 123
	 *         )
	 * )
	 * </pre>
	 *
	 * @param string|array $request_uri $_SERVER["REQUEST_URI"] by default
	 * @return array
	 */
	public function get_dispatch_data($request_uri = null){

		if(is_array($request_uri))
			$url_arr = $request_uri;
		else
			$url_arr = $this->explode_url($request_uri);

		// controller name
		$return['controller'] = (isset($url_arr[0]) ? ucfirst($url_arr[0]) : $this->psa_registry->PSA_CFG['mvc']['default_controller_name']) . $this->psa_registry->PSA_CFG['mvc']['default_controller_suffix'];
		// action name
		$return['action'] = (isset($url_arr[1]) ? $url_arr[1] : $this->psa_registry->PSA_CFG['mvc']['default_action_name']) . $this->psa_registry->PSA_CFG['mvc']['default_action_suffix'];
		// action arguments
		$return['arguments'] = isset($url_arr[2]) ? array_slice($url_arr, 2) : array();

		return $return;
	}


	/**
	 * Invokes method in a class with arguments.
	 *
	 * This method is intended to be used to call controller method with arguments.
	 * If you call it without parameters it will try to get controller name, method and
	 * parameters from URL. In that case it actually calls {@link get_dispatch_data()}
	 * method to get data for dispatch.
	 *
	 * @param string $class_name Class name.
	 * @param string $method_name Method to invoke.
	 * @param array $method_arguments Arguments for invoking method.
	 * @throws Psa_Router_Exception
	 * @return mixed Returns the calling method result.
	 */
	public function dispach($class_name = null, $method_name = null, array $method_arguments = array()){

		// get dispatch data from url
		if(!$class_name){
			$dispatch_data = $this->get_dispatch_data();
			$class_name = $dispatch_data['controller'];
			$method_name = $dispatch_data['action'];
			$method_arguments = $dispatch_data['arguments'];
		}

		// make new object
		if(class_exists($class_name))
			$object = new $class_name;
		else{
			throw new Psa_Router_Exception("Trying to make a new instance of unexisting class: $class_name", 101);
		}

		$return = null;

		// check if method exists
		if(method_exists($object, $method_name)){

			// I couldn't use call_user_func_array() here because it is suitable only for static methods.
			// (it looks that as from 5.3 call_user_func_array() can also be used)
			// So I use PHP reflection api to invoke method with array of arguments.
			// Also here can eval() be used.
			// Simplest and the fastest solution would be: $object->$method_name($method_arguments);
			// but then invoking method must be written to accept one parameter which is array with values
			// that normally would be separate method parameters.

			// create an instance of the Reflection_Method class
			$invoke_method = new ReflectionMethod($object, $method_name);

			// If profile log is enabled
			if($this->psa_registry->PSA_CFG['profile_log'] && !isset($object->psa_no_profile_log)){
				// request id for profile logging
				static $request_id = null;

				$time_start = microtime(true);
				if(!$request_id)
					$request_id = uniqid('', true);
			}

			// call method with arguments
			$return = $invoke_method->invokeArgs($object, $method_arguments);

			// If profile log is enabled
			if($this->psa_registry->PSA_CFG['profile_log'] && !isset($object->psa_no_profile_log)){
				$log_data['method'] = $class_name . '->' . $method_name;
				$log_data['total_time'] = microtime(true) - $time_start;
				$log_data['method_arguments'] = print_r($method_arguments,true);
				$log_data['request_id'] = $request_id;
				Psa_Profile_Logger::get_instance()->log($log_data);
			}
		}
		else{
			throw new Psa_Router_Exception("Trying to call unexisting method: $class_name::$method_name", 102);
		}

		return $return;
	}
}
