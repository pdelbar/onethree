<?php

/**
 * Defines how Mongo should be addressed to perform certain storage and retrieval actions
 *


  * @TODO review this file and clean up historical code/comments
 * @subpackage Store

 **/
class One_Store_Mongo extends One_Store
{
	/**
	 * @var One_Renderer The One_Renderer used for this scheme
	 */
	protected $_renderer = NULL;

	/**
	 * @var MongoDB MongoDB database object used
	 */
	protected $_dbh = NULL;

	/**
	 * Return the proper Database-Object according to the scheme
	 *
	 * @param One_Scheme $scheme
	 * @return MongoDB
	 */
	protected function db(One_Scheme $scheme)
	{
		if(is_null($this->_dbh)) {
			$this->dbFromConnection($scheme->getConnection());
		}

		return $this->_dbh;
	}

	/**
	 * Return the proper Database-Object according to the connection
	 *
	 * @param One_Store_Connection_Interface $connection
	 * @return MongoDB
	 */
	protected function dbFromConnection(One_Store_Connection_Interface $connection )
	{
		if(is_null($this->_dbh)) {
			$this->_dbh = $connection->open();
		}
		return $this->_dbh;
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
		return One_Query_Renderer::getInstance('mongo');
	}

	/**
	 * Use the selectors to retrieve a number of objects.
	 *
	 * @param One_Scheme $scheme
	 * @param array $selectors
	 * @return array
	 */
	public function select( &$scheme, $selectors )
	{
		$query = One_Repository::selectQuery( $scheme );

		if (count($selectors))
		{
			foreach ($selectors as $sel)
			{
				list( $path, $val, $op ) = $sel;
				$query->where( $path, $op, $val );
			}
		}

		$selection = $this->executeSchemeQuery( $query );

		return ( count( $selection ) > 0 ) ? $selection : NULL;
	}

	/**
	 * Convert an array to an instance of the specified scheme
	 *
	 * @param One_Scheme $scheme
	 * @param array $row
	 * @return One_Model
	 */
	private function arrayToInstance($scheme, $row)
	{
		// check the scheme cache
		$id = (string) $row['_id'];

		$cached = One_Model_IdentityMap::find($scheme->getName(), $id);
		if ($cached) return $cached;

		// not found : create a new instance
		// @TODO: use a specific class specified in the scheme
		$model = One::make($scheme->getName());

		$row['_id'] = (string) $row['_id'];
		$model->fromArray( $row );

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
	public function selectOne(One_Scheme $scheme, $identityValue)
	{
		$cached = One_Model_IdentityMap::find($scheme->getName(), $identityValue);
		if($cached) {
			return $cached;
		}

		$db             = $this->db($scheme);
		$connectionMeta = $scheme->getResources();
		$collection     = $db->selectCollection($connectionMeta['collection']);

		try {
			$result = $collection->findOne(array('_id' => new MongoId($identityValue)));
		}
		catch(Exception $e) {
			throw new One_Exception('Query failed: '.$e->getMessage());
		}


		if(null !== $result) {
			$result = $this->arrayToInstance($scheme, $result);
		}

		return $result;
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
		$scheme      = $query->getScheme();
		$db          = $this->db($scheme);
		$renderer    = $this->getRenderer();
		$searchQuery = $renderer->render($query, $overrideFilters);
		$searchQuery = json_decode($searchQuery);

		$connectionMeta = $scheme->getResources();
		$collection     = $db->selectCollection($connectionMeta['collection']);

		try {
			$results = $collection->find($searchQuery->query, $searchQuery->fields);
		}
		catch(Exception $e) {
			throw new One_Exception('Query failed: '.$e->getMessage());
		}

		$selection = array();
		$instanceScheme = $renderer->getInstanceScheme();
		foreach($results as $result)
		{
			if($asInstance) {
				$selection[] = $this->arrayToInstance($instanceScheme, $result);
			}
			else
			{
				$obj = new StdClass();
				foreach($row as $key => $val)
				{
					if('_id' == $key) {
						$val = (string) $val;
					}
					$obj->$key = $val;
				}
				$selection[] =  $obj;
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
		$scheme      = $query->getScheme();
		$db          = $this->db($scheme);
		$renderer    = $this->getRenderer();
		$searchQuery = $renderer->render($query, $overrideFilters);

		$connectionMeta = $scheme->getResources();
		$collection     = $db->selectCollection($connectionMeta['collection']);

		try {
			$count = $collection->count($searchQuery['query']);
		}
		catch(Exception $e) {
			throw new One_Exception('Query failed: '.$e->getMessage());
		}

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
	public function &loadAll( &$store, $sql, $className = 'stdClass' )
	{
		$scheme      = $query->getScheme();
		$db          = $this->db($scheme);

		$connectionMeta = $scheme->getResources();
		$collection     = $db->selectCollection($connectionMeta['collection']);

		try {
			$results = $collection->find();
		}
		catch(Exception $e) {
			throw new One_Exception('Query failed: '.$e->getMessage());
		}

		$scheme = NULL;
		if( strtolower( $className ) <> 'stdclass' ) {
			$scheme = One_Repository::getScheme($className);
		}

		$selection = array();
		foreach($results as $result)
		{
			if(null !== $scheme) {
				$selection[] = $this->arrayToInstance($instanceScheme, $row);
			}
			else
			{
				$obj = new StdClass();
				foreach($row as $key => $val) {
					if('_id' == $key) {
						$val = (string) $val;
					}
					$obj->$key = $val;
				}
				$selection[] =  $obj;
			}
		}

		return $selection;
	}

	/**
	 * Add a relationship to the model
	 *
	 * @param One_Model $model
	 * @param One_Relation_Adapter $link
	 */
	public function addRelations(One_Model $model, One_Relation_Adapter $link)
	{
		return NULL;
	}

	/**
	 * Save a relationship of the model
	 *
	 * @param One_Model $model
	 * @param One_Relation_Adapter $link
	 */
	public function saveRelations(One_Model $model, One_Relation_Adapter $link)
	{
		return NULL;
	}

	/**
	 * Delete a relationship from the model
	 *
	 * @param One_Model $model
	 * @param One_Relation_Adapter $link
	 */
	public function deleteRelations(One_Model $model, One_Relation_Adapter $link)
	{
		return NULL;
	}

	/**
	 * Insert a single instance
	 *
	 * @param One_Model $model
	 */
	public function insert(One_Model $model)
	{
//		$scheme = One_Repository::getScheme($model->getSchemeName());
//		$db = $this->db($scheme);
//
//		// determine table to insert into
//		$table = $this->getTable($scheme);
//
//		$keys  = array();
//		$vals  = array();
//		$idSet = false;
//
//		$idAttr = $scheme->getIdentityAttribute();
//
//		foreach($scheme->get('attributes') as $attribute)
//		{
//			// if the model's identity attribute is set (probably to zero for new items),
//			// we need to skip it when inserting .
//			// @todo: should only be the case for auto increment id's, we
//			// ought to allow preset values for id fields which don't auto increment...
//
//			if($attribute->getName() <> $idAttr->getName())
//			{
//				$attrName = $attribute->getName();
//				$keys[$attribute->getName()] = $attribute->getName();
//				if(isset($model->$attrName)) {
//					$vals[$attribute->getName()] = $attribute->toString(mysql_real_escape_string($model->$attrName));
//				}
//				else {
//					$vals[$attribute->getName()] = $attribute->toString('');
//				}
//			}
//			else
//			{
//				if(!is_null($model[$attribute->getName()]) && trim($model[$attribute->getName()]) != '0' && trim($model[$attribute->getName()]) != '')
//				{
//					$keys[$attribute->getName()] = $attribute->getName();
//					$vals[$attribute->getName()] = $attribute->toString(mysql_real_escape_string($model[$attribute->getName()]));
//					$idSet = $model[$attribute->getName()];
//				}
//			}
//		}
//
//		$modified = $model->getModified();
//		foreach ($scheme->getLinks() as $link)
//		{
//			if ($link->getLinkType() == "manytoone")
//			{
//				$fk = $link->fk();
//				if(isset($modified[ $fk ]))
//				{
//					$keys[$fk] = $fk;
//					// @todo fetch type threw relation => scheme => identityAttribute
//					$vals[$fk] = '"'.mysql_real_escape_string($model[$fk]).'"';
//				}
//			}
//		}
//
//		$sql = 'INSERT INTO `'.$table.'` (`';
//		$sql .= implode('`, `', $keys);
//		$sql .= '`) VALUES (';
//		$sql .= implode(', ', $vals);
//		$sql .= ')';
//
//		if(!mysql_query($sql, $db)) {
//			throw new One_Exception(mysql_error().$sql);
//		}
//
//		if($idSet !== false) {
//			$newId = $idSet;
//		}
//		else {
//			$newId = mysql_insert_id($db);
//		}
//
//		if($newId)
//		{
//			$idfield = $idAttr->getName();
//			$model->$idfield = $newId;
//
//			$modifiedRelations = $model->getModifiedRelations();
//			// Handle ManyToMany relations
//			foreach($scheme->getLinks() as $link)
//			{
//				if($link->getLinkType() == "manytomany")
//				{
//					if (isset($modifiedRelations[$link->getName()])) {
//						$model->saveRelated($link);
//					}
//				}
//			}
//		}
	}

	/**
	 * Update a single instance
	 *
	 * @param One_Model $model
	 */
	public function update(One_Model $model)
	{
//		$scheme = One_Repository::getScheme($model->getSchemeName());
//		$db = $this->db($scheme);
//
//		// @TODO update query should be done in the renderer
//		// determine table to insert into
//		$table = $this->getTable($scheme);
//		$sql = "UPDATE " . $table . " SET ";
//
//		//create clauses
//		$modified = $model->getModified();
//		$modifiedRelations = $model->getModifiedRelations();
//
//		$data = new stdClass();
//
//		foreach($scheme->get('attributes') as $attName => $at)
//		{
//			if(isset($modified[$attName])) {
//				$data->$attName = $modified[$attName];
//			}
//		}
//
//		// Check for relationships (FK values), cannot use attribute but must use column or link name
//		// JL 06JAN2008 - Three possible situations, two are needed:
//		// * ManyToOne
//		//		* The FK is a field in the model's record
//		//		* We need to set this field BEFORE saving the record
//		// * ManyToMany
//		//		* Relations are in a separate table
//		//		* We should set them AFTER saving the record (especially when inserting a new record)
//		// * OneToMany
//		// 		* Not needed for now - When editing, we'll usually edit the child and select it's parent
//
//		$mtos = array();
//		foreach($scheme->getLinks() as $link)
//		{
//			if($link->getLinkType() == "manytoone")
//			{
//				$fk = $link->fk();
//				if(isset($modified[$fk]))
//				{
//					$data->$fk = $modified[$fk];
//					$mtos[$fk] = $modified[$fk];
//				}
//			}
//		}
//
//		$clauses = array();
//		foreach($scheme->get('attributes') as $attName => $at)
//		{
//			if(isset($modified[$attName]))
//			{
//				$clauses[] = One_Query_Renderer::getInstance('mysql')->formatAttribute($at, $modified[$attName]);
//			}
//		}
//
//		if(count($mtos) > 0)
//		{
//			foreach($mtos as $k => $v)
//			{
//				$clauses[] = '`'.$k.'` = "'.mysql_real_escape_string($v).'"';
//			}
//		}
//
//		$sql .= implode(', ', $clauses);
//
//		$idAttr = $scheme->getIdentityAttribute();
//		$id = $idAttr->getName();
//		$value = $model->$id;
//		$value = $idAttr->toString($value);
//		$data->$id = $value;
//
//		$sql .= " WHERE ".$id.' = '.$data->$id;
//
//		// Don't perform the update if no updates have to be performed
//		if(count($clauses) > 0)
//		{
//			if(mysql_query($sql, $db) === false) {
//				throw new One_Exception('Update failed: '.mysql_error($db));
//			}
//		}
//
//		// Handle ManyToMany relations
//		foreach($scheme->getLinks() as $link)
//		{
//			if($link->getLinkType() == "manytomany")
//			{
//				if(isset($modifiedRelations[$link->getName()])) {
//					$model->saveRelated($link);
//				}
//			}
//		}
	}

	/**
	 * delete a single instance
	 *
	 * @param One_Model $model
	 */
	public function delete(One_Model $model)
	{
//		$scheme = One_Repository::getScheme($model->getSchemeName());
//		$db = $this->db($scheme);
//
//		// determine table to insert into
//		$table = $this->getTable($scheme);
//		$sql = 'DELETE FROM '.$table;
//
//		$idAttr = $scheme->getIdentityAttribute();
//		$id = $idAttr->getName();
//		$value = $model->$id;
//		$value = $idAttr->toString(mysql_real_escape_string($value, $db));
//
//		$sql .= ' WHERE `'.$id.'` = '.$value;
//
//		// execute query
//		if(!mysql_query( $sql, $db )) {
//			throw new One_Exception(mysql_error().$sql);
//		}
	}

	/**
	 * Get the MongoCollection used for the scheme
	 *
	 * @param One_Scheme $scheme
	 * @return string
	 */
	public function getDatasource(One_Scheme $scheme)
	{
		$source = $scheme->getView();
		if( is_null( $source ) )
		{
			$resources = $scheme->getResources();
			$source = $resources['collection'];
		}

		return $source;
	}

	/**
	 * Get the table used for the scheme
	 * @param One_Scheme $scheme
	 * @return string Table name used for the scheme
	 */
	protected function getCollection(One_Scheme $scheme)
	{
		$resources = $scheme->getResources();
		if(isset($resources['collection'])) {
			return $resources['collection'];
		}
		else {
			throw new One_Exception('A collection must be defined for the scheme "'.$scheme->getName().'"');
		}
	}

	/**
	 * Function to set the proper encoding
	 * @param One_Scheme $scheme
	 * @param string $encoding (utf8, iso-8859-1, ...)
	 */
	public function setEncoding(One_Scheme $scheme, $encoding)
	{
		return true;
	}
}
