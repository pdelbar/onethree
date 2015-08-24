<?php defined('_JEXEC') or die('Restricted access');

  class OneControllerRest extends JControllerBase
  {
    public function execute()
    {

      try {
        $app        = JFactory::getApplication();
        $menu       = $app->getMenu();
        $menuItem   = $menu->getActive();
        $alias      = $menuItem->route;
        $controller = new One_Controller_Rest(JFactory::getApplication()->input->getArray());
        $controller->run($alias);
      } catch (Exception $e) {
        if (One_Config::get('debug.exitOnError') === false) {
          echo $e->getMessage();
        }
        else {
          throw new Exception($e);
        }
      }

    }
  }