<?php

  /**
   * The One_Store_Connection_Joomla2 class supplies the connection to a Joomla 2.5 database
   *
   * ONEDISCLAIMER
   **/
  class One_Store_Connection_Joomla2 extends One_Store_Connection_Abstract
  {
    /**
     * Open the connection
     *
     * @return One_Store_Connection_Mysql
     */
    public function open()
    {
      $db = JFactory::getDbo();

      // Set the proper encoding if needed
      $encoding = $this->getEncoding();
      if (null != $encoding) {
        $db->setQuery('SET NAMES "' . $db->getEscaped($encoding) . '"');
        $db->query();
      }

      return $db;
    }
  }
