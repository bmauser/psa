<?php
/**
 * @package PSA
 */


/**
 * Simple registry class.
 *
 * Registry object can be used to store some values or references to other objects to make
 * them globally accessible. You can use it as storage for data you want make accessible from any scope.
 * In <i>models</i> and <i>views</i> you can access <kbd>Psa_Registry</kbd> object through
 * reference in local scope.
 * For example, you can add property to <kbd>Psa_Registry</kbd> object in your model method
 * by
 * <code>
 * Reg()->my_value = '123';
 * </code>
 * It implements {@link http://en.wikipedia.org/wiki/Singleton_pattern singleton pattern}
 * so you can get reference to registry object from any scope with
 * {@link get_instance()} method. You cannot make instance of Psa_Registry object with
 * the <var>new</var> operator.
 * 
 * @asFunction Reg Psa_Registry propSelector
 */
class Psa_Registry {


	/**
	 * Application folder from web root folder.
	 *
	 * @var string
	 */
	public $basedir_web;


	/**
	 * Application base URI.
	 *
	 * @var string
	 */
	public $base_url;

}
