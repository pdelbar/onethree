<?php

  /**
   * Keeps track of One_Model instances and their id, to prevent loading the same
   * object twice.
   *
   * *** this should be triggered by a cache behavior, not generalized
   *
   * ONEDISCLAIMER
   */
  class One_Model_IdentityMap
  {

    private static $registry;

    /**
     * Add a model to the map
     *
     * @param One_Model $model
     */
    public static function add(One_Model $model)
    {
      $scheme        = $model->getSchemeName();
      $identity_name = $model->getIdentityName();
      $identity      = $model->$identity_name;
      if (!$identity) {
        throw new One_Exception("The model has no identity yet and cannot be stored in the identity map.");
      }
      $key = "model.$scheme.$identity";
      if (!self::$registry) self::$registry = new One_Registry();
      self::$registry->set($key, $model);
    }

    /**
     * Find a model for a scheme and id combo, false on fail
     *
     * @param string $schemeName Scheme name
     * @param mixed $identity Identity
     * @return One_Model
     */
    public static function find($schemeName, $identity)
    {
      $key = "model.$schemeName.$identity";
      if (!self::$registry) {
        self::$registry = new One_Registry();
        return false;
      }
      return self::$registry->get($key);
    }

  }