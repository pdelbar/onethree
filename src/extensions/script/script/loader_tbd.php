<?php

class One_Script_Loader {
  public static function register() {
    if (function_exists('__autoload')) spl_autoload_register('__autoload');
    spl_autoload_register(array('One_Script_Loader', 'load'));
  }

  static function load($className, $verbose = false) {
    if ($className == 'One_Script') {
      $parts = preg_split('/\./', strtolower(preg_replace("/([A-Z])/", ".$1", substr($className, 1))));
      if (self::_load($parts)) {
        return;
      }
    }

    if (substr($className, 0, 2) == 'ns') {
      $parts = preg_split('/\./', strtolower(preg_replace("/([A-Z])/", ".$1", substr($className, 2))));

      if (self::_load($parts)) {
        return;
      }
    }

    if ($verbose) {
      echo "One_Script_Loader error: could not locate $className.";
    }
  }

  static function _load($parts) {
    $path = One_Locator::locate(implode("/", $parts) . '.php');

    if ($path === null) {
      return false;
    }

		require_once $path;

		return true;
	}

  public static function isPackage($pkName) {
    $path = One_Locator::locate('script' . DIRECTORY_SEPARATOR . 'package' . DIRECTORY_SEPARATOR . $pkName . '.php');
//    echo '<br><b>', $pkName, '</b> is ', ($path === null ? 'NOT' : ' '), ' a package';

    //*** temporary
    if ($path) return true;
    $path = One_Locator::locate('script' . DIRECTORY_SEPARATOR . 'package' . DIRECTORY_SEPARATOR . $pkName . '_tbd.php');
    return ($path !== null);
  }

}

