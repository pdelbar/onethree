<?php

/**
 * This class handles the list view of the chosen item
 *
  * @TODO review this file and clean up historical code/comments
 * ONEDISCLAIMER
 **/
class One_Controller_Action_Jqexport extends One_Controller_Action_List {
  /**
   * This method returns the list view of the currently chosen item
   *
   * @return string The list view of the currently chosen item
   */
  public function execute() {
    $this->authorize();

    $results = $this->getData();

    $view = $this->getVariable('listFormDataView', 'jqexport');
    $this->view = new One_View($this->scheme, $view);
    $this->view->setModel($results);
    $this->view->show();
    exit;
  }

  protected function getData() {
    $schemeName = $this->scheme;
    if ($this->scheme instanceof One_Scheme) {
      $schemeName = $this->scheme->getName();
    }

    $factory = One_Repository::getFactory($schemeName);
    $query = $factory->selectQuery($this->scheme);

    $ids = explode(',', $this->options['listFormData']);
    $idAttr = One_Repository::getScheme($schemeName)->getIdentityAttribute()->getName();
    $query->where($idAttr, 'in', $ids);

    $this->processQueryConditions($query);

    $results = $query->execute();

    return $results;
  }

  /**
   * Returns whether the user is allowed to perform this task
   *
   * @return boolean
   */
  public function authorize() {
    return true;
    return One_Permission::authorize('jqexport', $this->scheme, 0);
  }
}
