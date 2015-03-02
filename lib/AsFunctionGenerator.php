<?php
//@todo
//include PSA_BASE_DIR . '/wri/asfunctions.php';

//function &eCfg(){
//	return psa_get_config();
//}


/**
 * @package PSA/more
 */

/**
 * Class for collecting (registering) information about file paths in your
 * application.
 *
 * If <var>$PSA_CFG['develop_mode']</var> is true and
 * <var>$PSA_CFG['asFunction']['develop_mode_check']</var>
 * is true, file registration is done on every request for convenience that you
 * don't need to manually
 * register files if you add some class to your project.
 *
 * This class implements {@link http://en.wikipedia.org/wiki/Singleton_pattern
 * singleton pattern}
 * so you can get reference to PreInit object from any scope with
 * {@link get_instance()} method. You cannot make instance of PreInit object
 * with the
 * <var>new</var> operator.
 *
 * @see register_files.php
 * @asFunction AsFunctionGenerator AsFunctionGenerator getInstance
 */
class AsFunctionGenerator {


	/**
	 * Registers files.
	 *
	 * It searches for .php files in folders defined by <var>$PSA_CFG['folders']['autoload']</var> and
	 * returns array with file names and corresponding paths.
	 *
	 *
	 * @return array|int Array with files data.
	 * @param array $additional_folders Array with paths.
	 * @see save()
	 * @see config.php
	 * @return array
	 */
	function scan($additional_folders = array()){

		$return = array();

		// search for files inside folders specified in Cfg()['folders']['autoload'] array
		$folders_autoload = Cfg('asFunction.check_dir');
		if($additional_folders){
			$folders_autoload = array_merge($folders_autoload, $additional_folders);
		}
		if(is_array($folders_autoload)){
			foreach ($folders_autoload as $folder_path){
				$this->checkDir(PSA_BASE_DIR . '/' . $folder_path, $return);
			}
		}

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
	protected function checkDir($dir, &$return, $recursively = false){

		static $files = array();

		if(!file_exists($dir) or !$handle = opendir($dir)){
			include_once PSA_BASE_DIR . '/lib/exceptions/FileException.php';
			throw new FileException("Cannot open $dir.", 505);
		}


		if($handle){

			while(($file = readdir($handle)) !== false){

				// skip files which start with .
				if($file[0] == '.'){
					continue;
				}

				// full filesystem path
				$filepath = $dir . '/' . $file;

				if($recursively && is_dir($filepath)){
					// call self for this directory
					$this->checkDir($filepath, $return, $recursively);
				}
				// foreach file
				else{
					// if file extension is '.php'
					if(substr($file, -4, 4) == '.php'){

						// file full path
						$filepath = realpath($filepath);
						
						// get data from @asFunction tags
						$this->checkFile($filepath, $files);
						
						/*
						 @asFunction PSA_CFG psa_get_config() propSelector exceptionClass 
						 @return asdasdasd
						 @asFunction Message MessageCoo getInstance ret:\asdasd\UserObject 
						 @asFunction Logger from>\aass\asasa\Class  getInstance ret:\asdasd\UserObject
						 * 
						 @asFunction Cfg \sadsd\asddsa\sads() propSelector
						 @asFunction Reg \sadsd\asddsa\sads[] propSelector
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
	
	
	protected function checkFile($filepath, &$files){
	
		// .php file content
		$source = file_get_contents($filepath);
		
		$return = array();
		
		// if there is @asFunction tag
		if(strpos($source, 'asFunction') !== false){
				
			// find all @asFunction tags in phpdoc or // comments
			preg_match_all('/(\*|\/\/) +@asFunction +(.*?)\n/', $source, $asFunction_comments);
				
			if(isset($asFunction_comments[2])){
				foreach ($asFunction_comments[2] as $doc_tag) {

					$asFunction_line = trim(preg_replace('/\s+/', ' ', $doc_tag));
					$params = explode(' ', $asFunction_line, 4);
					
					// invalid asFunction comment
					if(count($params) < 3){
						trigger_error("Invalid @asFunction tag $doc_tag in $filepath", E_USER_NOTICE);
						continue;
					}
					
					$function_name = trim($params[0]);
					$target = trim($params[1]);
					
					$target_type = 'class';
					
					if(substr($target, -2) == '()')
						$target_type = 'function';
					else if(substr_count($target, '$'))
						$target_type = 'var';
					
					// remove () or []
					if($target_type == 'function')
						$target = substr($target, 0, -2);

					//if(isset($files['asFunction'][$function_name]))
					//	trigger_error("Replaceing @asFunction $function_name from {$files['asFunction'][$function_name]['tag_file']}", E_USER_NOTICE);
					
					$template_params = array();
					
					// get template params
					if(isset($params[3])){
						
						$tparams_csv_arr = str_getcsv(trim($params[3]), ' ');
						
						foreach ($tparams_csv_arr as $param_value) {
							
							$key_val = explode('=', $param_value, 2);
							
							if(isset($key_val[1]))
								$template_params[$key_val[0]] = $key_val[1];
							else
								$template_params[] = $param_value;
						}
					}
					
					// data for temlplate
					$files['asFunction'][$function_name] = array(
							'function' => $function_name,
							'target' => $target,
							'target_type' => $target_type,
							'template' => trim($params[2]),
							'tag_file' => $filepath,
							'params' => $template_params,
					);
				}
			}
		}
	}
	
	
	/**
	 * Saves data about registered files returned from {@link register()} method to the file.
	 *
	 * By default the data is stored in <kbd>autoload_data.php</kbd> file. You can change that with
	 * <var>$PSA_CFG['autoload_data_file']</var> config value.
	 * If called without arguments, it first calls {@link register()} method.
	 * Throws {@link FileException} on error.
	 *
	 * @param array $data array returned from {@link register()} method
	 * @see register()
	 * @see config.php
	 * @throws FileException
	 * @return int 1 for sucess
	 */
	public function write($data = null){
		
		// call register() method if no data is passed
		if(!$data)
			$data = $this->scan();
		
		$file_content = $this->getContent($data['asFunction']);
		
		// save file
		if(file_put_contents(Cfg('asFunction.file_path'), $file_content))
			return 1;
		
		include_once PSA_BASE_DIR . '/lib/exceptions/FileException.php';
		throw new FileException('Error saving asFunction file to ' . Cfg()['asFunction.file_path'], 506);
		
	}
	
	
	protected function getContent($data){
		
		include_once PSA_BASE_DIR . '/lib/Dully.php';
		
		$templates_dir =  PSA_BASE_DIR . '/' . Cfg('asFunction.template_dir');
		
		$dully = new Dully($templates_dir);
		
		$content = "<?php\n";
		
		foreach ($data as $asFunction) {
			
			$template_file = $asFunction['template'] . '.php';
			
			if(file_exists($templates_dir . '/' . $template_file)){
				$dully->assign('gf', $asFunction);
				$content .= $dully->fetch($template_file);
			}
			else{
				include_once PSA_BASE_DIR . '/lib/exceptions/FileException.php';
				throw new FileException("Template file for @asFunction $template_file doesn't exists", 507);
			}
		}
	
		return $content;
	}
}
