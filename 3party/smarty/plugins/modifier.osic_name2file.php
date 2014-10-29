<?php
function smarty_modifier_osic_name2file($string)
{
	$subs = explode('.', $string);
	return strtolower($subs[count($subs) - 1]);
}
