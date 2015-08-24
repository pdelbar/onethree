<?php

  /**
   * One_Relation is the meta-definition of a relation between models
   *
   * ONEDISCLAIMER
   **/
  class One_Relation
  {
    /**
     * @var string Name of the relation
     */
    public $name;

    /**
     * @var array Meta-information of the relation
     */
    public $meta = array();

    /**
     * @var array Roles contained in the relation
     */
    public $roles = array();

    /**
     * Class constructor
     *
     * @param string $relationName
     */
    public function __construct($relationName)
    {
      $this->name = $relationName;
    }

    /**
     * Set the meta-data of the relation
     *
     * @param DOMDocument $meta
     */
    public function setMeta(array $meta)
    {
      $this->meta = $meta;
    }

    /**
     * Set the roles of the relation
     *
     * @param array $roles
     */
    public function setRoles(array $roles)
    {
      $this->roles = $roles;
    }

    /**
     * Instantiate the links for this scheme under this relationship. Note that we're building the One_Relation_Adapter
     * objects on the side of this scheme only.
     *
     * @param One_Scheme $scheme
     */
    public function createLinks(One_Scheme $scheme)
    {
      $params       = array();
      $params['id'] = $this->name;

      // decode relationship
      $keys = array_keys($this->roles);
      $one  = $this->roles[$keys[0]];
      $two  = $this->roles[$keys[1]];

      if ($one->schemeName == $two->schemeName) {
        // example: parent-child relationship inside a single scheme
        foreach ($this->roles as $role) {
          $this->createLink($scheme, $one, $two);
          $this->createLink($scheme, $two, $one);
        }
      }
      else {
        // different schemes on both sides : check which side applies
        if ($one->schemeName == $scheme->getName()) {
          $this->createLink($scheme, $two, $one);
        }
        else {
          $this->createLink($scheme, $one, $two);
        }
      }

    }

    /**
     * Creates a link from one scheme to another
     *
     * @param One_Scheme $scheme
     * @param One_Relation_Role $role
     * @param One_Relation_Role $otherRole
     */
    public function createLink(One_Scheme $scheme, One_Relation_Role $targetRole, One_Relation_Role $schemeRole)
    {
      $params           = array();
      $params['id']     = $this->name;
      $params['name']   = $targetRole->name;
      $params['target'] = $targetRole->schemeName;
      $params['style']  = $schemeRole->cardinality . 'To' . ucfirst($targetRole->cardinality);

      // insert fk information
      if ($schemeRole->cardinality == "many") {
        $params['fk:local'] = $schemeRole->meta['fk'];
      }
      if ($targetRole->cardinality == "many") {
        $params['fk:remote'] = $targetRole->meta['fk'];
      }

      //PD 22OCT08: remember that this is a hybrid target
      if ($schemeRole->schemeName == "*") {
        $params['hybrid'] = $schemeRole->name;
      }

      $meta = array_merge($this->meta, $params);
      $lnk  = new One_Relation_Adapter($meta);

      $scheme->addLink($lnk);
    }

    /**
     *  Get a specific role in the relation
     *
     * @param string $roleName
     * @return One_Relation_Role
     */
    public function getRole($roleName)
    {
      if (array_key_exists($roleName, $this->roles)) {
        return $this->roles[$roleName];
      }
      else {
        throw new One_Exception('The role ' . $roleName . 'does not exist for this relation');
      }
    }

    /**
     *  Get all roles in the relation
     *
     * @return array List of the One_Relation_Role 's in the relation
     */
    public function getRoles()
    {
      return $this->roles;
    }
  }
