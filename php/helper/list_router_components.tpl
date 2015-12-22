<%if $router->cache != NULL%>
cache
<%/if%>
<%if $router->storage != NULL%>
storage
<%/if%>
<%if $obsolete_router != NULL%>
<%if $obsolete_router->cache != NULL%>
obsolete_cache
<%/if%>
<%if $obsolete_router->storage != NULL%>
obsolete_storage
<%/if%>
<%/if%>
