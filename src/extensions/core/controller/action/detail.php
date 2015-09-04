<?php

  /**
   * This class handles the detail view of the chosen item
   *
   * ONEDISCLAIMER
   **/
  class One_Controller_Action_Detail extends One_Controller_Action
  {
    /**
     * @var mixed the ID of the chosen item
     */
    public $id;

    /**
     * Class constructor
     *
     * @param One_Controller $controller
     * @param array $options
     */
    public function __construct(One_Controller_Interface $controller, $options = array())
    {
      parent::__construct($controller, $options);

      $view = $this->getVariable('view', 'detail');

      $this->view = new One_View($this->scheme, $view);
      $this->id   = $this->getVariable('id');
    }

    /**
     * This method returns the detail view of the currently chosen item
     *
     * @return string The detail view of the currently chosen item
     */
    public function execute()
    {
      $this->authorize($this->scheme->getName(), $this->id);

      $factory = One_Repository::getFactory($this->scheme->getName());
      $model   = $factory->selectOne($this->id);

      if (is_null($model)) {
        throw new One_Exception('Item could not be found');
      }

      $this->view->setModel($model);
      return $this->view->show();

    }

    /**
     * Returns whether the user is allowed to perform this task
     *
     * @param One_Scheme $scheme
     * @param mixed $id
     * @return boolean
     */
    public function authorize($scheme, $id)
    {
      return One_Permission::authorize('detail', $scheme, $id);
    }

    /**
     * Return the standard routing for this action.
     */
//    public static function getStandardRouting($options)
//    {
//      return array('alias' => 'detail/' . $options['view'], 'useId' => true);
//    }
  }
