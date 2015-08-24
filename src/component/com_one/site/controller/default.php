<?php
  /**
   * ONEDISCLAIMER
   */

  defined('_JEXEC') or die('Restricted access');

  require_once JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'dispatcher.php';

  class OneControllerDefault extends JControllerBase
  {
    public function execute()
    {
      $dispatcher = new OneDispatcherJoomla(JFactory::getApplication()->input->getArray());
      $dispatcher->dispatch();
      return true;
    }
  }