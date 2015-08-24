<?php

/**
 * This class handles the updating of an edit form
 *
 * @TODO review this file and clean up historical code/comments
 *
 * ONEDISCLAIMER
 **/
class One_Action_EditUpdate extends One_Action_Edit {
  /**
   * Class constructor
   *
   * @param One_Controller_Interface $parent The parent action
   * @param array $options Additional options
   */
  public function __construct(One_Controller $controller, $options = array()) {
    parent::__construct($controller, $options);

    // remove former errors in case they're still there
    $session = One_Repository::getSession();
    $session->remove('errors', 'OneFormErrors');
  }

  /**
   * This method validates a submitted form and returns to the proper page according to whether the submission
   * contained errors or whether the form was saved or applied
   */
  public function execute() {
    $session = One_Repository::getSession();
    $isNew = false;
    if ($this->id) {
      // update existing
      $factory = One_Repository::getFactory($this->scheme->getName());
      $model = $factory->selectOne($this->id);

      if (is_null($model) && !$factory->getScheme()->getIdentityAttribute()->isAutoInc()) {
        $model = One::make($this->scheme->getName());
        $isNew = true;
      }
    } else {
      $model = One::make($this->scheme->getName());
    }

    $idAttrName = $model->getScheme()->getIdentityAttribute()->getName();

    $this->authorize($this->scheme->getName(), $model->$idAttrName);

    $formFile = $this->getVariable('formFile', 'form');
    $form = One_Form_Factory::createForm($this->scheme, $formFile, $this->getVariable('lang'), 'oneForm', '');

    $flow = One_Controller_Flow::getInstance($this->scheme)->getRedirects();

    $noErrors = $form->validate();

    if ($noErrors || (is_array($noErrors) && count($noErrors) == 0)) {
      $form->bindModel($model);

      if ($this->id && !$isNew) {
        $model->update();
        $id = $this->id;
      } else {
        $model->insert();
        $idAttr = $this->scheme->getIdentityAttribute()->getName();
        $id = $model->$idAttr;
      }

      $this->model = $model;

      // handle redirects
      // @TODO this code can use some cleanup
      $redirects = array_merge($flow, $form->getRedirects());
      $todo = is_null($this->getVariable('action')) ? $this->getVariable('task') : $this->getVariable('action');
      if (isset($this->options['flow']))
        $todo = $this->options['flow'];
      $redirect = $redirects['default'];
      if (isset($redirects[$todo])) {
        $redirect = $redirects[$todo];
      }
      if (isset($redirect['id']) && strtoupper(trim($redirect['id'])) == '::ID::') {
        $redirect['id'] = $model->$idAttrName;
      }
      $redirect = $this->replaceOtherVariables($redirect);
      $this->controller->setRedirect($redirect);
    } else // errors did occur
    {
      $errors = base64_encode(serialize($form->getErrors()));

      $session->set('executedReturn', $model, 'executedForm');
      $session->set('errors', $form->getErrors(), 'OneFormErrors');
      $session->set('posted', $_REQUEST, 'OneFormErrors');

      $id = $this->id;
      $toView = 'edit';

      if (!is_null($this->getVariable('returnToOne'))) {
        parse_str(base64_decode($this->getVariable('returnToOne')), $returnVals);
        $this->controller->setRedirect($returnVals);
      } else {
        $redirects = array_merge($flow, $form->getRedirects());

        $todo = 'default';

        if (isset($this->options['flowerror'])) {

          $todo = $this->options['flowerror'];

        } elseif (isset($redirects['formerror'])) {

          $todo = 'formerror';
        }

        $redirect = $redirects[$todo];


        if (isset($redirect['id']) && strtoupper(trim($redirect['id'])) == '::ID::') {
          $redirect['id'] = $model->$idAttrName;
        }

        $redirect = $this->replaceOtherVariables($redirect);

        $this->controller->setRedirect($redirect);
      }
      return false;
    }
  }
}
