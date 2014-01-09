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
 * @package PSA/more
 * @version $Id: Psa_Files.php 169 2013-12-11 01:26:22Z bmauser $
 */


/**
 * Class that collects (registers) information about file paths in your application for autoloading.
 *
 * If <var>$PSA_CFG['develop_mode']</var> is true and <var>$PSA_CFG['develop_mode_register_files']</var>
 * is true, file registration is done on every request for convenience that you don't need to manually
 * register files if you add some class to your project.
 *
 * This class implements {@link http://en.wikipedia.org/wiki/Singleton_pattern singleton pattern}
 * so you can get reference to Psa_Files object from any scope with
 * {@link get_instance()} method. You cannot make instance of Psa_Files object with the
 * <var>new</var> operator.
 *
 * @see register_files.php
 *
 */
class Psa_Files extends Psa_Singleton{

	/**
	 * Array which holds data about registered files.
	 *
	 * This array is set with {@link set_data()} method.
	 * Array structure is the same as array returned from {@link register()} method.
	 *
	 * @var array
	 */
	public $files_data = array();


	/**
	 * Returns object's instance.
	 *
	 * You should statically call this method with scope resolution operator (::) which gives you
	 * instance to the object from any scope in your application.
	 *
	 * <b>Example:</b>
	 * <code>
	 * $files = Psa_Files::get_instance();
	 * </code>
	 *
	 * @return Psa_Files Instance of Psa_Files object.
	 */
	public static function get_instance($classname = __CLASS__){
		return parent::get_instance($classname);
	}


	/**
	 * Reads data about registered files from generated <kbd>autoload_data.php</kbd> file and
	 * fills {@link $files_data} member array.
	 *
	 * <b>Note:</b> you don't need to call this method if you called {@link save()} or {@link register()}
	 * method before because {@link register()} method will also fill {@link $files_data} array.
	 * This method only reads previously saved files data from the file.
	 *
	 * @see $files_data
	 * @see register()
	 * @see save()
	 * @return int 1-sucess, 0-failure
	 */
	public function set_data() {

		// config array
		$PSA_CFG = Psa_Registry::get_instance()->PSA_CFG;

		if (include $PSA_CFG['autoload_data_file']) {
			if(isset($autoload_data)){
				$this->files_data = $autoload_data;
				return 1;
			}

			include_once PSA_BASE_DIR . '/exceptions/Psa_File_Exception.php';
			throw new Psa_File_Exception("No autoload data in file {$PSA_CFG['autoload_data_file']}. Try to register files.", 501);
		}

		include_once PSA_BASE_DIR . '/exceptions/Psa_File_Exception.php';
		throw new Psa_File_Exception("Cannot open file {$PSA_CFG['autoload_data_file']}. Try to register files to create it.", 502);
	}


	/**
	 * Registers files.
	 *
	 * It searches for .php files in folders defined by <var>$PSA_CFG['folders']['hook_def']</var>,
	 * <var>$PSA_CFG['folders']['autoload']</var> and <var>$PSA_CFG['folders']['hook_autoload']</var> and
	 * returns array with file names and corresponding paths.
	 * It will search in content of .php files located in folders listed in <var>$PSA_CFG['folders']['hook_autoload']</var>
	 * array for classes that extends hook classes.
	 *
	 * Returning array may look like this:
	 * <code>
	 * Array
	 * (
	 *     [class_paths] => Array
	 *         (
	 *             [Main_View] => /app/psa/../views/Main_View.php
	 *             [Sum_View] => /app/psa/../views/Sum_View.php
	 *             [Default_Controller] => /app/psa/../controllers/Default_Controller.php
	 *             [Unauthorized_Exception] => /app/psa/../exceptions/Unauthorized_Exception.php
	 *             [Psa_Hook_After_Group_Create] => /psa/hooks/Psa_Hook_After_Group_Create.php
	 *             ....
	 *         )
	 *
	 *     [hooks] => Array
	 *         (
	 *             [Psa_Hook_Before_Group_Delete] => Array
	 *                 (
	 *                     [My_Hook1] => /app/psa/../hooks/My_Hook1.php
	 *                     [My_Hook2] => /pplication/psa/../hooks/My_Hook2.php
	 *                 )
	 *         )
	 * )
	 * </code>
	 *
	 * @return array|int Array with files data.
	 * @param array $additional_autoload_folders Array with paths.
	 * @param array $additional_hook_autoload_folders Array with paths.
	 * @see save()
	 * @see config.php
	 */
	function register($additional_autoload_folders = array(), $additional_hook_autoload_folders = array()){

		$PSA_CFG = Psa_Registry::get_instance()->PSA_CFG;

		$all_hook_types = array();

		// find all available hook types from file names in each $PSA_CFG['folders']['hook_def'] dir
		if(isset($PSA_CFG['folders']['hook_def']) && $PSA_CFG['folders']['hook_def']){
			foreach ($PSA_CFG['folders']['hook_def'] as $hooks_folder) {

				$hook_folder_path = PSA_BASE_DIR . '/' . $hooks_folder;

				if ($handle = @opendir($hook_folder_path)) {
					while (false !== ($file = readdir($handle))) {

						if(substr($file, -4, 4) == '.php'){
							// get the part of the filename to the first dot. This is the name of the hook class.
							$psa_hook_class_name = str_replace(strstr($file, '.'), '', $file);
							$all_hook_types[$psa_hook_class_name] = $hook_folder_path . '/' . $file;
						}
					}
					closedir($handle);
				}
				else{
					throw new Psa_File_Exception("files register: unable to open dir with hooks: $hook_folder_path", 503);
				}
			}
		}

		$return = array();

		// search for files inside folders specified in $PSA_CFG['folders']['autoload'] array
		$folders_autoload = $PSA_CFG['folders']['autoload'];
		if($additional_autoload_folders){
			$folders_autoload = array_merge($folders_autoload, $additional_autoload_folders);
		}
		if(is_array($folders_autoload)){
			foreach ($folders_autoload as $folder_path){

				$this->check_files(PSA_BASE_DIR . "/$folder_path", null, $return);
			}
		}

		// search for hooks inside folders specified in $PSA_CFG['folders']['hook_autoload'] array
		if(isset($PSA_CFG['folders']['hook_autoload']))
			$folders_hook_autoload = $PSA_CFG['folders']['hook_autoload'];
		else
			$folders_hook_autoload = array();
		if($additional_hook_autoload_folders){
			$folders_hook_autoload = array_merge($folders_hook_autoload, $additional_hook_autoload_folders);
		}
		if(is_array($folders_hook_autoload) && $all_hook_types){
			foreach ($folders_hook_autoload as $folder_path){
				$this->check_files(PSA_BASE_DIR . "/$folder_path", $all_hook_types, $return);
			}
		}

		if(!isset($return['class_paths']))
			$return['class_paths'] = array();

		// put also hooks into return array which will be used for class autoloading
		if($all_hook_types){
			$return['class_paths'] = array_merge($return['class_paths'], $all_hook_types);
		}

		return $this->files_data = $return;
	}


	/**
	 * Recursively checks for files inside <var>$dir</var>.
	 *
	 * This is helper method for register().
	 *
	 * @see register()
	 * @return int 1-sucess, 0-if cannot open <kbd>$dir</kbd> or array with files data
	 * @ignore
	 */
	protected function check_files($dir, $all_hook_types = null, &$return, $recursion = false, $recursion_depth = 0){

		static $files = array();

		$PSA_CFG = Psa_Registry::get_instance()->PSA_CFG;

		if(!file_exists($dir) or !$handle = opendir($dir)){

			// if logging is enabled
			/*
			if($PSA_CFG['logging']['max_log_level'] >= 2){
				// parameters for Psa_Logger::log() method
				$log_data['message']  = "File registration: folder $dir don't exists or not readable";
				$log_data['function'] = __FUNCTION__;
				$log_data['level']    = 2;
				Psa_Logger::get_instance()->log($log_data);
			}
			*/

			return 0;
		}


		if ($handle){

			while (($file = readdir($handle)) !== false){

				if ($file=='.' or $file=='..'  or strcasecmp($file,'.svn')==0 or strcasecmp($file,'.cvs')==0 or strcasecmp($file,'.git')==0){
					continue;
				}

				// full filesystem path
				$filepath = $dir . '/' . $file;

				if ($recursion && is_dir($filepath)){
					// call self for this directory
					$this->check_files($filepath, $all_hook_types, $return, $recursion, $recursion_depth+1);
				}
				// check for classes in php files
				else{

					// if file extension is '.php'
					if(substr($file, -4, 4) == '.php'){

						// file name without extension
						if(strpos($file, '.class.php'))
							$file_basename = basename($file, '.class.php');
						else
							$file_basename = basename($file, '.php');

						// check if file is registered allready
						if(isset($files['class_paths'][$file_basename])){
							continue 1;
						}



						// check for hooks
						if($all_hook_types){

							// Entire file content.
							// I guess you won't have large .php files to consume all memory limited by memory_limit php.ini directive
							$file_content = file_get_contents($filepath);

							// skip files that contains 'PSA_SKIP_FILE_REGISTER' text anywhere
							if(!(strpos($file_content,'PSA_SKIP_FILE_REGISTER') === false))
								continue;


							foreach ($all_hook_types as $hook_class_name => $hook_file_path){

								// match class name that extends hook class
								if(preg_match_all('/class +([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*).*? extends +' . $hook_class_name . '/', $file_content, $matches)){

									foreach ($matches[1] as $hook_name) {
										$files['hooks'][$hook_class_name][$hook_name] = realpath($filepath);
										$files['class_paths'][$hook_name] = realpath($filepath);
									}
								}
							}
						}
						// register all files
						else{
							$files['class_paths'][$file_basename] = realpath($filepath);
						}

					}
				}
			}
			unset($file_content);

			@closedir($handle);
		}
		else
			return 0;

		$return = $files;
	}


	/**
	 * Saves data about registered files returned from {@link register()} method to the file.
	 *
	 * By default the data is stored in <kbd>autoload_data.php</kbd> file. You can change that with
	 * <var>$PSA_CFG['autoload_data_file']</var> config value.
	 * If called without arguments, it first calls {@link register()} method.
	 * Throws {@link Psa_File_Exception} on error.
	 *
	 * @param array $files_data array returned from {@link register()} method
	 * @see register()
	 * @see config.php
	 * @throws Psa_File_Exception
	 * @return int 1 for sucess
	 */
	function save($files_data = null){

		// call register() method if no data is passed
		if(!$files_data)
			$files_data = $this->register();

		// config array
		$PSA_CFG = Psa_Registry::get_instance()->PSA_CFG;

		$file_content = "<?php\n\n\$autoload_data = " . var_export($files_data, 1) . ";\n";

		// save file
		if(file_put_contents($PSA_CFG['autoload_data_file'], $file_content)){
			return 1;
		}

		throw new Psa_File_Exception('Error saving data about registered files to file ' . $PSA_CFG['autoload_data_file'], 504);
	}
}
