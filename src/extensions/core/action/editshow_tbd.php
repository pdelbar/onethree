<?php

/**
 * This class handles the visualisation of an edit form
 *
 * @TODO review this file and clean up historical code/comments
 *
 * ONEDISCLAIMER
 **/
class One_Action_EditShow extends One_Action_Edit {
  /**
   * This method composes the form and returns the output
   *
   * @return string Output of the form
   */
  public function execute() {
    // Fetch the model needed for this form. We will need it because authorization can depend on the model itself
    if ($this->id) {
      $factory = One_Repository::getFactory($this->scheme->getName());
      $model = $factory->selectOne($this->id);
    } else {
      $model = One::make($this->scheme->getName());
    }

    if (is_null($model)) {
      throw new One_Exception('Could not generate a form for scheme "' . $this->scheme->getName() . '" with id ' . $this->id);
    }

    $this->authorize($this->scheme->getName(), $model->id);

    $session = One_Repository::getSession();

    $formFile = $this->getVariable('formFile', 'form');
    $form = One_Form_Factory::createForm($this->scheme, $formFile, $this->getVariable('lang'), 'oneForm', '');

    // Create a DOM and render the form in it
    $dom = One_Repository::getDom();
    $form->render($model, $dom);

    $this->view->setModel($model);
    $this->view->set('scheme', $this->scheme);
    $this->view->set('form', $form);
    $this->view->set('dom', $dom);
    $this->view->set('errors', $session->get('errors', 'OneFormErrors'));

    $vForm = $this->view->show();

    $session->remove('errors', 'OneFormErrors');
    $session->remove('posted', 'OneFormErrors');

    return $vForm;
  }
}
