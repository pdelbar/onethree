<?php

  /**
   * Factory class to fetch different kinds of object one|content
   *
   * @TODO review this file and clean up historical code/comments
   * @subpackage Repository
   * ONEDISCLAIMER
   **/
  class One_Repository
  {
    // @TODO implement better caching


    /**
     * @staticvar array cache that contains used schemes
     */
    protected static $schemeCache = array();


    /**
     * Return the named scheme, loading it if necessary
     *
     * @meta
     * @param String $schemeName
     * @return One_Scheme
     */
    public static function getScheme($schemeName)
    {
      if (!array_key_exists($schemeName, self::$schemeCache)) {
        $scheme    = One_Scheme_Reader_Xml::load($schemeName);
        $behaviors = $scheme->get('behaviors');
        if ($behaviors) {
          foreach ($behaviors as $behavior) {
            $behavior->onLoadScheme($scheme);
          }
        }
        self::$schemeCache[$schemeName] = $scheme;
      }
      $scheme = self::$schemeCache[$schemeName];
      return $scheme;
    }

    /**
     * Return an array with available scheme names
     *
     * @meta
     * @return array
     */
    public static function getSchemeNames()
    {
      $pattern = One_Config::get('locator.root') . 'meta/scheme/';
      $places  = One_Locator::locateAllUsing('*.xml', $pattern);
      $schemes = array();
      foreach ($places as $place) {
        preg_match("|([a-zA-Z0-9_\-]*)\.xml$|", $place, $matches);
        $schemes[] = $matches[1];
      }
      return $schemes;
    }


    /**
     * @staticvar array cache that contains used stores
     */
    protected static $storeCache = array();

    /**
     * @staticvar array cache that contains used types
     */
    protected static $typeCache = array();

    /**
     * @staticvar array cache that contains used strategies
     */
    protected static $connectionCache = array();

    /**
     * @staticvar array cache that contains used behaviors
     */
    protected static $behaviorCache = array();

    /**
     * @staticvar array cache that contains used linktypes
     */
    protected static $linkTypeCache = array();

    /**
     * @staticvar array cache that contains used relations
     */
    protected static $relationCache = array();

    /**
     * @staticvar One_Session cache that contains the used session
     */
    protected static $session = NULL;

    /**
     * @staticvar array cache that contains used models
     */
    protected static $modelCache = array();


    /**
     * Return an instance of the appropriate One_Factory subclass
     *
     * @param string $schemeName
     * @return One_Factory
     */
    public static function getFactory($schemeName)
    {
      $scheme    = is_a($schemeName, 'One_Scheme') ? $schemeName : self::getScheme($schemeName);
      $resources = $scheme->getResources();

      if (isset($resources['factory']) && !is_null($resources['factory'])) {
        $factoryName = 'One_Factory_' . ucFirst(strtolower($resources['factory']));
      }
      else {
        $factoryName = 'One_Factory_' . ucFirst($scheme->getName());
      }

      if (class_exists($factoryName)) {
        $factory = new $factoryName($scheme);
      }
      else {
        $factory = new One_Factory($scheme);
      }

      return $factory;
    }

    /**
     * Get the specified Store
     *
     * @param string $storeName
     * @return One_Store
     */
    public static function getStore($storeName)
    {
      return One_Store::getInstance($storeName); // May never be cached to avoid writing in the wrong store
    }

    /**
     * Get the specified StoreConnection
     *
     * @param string $connectionName
     * @param string $type
     * @return One_Store_Connection_Interface
     */
    public static function getConnection($connectionName)
    {
      if (!array_key_exists($connectionName, self::$connectionCache)) {
        self::$connectionCache[$connectionName] = One_Store_Connection_Reader_Xml::load($connectionName);
      }

      return self::$connectionCache[$connectionName];
    }


    /**
     * Return an array with available filter names
     *
     * @meta
     * @return array
     */
    public static function getFilterNames($schemeName = NULL)
    {
      $pattern = One_Config::get('locator.root') . 'filter/' . ($schemeName !== null ? '{scheme/,}' : '');
      $places  = One_Locator::locateAllUsing('*.xml', $pattern);
      $filters = array();
      foreach ($places as $place) {
        preg_match("|([a-zA-Z0-9_\-]*)\.xml$|", $place, $matches);
        $filters[] = $matches[1];
      }
      sort($filters);
      return $filters;
    }

    /**
     * Get the specified relation
     *
     * @param string $relationName
     * @return mixed
     */
    public static function getRelation($relationName)
    {
      if (!array_key_exists($relationName, self::$relationCache)) {
        self::$relationCache[$relationName] = One_Relation_Reader_Xml::load($relationName);
      }

      return self::$relationCache[$relationName];
    }

    /**
     * Get the specified behaviour
     *
     * @param string $behaviorSpec
     * @return One_Behaviour_Scheme
     */
    public static function getBehavior($behaviorSpec, $schemeName = NULL)
    {
      $schemeBehaviorExists = false;

      $behavior = NULL;
      if (!is_null($schemeName)) {
        if (class_exists('One_Behavior_' . ucfirst($schemeName) . '_' . ucfirst($behaviorSpec))) {
          $schemeBehaviorExists = true;
          $behavior             = (isset(self::$behaviorCache[$schemeName . '_' . ucfirst($behaviorSpec)])) ? self::$behaviorCache[$schemeName . '_' . ucfirst($behaviorSpec)] : NULL;
        }
        else {
          if (isset(self::$behaviorCache[$behaviorSpec])) {
            $behavior = self::$behaviorCache[$behaviorSpec];
          }
        }
      }
      else {
        if (isset(self::$behaviorCache[$behaviorSpec])) {
          $behavior = self::$behaviorCache[$behaviorSpec];
        }
      }

      if (is_null($behavior)) {
        if ($schemeBehaviorExists) {
          $className = 'One_Behavior_' . ucfirst($schemeName) . '_' . ucfirst($behaviorSpec);
          $forCache  = $schemeName . ucfirst($behaviorSpec);
        }
        else {
          $className = 'One_Behavior_' . ucfirst($behaviorSpec);
          $forCache  = $behaviorSpec;
        }

        $behavior                       = new $className();
        self::$behaviorCache[$forCache] = $behavior;
      }

      return $behavior;
    }

    /**
     * Get the specified type
     *
     * @param string $typeName
     * @return One_Scheme_Attribute_Type
     */
    public static function getType($typeName)
    {
      if (!array_key_exists($typeName, self::$typeCache)) {
        $className                  = "One_Scheme_Attribute_Type_" . ucfirst($typeName);
        self::$typeCache[$typeName] = new $className();
      }

      return self::$typeCache[$typeName];
    }

    /**
     * Get the specified LinkType
     *
     * @param string $linkTypeName
     * @return One_Relation_Adapter
     */
    public static function getLinkType($linkTypeName)
    {
      if (!array_key_exists($linkTypeName, self::$linkTypeCache)) {
        $className                          = "One_Relation_Adapter_" . ucfirst(strtolower($linkTypeName));
        self::$linkTypeCache[$linkTypeName] = new $className();
      }

      return self::$linkTypeCache[$linkTypeName];
    }


    /**
     * Returns a DOM - object
     *
     * @static
     * @return One_Dom
     */
    public static function getDom($type = NULL)
    {
      $domClass = 'One_Dom_' . ucfirst(strtolower($type));
      if (!is_null($type) && class_exists($domClass)) {
        return new $domClass();
      }
      if (One_Config::get('dom.type')) {
        $domClass = 'One_Dom_' . One_Config::get('dom.type');
        return new $domClass();
      }
      return new One_Dom();
    }

    //-------------------------------------------------------------------------------
    // SELECTION OF MODELS
    //-------------------------------------------------------------------------------

    /**
     * Select a single instance of this scheme.
     *
     * @static
     * @param string $schemeName
     * @param mixed $identityValue
     * @return One_Model|array
     */
    public static function selectOne($schemeName, $identityValue)
    {
      $factory = self::getFactory($schemeName);
      return $factory->selectOne($identityValue);
    }

    /**
     * Return a One_Query for the given scheme to allow for the selection of multiple models
     *
     * @static
     * @param String $schemeName
     * @return One_Query
     */
    public static function selectQuery($schemeName)
    {
      $factory = self::getFactory($schemeName);
      return $factory->selectQuery();
    }

    /**
     * Return the number of One_Models of the specified kind
     *
     * @param string $schemeName
     * @return int
     */
    public static function selectCount($schemeName)
    {
      $factory = self::getFactory($schemeName);
      return $factory->selectCount();
    }

    /**
     * Gets the controller for the specified scheme
     *
     * @param mixed $schemeName Can be either the name of a scheme or an instance of One_Scheme
     * @param array $options
     * @return One_Controller
     */
    public static function getController($schemeName, $options = array())
    {
      $useSchemeName = $schemeName;
      if ($schemeName instanceof One_Scheme) {
        $useSchemeName = $schemeName->getName();
      }

      $controller = 'One_Controller_' . ucfirst(strtolower($useSchemeName));
      if (class_exists($controller)) {
        return new $controller($options);
      }
      else {
        return new One_Controller($options);
      }
    }

    /**
     * Gets the admin controller for the specified scheme
     *
     * @param string $type Type of the admin controller
     * @param mixed $schemeName Can be either the name of a scheme or an instance of One_Scheme
     * @param array $options
     * @return One_Admin_Controller
     * @deprecated
     */
    public static function getAdminController($type, $schemeName, $options = array())
    {
      $useSchemeName = $schemeName;
      if ($schemeName instanceof One_Scheme) {
        $useSchemeName = $schemeName->getName();
      }

      switch (strtolower($type)) {
        case 'content':
          $type = 'Content';
          break;
        case 'meta':
          $type = 'Meta';
          break;
        case 'view':
          $type = 'View';
          break;
        default:
          throw new One_Exception($type . ' is not a valid admin-controller');
          return;
      }

      $controller = 'One_Admin_' . $type . '_Controller_' . ucfirst(strtolower($useSchemeName));

      if (class_exists($controller)) {
        return new $controller($options);
      }
      else {
        $controller = 'One_Admin_' . $type . '_Controller';
        return new $controller($options);
      }
    }

    /**
     * Get One_Session singleton
     *
     * @static
     * @return One_Session
     */
    public static function getSession()
    {
      if (is_null(self::$session)) {
        self::$session = new One_Session();
      }

      return self::$session;
    }

    /**
     * Get an instance of a filter
     *
     * @param string $filterName
     * @param string $schemeName
     * @return One_Filter_Interface
     */
    public static function getFilter($filterName, $schemeName = NULL, $filterParameters = array())
    {
      $schemeFilter  = 'One_Filter_Scheme_' . ucfirst($schemeName) . '_' . ucfirst($filterName);
      $generalFilter = 'One_Filter_' . ucfirst($filterName);

      if (class_exists($schemeFilter)) {
        $filter = new $schemeFilter($filterParameters);
        return $filter;
      }
      else {
        if (class_exists($generalFilter)) {
          $filter = new $generalFilter($filterParameters);
          return $filter;
        }
        else {
          throw new One_Exception('Filter "' . $filterName . '" could not be found');
        }
      }
    }

    /**
     * @return One_Templater
     */
    public static function getTemplater($templaterClass = NULL)
    {
      if (is_null($templaterClass)) {
        $templaterClass = One_Config::get('view.templater', 'One_View_Templater_Script');
      }
      return new $templaterClass('');
    }

  }
