<?php

/**
 * Factory class to fetch different kinds of object one|content
 *
 * ONEDISCLAIMER
 **/

die('Loading deprecated file: ' . __FILE__ );

class One_Dictionary {
  /**
   * Return an array with available scheme names
   * @return array
   */
  public static function getSchemeNames() {
    $folders = array(
      One_Config::getInstance()->getCustomPath() . DIRECTORY_SEPARATOR . 'meta' . DIRECTORY_SEPARATOR . 'scheme',
      ONE_LIB_PATH . DIRECTORY_SEPARATOR . 'meta' . DIRECTORY_SEPARATOR . 'scheme'
    );

    $schemes = array();

    foreach ($folders as $folder) {
      if ( is_dir($folder) && $dh = opendir($folder) )
        while (($file = readdir($dh)) !== false)
          if ( is_file($folder . DIRECTORY_SEPARATOR . $file) && preg_match('/^(.+)\.xml$/iU', $file, $matches) && !in_array($matches[1], $schemes) )
            $schemes[] = $matches[1];
    }
    return $schemes;
  }

  /**
   * Return an array with available filter names
   * @return array
   */
  public static function getFilterNames($schemeName = null) {
    $folders = array(
      ONE_LIB_PATH . DIRECTORY_SEPARATOR . 'filter',
      ONE_LIB_PATH . 'lib' . DIRECTORY_SEPARATOR . 'filter'
    );

    if ( !is_null($schemeName) && trim($schemeName) != '' ) {
      $scheme           = One_Repository::getScheme($schemeName); // easy way of checking whether the scheme exists or not, will throw exception if it doesn't exist
      $schemeFilterPath = ONE_LIB_PATH . DIRECTORY_SEPARATOR . 'filter' . DIRECTORY_SEPARATOR . 'scheme' . DIRECTORY_SEPARATOR . $schemeName;

      if ( is_dir($schemeFilterPath) )
        array_unshift($folders, $schemeFilterPath);
    }

    $filters = array();

    foreach ($folders as $folder) {
      if ( is_dir($folder) && $dh = opendir($folder) )
        while (($file = readdir($dh)) !== false)
          if ( is_file($folder . DIRECTORY_SEPARATOR . $file) && preg_match('/^(.+)\.php$/iU', $file, $matches) && $file != 'interface.php' && !in_array($matches[1], $filters) )
            $filters[] = $matches[1];
    }

    sort($filters);

    return $filters;
  }
}
