<?php

  /**
   * The One_Scheme class contains all meta-information about One_Models.
   *
   * As One_Scheme keeps quite a lot of information about its structure, we'll make it a subclass
   * of One_Registry.
   *
   * ONEDISCLAIMER
   *
   * *** TODO: replace local variables by the hash entries
   **/
  class One_Scheme extends One_Registry
  {


    /**
     * @var array Added for more flexible options management
     */
    public $information = array();

    /**
     * @var string $_name The name of the scheme
     */
    protected $_name;

    /**
     * @var array $_resource Scheme-store information to know the specific location of data (table, module)
     * @TODO I think this should be moved to an instance of a store? Yes it should
     */
    protected $_resource = array();

    /**
     * @var array $_links An array of One_Relation_Adapter instances
     */
    protected $_links = array();

    /**
     * @var array $_linksById An array of One_Relation_Adapter instances
     */
    protected $_linksById = array();

    /**
     * @var array $_foreignKeys An array of foreign keys being used in this scheme
     */
    protected $_foreignKeys = array();

    /**
     * @var One_Store_Connection_Interface A reference to the One_Store in which the model sits
     */
    protected $_connection;

    /**
     * @var array Array of permissions for this scheme
     */
    protected $_rules = array();


    /**
     * Class constructor
     *
     * @param string $schemeName
     */
    public function __construct($schemeName)
    {
      parent::__construct();
      $this->set('info.name', $schemeName);
      $this->set('info.title', $schemeName);

      $this->_name = $schemeName;
    }

    /**
     * Returns the name of the scheme
     *
     * @return string
     */
    public function getName()
    {
      return $this->_name;
    }

    /**
     * Gets the connection for the scheme
     *
     * @return One_Store_Connection_Interface
     */
    public function getConnection()
    {
      return $this->_connection;
    }

    /**
     * Sets the storeconnection of the scheme
     *
     * @param One_Store_Connection_Interface $connection
     */
    public function setConnection(One_Store_Connection_Interface $connection)
    {
      $this->_connection = $connection;
    }

    /**
     * Gets the store for the scheme
     *
     * @return One_Store
     */
    public function getStore()
    {
      $store = $this->_connection->getStore();
      return $store;
    }

    //-------------------------------------------------------------------------------
    // ATTRIBUTES
    //-------------------------------------------------------------------------------


    /**
     * Add an attribute to the scheme
     *
     * @param One_Scheme_Attribute $attribute
     */
    public function addAttribute(One_Scheme_Attribute $attribute)
    {
      $this->set('attributes.'.$attribute->getName(), $attribute);
    }

    /**
     * Retrieve attribute based on name OR alias
     *
     * @param string $name
     * @return One_Scheme_Attribute
     */
    public function getAttribute($name)
    {
      return $this->get('attributes.'.$name);
    }

    /**
     * Retrieves whether the scheme has a certain attribute
     *
     * @param string $name
     * @return boolean
     */
    public function hasAttribute($name)
    {
      return null !== $this->getAttribute($name);
    }

    /**
     * Get all links from the scheme
     *
     * @return array
     */
    public function getLinks()
    {
      return $this->_linksById;
    }

    /**
     * Get a specific link
     *
     * @param string $roleName
     * @return One_Relation_Adapter
     */
    public function getLink($roleName)
    {
      return (isset($this->_links[$roleName])) ? $this->_links[$roleName] : NULL;
    }

    /**
     * Add a link to the scheme
     *
     * @param One_Relation_Adapter $link
     */
    public function addLink(One_Relation_Adapter $link)
    {
      $this->_links[$link->getName()]       = $link;
      $this->_linksById[$link->getLinkId()] = $link;

      if ($link->getAdapterType() instanceof One_Relation_Adapter_Manytoone) {
        if (null !== $link->getForeignKey()) {
          $this->_foreignKeys[] = $link->getForeignKey();
        }
      }
    }

    /**
     * Return the list of all local foreign keys that are present in the One_Scheme
     *
     * @return array List of local foreign keys
     */
    public function getForeignKeys()
    {
      return $this->_foreignKeys;
    }

    /**
     * Return all roles in the scheme
     *
     * @return array
     */
    public function getRoles()
    {
      return $this->_links;
    }


    /**
     * Check whether or not the scheme has a specified behavior
     *
     * @param string $behaviorName
     * @return boolean
     */
    public function hasBehavior($behaviorName)
    {
      foreach ($this->get('behaviors') as $behavior) {
        $possibleNames = array(
          'One_Behavior_' . ucfirst($behaviorName),
          'One_Behavior_' . ucfirst($this->getName()) . '_' . ucfirst($behaviorName)
        );
        if (in_array(get_class($behavior), $possibleNames)) {
          return true;
        }
      }

      return false;
    }

    /**
     * Get all rules in the scheme for a specific task
     *
     * @todo rename to getRules
     * @param string $taskName
     * @return array
     */
    public function getRules($taskName)
    {
      return (isset($this->_rules[$taskName])) ? $this->_rules[$taskName] : NULL;
    }

    /**
     * Add a rule to the scheme for a specific task
     *
     * @param string $taskName
     * @param One_Permission_Rule $rule
     */
    public function addRule($taskName, One_Permission_Rule $rule)
    {
      if (!isset($this->_rules[$taskName])) {
        $this->_rules[$taskName] = array();
      }

      $this->_rules[$taskName][] = $rule;
    }


    /**
     * Get the identity attribute of this scheme
     *
     * @return One_Scheme_Attribute
     */
    public function getIdentityAttribute()
    {
      $atts = array();

      foreach ($this->get('attributes') as $attr) {
        if ($attr->isIdentity()) {
          return $attr;
        }
      }
      return NULL;
    }

    /**
     * Get the keyvalue attribute of this scheme.
     * If no keyvalue-attribute is indicated, return the identity-attribute.
     *
     * @return One_Scheme_Attribute
     */
    public function getKeyvalueAttribute()
    {
      $atts = array();

      foreach ($this->get('attributes') as $attr) {
        if ($attr->isKeyValue()) {
          return $attr;
        }
      }

      return $this->getIdentityAttribute();
    }


    /**
     * Update the model
     *
     * @param One_Model $model
     * @return mixed
     * @TODO Should CRUD be here?
     */
    public function update(One_Model $model)
    {
      $store = $this->getStore();
      return $store->update($model);
    }

    /**
     * Insert the model
     *
     * @param One_Model $model
     * @return mixed
     * @TODO Should CRUD be here?
     */
    public function insert(One_Model $model)
    {
      $store = $this->getStore();
      return $store->insert($model);
    }

    /**
     * Delete the model
     *
     * @param One_Model $model
     * @return mixed
     * @TODO Should CRUD be here?
     */
    public function delete(One_Model $model)
    {
      $store = $this->getStore();
      return $store->delete($model);
    }

    /**
     * Set the resources of the scheme
     *
     * @param array $options
     */
    public function setResources(array $options)
    {
      $this->_resource = $options;
    }

    /**
     * Get the resources of the scheme
     *
     * @return array
     */
    public function getResources()
    {
      return $this->_resource;
    }




  }
