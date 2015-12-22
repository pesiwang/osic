<?php
function smarty_modifier_osic_name2class($string)
{
	$string = preg_replace('/_([a-z])/ei', "strtoupper('\\1')", $string);
	return ucfirst(preg_replace('/\.([a-z])/ei', "'_' . strtoupper('\\1')", $string));
}
