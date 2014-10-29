<?php
namespace osi;
<%if $router->cache != NULL%>
require_once __DIR__ . '/<%$name|osic_name2file%>.cache.php';
<%/if%>
<%if $router->storage != NULL%>
require_once __DIR__ . '/<%$name|osic_name2file%>.storage.php';
<%/if%>

class SetRouter_<%$name|osic_name2class%>
{
	static public function load($id){
		$elements = NULL;
<%if $router->cache != NULL%>
		$elements = SetCache_<%$name|osic_name2class%>::load($id);
<%/if%>
<%if $router->storage != NULL%>
		if(!isset($elements)){
			$elements = SetStorage_<%$name|osic_name2class%>::load($id);
<%if $router->cache != NULL%>
			if(isset($elements))
				SetCache_<%$name|osic_name2class%>::save($id, $elements);
<%/if%>
		}
<%/if%>
		return $elements;
	}

	static public function save($id, Array $elements){
<%if $router->cache != NULL%>
		SetCache_<%$name|osic_name2class%>::save($id, $elements);
<%/if%>
<%if $router->storage != NULL%>
		SetStorage_<%$name|osic_name2class%>::save($id, $elements);
<%/if%>
	}
}
<%if $obsolete_router != NULL%>

class SetRouterObsolete_<%$name|osic_name2class%>
{
	static public function load($id){
		$elements = NULL;
<%if $obsolete_router->cache != NULL%>
		$elements = SetCacheObsolete_<%$name|osic_name2class%>::load($id);
<%/if%>
<%if $obsolete_router->storage != NULL%>
		if(!isset($elements)){
			$elements = SetStorageObsolete_<%$name|osic_name2class%>::load($id);
<%if $obsolete_router->cache != NULL%>
			if(isset($elements))
				SetCacheObsolete_<%$name|osic_name2class%>::save($id, $elements);
<%/if%>
		}
<%/if%>
		return $elements;
	}

	static public function save($id, Array $elements){
<%if $obsolete_router->cache != NULL%>
		SetCacheObsolete_<%$name|osic_name2class%>::save($id, $elements);
<%/if%>
<%if $obsolete_router->storage != NULL%>
		SetStorageObsolete_<%$name|osic_name2class%>::save($id, $elements);
<%/if%>
	}
}
<%/if%>
