

/**
 * From asFunction tag in <?php echo $gf['tag_file'] ?> 
 *
 * @param string $selector
 */
function &<?php echo $gf['function'] ?>($selector = null){
	
	static $obj = null;
	
	if($obj === null){
		<?php if($gf['target_type'] == 'function'){ ?>$obj = <?php echo $gf['target'] ?>();
		<?php } elseif($gf['target_type'] == 'array'){ ?>$obj = &<?php echo $gf['target'] ?>;
		<?php } elseif($gf['target_type'] == 'object'){ ?>$obj = <?php echo $gf['target'] ?>;
		<?php } else { ?>$obj = new <?php echo $gf['target'] ?>();<?php } ?>
	}
	
	if(!$selector)
		return $obj;
	
	return psa_get_set_property_by_selector($obj, $selector);	
}
