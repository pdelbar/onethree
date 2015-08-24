<?php

  /**
   * Class One_Store_Connection_Abstract
   *
   * ONEDISCLAIMER
   */
  abstract class One_Store_Connection_Abstract implements One_Store_Connection_Interface
  {
    /**
     * @var string The name of the store
     */
    protected $_connectionName;

    /**
     * @var One_Store The 'storage engine' to use
     */
    protected $_store;

    /**
     * @var array Maintained for the short term
     */
    protected $_meta;

    /**
     * @var string The encoding to use for the connection (utf8, iso-8859-1, ...)
     */
    protected $_encoding;


    /**
     * Class constructor
     *
     * @param string $connectionName
     */
    public function __construct($connectionName)
    {
      $this->_name = $connectionName;
    }

    /**
     * Returns the name of the store
     *
     * @return string
     */
    public function getName()
    {
      return $this->_name;
    }

    /**
     * Returns the meta-data of the store
     *
     * @return array
     */
    public function getMeta()
    {
      return $this->_meta;
    }

    /**
     * Set the meta-data
     *
     * @param array $meta
     * @return One_Store_Connection_Abstract
     */
    public function setMeta(array $meta)
    {
      $this->_meta = $meta;
      return $this;
    }

    /**
     * Returns the One_Store for the connection
     *
     * @return One_Store
     */
    public function getStore()
    {
      return $this->_store;
    }

    /**
     * Set the store for the connection
     *
     * @param One_Store $store
     * @return One_Store_Connection_Abstract
     */
    public function setStore(One_Store $store)
    {
      $this->_store = $store;
      return $this;
    }

    /**
     * Returns the encoding for the connection
     *
     * @return string
     */
    public function getEncoding()
    {
      return $this->_encoding;
    }

    /**
     * Set the encoding for the connection
     *
     * @param string
     * @return One_Store_Connection_Abstract
     */
    public function setEncoding($encoding)
    {
      $this->_encoding = $encoding;
      return $this;
    }

    public function open()
    {
    }

    public function close($ch = NULL)
    {
    }
  }