<?php

  /**
   * The One_Store_Connection_Mysqli class supplies the connection to a MySQL database
   *
   * ONEDISCLAIMER
   **/
  class One_Store_Connection_Mysqli extends One_Store_Connection_Abstract
  {
    /**
     * Open the connection
     *
     * @return One_Store_Connection_Mysqli
     */
    public function open()
    {
      $meta = $this->getMeta();
      $link = new mysqli($meta['db']['host'], $meta['db']['user'], $meta['db']['password'], $meta['db']['database']);
//      $link = mysqli_connect($meta['db']['host'], $meta['db']['user'], $meta['db']['password'], $meta['db']['database']);

      if ($link->connect_error) {
        throw new One_Exception('Connect error (' . $link->connect_errno . ') '. $link->connect_error);
      }
//      if (!$link) {
//        throw new One_Exception('Not connected : ' . mysql_error());
//      }

//      $db = mysql_select_db($meta['db']['database'], $link);
//      if (!$db) {
//        throw new One_Exception('Cannot select : ' . mysql_error());
//      }

      // Set the proper encoding if needed
      $encoding = $this->getEncoding();
      if (null != $encoding) {
        $link::query('SET NAMES "' . mysql_real_escape_string('.$encoding.') . '"');
      }

      return $link;
    }

    /**
     * Close the connection
     *
     * @return One_Store_Connection_Mysqli
     */
    public function close($link = NULL)
    {
      $link->close();
    }
  }
