<?php

/**
 * This is the parent class which takes care of the edit actions in general.
 * Only subclasses should be instantiated.
 *
 * @TODO review this file and clean up historical code/comments
 * ONEDISCLAIMER
 **/
class One_Action_Edit extends One_Action {
  /**
   * @var mixed the ID of the chosen item
   */
  protected $id;

  /**
   * Class constructor
   *
   * @param One_Controller $controller
   * @param array $options
   */
  public function __construct(One_Controller $controller, $options = array()) {
    parent::__construct($controller, $options);

    $view = $this->getVariable('view', 'edit');
//    echo 'Looking for view ', $view, ' in scheme ', $this->scheme->getName();
    $this->view = new One_View($this->scheme, $view);

    $this->id = $this->getVariable('id', null);
    if ($this->id === null) {
      $id = $this->scheme->getIdentityAttribute();
      $id = $id->getName();
      $this->id = $this->getVariable($id, null);
    }
  }

  public function execute() {
    switch (strtolower($this->getVariable('action', 'show'))) {
      case 'save':
      case 'apply':
      case 'update':
      case 'insert':
        $actionClass = new One_Action_EditUpdate($this->controller, $this->options);
        break;
      case 'cancel':
        $actionClass = new One_Action_EditCancel($this->controller, $this->options);
        break;
      case 'show':
      default:
        $actionClass = new One_Action_EditShow($this->controller, $this->options);
        break;
    }

    return $actionClass->execute();
  }

  /**
   * Returns whether the user is allowed to perform this task
   *
   * @param One_Scheme $scheme
   * @param mixed $id
   * @return boolean
   */
  public function authorize($scheme, $id) {
    return One_Permission::authorize('edit', $scheme, $id);
  }
}
