<?php

  /**
   *  The oneToMany link type is used to link this object to many other
   *  objects. To do that, these objects must keep a foreign key value to
   *  this object.
   *
   *  The foreign key included consists of all identity fields of the target.
   *
   * ONEDISCLAIMER
   **/
  class One_Relation_Adapter_Onetomany extends One_Relation_Adapter_Abstract
  {
    /**
     * Returns the name of this linktype
     *
     * @return string
     */
    public function getName()
    {
      return 'onetomany';
    }

    /**
     * Return all related items
     *
     * @return array
     */
    public function getRelated(One_Relation_Adapter $link, One_Model $model, array $options = array())
    {
      $linkName = $link->getName();

      // identify the target scheme
      $source = One_Repository::getScheme($model->getSchemeName());
      $target = One_Repository::getScheme($link->getTarget());

      $backlinks = $target->getLinks();
      $backlink  = $backlinks[$link->getLinkId()];
      if (!$backlink) {
        throw new One_Exception('The role "' . $roleName . '" does not exist for this model');
      }

      $at     = $source->getIdentityAttribute()->getName();
      $column = $this->remoteFK($link, $source, $backlink);

      // bind the data using the data
      $localValue = $model->$at;

      // create query and execute
      $q = One_Repository::selectQuery($link->getTarget());
      $q->setOptions($options);
      if (isset($link->meta['hybrid'])) {
        $var = $link->meta['hybrid'] . '_scheme';
        $q->where($var, 'eq', $source->getName());
      }
      $q->where($column, 'eq', $localValue);
      return $q->execute();
    }

    public function countRelated(One_Relation_Adapter $link, One_Model $model, array $options = array())
    {
      $linkName = $link->getName();

      // identify the target scheme
      $source = One_Repository::getScheme($model->getSchemeName());
      $target = One_Repository::getScheme($link->getTarget());

      $backlinks = $target->getLinks();
      $backlink  = $backlinks[$link->getLinkId()];
      if (!$backlink) {
        throw new One_Exception('The role "' . $roleName . '" does not exist for this model');
      }

      $at     = $source->getIdentityAttribute()->getName();
      $column = $this->remoteFK($link, $source, $backlink);

      // bind the data using the data
      $localValue = $model->$at;

      // create query and execute
      $q = One_Repository::selectQuery($link->getTarget());
      $q->setOptions($options);
      if (isset($link->meta['hybrid'])) {
        $var = $link->meta['hybrid'] . '_scheme';
        $q->where($var, 'eq', $source->getName());
      }
      $q->where($column, 'eq', $localValue);
      return $q->getCount();
    }

    /**
     * Overload toString function
     *
     * @param mixed
     * @return string
     */
    public function toString($value)
    {
      return $value;
    }
  }
