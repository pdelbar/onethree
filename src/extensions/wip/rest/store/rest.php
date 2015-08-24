<?php

require_once 'unirest/Unirest.php';

/**
 * @TODO review this file and clean up historical code/comments
 * ONEDISCLAIMER
 **/
class One_Store_Rest extends One_Store {

  protected $routeMap;

  protected function init(One_Scheme $scheme) {
    $connection = $scheme->getConnection();
    $this->routeMap = $connection->getRouteMap($scheme);
  }

  /**
   * Use the selectors to retrieve a number of objects.
   *
   * @param One_Scheme $scheme
   * @param array $selectors
   * @return array
   */
  public function select(&$scheme, $selectors) {

//    if (count($selectors)) {
//      foreach ($selectors as $sel) {
//        list($path, $val, $op) = $sel;
//        $query->where($path, $op, $val);
//      }
//    }
    $this->init($scheme);

    $route = $this->map->selectRoute;
    $response = Unirest\Request::get($route);
    print_r($response);

//    $selection = $this->executeSchemeQuery($query);

//    return (count($selection) > 0) ? $selection : NULL;
  }

  /**
   * Convert an array to an instance of the specified scheme
   *
   * @param One_Scheme $scheme
   * @param array $row
   * @return One_Model
   */
  private function &arrayToInstance(&$scheme, &$row) {
    // check the scheme cache
    $idAttribute = $scheme->getIdentityAttribute();
    $id = $row[$idAttribute->getName()];

    $cached = One_Model_IdentityMap::find($scheme->getName(), $id);
    if ($cached) return $cached;

    // not found : create a new instance
    // @TODO: use a specific class specified in the scheme
    $model = One::make($scheme->getName());

    $model->fromArray($row);

    // fire afterLoad event for model
    $model->afterLoad();

    One_Model_IdentityMap::add($model);

    return $model;
  }

  /**
   * Select a single instance.
   *
   * @param mixed $scheme Can be the name of a One_Scheme or the One_Scheme object itself
   * @param mixed $identityValue
   * @return One_Model
   */
  public function selectOne(One_Scheme_Interface $scheme, $identityValue) {
    $cached = One_Model_IdentityMap::find($scheme->getName(), $identityValue);
    if ($cached) {
      return $cached;
    }

    $db = $this->db($scheme);
    $renderer = $this->getRenderer();
    $query = One_Repository::selectQuery($scheme->getName());

    $idAttr = $scheme->getIdentityAttribute();
    $column = $idAttr->getName();
    $value = $idAttr->toString($identityValue);

    $query->where($column, 'eq', $value);

    $result = $this->executeSchemeQuery($query);

    return (count($result) > 0) ? $result[0] : NULL;
  }

  /**
   * Run this scheme query and return the results
   *
   * @param One_Query $query
   * @param boolean $asInstance
   * @param boolean $overrideFilters
   * @return array
   */
  public function executeSchemeQuery(One_Query $query, $asInstance = true, $overrideFilters = false) {
    $scheme = $query->getScheme();
    $db = $this->db($scheme);
    $renderer = $this->getRenderer();
    $sql = $renderer->render($query, $overrideFilters);

    $result = mysql_query($sql, $db);

    if ($result == false)
      throw new One_Exception(mysql_error() . '<br />' . $sql);

    $selection = array();
    $instanceScheme = $renderer->getInstanceScheme();
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      if ($asInstance)
        $selection[] = $this->arrayToInstance($instanceScheme, $row);
      else {
        $obj = new StdClass();
        foreach ($row as $key => $val) {
          $obj->$key = $val;
        }
        $selection[] = $obj;
      }
    }

    return $selection;
  }

  /**
   * Return the number of results when the query is performed
   *
   * @param One_Query $query
   * @param boolean $overrideFilters
   * @return int
   */
  public function executeSchemeCount(One_Query $query, $overrideFilters = false) {
    $scheme = $query->getScheme();
    $db = $this->db($scheme);
    $renderer = $this->getRenderer();

    // Need to remember the old Select in case the One_Query is reused afterwards
    $oldSelect = $query->getSelect();

    $query->setSelect(array('COUNT(*) AS total'));
    $sql = $renderer->render($query, $overrideFilters);

    // put the old Select back into the One_Query
    $query->setSelect($oldSelect);

    // execute query
    $result = mysql_query($sql, $db);
    if ($result == false) {
      throw new One_Exception(mysql_error() . '<br />' . $sql);
    }
    $count = mysql_result($result, 0, 'total');

    return $count;
  }

  /**
   * Load all rows, converted into objects of the specified class
   *
   * @param One_Store $store
   * @param string $sql
   * @param string $className
   * @return array
   */
  public function &loadAll(&$store, $sql, $className = 'stdClass') {
    $db = $this->dbFromConnection($store);

    $selection = array();

    $result = mysql_query($sql, $db);

    if (strtolower($className) <> 'stdclass')
      $scheme = One_Repository::getScheme($className);

    // giving the className as second parameter should work, but mysteriously doesn't (no error messages whatsoever)
    // while( $object = mysql_fetch_object( $result, $className ) )
    while ($row = mysql_fetch_array($result)) {
      if (!$scheme)
        $selection[] = (object)$row;
      else
        $selection[] = $this->arrayToInstance($scheme, $row);
    }

    return $selection;
  }

  /**
   * Add a relationship to the model
   *
   * @param One_Model $model
   * @param One_Relation_Adapter $link
   */
  public function addRelations(One_Model $model, One_Relation_Adapter $link) {
    $added = $model->getAddedRelations();

    if (isset($added[$link->getName()])) {
      // @todo - this probably isn't the correct way to get to the db object we need?
      // the db object should be based on the info in the $link, not the $model ...
      $scheme = One_Repository::getScheme($model->getSchemeName());
      $db = $this->db($scheme);

      $table = $link->meta['table'];
      $localKey = $link->fk('local');
      $remoteKey = $link->fk('remote');

      $localId = $model->getIdentityName();
      $localValue = $model->$localId;

      // Insert the new (modified) relations in the given model
      $values = array();
      foreach ($added[$link->getName()] as $remoteValue) {
        if (is_array($remoteValue)) {
          foreach ($remoteValue as $rVal) {
            $values[] = '( "' . mysql_real_escape_string($localValue, $db) . '", "' . mysql_real_escape_string($rVal, $db) . '") ';
          }
        } else
          $values[] = '( "' . mysql_real_escape_string($localValue, $db) . '", "' . mysql_real_escape_string($remoteValue, $db) . '") ';
      }

      // only run the insert query if we actually received new values
      if (count($values)) {
        $sql = 'INSERT INTO	`' . $table . '`	(`' . $localKey . '`, `' . $remoteKey . '`) ' .
          'VALUES ' . implode(", ", $values);

        mysql_query($sql, $db);
      }
    }
  }

  /**
   * Save a relationship of the model
   *
   * @param One_Model $model
   * @param One_Relation_Adapter $link
   */
  public function saveRelations(One_Model $model, One_Relation_Adapter $link) {
    $modified = $model->getModifiedRelations();

    if (isset($modified[$link->getName()])) {
      $linkConnection = One_Repository::getConnection($link->meta['connection']);

      if (!($linkConnection->getStore() instanceof One_Store_Mysql)) {
        $linkConnection->getStore()->saveRelations($model, $link);
      } else {
        $db = $this->dbFromConnection($linkConnection);

        $table = $link->meta['table'];
        $localKey = $link->fk('local');
        $remoteKey = $link->fk('remote');

        $localId = $model->getIdentityName();
        $localValue = $model->$localId;

        // Start by removing the old relations between these models
        $sql = 'DELETE FROM `' . $table . '` ' .
          'WHERE `' . $localKey . '` = "' . mysql_real_escape_string($localValue, $db) . '"';

        mysql_query($sql, $db);

        // Insert the new (modified) relations in the given model
        $values = array();
        foreach ($modified[$link->getName()] as $remoteValue)
          $values[] = '( "' . mysql_real_escape_string($localValue, $db) . '", "' . mysql_real_escape_string($remoteValue, $db) . '") ';

        // only run the insert query if we actually received new values
        if (count($values)) {
          $sql = 'INSERT INTO	`' . $table . '` (`' . $localKey . '`, `' . $remoteKey . '`) ' .
            'VALUES ' . implode(", ", $values);

          mysql_query($sql, $db);
        }
      }
    }
  }

  /**
   * Delete a relationship from the model
   *
   * @param One_Model $model
   * @param One_Relation_Adapter $link
   */
  public function deleteRelations(One_Model $model, One_Relation_Adapter $link) {
    $deleted = $model->getDeletedRelations();

    if (isset($deleted[$link->getName()])) {
      // @todo - this probably isn't the correct way to get to the db object we need?
      // the db object should be based on the info in the $link, not the $model ...
      $scheme = One_Repository::getScheme($model->getSchemeName());
      $db = $this->db($scheme);

      $table = $link->meta['table'];
      $localKey = $link->fk('local');
      $remoteKey = $link->fk('remote');

      $localId = $model->getIdentityName();
      $localValue = $model->$localId;

      // Insert the new (modified) relations in the given model
      $values = array();
      foreach ($deleted[$link->getName()] as $remoteValue) {
        if (is_array($remoteValue)) {
          foreach ($remoteValue as $rVal) {
            $values[] = '`' . $remoteKey . '` = "' . mysql_real_escape_string($rVal, $db) . '"';
          }
        } else
          $values[] = '`' . $remoteKey . '` = "' . mysql_real_escape_string($remoteValue, $db) . '"';
      }

      // only run the insert query if we actually received new values
      if (count($values)) {
        $sql = 'DELETE FROM `' . $table . '` ' .
          'WHERE `' . $localKey . '` = "' . mysql_real_escape_string($localValue, $db) . '"' .
          'AND ( ' . implode(' OR ', $values) . ' )';

        mysql_query($sql, $db);
      }
    }
  }

  /**
   * Insert a single instance
   *
   * @param One_Model $model
   */
  public function insert(One_Model $model) {
    $scheme = One_Repository::getScheme($model->getSchemeName());
    $db = $this->db($scheme);

    // determine table to insert into
    $table = $this->getTable($scheme);

    $keys = array();
    $vals = array();
    $idSet = false;

    $idAttr = $scheme->getIdentityAttribute();

    foreach ($scheme->get('attributes') as $attribute) {
      // if the model's identity attribute is set (probably to zero for new items),
      // we need to skip it when inserting .
      // @todo: should only be the case for auto increment id's, we
      // ought to allow preset values for id fields which don't auto increment...

      if ($attribute->getName() <> $idAttr->getName()) {
        $attrName = $attribute->getName();
        $keys[$attribute->getName()] = $attribute->getName();
        if (isset($model->$attrName)) {
          $vals[$attribute->getName()] = $attribute->toString(mysql_real_escape_string($model->$attrName));
        } else {
          $vals[$attribute->getName()] = $attribute->toString('');
        }
      } else {
        if (!is_null($model[$attribute->getName()]) && trim($model[$attribute->getName()]) != '0' && trim($model[$attribute->getName()]) != '') {
          $keys[$attribute->getName()] = $attribute->getName();
          $vals[$attribute->getName()] = $attribute->toString(mysql_real_escape_string($model[$attribute->getName()]));
          $idSet = $model[$attribute->getName()];
        }
      }
    }

    $modified = $model->getModified();
    foreach ($scheme->getLinks() as $link) {
      if ($link->getLinkType() == "manytoone") {
        $fk = $link->fk();
        if (isset($modified[$fk])) {
          $keys[$fk] = $fk;
          // @todo fetch type threw relation => scheme => identityAttribute
          $vals[$fk] = '"' . mysql_real_escape_string($model[$fk]) . '"';
        }
      }
    }

    $sql = 'INSERT INTO `' . $table . '` (`';
    $sql .= implode('`, `', $keys);
    $sql .= '`) VALUES (';
    $sql .= implode(', ', $vals);
    $sql .= ')';

    if (!mysql_query($sql, $db)) {
      throw new One_Exception(mysql_error() . $sql);
    }

    if ($idSet !== false) {
      $newId = $idSet;
    } else {
      $newId = mysql_insert_id($db);
    }

    if ($newId) {
      $idfield = $idAttr->getName();
      $model->$idfield = $newId;

      $modifiedRelations = $model->getModifiedRelations();
      // Handle ManyToMany relations
      foreach ($scheme->getLinks() as $link) {
        if ($link->getLinkType() == "manytomany") {
          if (isset($modifiedRelations[$link->getName()])) {
            $model->saveRelated($link);
          }
        }
      }
    }
  }

  /**
   * Update a single instance
   *
   * @param One_Model $model
   */
  public function update(One_Model $model) {
    $scheme = One_Repository::getScheme($model->getSchemeName());
    $db = $this->db($scheme);

    // @TODO update query should be done in the renderer
    // determine table to insert into
    $table = $this->getTable($scheme);
    $sql = "UPDATE `" . $table . "` SET ";

    //create clauses
    $modified = $model->getModified();
    $modifiedRelations = $model->getModifiedRelations();

    $data = new stdClass();

    foreach ($scheme->get('attributes') as $attName => $at) {
      if (isset($modified[$attName])) {
        $data->$attName = $modified[$attName];
      }
    }

    // Check for relationships (FK values), cannot use attribute but must use column or link name
    // JL 06JAN2008 - Three possible situations, two are needed:
    // * ManyToOne
    //		* The FK is a field in the model's record
    //		* We need to set this field BEFORE saving the record
    // * ManyToMany
    //		* Relations are in a separate table
    //		* We should set them AFTER saving the record (especially when inserting a new record)
    // * OneToMany
    // 		* Not needed for now - When editing, we'll usually edit the child and select it's parent

    $mtos = array();
    foreach ($scheme->getLinks() as $link) {
      if ($link->getLinkType() == "manytoone") {
        $fk = $link->fk();
        if (isset($modified[$fk])) {
          $data->$fk = $modified[$fk];
          $mtos[$fk] = $modified[$fk];
        }
      }
    }

    $clauses = array();
    foreach ($scheme->get('attributes') as $attName => $at) {
      if (isset($modified[$attName])) {
        $clauses[] = One_Query_Renderer::getInstance('mysql')->formatAttribute($at, $modified[$attName]);
      }
    }

    if (count($mtos) > 0) {
      foreach ($mtos as $k => $v) {
        $clauses[] = '`' . $k . '` = "' . mysql_real_escape_string($v) . '"';
      }
    }

    $sql .= implode(', ', $clauses);

    $idAttr = $scheme->getIdentityAttribute();
    $id = $idAttr->getName();
    $value = $model->$id;
    $value = $idAttr->toString($value);
    $data->$id = $value;

    $sql .= " WHERE " . $id . ' = ' . $data->$id;
    // Don't perform the update if no updates have to be performed
    if (count($clauses) > 0) {
      if (mysql_query($sql, $db) === false) {
        throw new One_Exception('Update failed: ' . mysql_error($db));
      }
    }

    // Handle ManyToMany relations
    foreach ($scheme->getLinks() as $link) {
      if ($link->getLinkType() == "manytomany") {
        if (isset($modifiedRelations[$link->getName()])) {
          $model->saveRelated($link);
        }
      }
    }
  }

  /**
   * delete a single instance
   *
   * @param One_Model $model
   */
  public function delete(One_Model $model) {
    $scheme = One_Repository::getScheme($model->getSchemeName());
    $db = $this->db($scheme);

    // determine table to insert into
    $table = $this->getTable($scheme);
    $sql = 'DELETE FROM ' . $table;

    $idAttr = $scheme->getIdentityAttribute();
    $id = $idAttr->getName();
    $value = $model->$id;
    $value = $idAttr->toString(mysql_real_escape_string($value, $db));

    $sql .= ' WHERE `' . $id . '` = ' . $value;

    // execute query
    if (!mysql_query($sql, $db)) {
      throw new One_Exception(mysql_error() . $sql);
    }
  }

  /**
   * Set the attributes and values to be usable in a query
   *
   * @param One_Scheme _Attribute $attribute
   * @param mixed $value
   * @return String
   * @deprecated This will be done by the One_Query instance now
   */
  private function setToSQL($attribute, $value) {
    return '`' . $attribute->getName() . '` = ' . $attribute->toString(mysql_real_escape_string($value));
  }

  /**
   * Get the mysql table used for the scheme
   *
   * @param One_Scheme $scheme
   * @return string
   */
  public function getDatasource(One_Scheme_Interface $scheme) {
    $source = $scheme->getView();
    if (is_null($source)) {
      $resources = $scheme->getResources();
      $source = $resources['table'];
    }

    return $source;
  }

  /**
   * Get the table used for the scheme
   * @param One_Scheme $scheme
   * @return string Table name used for the scheme
   */
  protected function getTable(One_Scheme_Interface $scheme) {
    $resources = $scheme->getResources();
    if (isset($resources['table'])) {
      return $resources['table'];
    } else {
      throw new One_Exception('A table must be defined for the scheme "' . $scheme->getName() . '"');
    }
  }

  /**
   * Function to set the proper encoding
   * @param One_Scheme_Interface $scheme
   * @param string $encoding (utf8, iso-8859-1, ...)
   */
  public function setEncoding(One_Scheme_Interface $scheme, $encoding) {
    $db = $this->db($scheme);
    $sql = 'set names "' . mysql_real_escape_string($encoding) . '"';

    $result = mysql_query($sql, $db);

    if ($result === false)
      throw new One_Exception(mysql_error() . '<br />' . $sql);
  }
}
