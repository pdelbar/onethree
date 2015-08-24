<?php

  /**
   * Interface One_Store_Connection_Interface
   *
   * ONEDISCLAIMER
   */
  interface One_Store_Connection_Interface
  {
    /**
     * Open the connection
     *
     * @return One_Store_Connection_Interface
     */
    public function open();

    /**
     * Close the connection
     *
     * @return One_Store_Connection_Interface
     */
    public function close($ch = NULL);

    /**
     * Set the store
     *
     * @param One_Store $store Store
     * @return One_Store_Connection_Interface
     */
    public function setStore(One_Store $store);

    /**
     * Get the store
     *
     * @return One_Store
     */
    public function getStore();
  }