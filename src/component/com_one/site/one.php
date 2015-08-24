<?php
  /**
   * ONEDISCLAIMER
   */

  defined('_JEXEC') or die('Restricted access');

  if (!JPluginHelper::isEnabled('system','one')) {
    throw new Exception("The one|content system plugin is not enabled.");
  };

  /**
   * Identify the default controller to instantiate by examining the current menu item (if any)
   */
  $controllerName = 'default';

  $app  = JFactory::getApplication();
  $menu = $app->getMenu();
  $item = $menu->getActive();
  if ($item !== null) {
    if (isset($item->query['controller'])) {
      $controllerName = $item->query['controller'];
    }
  }

  /**
   * If the controller has been overridden, change it
   */
  $controllerName = $app->input->get('controller', $controllerName);
  require_once(JPATH_COMPONENT . '/controller/' . $controllerName . '.php');

  /**
   * Instantiate the controller and execute
   */
  $controllerClassName  = 'OneController' . ucfirst($controllerName);
  $controller = new $controllerClassName();

  $controller->execute();