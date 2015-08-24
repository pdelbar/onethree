<?php
  defined('_JEXEC') or die;

  class OneControllerSchemes extends JControllerAdmin
  {
    public function getModel($name = 'schemes', $prefix = 'SchemedModel', $config = array('ignore_request' => true))
    {
      $model = parent::getModel($name, $prefix, $config);
      return $model;
    }
  }
