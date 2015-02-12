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
 * @version $Id: Psa_Result.php 142 2013-09-26 17:10:52Z bmauser $
 */


/**
 * Class for an object that can be used for storing results from model methods.
 *
 * The only purpose of <kbd>Psa_Result</kbd> object is to transfer results from model to view, 
 * but a nicer way to do that is to make model methods to return the value and pass it 
 * to the view method as argument in the controller (in action method).
 * In <i>models</i> and <i>views</i> you can access <kbd>Psa_Result</kbd> object through
 * references in local scope.
 * For example, you can add property to <kbd>Psa_Result</kbd> object in your model
 * by
 * <code>
 * $this->psa_result->my_value = '123';
 * </code>
 *
 * Or you can get reference to <kbd>Psa_Result</kbd> object from any scope with:
 * <code>
 * Psa_Result::get_instance();
 * </code>
 *
 * It implements {@link http://en.wikipedia.org/wiki/Singleton_pattern singleton pattern}
 * so you can get reference to result object from any scope with
 * {@link get_instance()} method. You cannot make instance of Psa_Result object with
 * the <var>new</var> operator.
 *
 * <b>Example:</b>
 *
 * <code>
 * <?php
 *
 * class example_model {
 *
 * 	function get_numbers(){
 *
 * 		// put some numbers in the result object using the reference in local scope
 * 		$this->psa_result->numbers = array(1,2,3);
 *
 * 		// or the same thing but using get_instance() method. This works from any scope.
 * 		Psa_Result::get_instance()->more_numbers = array(4,5,6);
 * 	}
 * }
 *
 * ?>
 * </code>
 */
class Psa_Result {

	/**
	 * Returns object's instance.
	 *
	 * You should statically call this method with scope resolution operator
	 * (<kbd>Psa_Result::get_instance()</kbd>) which gives you
	 * instance to the object from any scope in your application.
	 *
	 * @return Psa_Result
	 */
	public static function get_instance($classname = __CLASS__){
		return parent::get_instance($classname);
	}

}
