<?php
  defined('_JEXEC') or die;

  class OneController extends JControllerLegacy
  {
    protected $default_view = 'schemes';

    public function display($cachable = false, $urlparams = false)
    {
//      require_once JPATH_COMPONENT . '/helpers/one.php';

      $view   = $this->input->get('view', 'schemes');
      $layout = $this->input->get('layout', 'default');

      parent::display();
      return $this;
    }
  }