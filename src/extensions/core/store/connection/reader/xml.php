<?php

  /**
   * The One_Store_Connection_Reader_Xml class loads One_Store_Connection definitions from an XML and instantiates them
   * as a factory pattern.
   *
   * ONEDISCLAIMER
   **/
  class One_Store_Connection_Reader_Xml
  {
    /**
     * Loads a store instantiation
     *
     * @param string $connectionName
     * @return One_Store_Connection
     */
    public static function load($connectionName)
    {
      // read the scheme's metafile
      $connectionpath = One_Locator::locate('meta' . DIRECTORY_SEPARATOR . 'connection' . DIRECTORY_SEPARATOR . $connectionName . '.xml');
      if ($connectionpath === null) {
        throw new One_Exception('Could not find the one|content store-connection called <strong>' . $connectionName . '</strong>.');
      }


      // read the scheme's metafile
      $dom = new DOMDocument('1.0', 'utf-8');
      if ($dom->load($connectionpath)) {
        // Check if the connection tag was defined
        $connectionSpecs = $dom->getElementsByTagName('connection');
        if ($connectionSpecs->length == 0) {
          throw new One_Exception('one|content store-connection called <strong>' . $connectionName . '</strong> was not properly defined');
        }
        $connectionSpec = $connectionSpecs->item(0);

        $xpath = new DOMXPath($dom);

        // Get the connection type
        $type = self::getType($xpath);

        //Create the One_Store_Connection
        $className = 'One_Store_Connection_' . ucfirst(strtolower($type));
        if (!class_exists($className)) {
          throw new One_Exception('A connection of type "' . $type . '" does not exist');
        }
        $connection = new $className($connectionName);

        // get and set the store
        $store = self::getStore($xpath);
        $connection->setStore($store);

        // get and set the encoding for the connection
        $encoding = self::getEncoding($xpath);
        $connection->setEncoding($encoding);

        $meta = array();
        if ($connectionSpec->hasChildNodes()) {
          $child = $connectionSpec->firstChild;
          do {
            if ($child->nodeType == XML_ELEMENT_NODE) {
              unset($childName);
              unset($attributes);
              $childName = $child->nodeName;

              $meta[$childName] = array();
              $attributes       = $child->attributes;
              if (count($attributes) > 0) {
                for ($i = 0; $i < $attributes->length; $i++) {
                  $attribute                          = $attributes->item($i);
                  $meta[$childName][$attribute->name] = $attribute->value;
                }
              }
            }
            $child = $child->nextSibling;
          } while (!is_null($child));
        }

        $connection->setMeta($meta);

        return $connection;
      }
      else {
        throw new One_Exception('one|content store-connection called <strong>' . $connectionName . '</strong> was not properly defined');
      }
    }

    /**
     * Get the type of the connection
     *
     * @param DOMXPath $xpath
     * @return string
     */
    protected static function getType(DOMXPath $xpath)
    {
      $typeAttr = $xpath->query('/connection/@type');
      $type     = $typeAttr->item(0)->value;

      return $type;
    }

    /**
     * Get the proper store for this connection
     *
     * @param DOMXPath $xpath
     * @return One_Store
     */
    protected static function getStore(DOMXPath $xpath)
    {
      $storeAttr = $xpath->query('/connection/@type');
      $store     = One_Repository::getStore($storeAttr->item(0)->value);

      return $store;
    }

    /**
     * Get the encoding for this connection if set
     *
     * @param DOMXPath $xpath
     * @return string
     */
    protected static function getEncoding(DOMXPath $xpath)
    {
      $storeAttr = $xpath->query('/connection/@encoding');

      $encoding = null;
      if ($storeAttr->length > 0) {
        $encoding = $storeAttr->item(0)->value;
      }

      return $encoding;
    }
  }
