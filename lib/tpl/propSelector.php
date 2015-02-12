

/**
 * From asFunction tag in <?php echo $gf['tag_file'] ?> 
 *
 * @param string $selector
 */
function &<?php echo $gf['function'] ?>($selector = null){
	
	if(!$selector)
		return <?php echo $gf['target'] ?><?php if($gf['target_is_function']){ ?>()<?php } ?>;
	
	return psa_get_set_property_by_selector(<?php echo $gf['target'] ?><?php if($gf['target_is_function']){ ?>()<?php } ?>, $selector);	
}
