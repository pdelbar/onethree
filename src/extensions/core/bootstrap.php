<?php

  /**
   * Class One_Bootstrap
   *
   * This class initializes one|content according to the required settings for basic paths etc.
   */

  require_once 'loader.php';

  class One_Bootstrap
  {
    public static function bootstrap($oneLibFolder, $oneCustomFolder)
    {
      // Step 1: setup the basic configuration

      require_once $oneLibFolder . 'core/config.php';
      One_Config::set('locator.root', '{' . $oneCustomFolder . '*,' . $oneLibFolder . '*}/');

      // Step 2: register the autoloader

      require_once $oneLibFolder . 'core/loader.php';
      One_Loader::register($oneLibFolder, $oneCustomFolder);

      // Step 3: load extensions

      One_Config::loadExtensions();
      One_Config::callExtensions('afterInitialise');

      // Step 4: load the main One class
      require_once $oneLibFolder . 'core/one_tbd.php';

    }
  }