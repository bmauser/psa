<?php
/**
 * Front controller script.
 * Your site bootstrap file (usually index.php) shoud only include this file, so all requests
 * will go through this script. It includes main PSA files, instances some objects and
 * calls psa_main method of the Main class.
 *
 *
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
 * @version $Id: front_controller.php 171 2013-12-11 17:43:52Z bmauser $
 */


// PSA main directory
define('PSA_BASE_DIR', dirname(__FILE__));


// include PSA config file
include PSA_BASE_DIR . '/config.php';


// Error reporting. Enable or disable this through $PSA_CFG['develop_mode'] configuration option
if($PSA_CFG['develop_mode']){
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
}
else{
	ini_set('display_errors', 0);
}


// include required files
include PSA_BASE_DIR . '/lib/functions.php';
include PSA_BASE_DIR . '/lib/Psa_Singleton.php';
include PSA_BASE_DIR . '/lib/Psa_PDO.php';
include PSA_BASE_DIR . '/lib/Psa_Logger.php';
include PSA_BASE_DIR . '/lib/Psa_Files.php';
include PSA_BASE_DIR . '/lib/Psa_Registry.php';


// registry object
$psa_registry = Psa_Registry::get_instance();

// put $PSA_CFG config array to the registry
$psa_registry->PSA_CFG = $PSA_CFG;


// register files on every request
if($PSA_CFG['develop_mode'] && $PSA_CFG['develop_mode_register_files']){
	Psa_Files::get_instance()->save();
}


// register psa_autoload() function as __autoload() implementation
spl_autoload_register('psa_autoload');

// database connection wrapper object
$psa_registry->psa_database = new Psa_PDO();



// if in web mode
if(isset($_SERVER['REQUEST_URI'])){
	// get application base URL
	if(isset($PSA_CFG['folders']['basedir_web']))
		$psa_registry->basedir_web = $PSA_CFG['folders']['basedir_web'];
	else
		$psa_registry->basedir_web = str_replace('/' . basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
	$psa_registry->base_url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $psa_registry->basedir_web;
}


// include Main.php file
include PSA_BASE_DIR . '/' . $PSA_CFG['folders']['autoload'][0] . '/Main.php';


// remove from global scope
unset($PSA_CFG, $psa_registry);


// call application
$main = new Main;
$main->psa_main();
