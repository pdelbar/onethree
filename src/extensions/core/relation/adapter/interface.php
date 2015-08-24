<?php

  /**
   * One_Relation_Adapter is the way how the relationship is built.
   * One-to-many, many-to-one or many-to-many
   *
   * ONEDISCLAIMER
   **/
  interface One_Relation_Adapter_Interface
  {
    /**
     * Returns the name of the linktype
     *
     * @return string
     */
    public function getName();

    /**
     * Instantiate the related objects
     *
     * @param One_Relation_Adapter $link
     * @param One_Model $model
     * @param array $options @see One_Query::setOptions()
     * @return One_Model|array Can be One_Model or array of One_Models
     */
    public function getRelated(One_Relation_Adapter $link, One_Model $model, array $options = array());

    public function countRelated(One_Relation_Adapter $link, One_Model $model, array $options = array());
  }