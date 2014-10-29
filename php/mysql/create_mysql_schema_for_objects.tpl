<%if $router->storage != NULL%>
<%foreach $router->storage->servers item=server%>
<%if is_a($server, 'TRouterMediaServerMysql')%>
mysql -u<%$server->user%><%if strlen($server->password)>0%> -p<%$server->password%><%/if%> -h <%$server->host%> -P <%$server->port%> -e"CREATE DATABASE IF NOT EXISTS <%$server->database%>";
mysql -u<%$server->user%><%if strlen($server->password)>0%> -p<%$server->password%><%/if%> -h <%$server->host%> -P <%$server->port%> -e"CREATE TABLE IF NOT EXISTS <%$server->database%>.<%$server->table%>(id <%if strcasecmp($object->key->type, 'INTEGER')==0%>INTEGER NOT NULL<%elseif strcasecmp($object->key->type, 'BIGINT')==0%>BIGINT NOT NULL<%elseif strcasecmp($object->key->type, 'STRING')==0%><%if $object->key->isFixedLength%>BINARY<%else%>VARBINARY<%/if%>(<%$object->key->length%>) NOT NULL<%/if%>, data MEDIUMBLOB, PRIMARY KEY(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8";
<%/if%>
<%/foreach%>
<%/if%>
<%if $obsolete_router != NULL%>
<%if $obsolete_router->storage != NULL%>
<%foreach $obsolete_router->storage->servers item=server%>
<%if is_a($server, 'TRouterMediaServerMysql')%>
mysql -u<%$server->user%><%if strlen($server->password)>0%> -p<%$server->password%><%/if%> -h <%$server->host%> -P <%$server->port%> -e"CREATE DATABASE IF NOT EXISTS <%$server->database%>";
mysql -u<%$server->user%><%if strlen($server->password)>0%> -p<%$server->password%><%/if%> -h <%$server->host%> -P <%$server->port%> -e"CREATE TABLE IF NOT EXISTS <%$server->database%>.<%$server->table%>(id <%if strcasecmp($object->key->type, 'INTEGER')==0%>INTEGER NOT NULL<%elseif strcasecmp($object->key->type, 'BIGINT')==0%>BIGINT NOT NULL<%elseif strcasecmp($object->key->type, 'STRING')==0%><%if $object->key->isFixedLength%>BINARY<%else%>VARBINARY<%/if%>(<%$object->key->length%>) NOT NULL<%/if%>, data MEDIUMBLOB, PRIMARY KEY(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8";
<%/if%>
<%/foreach%>
<%/if%>
<%/if%>
