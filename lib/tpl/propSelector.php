

/**
 * From asFunction tag in <?php echo $gf['tag_file'] ?> 
 *
 * @param string $selector
 */
function &<?php echo $gf['function'] ?>($selector = null){
	
	static $obj = null;
	
	if($obj === null){
		<?php if($gf['target_type'] == 'function'){ ?>$obj = <?php echo $gf['target'] ?>();
		<?php } elseif($gf['target_type'] == 'var'){ ?>$obj = &<?php echo $gf['target'] ?>;
		<?php } else { ?>$obj = new <?php echo $gf['target'] ?>();<?php } ?> 
	}
	
	if(!$selector)
		return $obj;
	
	<?php if(isset($gf['params']['exception'])){
		if($gf['params']['exception'] == 'no')
			$gf['params']['exception'] = '';
	?>		
	return getPropertyBySelector($obj, $selector, '<?php echo @$gf['params']['exception'] ?>', '<?php echo @$gf['params']['message'] ?>');
	<?php } else { ?> 
	return getPropertyBySelector($obj, $selector);
	<?php } ?> 
}
