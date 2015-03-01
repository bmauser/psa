

/**
 * From asFunction tag in <?php echo $gf['tag_file'] ?> 
 *
 * @param string $instance_name
 * @return <?php if(!$gf['target_type'] == 'function'){?><?php echo $gf['target'] ?><?php }else{ ?><?php if(isset($gf['params'][0])) echo $gf['params'][0] ?><?php } ?> 
 */
function <?php echo $gf['function'] ?>($instance_name = null){
	
	// no arguments for constructor
	if(func_num_args() <= 1)
		$args = array();
	// with constructor arguments
	else{
		$args = func_get_args();
		array_shift($args);
	}
	
	return getInstance('<?php echo $gf['target'] ?>', $instance_name, $args, <?php echo ($gf['target_type'] == 'function') ? 1 : 0; ?>, null);
}