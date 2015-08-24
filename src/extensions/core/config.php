<?php

  require_once "registry/registry.php";

  /**
   * One_Config is a singleton class containing a One_Registry for all values we want to store for this
   * configuration
   *
   * ONEDISCLAIMER
   **/
  class One_Config
  {
    /**
     * @var One_Registry instance keeping the config information
     */
    private static $registry;

    /**
     * Get
     */
    public static function get($key, $default = null)
    {
      if (empty(self::$registry)) {
        self::$registry = new One_Registry();
      }
      return self::$registry->get($key,$default);
    }

    public static function set($key, $value)
    {
      if (empty(self::$registry)) {
        self::$registry = new One_Registry();
      }
      return self::$registry->set($key, $value);
    }


    /**
     * Load extenders. This is neat stuff. Basically, each extension folder (under ONE_LIB_PATH or ONE_CUSTOM_PATH)
     * can contain an extension.php file. These files can define a local One_Extension subclass and inject it into the
     * One_Config::get('extensions') list. Extensions are loaded on initialize and other events. Well, they could be
     * and will be in the future. Now it's only for initialization.
     */
    public static function loadExtensions()
    {
      // call plugin extenders. These define a class for a plugin that is added to the extenders list
      $extenders = One_Locator::locateAllUsing('extension.php', self::get('locator.root'));
      if (count($extenders)) {
        foreach ($extenders as $extenderPath) {
          require_once $extenderPath;
        }
      }
    }

    public static function callExtensions($event, $arguments = array())
    {
      $methodName = 'on' . ucfirst($event);
      $extenders  = self::get('extensions');
      if (count($extenders)) {
        foreach ($extenders as $extensionName => $extensionClass) {
          $extensionClass::$methodName($arguments);
        }
      }
    }


    // ---------------------------------------------------------------------------------------------------
    // deprecated shit, kept here to trigger warnings when used
    // ---------------------------------------------------------------------------------------------------

    /**
     * Get a One instance
     *
     * @return One_Config
     */
    public static function &getInstance($application = 'site')
    {
      throw new One_Exception_Deprecated('Someone is calling One_Config->getInstance(). Please
      consider a different approach.');
//      if (empty(self::$_instance)) {
//        self::$_instance = new One_Config($application);
//      }
//
//      return self::$_instance;
    }


    /**
     * Get the siteroot-url
     *
     * @return string
     */
    public function getSiterootUrl()
    {
      throw new One_Exception_Deprecated('Someone is calling One_Config->getSiterootUrl(), which probably means that the caller
      is trying to load a file from inside the one library using a web URL -- not so happy about that. Please
      consider a different approach.');
//      return $this->_siterooturl;
    }

    /**
     * Get the url
     *
     * @return string
     */
    public function getUrl()
    {
      throw new One_Exception_Deprecated('Someone is calling One_Config->getUrl(), which probably means that the caller
      is trying to load a file from inside the one library using a web URL -- not so happy about that. Please
      consider a different approach.');
//      return $this->_url;
    }

    public function getAddressOne()
    {
      throw new One_Exception_Deprecated('Please do not use getAddressOne ...');
//      return $this->_addressOne;
    }


  }
