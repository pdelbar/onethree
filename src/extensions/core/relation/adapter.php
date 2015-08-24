<?php

  /**
   * Class that holds the data of a specific relation
   *
   * ONEDISCLAIMER
   **/
  class One_Relation_Adapter
  {
    /**
     * @var string Name of the link
     */
    protected $name;

    /**
     * @var mixed ID of the link
     */
    protected $linkId;

    /**
     * @var array Target data
     */
    protected $target;

    /**
     * @var One_Relation_Adapter_Interface Instance of the proper linktype
     */
    protected $adapterType;

    /**
     * @var array All data in the link
     */
    protected $__data;

    /**
     * @var array Meta-data of the link
     */
    public $meta;

    /**
     * Class constructor
     *
     * @param array $linkMeta
     */
    public function __construct($linkMeta)
    {
      // decode type attributes into parameters
      $this->meta = array();
      foreach ($linkMeta as $k => $v) {
        $this->meta[$k] = (string)$v;
      }

      // retrieve link type
      $this->adapterType = One_Repository::getLinkType($this->meta['style']);

      // remember link name
      $this->name   = $this->meta['name'];
      $this->linkId = $this->meta['id'];

      $this->target = $this->meta['target'];

      $this->__data = null;
    }

    /**
     * Returns the name of the link
     *
     * @return string
     */
    public function getName()
    {
      return $this->name;
    }

    /**
     * Returns the id of the link
     *
     * @return mixed
     */
    public function getLinkId()
    {
      return $this->linkId;
    }

    /**
     * Returns the target of the link
     *
     * @return array
     */
    public function getTarget()
    {
      return $this->target;
    }

    /**
     * Returns the linkType of the link
     *
     * @return One_Relation_Adapter
     */
    public function getAdapterType()
    {
      return $this->adapterType;
    }

    /**
     * Add a relation to the link
     *
     * @param mixed $model Can be One_Model or array of One_Models
     */
    public function add($model)
    {
      $connectionName = $this->meta['connection'];
      $connection     = One_Repository::getConnection($connectionName);
      $store          = $connection->getStore();
      $store->addRelations($model, $this);
    }

    /**
     * Save a relation to the link
     *
     * @param mixed $model Can be One_Model or array of One_Models
     */
    public function save($model)
    {
      $connectionName = $this->meta['connection'];
      $connection     = One_Repository::getConnection($connectionName);
      $store          = $connection->getStore();
      $store->saveRelations($model, $this);
    }

    /**
     * Remove a relation from the link
     *
     * @param mixed $model Can be One_Model or array of One_Models
     */
    public function remove($model)
    {
      $connectionName = $this->meta['connection'];
      $connection     = One_Repository::getConnection($connectionName);
      $store          = $connection->getStore();
      $store->deleteRelations($model, $this);
    }

    /**
     * Get all related objects from the model
     *
     * @param One_Model $model
     * @param array $options @see One_Query::setOptions()
     * @return One_Model|array Can be One_Model or array of One_Models
     */
    public function getRelated(One_Model $model, array $options = array())
    {
      return $this->adapterType->getRelated($this, $model, $options);
    }

    /**
     * Overrides default toString method
     *
     * @return string
     */
    public function __toString()
    {
      return " link" . "(" . $this->adapterType . "):" . $this->meta['target'];
    }

    /**
     * Returns the foreign key for the chosen side
     *
     * @param string $side Should be either local or remote
     * @return string Name of the foreign key
     */
    public function getForeignKey($side = 'local')
    {
      $fk = NULL;
      if (isset($this->meta['fk:' . $side])) {
        $fk = $this->meta['fk:' . $side];
      }
      return $fk;
    }

    /**
     * Returns the foreign key
     *
     * @param string $side Should be either local or remote
     * @return string Name of the foreign key
     * @deprecated Use One_Relation_Adapter::getForeignKey() instead
     */
    public function fk($side = 'local')
    {
      return $this->getForeignKey($side);
    }


    /**
     * @deprecated remove this method
     */
    public function __call($method, $args)
    {
      if (method_exists($this, 'get' . ucfirst($method))) {
        throw new One_Exception_Deprecated('Use get' . ucfirst($method) . ' instead');
      }
      throw new One_Exception('method "' . $method . '" not found');
    }
  }
