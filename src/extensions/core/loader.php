<?php

  require_once 'locator.php';

  /**
   * One_Loader
   *
   * This class contains the autoloader for one|content classes
   *
   * ONEDISCLAIMER
   **/
  class One_Loader
  {

    /**
     * Register One_Loader in the autoloader sequence
     */
    public static function register()
    {
      if (function_exists('__autoload')) {
        spl_autoload_register('__autoload');
      }
      spl_autoload_register(array('One_Loader', 'load'));
    }

    /**
     * Load the specified class
     *
     * @param string $className
     */
    public static function load($className)
    {
      if (substr($className, 0, 4) == 'One_') {
        $file = self::classnameToDirectory($className, '_tbd');
        if (!self::tryLoading($file)) {
          $file = self::classnameToDirectory($className);
          self::tryLoading($file);
        }
      }
    }

    /**
     * Look for the specified file using the locator. If it exists, autoload it and return true, otherwise return false
     *
     * @param string $filename
     * @return boolean
     */
    public static function tryLoading($filename)
    {
      $path = One_Locator::locate($filename);
      if ($path === null) {
        return false;
      }

      require_once $path;
      return true;
    }

    /**
     * Convert the classname to the correct folder structure (split on _ and remove leading One_)
     *
     * @param $className
     */
    protected static function classnameToDirectory($className, $additionalPart = '')
    {
      $parts = explode('_', $className);
      array_shift($parts);

      // correct for single-part classes like One_Model -> look in model/model.php
      if (count($parts) == 1) {
        $parts[] = $parts[0];
      }

      return strtolower(implode('/', $parts) . $additionalPart . '.php');
    }


  }

