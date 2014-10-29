<?php
namespace osi;
<%if $router->cache != NULL%>
require_once __DIR__ . '/<%$name|osic_name2file%>.cache.php';
<%/if%>
<%if $router->storage != NULL%>
require_once __DIR__ . '/<%$name|osic_name2file%>.storage.php';
<%/if%>

class ObjectRouter_<%$name|osic_name2class%>
{
	static public function get($id){
		$object = null;
<%if $router->cache != NULL%>
		$object = ObjectCache_<%$name|osic_name2class%>::get($id);
<%/if%>
<%if $router->storage != NULL%>
		if(!isset($object)){
			$object = ObjectStorage_<%$name|osic_name2class%>::get($id);
<%if $router->cache != NULL%>
			if(isset($object))
				ObjectCache_<%$name|osic_name2class%>::set($id, $object);
<%/if%>
		}
<%/if%>
		return $object;
	}

	static public function set($id, Object_<%$name|osic_name2class%> $object){
<%if $router->cache != NULL%>
		ObjectCache_<%$name|osic_name2class%>::set($id, $object);
<%/if%>
<%if $router->storage != NULL%>
		ObjectStorage_<%$name|osic_name2class%>::set($id, $object);
<%/if%>
	}

	static public function del($id){
<%if $router->cache != NULL%>
		ObjectCache_<%$name|osic_name2class%>::del($id);
<%/if%>
<%if $router->storage != NULL%>
		ObjectStorage_<%$name|osic_name2class%>::del($id);
<%/if%>
	}
}
<%if $obsolete_router != NULL%>

class ObjectRouterObsolete_<%$name|osic_name2class%>
{
	static public function get($id){
		$object = null;
<%if $obsolete_router->cache != NULL%>
		$object = ObjectCacheObsolete_<%$name|osic_name2class%>::get($id);
<%/if%>
<%if $obsolete_router->storage != NULL%>
		if(!isset($object)){
			$object = ObjectStorageObsolete_<%$name|osic_name2class%>::get($id);
<%if $obsolete_router->cache != NULL%>
			if(isset($object))
				ObjectCacheObsolete_<%$name|osic_name2class%>::set($id, $object);
<%/if%>
		}
<%/if%>
		return $object;
	}

	static public function set($id, Object_<%$name|osic_name2class%> $object){
<%if $obsolete_router->cache != NULL%>
		ObjectCacheObsolete_<%$name|osic_name2class%>::set($id, $object);
<%/if%>
<%if $obsolete_router->storage != NULL%>
		ObjectStorageObsolete_<%$name|osic_name2class%>::set($id, $object);
<%/if%>
	}

	static public function del($id){
<%if $obsolete_router->cache != NULL%>
		ObjectCacheObsolete_<%$name|osic_name2class%>::del($id);
<%/if%>
<%if $obsolete_router->storage != NULL%>
		ObjectStorageObsolete_<%$name|osic_name2class%>::del($id);
<%/if%>
	}
}
<%/if%>
