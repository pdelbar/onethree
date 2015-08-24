<?php

  /**
   * The One_Store_Connection_Mysql class supplies the connection to a MySQL database
   *
   * ONEDISCLAIMER
   **/
  class One_Store_Connection_Mysql extends One_Store_Connection_Abstract
  {
    /**
     * Open the connection
     *
     * @return One_Store_Connection_Mysql
     */
    public function open()
    {
      $meta = $this->getMeta();
      $link = mysql_connect($meta['db']['host'], $meta['db']['user'], $meta['db']['password'], true);

      if (!$link) {
        throw new One_Exception('Not connected : ' . mysql_error());
      }

      $db = mysql_select_db($meta['db']['database'], $link);
      if (!$db) {
        throw new One_Exception('Cannot select : ' . mysql_error());
      }

      // Set the proper encoding if needed
      $encoding = $this->getEncoding();
      if (null != $encoding) {
        mysql_query('SET NAMES "' . mysql_real_escape_string('.$encoding.') . '"', $link);
      }

      return $link;
    }

    /**
     * Close the connection
     *
     * @return One_Store_Connection_Mysql
     */
    public function close($ch = NULL)
    {
      mysql_close($ch);
    }
  }
