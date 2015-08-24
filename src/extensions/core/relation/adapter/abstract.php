<?php

  /**
   * One_Relation_Adapter is the way how the relationship is built.
   * One-to-many, many-to-one or many-to-many
   *
   * ONEDISCLAIMER
   **/
  abstract class One_Relation_Adapter_Abstract implements One_Relation_Adapter_Interface
  {
    /**
     * Returns the name of the linktype
     *
     * @return string
     * @deprecated
     */
    public function name()
    {
      throw new One_Exception_Deprecated('Use getName() instead');
    }

    /**
     *  Return the columns to insert into the load request to be able to
     *  recreate the link
     *
     * @param One_Relation_Adapter $adapter
     * @return array
     */
    public function columns(One_Relation_Adapter $adapter)
    {
      return array();
    }

    /**
     * Returns the string value of the value
     *
     * @param mixed $value
     * @return string
     */
    public function toString($value)
    {
      return $value;
    }

    /**
     * Overrides default toString method
     *
     * @return string
     */
    public function __toString()
    {
      return $this->getName();
    }

    /**
     *  find out what the scheme's FK field for this relationship is called.
     *  By default, this is formed by
     *      ROLENAME_NAMEOFTARGETIDCOLUMN,
     *  but this can be overridden by the FK setting in the meta description.
     *
     * @param One_Relation_Adapter $adapter
     * @param One_Scheme $target
     * @return string
     */
    public function localFK(One_Relation_Adapter $adapter, One_Scheme $target)
    {
      if ($adapter->meta['fk:local']) {
        return $adapter->meta['fk:local'];
      }

      $col = $target->getIdentityAttribute()->getName();
      return $adapter->getName() . '_' . $col;
    }

    /**
     *  find out what the scheme's FK field for this relationship is called.
     *  By default, this is formed by
     *      ROLENAME_NAMEOFTARGETIDCOLUMN,
     *  but this can be overridden by the FK setting in the meta description.
     *
     * @param One_Relation_Adapter $adapter
     * @param One_Scheme $source
     * @param One_Relation_Adapter $backlink
     * @return string
     */
    public function remoteFK(One_Relation_Adapter $adapter, One_Scheme $source, One_Relation_Adapter $backlink)
    {
      if ($adapter->meta['fk:remote']) {
        return $adapter->meta['fk:remote'];
      }

      $column = $source->getIdentityAttribute()->getName();
      return $backlink->getName() . "_" . $column;
    }
  }
