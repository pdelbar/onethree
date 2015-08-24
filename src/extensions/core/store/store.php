<?php

  /**
   * This is basicly a Factory for getting instances of One_Store
   *
   * ONEDISCLAIMER
   **/
  class One_Store
  {
    const QUERYCLASS = 'One_Query';

    /**
     * Get a specified instance of One_Store
     *
     * @param string $type
     * @throws One_Exception
     * @return One_Store
     */
    public static function getInstance($type)
    {
      $className = 'One_Store_' . ucfirst(strtolower($type));
      if (class_exists($className)) {
        $store = new $className($name);
        return $store;
      }
      else {
        throw new One_Exception('A store of type "' . $type . '" does not exist');
      }
    }

    /**
     * Return the type of One_Query class required for this store
     *
     * @return string
     */
    protected function getQueryClass()
    {
      $c = get_called_class();
      return $c::QUERYCLASS;
    }

    /**
     * Return the appropriate type of query object for this scheme
     *
     * @param One_Scheme $scheme
     * @return One_Query
     */
    public function getQuery(One_Scheme $scheme)
    {
      $queryClass = $this->getQueryClass();
      return new $queryClass($scheme);
    }
  }
