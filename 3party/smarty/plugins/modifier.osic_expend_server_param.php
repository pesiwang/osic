<?php
function smarty_modifier_osic_expend_server_param($server)
{
	if(is_a($server, 'TRouterMediaServerMemcache'))
		return 'array(\'host\' => \'' . $server->host . '\', \'port\' => ' . $server->port . ', \'prefix\' => \'' . $server->prefix . '\')';
	if(is_a($server, 'TRouterMediaServerRedis'))
		return 'array(\'host\' => \'' . $server->host . '\', \'port\' => ' . $server->port . ', \'prefix\' => \'' . $server->prefix . '\')';
	if(is_a($server, 'TRouterMediaServerMysql'))
		return 'array(\'host\' => \'' . $server->host . '\', \'port\' => ' . $server->port . ', \'user\' => \'' . $server->user . '\', \'password\' => \'' . $server->password . '\', \'database\' => \'' . $server->database . '\', \'table\' => \'' . $server->table . '\')';
	
	return '';
}
