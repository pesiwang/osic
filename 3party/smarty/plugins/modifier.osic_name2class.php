<?php
function smarty_modifier_osic_name2class($string)
{
	return ucfirst(preg_replace('/\.([a-z])/ei', "strtoupper('\\1')", $string));
}
