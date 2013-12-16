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
 * @version $Id: Psa_Model.php 142 2013-09-26 17:10:52Z bmauser $
 */


/**
 * Models are place for all the domain business logic of your application.
 *
 * In the model you do all calculations, get and store data to the database and all other business logic
 * of your application. When you extend this class just some object properties will be set, it has only
 * simple constructor method.
 *
 * Your model classes will contain methods that are called from the controller. You should organize your model methods
 * into more model classes to be logically grouped. You can also put classes for each group of operations into
 * different subfolder.
 *
 * You can put results from the model into {@link Psa_Result} object because it is accessible from templates
 * and view objects (see {@link Psa_Dully_View} or {@link Psa_Smarty_View}). But it is better and cleaner to make
 * all your models to return values and then in the controller action method pass results from model to view method.
 *
 * You can write your models by extending this class. Here is an example:
 *
 * <code>
 * <?php
 * class my_model extends Psa_Model{
 *
 *     $this->psa_database->query("SELECT * FROM my_table WHERE enabled = 1");
 *     return $this->psa_database->fetch_all();
 * }
 * ?>
 * </code>
 *
 * <br>By extending <i>Psa_Model</i> class only {@link $psa_result}, {@link $psa_registry} and {@link $psa_database} properties
 * are set. So if you do not need any of them in your model methods your model class don't need to extend <i>Psa_Model</i>.
 * For example this is also a valid model class:
 *
 * <code>
 * <?php
 * class my_model{
 *
 *     // Maximum number in result from functions below
 *     var $max_number = 150;
 *
 *     // Puts even numbers till $to_number in $psa_result object.
 *     function even_numbers($to_number){
 *
 *          for($i=0; $i <= $this->max_number; $i++){
 *               if($i % 2 == 0)
 *               $return[] = $i;
 *          }
 *
 *          return $return;
 *     }
 * }
 * ?>
 * </code>
 *
 */
class Psa_Model{

	/**
	 * Reference to result object
	 * @var Psa_Result
	 */
	protected $psa_result;


	/**
	 * Reference to registry object
	 * @var Psa_Registry
	 */
	protected $psa_registry;


	/**
	 * Reference to database connect object
	 * @var Psa_PDO
	 */
	protected $psa_database;


	/**
	 * Constructor.
	 */
	function __construct(){

		// reference to database object
		$this->psa_database = Psa_Registry::get_instance()->psa_database;

		// set reference to result object
		$this->psa_result = Psa_Result::get_instance();

		// set reference to registry object
		$this->psa_registry = Psa_Registry::get_instance();
	}
}
