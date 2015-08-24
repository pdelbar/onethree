<?php

  /**
   * The One_Behavior class implements the basic methods to extend scheme behavior
   *
   * ONEDISCLAIMER
   **/
  abstract class One_Behavior
  {
    /**
     * Method that returns the name of the behavior
     *
     * @return string
     * @abstract
     */
    abstract public function getName();

    /**
     * Function performed after loading the model
     *
     * @param One_Scheme $scheme
     * @param One_Model $model
     */
    public function afterLoadModel(One_Scheme $scheme, One_Model $model)
    {
    }

    /**
     * Function performed before updating the model
     *
     * @param One_Scheme $scheme
     * @param One_Model $model
     */
    public function beforeUpdateModel(One_Scheme $scheme, One_Model $model)
    {
    }

    /**
     * Function performed after updating the model
     *
     * @param One_Scheme $scheme
     * @param One_Model $model
     */
    public function afterUpdateModel(One_Scheme $scheme, One_Model $model)
    {
    }

    /**
     * Function performed before inserting the model
     *
     * @param One_Scheme $scheme
     * @param One_Model $model
     */
    public function beforeInsertModel(One_Scheme $scheme, One_Model $model)
    {
    }

    /**
     * Function performed after inserting the model
     *
     * @param One_Scheme $scheme
     * @param One_Model $model
     */
    public function afterInsertModel(One_Scheme $scheme, One_Model $model)
    {
    }

    /**
     * Function performed before deleting the model
     *
     * @param One_Scheme $scheme
     * @param One_Model $model
     */
    public function beforeDeleteModel(One_Scheme $scheme, One_Model $model)
    {
    }

    /**
     * Function performed after deleting the model
     *
     * @param One_Scheme $scheme
     * @param One_Model $model
     */
    public function afterDeleteModel(One_Scheme $scheme, One_Model $model)
    {
    }

    /**
     * Function performed on creation of the model
     *
     * @param string $schemeName
     */
    public function onCreateModel($schemeName)
    {
      return null;
    }

    /**
     * Function performed on loading the scheme
     *
     * @param One_Scheme $scheme
     */
    public function onLoadScheme(One_Scheme $scheme)
    {
    }

    /**
     * Function performed on selection
     *
     * @param One_Query $query
     */
    public function onSelect(One_Query $query)
    {
    }
  }
