<?php

  /**
   * Defines how Joomla 2.x should be addressed to perform certain storage and retrieval actions
   *
   * ONEDISCLAIMER
   **/
  class One_Store_Joomla2 extends One_Store
  {

    const QUERYCLASS = 'One_Query_Sql';

    /**
     * Return the proper Database-Object according to the scheme
     *
     * @param One_Scheme $scheme
     * @return JDatabase
     * @access protected
     */
    protected function db(One_Scheme $scheme)
    {
      return $this->dbFromConnection($scheme->getConnection());
    }

    /**
     * Return the proper Database-Object according to the connection
     *
     * @param One_Store_Connection_Interface $connection
     * @return object
     */
    protected function dbFromConnection(One_Store_Connection_Interface $connection)
    {
      return $connection->open();
    }

    /**
     * Return the One_Renderer for this One_Store
     *
     * @return One_Renderer
     */
    protected function getRenderer()
    {
      // Must return a new instance every time as in rare cases some joins or selectfields or ...
      // from a different query will be reused leading to unwanted queries
      return One_Query_Renderer::getInstance('mysql');
    }

    /**
     * Perform a query and return the selection
     *
     * @param One_Query $query
     * @return array
     */
    public function doQuery(One_Query $query)
    {
      $scheme   = $query->getScheme();
      $db       = $this->db($scheme);
      $renderer = $this->getRenderer();

      $sql = $renderer->render($query);

      $db->setQuery($sql);
      $rows = $db->loadAssocList();

      $selection = array();
      if (is_array($rows)) {
        foreach ($rows as $row) {
          $selection[] = $this->arrayToInstance($scheme, $row);
        }
      }

      return $selection;
    }

    /**
     * Use the selectors to retrieve a number of objects.
     *
     * @param One_Scheme $scheme
     * @param array $selectors
     * @return array
     */
    public function select(One_Scheme $scheme, array $selectors = array())
    {
      $query = One_Repository::selectQuery($scheme);

      if (count($selectors)) {
        foreach ($selectors as $sel) {
          list($path, $val, $op) = $sel;
          $query->where($path, $op, $val);
        }
      }

      $selection = $this->executeSchemeQuery($query);

      return (count($selection) > 0) ? $selection : NULL;
    }

    /**
     * Convert an array to an instance of the specified scheme
     *
     * @param One_Scheme $scheme
     * @param array $row
     * @return One_Model
     */
    protected function arrayToInstance(One_Scheme $scheme, $row)
    {
      // check the scheme cache
      $idAttribute = $scheme->getIdentityAttribute();
      $id          = $row[$idAttribute->getName()];

      $cached = One_Model_IdentityMap::find($scheme->getName(), $id);
      if ($cached) {
        return $cached;
      }

      // not found : create a new instance
      //TODO: use a specific class specified in the scheme
      $model = One::make($scheme->getName());

      // PD17OCT08: for optimal performance, raw-store the data row entirely
      $model->fromArray($row);

      // fire afterLoad event for model
      $model->afterLoad();

      One_Model_IdentityMap::add($model);
      return $model;
    }

    /**
     * Select a single instance.
     *
     * @param One_Scheme $scheme
     * @param mixed $identityValue
     * @return One_Model
     */
    public function selectOne(One_Scheme $scheme, $identityValue)
    {
      $cached = One_Model_IdentityMap::find($scheme->getName(), $identityValue);
      if ($cached) {
        return $cached;
      }

      $db       = $this->db($scheme);
      $renderer = $this->getRenderer();
      $query    = One_Repository::selectQuery($scheme);

      $idAttr = $scheme->getIdentityAttribute();
      $column = $idAttr->getName();
      $value  = $idAttr->toString($identityValue);

      $query->where($column, 'eq', $value);

      $result = $this->executeSchemeQuery($query);

      if (count($result) > 0) {
        $model = $result[0];
        One_Model_IdentityMap::add($model);
        return $model;
      }
      return NULL;
    }

    /**
     * Run this scheme query and return the results
     *
     * @param One_Query $query
     * @param boolean $asInstance
     * @param boolean $overrideFilters
     * @return array
     */
    public function executeSchemeQuery(One_Query $query, $asInstance = true, $overrideFilters = false)
    {
      $scheme   = $query->getScheme();
      $db       = $this->db($scheme);
      $renderer = $this->getRenderer();
      $sql      = $renderer->render($query, $overrideFilters);

      $db->setQuery($sql);
      $rows = $db->loadAssocList();

      $selection = array();
      if (is_array($rows)) {
        $instanceScheme = $renderer->getInstanceScheme();
        foreach ($rows as $row) {
          if ($asInstance) {
            $selection[] = $this->arrayToInstance($instanceScheme, $row);
          }
          else {
            $obj = new StdClass();
            foreach ($row as $key => $val) {
              $obj->$key = $val;
            }
            $selection[] = $obj;
          }
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
    public function executeSchemeCount(One_Query $query, $overrideFilters = false)
    {
      $scheme   = $query->getScheme();
      $db       = $this->db($scheme);
      $renderer = $this->getRenderer();

      // Need to remember the old Select in case the One_Query is reused afterwards
      $oldSelect = $query->getSelect();

      $query->setSelect(array('COUNT( * ) AS total'));
      $sql = $renderer->render($query, $overrideFilters);

      // put the old Select back into the One_Query
      $query->setSelect($oldSelect);

      $db->setQuery($sql);

      return intval($db->loadResult());
    }

    /**
     * Add a relationship to the model
     *
     * @param One_Model $model
     * @param One_Relation_Adapter $link
     */
    public function addRelations(One_Model $model, One_Relation_Adapter $link)
    {
      $added = $model->getAddedRelations();
      //print_r($added);
      if (isset($added[$link->getName()])) {
        // @todo - this probably isn't the correct way to get to the db object we need?
        // the db object should be based on the info in the $link, not the $model ...
        $scheme = One_Repository::getScheme($model->getSchemeName());
        $db     = $this->db($scheme);

        $table     = $link->meta['table'];
        $localKey  = $link->fk('local');
        $remoteKey = $link->fk('remote');

        $localId    = $model->getIdentityName();
        $localValue = $model->$localId;

        // Insert the new (modified) relations in the given model
        $values = array();
        foreach ($added[$link->getName()] as $remoteValue) {
          if (is_array($remoteValue)) {
            foreach ($remoteValue as $rVal) {
              $values[] = '( "' . mysql_real_escape_string($localValue) . '", "' . mysql_real_escape_string($rVal) . '") ';
            }
          }
          else {
            $values[] = '( "' . mysql_real_escape_string($localValue) . '", "' . mysql_real_escape_string($remoteValue) . '") ';
          }
        }

        // only run the insert query if we actually received new values
        if (count($values)) {
          $sql = 'INSERT INTO	`' . $table . '`	(`' . $localKey . '`, `' . $remoteKey . '`) ' .
            'VALUES ' . implode(", ", $values);

          $db->setQuery($sql);
          $db->query();
        }
      }
    }

    /**
     * Save a relationship of the model
     *
     * @param One_Model $model
     * @param One_Relation_Adapter $link
     */
    public function saveRelations(One_Model $model, One_Relation_Adapter $link)
    {
      $modified = $model->getDeltaRelations();

      if (isset($modified[$link->getName()])) {
        $linkConnection = One_Repository::getConnection($link->meta['connection']);

        if (!($linkConnection->getStore() instanceof One_Store_Joomla2)) {
          $linkConnection->getStore()->saveRelations($model, $link);
        }
        else {

          $db = $this->dbFromConnection($linkConnection);

          $table     = $link->meta['table'];
          $localKey  = $link->fk('local');
          $remoteKey = $link->fk('remote');

          $localId    = $model->getIdentityName();
          $localValue = $model->$localId;

          // Start by removing the old relations between these models
          $sql = 'DELETE FROM `' . $table . '` ' .
            'WHERE `' . $localKey . '` = "' . $localValue . '"';

          $db->setQuery($sql);
          $db->query();

          // Insert the new (modified) relations in the given model
          $values = array();
          foreach ($modified[$link->getName()] as $remoteValue) {
            $values[] = '("' . $localValue . '", "' . $remoteValue . '")';
          }


          // only run the insert query if we actually received new values
          if (count($values) > 0) {

            $sql = 'INSERT INTO `' . $table . '` (`' . $localKey . '`, `' . $remoteKey . '`) ' .
              'VALUES ' . implode(', ', $values);

            $db->setQuery($sql);
            $db->query();
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
    public function deleteRelations(One_Model $model, One_Relation_Adapter $link)
    {
      $deleted = $model->getDeletedRelations();

      if (isset($deleted[$link->getName()])) {
        // @todo - this probably isn't the correct way to get to the db object we need?
        // the db object should be based on the info in the $link, not the $model ...
        $scheme = One_Repository::getScheme($model->getSchemeName());
        $db     = $this->db($scheme);

        $table     = $link->meta['table'];
        $localKey  = $link->fk('local');
        $remoteKey = $link->fk('remote');

        $localId    = $model->getIdentityName();
        $localValue = $model->$localId;

        // Insert the new (modified) relations in the given model
        $values = array();
        foreach ($deleted[$link->getName()] as $remoteValue) {
          if (is_array($remoteValue)) {
            foreach ($remoteValue as $rVal) {
              $values[] = '`' . $remoteKey . '` = "' . mysql_real_escape_string($rVal, $db) . '"';
            }
          }
          else {
            $values[] = '`' . $remoteKey . '` = "' . mysql_real_escape_string($remoteValue, $db) . '"';
          }
        }

        // only run the insert query if we actually received new values
        if (count($values)) {
          $sql = 'DELETE FROM `' . $table . '` ' .
            'WHERE `' . $localKey . '` = "' . mysql_real_escape_string($localValue, $db) . '"' .
            'AND ( ' . implode(' OR ', $values) . ' )';

          $db->setQuery($sql);
          $db->query();
        }
      }
    }

    /**
     * Insert a single instance
     *
     * @param One_Model $model
     */
    public function insert(One_Model $model)
    {
      $scheme = One_Repository::getScheme($model->getSchemeName());
      $db     = $this->db($scheme);

      // determine table to insert into
      $table = $this->getTable($scheme);

      $idSet = false;

      $idAttr = $scheme->getIdentityAttribute();
      $data   = new stdClass();

      foreach ($scheme->get('attributes') as $attribute) {
//			$attName = $attribute->getName();
        $attName = $attribute->getColumn();
        // if the model's identity attribute is set (probably to zero for new items),
        // we need to skip it when inserting .
        // @todo: should only be the case for auto increment id's, we
        // ought to allow preset values for id fields which don't auto increment...

//			if($attName <> $idAttr->getName())
        if ($attName <> $idAttr->getColumn()) {
          if (isset($attName)) {
            $data->$attName = $model->$attName;
          }
          else {
            $data->$attName = '';
          }
        }
        else {
          if (!is_null($model[$attName]) && trim($model[$attName]) != '0' && trim($model[$attName]) != '') {
            $idSet          = $model[$attName];
            $data->$attName = $model[$attName];
          }
        }
      }

      $modified = $model->getModified();
      foreach ($scheme->getLinks() as $link) {
        if ($link->getLinkType() == "manytoone") {
          $fk = $link->fk();
          if (isset($modified[$fk])) {
            $data->$fk = $modified[$fk];
          }
        }
      }

      if ($idSet !== false) {
//			$db->insertObject($table, $data, $idAttr->getName());
        $db->insertObject($table, $data, $idAttr->getColumn());
      }
      else {
        $db->insertObject($table, $data);
      }

      if (trim($db->errorMsg()) != '') {
        throw new One_Exception($db->errorMsg());
      }

      if ($idSet !== false) {
        $newId = $idSet;
      }
      else {
        $newId = $db->insertid();
      }

      if ($newId) {
//			$idfield = $idAttr->getName();
        $idfield         = $idAttr->getColumn();
        $model->$idfield = $newId;

        $modifiedRelations = $model->getDeltaRelations();

        // Handle ManyToMany relations
        foreach ($scheme->getLinks() as $link) {
          if ($link->getLinkType() == "manytomany") {
            if (isset($modifiedRelations[$link->getName()])) {
              $model->saveRelated($link);
            }
          }
        }
      }

      return null;
    }

    /**
     * Update a single instance
     *
     * @param One_Model $model
     */
    public function update(One_Model $model)
    {
      $scheme = One_Repository::getScheme($model->getSchemeName());
      $db     = $this->db($scheme);

      // determine table to insert into
      $table = $this->getTable($scheme);

      //create clauses
      $modified          = $model->getModified();
      $modifiedRelations = $model->getDeltaRelations();

      $data = new stdClass();

      foreach ($scheme->get('attributes') as $attName => $at) {
        if (isset($modified[$attName])) {
          $colName        = $at->getColumn();
          $data->$colName = $modified[$attName];
//				$data->$attName = $modified[ $attName ];
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
      foreach ($scheme->getLinks() as $link) {
        if ($link->getLinkType() == "manytoone") {
          $fk = $link->fk();
          if (isset($modified[$fk])) {
            $data->$fk = $modified[$fk];
          }
        }
      }

      $idAttr = $scheme->getIdentityAttribute();
//		$id = $idAttr->getName();
      $id    = $idAttr->getColumn();
      $value = $model->$id;
      $value = $idAttr->toString($value);

      if ($idAttr->getType() instanceof One_SchemeAttribute_Type_String) {
        $value = preg_replace('/^\"(.*)\"$/i', '$1', $value);
      }

      $data->$id = $value;

      $nrChanged = 0;
      foreach ($data as $key => $val) {
        if ($key != $id) {
          $nrChanged++;
        }
      }

      if ($nrChanged > 0) {
        $db->updateObject($table, $data, $id);
      }

      if (trim($db->errorMsg()) != '') {
        throw new One_Exception($db->errorMsg());
      }

      // Handle ManyToMany relations
      foreach ($scheme->getLinks() as $link) {

        /*var_dump($link);
        exit();*/

        if ($link->getLinkType() == "manytomany") {
          if (isset($modifiedRelations[$link->getName()])) {
            $model->saveRelated($link);
          }
        }
      }

      return null;
    }

    /**
     * delete a single instance
     *
     * @param One_Model $model
     * @return void
     */
    public function delete(One_Model $model)
    {
      $scheme = One_Repository::getScheme($model->getSchemeName());
      $db     = $this->db($scheme);

      // determine table to insert into
      $table = $this->getTable($scheme);

      $sql = 'DELETE FROM ' . $table . ' ';

      $idAttr = $scheme->getIdentityAttribute();
//		$id = $idAttr->getName();
      $id    = $idAttr->getColumn();
      $value = $model->$id;
      $value = $idAttr->toString($value);

      $where = 'WHERE `' . $id . '` = ' . $value;

      $db->setQuery($sql . $where);
      $db->query();

      return null;
    }

    /**
     * Get the mysql table used for the scheme
     *
     * @param One_Scheme $scheme
     * @return string
     */
    public function getDatasource(One_Scheme $scheme)
    {
      $resources = $scheme->getResources();
      $source    = $resources['table'];

      return $source;
    }

    /**
     * Get the current Joomla user
     *
     * @return JUser
     */
    public function getCurrentUser()
    {
      $currUser          = new StdClass();
      $currUser->id      = 0;
      $currUser->name    = 'guest';
      $currUser->isAdmin = false;

      $user = JFactory::getUser();
      if ($user->guest != 1) {
        $currUser->id      = $user->id;
        $currUser->name    = $user->name;
        $currUser->isAdmin = in_array($user->gid, array(23, 24, 25));
      }

      return $currUser;
    }

    /**
     * Get the table used for the scheme
     *
     * @param One_Scheme $scheme
     * @return string Table name used for the scheme
     */
    protected function getTable(One_Scheme $scheme)
    {
      $resources = $scheme->getResources();
      if (isset($resources['table'])) {
        return $resources['table'];
      }
      else {
        throw new One_Exception('A table must be defined for the scheme "' . $scheme->getName() . '"');
      }
    }

    /**
     * Helper function to turn an object into an array (needed when nooku is being used)
     */
    protected function objectToArray($object)
    {
      if (!is_object($object) && !is_array($object)) {
        return $object;
      }
      if (is_object($object)) {
        $object = get_object_vars($object);
      }

      return array_map(array($this, 'objectToArray'), $object);
    }

    /**
     * Function to set the proper encoding
     *
     * @param One_Scheme $scheme
     * @param string $encoding (utf8, iso-8859-1, ...)
     */
    public function setEncoding(One_Scheme $scheme, $encoding)
    {
      $db  = $this->db($scheme);
      $sql = 'set names "' . mysql_real_escape_string($encoding) . '"';

      $db->setQuery($sql);
      $db->query();
    }
  }
