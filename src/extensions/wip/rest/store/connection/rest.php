<?php

/**
 * @TODO review this file and clean up historical code/comments
 *
 * ONEDISCLAIMER
 **/
class One_Store_Connection_Rest extends One_Store_Connection_Abstract {
  /**
   * Open the connection
   * @return One_Store_Connection_Rest
   */
  public function getRouteMap($scheme) {

    $routeMap = new stdClass;

    $meta = $this->getMeta();
    $routeMap->entrypoint = $meta['service']['entrypoint'];

    // default settings
    $stub = strtolower($scheme->getName());
    $routeMap->select = "$stub";
    $routeMap->selectOne = "$stub/@";
    $routeMap->create = "$stub";
    $routeMap->update = "$stub/@";
    $routeMap->delete = "$stub/@";

    // name of this parameter is all wrong, but ...
    $resources = $scheme->getResources();
    foreach (array('select', 'selectOne', 'create', 'update', 'delete') as $k) {
      if (isset($resources[$k])) $routeMap->$k = $resources[$k];
    }

    return $routeMap;
  }

}
