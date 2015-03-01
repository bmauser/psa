<?php
/**
 * @package PSA
 */


/**
 * Class with methods for parsing the requested URL into controller, action and arguments,
 * and for invoking action methods.
 *
 * Router's task is to decide which controller and action method to call.
 * That decision can be made from the given URL if your web server is set to use rewrite rules,
 * which is a common practice.
 *
 * Example:
 *
 * <code>
 * <?php
 * class Main extends Router{
 *
 *     // you have to define psa_main() method in your child class
 *     function psa_main(){
 *
 *         // put basedir_web into Result object to be available in all templates
 *         Res()->basedir_web = Reg()->basedir_web;
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
 * your <i>Main</i> class to extend <i>Router</i> class. <i>Main</i> class should be defined in the Main.php file, which is placed
 * in the first autoload directory (set by <var>$PSA_CFG['folders']['autoload']</var> in {@link config.php}). Use Main.php file as bootstrap
 * file for your application.
 * 
 * @asFunction Router Router getInstance
 */
class Router {


	/**
	 * Returns an array of URL elements after application base directory exploded by '/'.
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
	 * @param string $request_uri <kbd>$_SERVER["REQUEST_URI"]</kbd> by default
	 * @param string $basedir_web 
	 * @return array
	 */
	public function explodeRequestUri($request_uri = null, $basedir_web = null){
		
		if($request_uri === null){
			if(isset($_SERVER["REQUEST_URI"]) && $_SERVER["REQUEST_URI"])
				$request_uri = $_SERVER["REQUEST_URI"];
			else
				return array();
		}
		
		if(!$basedir_web && isset(Reg()->basedir_web))
			$basedir_web = Reg()->basedir_web;

		// remove query string from url
		$request_uri = explode('?', $request_uri)[0];

		// remove $basedir_web from $request_uri
		if($basedir_web)
			$request_uri = implode('', explode(Reg()->basedir_web, $request_uri, 2));

		// clean route string
		$request_uri = trim($request_uri, "/ \t\n\r\0\x0B");

		if($request_uri)
			return explode('/', $request_uri);

		return array();
	}


	/**
	 * Returns an array with the names of the controller and the action and array of arguments for
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
	 * @param string|array $uri_arr
	 * @return array
	 */
	public function getDispatchData(array $uri_arr = null){

		if(!$uri_arr)
			$uri_arr = $this->explodeRequestUri();

		// controller name
		$return['controller'] = (isset($uri_arr[0]) ? ucfirst($uri_arr[0]) : Cfg('mvc.default_controller_name')) . Cfg('mvc.default_controller_suffix');
		// action name
		$return['action'] = (isset($uri_arr[1]) ? $uri_arr[1] : Cfg('mvc.default_action_name')) . Cfg('mvc.default_action_suffix');
		// action arguments
		$return['arguments'] = isset($uri_arr[2]) ? array_slice($uri_arr, 2) : array();

		return $return;
	}


	/**
	 * Invokes method in a class with arguments.
	 *
	 * This method is intended to be used to call the controller method with arguments.
	 * If you call it without parameters, it will try to get controller name, method and
	 * parameters from the URL. In that case, it actually calls {@link getDispatchData()}
	 * method to get data for dispatch.
	 *
	 * @param string $class_name Class name.
	 * @param string $method_name Method to invoke.
	 * @param array $method_arguments Arguments for invoking method.
	 * @throws RouterException
	 * @return mixed Returns result from the called method.
	 */
	public function dispach($class_name = null, $method_name = null, array $method_arguments = array()){

		// get dispatch data from url
		if(!$class_name){
			$dispatch_data = $this->getDispatchData();
			$class_name = $dispatch_data['controller'];
			$method_name = $dispatch_data['action'];
			$method_arguments = $dispatch_data['arguments'];
		}

		if(!class_exists($class_name))
			throw new RouterException("Trying to make a new instance of unexisting class: $class_name", 101);
		
		// make new object
		$object = new $class_name;

		// check if method exists
		if(!method_exists($object, $method_name))
			throw new RouterException("Trying to call unexisting method: $class_name::$method_name", 102);

		$invoke_method = new ReflectionMethod($object, $method_name);
			
		// call method with arguments
		return $invoke_method->invokeArgs($object, $method_arguments);
	}
}
