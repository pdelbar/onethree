<?php

  /**
   * The One_Model class provides an abstraction of an object with
   *  - attributes
   *  - sets of related objects
   * and supporting a number of standard operations.
   *
   * ONEDISCLAIMER
   **/
  class One_Model implements ArrayAccess
  {
    /**
     * @var One_Scheme Contains the scheme this model is an instance of
     */
    private $_scheme;

    /**
     * @var array contains the values of the attributes for this model
     */
    private $_data = array();

    /**
     * @var array related objects
     */
    private $_related = array();

    /**
     * @var array keep list of modified attributes
     */
    private $_modified = array();

    /**
     * @var array keep list of modified attributes
     */
    private $_modifiedRelations = array();

    /**
     * @var array keep list of added relations
     */
    private $_addedRelations = array();

    /**
     * @var array keep list of deleted relations
     */
    private $_deletedRelations = array();

    /**
     * Class constructor
     *
     * @param One_Scheme $scheme
     */
    public function __construct(One_Scheme $scheme = null)
    {
      if (!is_null($scheme)) {
        $this->_scheme = $scheme;
      }
    }

    public function __call($method, $args)
    {
      $behaviors = $this->getScheme()->get('behaviors');
      if ($behaviors) {
        foreach ($behaviors as $behavior) {
          $methodname = 'onModel' . ucfirst($method);
          if (method_exists($behavior, $methodname)) {
            return $behavior->$methodname($this, $args);
          }
        }
      }

      throw new One_Exception('One_Model does not know how to execute method "' . $method . '"');
    }

    /**
     * Get the scheme name of this model
     *
     * @return string
     * @throws One_Exception
     */
    public function getSchemeName()
    {
      if ($scheme = $this->getScheme()) {
        return $scheme->getName();
      }
      throw new One_Exception('The model has no scheme');
    }

    /**
     * Get the name of the identity attribute
     *
     * @return string
     */
    public function getIdentityName()
    {
      return $this->getScheme()->getIdentityAttribute()->getName();
    }

    /**
     * Get the Model's One_Scheme
     *
     * @return One_Scheme
     */
    public function getScheme()
    {
      return $this->_scheme;
    }

    /**
     * Set the scheme for the model
     *
     * @param One_Scheme $scheme
     * @return One_Model
     */
    public function setScheme(One_Scheme $scheme)
    {
      $this->_scheme = $scheme;
      return $this;
    }

    /**
     * Resets all arrays that keeps track of modified, added or deleted attributes/relations
     */
    public function resetModified()
    {
      $this->_modified          = array();
      $this->_addedRelations    = array();
      $this->_modifiedRelations = array();
      $this->_deletedRelations  = array();
    }

    /**
     * Returns whether this model has been modified or not
     *
     * @return boolean
     */
    public function isModified()
    {
      return (count($this->_modified) + count($this->_modifiedRelations) + count($this->_addedRelations)) > 0;
    }

    /**
     * Returns the modified attributes
     *
     * @return array
     */
    public function getModified()
    {
      return $this->_modified;
    }

    /**
     * Returns all added and modified relations
     *
     * @return array
     * @deprecated
     */
    public function deltaRelations()
    {
      throw new One_Exception_Deprecated('Use getDeltaRelations instead');
    }

    /**
     * Returns all added and modified relations
     *
     * @return array
     */
    public function getDeltaRelations()
    {
      $a = array_merge($this->_addedRelations, $this->_modifiedRelations);
      return $a;
    }

    /**
     * Returns all added relations
     *
     * @deprecated
     * @return array
     */
    public function addedRelations()
    {
      throw new One_Exception_Deprecated('Use getAddedRelations instead');
    }

    /**
     * Returns all added relations
     *
     * @return array
     */
    public function getAddedRelations()
    {
      return $this->_addedRelations;
    }


    /**
     * Returns all modified relations
     *
     * @deprecated
     * @return array
     */
    public function modifiedRelations()
    {
      throw new One_Exception_Deprecated('Use getModifiedRelations instead');
    }

    /**
     * Returns all modified relations
     *
     * @return array
     */
    public function getModifiedRelations()
    {
      return $this->_modifiedRelations;
    }

    /**
     * Returns all deleted relations
     *
     * @deprecated
     * @return array
     */
    public function deletedRelations()
    {
      throw new One_Exception_Deprecated('Use getDeletedRelations instead');
    }

    /**
     * Returns all deleted relations
     *
     * @return array
     */
    public function getDeletedRelations()
    {
      return $this->_deletedRelations;
    }

    /**
     * Sets a variable in the model and add it to the modified attributes
     *
     * @param string $attributeName
     * @param mixed $value
     */
    public function __set($attributeName, $value)
    {
      $this->_data[$attributeName]     = $value;
      $this->_modified[$attributeName] = $value;
    }

    /**
     * Returns the attribute that the attributeName or alias belongs to
     *
     * @param string $key Attribute Name Or Alias
     * @return string
     */
    private function mapAttribute($key)
    {
      $scheme = $this->getScheme();
      if ($scheme instanceof One_Scheme && !is_null($attribute = $scheme->getAttribute($key))) {
        return $attribute->getName();
      }
      return $key;
    }

    /**
     * Returns the requested attribute's value or the relation
     *
     * @param string $attributeNameOrAlias Name of an attribute or relation
     * @return mixed
     */
    public function __get($attributeNameOrAlias)
    {
      $attributeName = $this->mapAttribute($attributeNameOrAlias);

      // check attributes first
      if (array_key_exists($attributeName, $this->_data)) {
        return $this->_data[$attributeName];
      }

      // check links
      if (array_key_exists($attributeName, $this->getScheme()->getRoles())) {
        return $this->getRelated($attributeName);
      }

      return null;
    }

    /**
     * Checks whether the given attribute is set
     *
     * @param string $attributeNameOrAlias
     * @return boolean
     */
    public function __isset($attributeNameOrAlias)
    {
      $attributeName = $this->mapAttribute($attributeNameOrAlias);
      return isset($this->_data[$attributeName]);
    }

    /**
     * Unsets the given attribute
     *
     * @param string $attributeNameOrAlias
     */
    public function __unset($attributeNameOrAlias)
    {
      $attributeName = $this->mapAttribute($attributeNameOrAlias);
      unset($this->_data[$attributeName]);
      unset($this->_modified[$attributeName]);
    }

    /**
     * Sets all data in the model from an array
     *
     * @param array $data
     * @return One_Model
     */
    public function fromArray($data)
    {
      $this->_data = $data;
      return $this;
    }

    /**
     * Get all the raw data of the model
     *
     * @return array
     */
    public function toArray()
    {
      return $this->_data;
    }

    /**
     * Sets all data in the model from an array
     *
     * @deprecated
     * @param array $row
     */
    public function absorbData($row)
    {
      throw new One_Exception_Deprecated("Use fromArray instead");
    }

    //-------------------------------------------------------------------------------
    //	HOOKS
    //-------------------------------------------------------------------------------

    /**
     * Perform afterLoad behaviors
     */
    public function afterLoad()
    {
      $behaviors = $this->getScheme()->get('behaviors');
      if ($behaviors) {
        foreach ($behaviors as $behavior) {
          $behavior->afterLoadModel($this->getScheme(), $this);
        }
      }

      $this->resetModified();
    }

    /**
     * Perform beforeUpdate behaviors
     */
    protected function beforeUpdate()
    {
      $behaviors = $this->getScheme()->get('behaviors');
      if ($behaviors) {
        foreach ($behaviors as $behavior) {
          $behavior->beforeUpdateModel($this->getScheme(), $this);
        }
      }
    }

    /**
     * Perform afterUpdate behaviors
     */
    protected function afterUpdate()
    {
      $behaviors = $this->getScheme()->get('behaviors');
      if ($behaviors) {
        foreach ($behaviors as $behavior) {
          $behavior->afterUpdateModel($this->getScheme(), $this);
        }
      }
    }

    /**
     * Perform beforeInsert behaviors
     */
    protected function beforeInsert()
    {
      $behaviors = $this->getScheme()->get('behaviors');
      if ($behaviors) {
        foreach ($behaviors as $behavior) {
          $behavior->beforeInsertModel($this->getScheme(), $this);
        }
      }
    }

    /**
     * Perform afterInsert behaviors
     */
    protected function afterInsert()
    {
      $behaviors = $this->getScheme()->get('behaviors');
      if ($behaviors) {
        foreach ($behaviors as $behavior) {
          $behavior->afterInsertModel($this->getScheme(), $this);
        }
      }
    }

    /**
     * Perform beforeDelete behaviors
     */
    protected function beforeDelete()
    {
      $behaviors = $this->getScheme()->get('behaviors');
      if ($behaviors) {
        foreach ($behaviors as $behavior) {
          $behavior->beforeDeleteModel($this->getScheme(), $this);
        }
      }
    }

    /**
     * Perform afterDelete behaviors
     */
    protected function afterDelete()
    {
      $behaviors = $this->getScheme()->get('behaviors');
      if ($behaviors) {
        foreach ($behaviors as $behavior) {
          $behavior->afterDeleteModel($this->getScheme(), $this);
        }
      }
    }

    //-------------------------------------------------------------------------------
    //	related objects implementation
    //-------------------------------------------------------------------------------

    /**
     * Get a related object by name of the link
     *
     * @param string $roleName Role name
     * @param array $options @see One_Query::setOptions()
     * @return One_Model
     */
    public function getRelated($roleName, $options = array())
    {
      $related = isset($this->_related[$roleName]) ? $this->_related[$roleName] : NULL;

      // if nothing was returned, go get the data
      if (is_null($related)) {
        $link    = $this->getScheme()->getLink($roleName);
        $related = $link->getRelated($this, $options);
      }

      return $related;
    }


    public function countRelated($roleName, $options = array())
    {
      $related  = isset($this->_related[$roleName]) ? $this->_related[$roleName] : NULL;
      $nrelated = 0;

      // if nothing was returned, go get the data
      if (is_null($related)) {
        $link     = $this->getScheme()->getLink($roleName);
        $nrelated = $link->countRelated($this, $options);
      }

      return $nrelated;
    }

    /**
     * Sets a relationship
     *
     * @param string $roleName
     * @param mixed $value
     */
    public function setRelated($roleName, $value)
    {
      $link     = $this->getScheme()->getLink($roleName);
      $linktype = $link->getAdapterType();

      switch ($linktype) {
        case 'manytoone':
          // find which FK field is used for the related object and change it's value in this model
          $fk        = $link->fk();
          $this->$fk = $value;
          break;
        case 'manytomany':
          $this->_modifiedRelations[$roleName] = $value; // @todo - $this->_related should be updated as well!!
          break;
      }
    }

    /**
     * Add a single relationship
     *
     * @param string $roleName
     * @param mixed $value
     */
    public function addRelated($roleName, $value = NULL)
    {
      $link     = $this->getScheme()->getLink($roleName);
      $linktype = $link->getAdapterType();

      switch ($linktype) {
        case 'manytoone':
          // find which FK field is used for the related object and change it's value in this model
          $fk        = $link->fk();
          $this->$fk = $value;
          break;
        case 'manytomany':
          $this->_addedRelations[$roleName][] = $value; // @todo - $this->_related should be updated as well!!
          break;
      }
    }

    /**
     * delete a single relationship
     *
     * @param string $roleName
     * @param mixed $value
     */
    public function deleteRelated($roleName, $value = NULL)
    {
      $link     = $this->getScheme()->getLink($roleName);
      $linktype = $link->getAdapterType();

      switch ($linktype) {
        case 'manytoone':
          // find which FK field is used for the related object and change it's value in this model
          $fk        = $link->fk();
          $this->$fk = NULL;
          break;
        case 'manytomany':
          $this->_deletedRelations[$roleName][] = $value; // @todo - $this->_related should be updated as well!!
          break;
      }
    }

    /**
     * Saves all "many-to-many" and "one-to-many"-relations
     *
     * @param One_Relation_Adapter $link
     * @return void
     */
    public function saveRelated(One_Relation_Adapter $link)
    {
      if (array_key_exists($link->getName(), $this->_modifiedRelations) && ($link->getAdapterType() <> "manytoone")) {
        $link->save($this);
      }

      if (array_key_exists($link->getName(), $this->_addedRelations) && ($link->getAdapterType() <> "manytoone")) {
        $link->add($this);
      }

      if (array_key_exists($link->getName(), $this->_deletedRelations) && ($link->getAdapterType() <> "manytoone")) {
        $link->remove($this);
      }
    }

    /**
     * Is the given id related
     *
     * @param string $roleName
     * @param mixed $id
     * @param array $options
     */
    public function isRelated($roleName, $id, $options = array())
    {
      $related = $this->getRelated($roleName, $options);

      foreach ($related as $relate) {
        if ($relate->id == $id) {
          return true;
        }
      }

      return false;
    }

    /**
     * Perform an update on the model
     */
    public function update()
    {
      $this->beforeUpdate();
      $this->getScheme()->update($this);
      $this->afterUpdate();
    }

    /**
     * Perform an insert on the model
     */
    public function insert()
    {
      $this->beforeInsert();
      $this->getScheme()->insert($this);
      $this->afterInsert();
    }

    /**
     * Perform a delete on the model
     */
    public function delete()
    {
      $this->beforeDelete();
      $this->getScheme()->delete($this);
      $this->afterDelete();
    }


    //-------------------------------------------------------------------------------
    //	ArrayAccess interface implementation
    //-------------------------------------------------------------------------------
    /**
     * Checks whether the attribute is set
     */
    public function offsetExists($attributeNameOrAlias)
    {
      $attributeName = $this->mapAttribute($attributeNameOrAlias);
      return $this->__isset($attributeName);
    }

    /**
     * Gets the attribute
     */
    public function offsetGet($attributeNameOrAlias)
    {
      $attributeName = $this->mapAttribute($attributeNameOrAlias);
      return $this->__get($attributeName);
    }

    /**
     * Sets the attribute
     */
    public function offsetSet($attributeNameOrAlias, $value)
    {
      $attributeName = $this->mapAttribute($attributeNameOrAlias);
      $this->__set($attributeName, $value);
      $this->_modified[$attributeName] = $value;
    }

    /**
     * Unsets the attribute
     */
    public function offsetUnset($attributeNameOrAlias)
    {
      $attributeName = $this->mapAttribute($attributeNameOrAlias);
      $this->__unset($attributeName);
      $this->_modified[$attributeName] = null;
    }
  }
