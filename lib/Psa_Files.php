<?php
//@todo
include PSA_BASE_DIR . '/wri/asfunctions.php';

function &eCfg(){
	return psa_get_config();
}


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
 * Class for collecting (registering) information about file paths in your
 * application for autoloading.
 *
 * If <var>$PSA_CFG['develop_mode']</var> is true and
 * <var>$PSA_CFG['develop_mode_register_files']</var>
 * is true, file registration is done on every request for convenience that you
 * don't need to manually
 * register files if you add some class to your project.
 *
 * This class implements {@link http://en.wikipedia.org/wiki/Singleton_pattern
 * singleton pattern}
 * so you can get reference to Psa_Files object from any scope with
 * {@link get_instance()} method. You cannot make instance of Psa_Files object
 * with the
 * <var>new</var> operator.
 *
 * @see register_files.php
 *
 */
class Psa_Files {


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

		if (include Cfg()['autoload_data_file']) {
			if(isset($autoload_data)){
				$this->files_data = $autoload_data;
				return 1;
			}

			include_once PSA_BASE_DIR . '/lib/exceptions/Psa_File_Exception.php';
			throw new Psa_File_Exception('No autoload data in file ' . Cfg()['autoload_data_file'] . '. Try to register files.', 501);
		}

		include_once PSA_BASE_DIR . '/lib/exceptions/Psa_File_Exception.php';
		throw new Psa_File_Exception('Cannot open file ' . Cfg()['autoload_data_file'] . '. Try to register files to create it.', 502);
	}


	/**
	 * Registers files.
	 *
	 * It searches for .php files in folders defined by <var>$PSA_CFG['folders']['autoload']</var> and
	 * returns array with file names and corresponding paths.
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
	 *             ....
	 *         )
	 * )
	 * </code>
	 *
	 * @return array|int Array with files data.
	 * @param array $additional_autoload_folders Array with paths.
	 * @see save()
	 * @see config.php
	 */
	function register($additional_autoload_folders = array()){

		$return = array();

		// search for files inside folders specified in Cfg()['folders']['autoload'] array
		$folders_autoload = Cfg()['folders']['autoload'];
		if($additional_autoload_folders){
			$folders_autoload = array_merge($folders_autoload, $additional_autoload_folders);
		}
		if(is_array($folders_autoload)){
			foreach ($folders_autoload as $folder_path){

				$this->check_files(PSA_BASE_DIR . "/$folder_path", $return);
			}
		}

		if(!isset($return['class_paths']))
			$return['class_paths'] = array();

		return $this->files_data = $return;
	}


	/**
	 * Recursively checks for files inside <var>$dir</var>.
	 *
	 * This is helper method for register().
	 *
	 * @see register()
	 * @return int 1-sucess, 0-if cannot open <kbd>$dir</kbd>
	 * @ignore
	 */
	protected function check_files($dir, &$return, $recursion = false, $recursion_depth = 0){

		static $files = array();

		if(!file_exists($dir) or !$handle = opendir($dir)){
			include_once PSA_BASE_DIR . '/lib/exceptions/Psa_File_Exception.php';
			throw new Psa_File_Exception("Cannot open $dir to check files for autoloader.", 505);
		}


		if($handle){

			while(($file = readdir($handle)) !== false){

				// skip files which start with .
				if($file[0] == '.'){
					continue;
				}

				// full filesystem path
				$filepath = $dir . '/' . $file;

				if($recursion && is_dir($filepath)){
					// call self for this directory
					$this->check_files($filepath, $return, $recursion, $recursion_depth + 1);
				}
				// foreach file
				else{

					// if file extension is '.php'
					if(substr($file, -4, 4) == '.php'){

						// file name without extension
						if(strpos($file, '.class.php'))
							$file_basename = basename($file, '.class.php');
						else
							$file_basename = basename($file, '.php');

						// file full path
						$filepath =  realpath($filepath);
						
						// get data from @asFunction tags
						$this->get_asFunction_tags($filepath, $files);
						
						
						// check if file is registered allready
						if(isset($files['class_paths'][$file_basename])){
							trigger_error('Name ' . $file_basename . " already regsitered for autoloading " . $files['class_paths'][$file_basename], E_USER_NOTICE);
							continue 1;
						}

						// save class path
						$files['class_paths'][$file_basename] = $filepath;
						

						
						
			
							
						/*
						 @asFunction PSA_CFG psa_get_config() propSelector
						 @return asdasdasd
						 @asFunction Message MessageCoo getInstance ret:\asdasd\UserObject 
						 @asFunction Logger from>\aass\asasa\Class  getInstance ret:\asdasd\UserObject
						 * 
						 @asFunction Cfg \sadsd\asddsa\sads() propSelector
						 @asFunction Reg \sadsd\asddsa\sads propSelector
						 * 
						 @asFunction Cfg \sadsd\asddsa\sads() getInstance
						 @asFunction Reg \sadsd\asddsa\sads getInstance
						 */
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
		
		$this->write_autoload_file($files_data);
		$this->write_asFunction_file($files_data['@asFunction']);
	}
	
	
	protected function get_asFunction_tags($filepath, &$files){
	
		// .php file content
		$source = file_get_contents($filepath);
		
		$return = array();
		
		// if there is @asFunction tag
		if(strpos($source, '@asFunction') !== false){
				
			// find all @asFunction tags in phpdoc or // comments
			preg_match_all('/(\*|\/\/) +@asFunction +(.*?)\n/', $source, $asFunction_comments);
				
			if(isset($asFunction_comments[2])){
				foreach ($asFunction_comments[2] as $doc_tag) {
						
					$params = explode(' ', trim(preg_replace('/\s+/', ' ', $doc_tag)));
					
					// invalid asFunction comment
					if(count($params) < 3){
						trigger_error("Invalid @asFunction tag $doc_tag in $filepath", E_USER_NOTICE);
						continue;
					}
					
					$function_name = trim($params[0]);
					$target = trim($params[1]);
					
					if(substr($target, -2) == '()'){
						$target = substr($target, 0, -2);
						$target_is_function = 1;
					}
					else
						$target_is_function = 0;

					//if(isset($files['@asFunction'][$function_name]))
					//	trigger_error("Replaceing @asFunction $function_name from {$files['@asFunction'][$function_name]['tag_file']}", E_USER_NOTICE);
						
					$files['@asFunction'][$function_name] = array(
							'function' => $function_name,
							'target' => $target,
							'target_is_function' => $target_is_function,
							'template' => trim($params[2]),
							'tag_file' => $filepath,
							'params' => array_slice($params, 3),
					);
				}
			}
		}
	}
	
	
	protected function write_autoload_file($files_data){
		
		$file_content = $this->autoload_file_content($files_data);
		
		// save file
		if(file_put_contents(Cfg()['autoload_data_file'], $file_content))
			return $file_content;
		
		include_once PSA_BASE_DIR . '/lib/exceptions/Psa_File_Exception.php';
		throw new Psa_File_Exception('Error saving data about registered files to ' . Cfg()['autoload_data_file'], 504);
		
	}
	
	
	protected function write_asFunction_file($data){
		
		$file_content = $this->asfunctions_file_content($data);
		
		// save file
		if(file_put_contents(Cfg()['@asFunction_file'], $file_content))
			return $file_content;
		
		include_once PSA_BASE_DIR . '/lib/exceptions/Psa_File_Exception.php';
		throw new Psa_File_Exception('Error saving @asFunction file to ' . Cfg()['@asFunction_file'], 506);
		
	}
	
	
	protected function asfunctions_file_content($data){
		
		include_once PSA_BASE_DIR . '/lib/Psa_Dully.php';
		
		$templates_dir =  PSA_BASE_DIR . '/' . Cfg()['folders']['@asFunction']['template_dir'];
		
		$dully = new Psa_Dully($templates_dir);
		
		$content = "<?php\n";
		
		foreach ($data as $asFunction) {
			
			$template_file = $asFunction['template'] . '.php';
			
			if(file_exists($templates_dir . '/' . $template_file)){
				$dully->assign('gf', $asFunction);
				$content .= $dully->fetch($template_file);
			}
			else{
				include_once PSA_BASE_DIR . '/lib/exceptions/Psa_File_Exception.php';
				throw new Psa_File_Exception("Template file for @asFunction $template_file doesn't exists", 507);
			}
		}
	
		return $content;
	}
	
	
	protected function autoload_file_content($files_data){
		
		return "<?php\n\n\$autoload_data['class_paths'] = " . var_export($files_data['class_paths'], 1) . ";\n";
	}
	
	
}
