<?php
  /**
   * ONEDISCLAIMER
   */

  defined('_JEXEC') or die('Restricted access');


  /**
   * Routing class from com_one
   */
  class OneRouter extends JComponentRouterBase
  {
    /**
     * Build the route for the com_one component
     *
     * Every one route needs a scheme and a task. Depending on the task, there may be a need for an additional piece
     * of information. For instance, task=list needs nothing else, but task=detail and taks=edit require an identifier.
     *
     * @param   array &$query An array of URL arguments
     * @return  array  The URL arguments to use to assemble the subsequent URL.
     * @since   3.3
     */
    public function build(&$query)
    {
//    echo '<div class="well">Building route for com_one query<br/><pre>';
//    print_r($query);
//    echo '</pre>';
      $segments = array();

      /**
       * First, check whether we have an Itemid, and see if we can reuse it for this route? This would require that
       * multiple pieces of information are equal.
       */

      // Get a menu item based on Itemid or currently active
      $app    = JFactory::getApplication();
      $menu   = $app->getMenu();
      $params = JComponentHelper::getParams('com_one');

      // We need a menu item.  Either the one specified in the query, or the current active one if none specified
      if (empty($query['Itemid'])) {
        $menuItem      = $menu->getActive();
        $menuItemGiven = false;
      }
      else {
        $menuItem      = $menu->getItem($query['Itemid']);
        $menuItemGiven = true;
      }

      // Check again, if this Itemid is not a com_one item, do not reuse the Itemid
      if ($menuItemGiven && isset($menuItem) && $menuItem->component != 'com_one') {
        $menuItemGiven = false;
        unset($query['Itemid']);
      }

      /**
       * If there is an Itemid, we can retrieve its information to check upon. Else, we assume that info is an
       * empty array.
       */
      $menudata = array();
      if ($menuItem) {
        $menudata = $menuItem->query;
      }


      // We need to have a scheme in the query or it is an invalid URL
      if (isset($query['scheme'])) {
        $schemeName = $query['scheme'];
      }
      else {
        // no scheme, fallback to default
        return $segments;
      }

      // We need to have a task in the query or it is an invalid URL
      if (isset($query['task'])) {
        $task = $query['task'];
      }
      else {
        // no task
        return $segments;
      }

      // We MAY have a view in the query
      if (isset($query['view'])) {
        $view = $query['view'];
      }
      else {
        $view = '';
      }

      /**
       * We know what the scheme and task are. The question is how we construct the route, so we need to know whether
       * this is going to be a special routing defined for this scheme, or the standard routing.
       *
       * If the routing is special, we can find the routing by the One_Routing::getAliasForOptions($scheme,$options)
       * call which needs task+view as parameters.
       *
       * If this fails, we need to go to the standard actions. If the scheme's controller implements the action, we
       * currently treat this as just any task, meaning we have no special route creation info.
       *
       * One_Routing::getAliasForOptions has been changed to look through the standard actions if no scheme-specific
       * route has been located.
       *
       * @TODO add a One_Controller::buildRoute/parseRoute pair to handle these special routes
       *
       * Else, we ask the standard One_Controller_Action class to tell us what the route should be. In that case, it needs to
       * answer in the same way as for a special routing.
       */
      $data      = array('task' => $task, 'view' => $view);
      $scheme    = One_Repository::getScheme($schemeName);
      $aliasData = One_Routing::getAliasForOptions($scheme, $data);

      if ($aliasData !== null) {
        unset($query['scheme']);
        unset($query['task']);
        unset($query['view']);

        $segments = explode('/', $aliasData['alias']);

        if ($aliasData['useId'] == 'true') {
          if (isset($aliasData['aliasField'])) {
            $aliasField  = $aliasData['aliasField'];
            $schemeQuery = One_Repository::selectQuery($aliasData['schemeName']);
            $schemeQuery->where($schemeQuery->getScheme()->getIdentityAttribute()->getName(), 'eq', $query['id']);
            $schemeQuery->setSelect(array($schemeQuery->getScheme()->getIdentityAttribute()->getName(), $aliasField));
            $results = $schemeQuery->execute(false);

            if (count($results)) {
              $segments[] = $results[0]->$aliasField;
            }
            unset($query['id']);
          }
          else {
            if (isset($query['id'])) {
              $segments[] = $query['id'];
            }
          }
        }

      }
      else {
        // nothing
      }

      $total = count($segments);
      for ($i = 0; $i < $total; $i++) {
        $segments[$i] = str_replace(':', '-', $segments[$i]);
      }

//    echo '<pre>';
//    print_r($segments);
//    echo '</pre></div>';
      return $segments;
    }

    /**
     * Parse the segments of a URL.
     *
     * @param   array &$segments The segments of the URL to parse.
     *
     * @return  array  The URL attributes to be used by the application.
     *
     * @since   3.3
     */
    public function parse(&$segments)
    {
      // Get the active menu item.
      $app  = JFactory::getApplication();
      $menu = $app->getMenu();
      $item = $menu->getActive();
      if (isset($item->query['controller']) && $item->query['controller'] == 'rest') {
        return array();
      }

      $options = One_Routing::getOptionsForAlias(implode('/', $segments));
//    echo '<pre>';
//    print_r($options);
//    echo '</pre>';

      if ($options !== null) {
        $vars           = array();
        $vars['scheme'] = $options['schemeName'];
        $vars['task']   = $options['options']['task'];
        $vars['view']   = $options['options']['view'];

        // check and find the alias or id field

        if ($options['useId'] && null !== $options['aliasField']) {
          $regularAlias = $segments[(count($segments) - 1)];
          $altAlias     = preg_replace('/:/', '-', $regularAlias, 1);

          $schemeQuery = One_Repository::selectQuery($options['schemeName']);

          if ($regularAlias == $altAlias) {
            $schemeQuery->where($options['aliasField'], 'eq', $regularAlias);
          }
          else {
            $queryOr = $schemeQuery->addOr();
            $queryOr->where($options['aliasField'], 'eq', $regularAlias);
            $queryOr->where($options['aliasField'], 'eq', $altAlias);
          }
          $results = $schemeQuery->execute();
          if (0 < count($results)) {
            $idAttr     = $schemeQuery->getScheme()->getIdentityAttribute()->getName();
            $vars['id'] = $results[0]->$idAttr;
          }
          unset($vars['aliasfield']);
        }
        else {
          if ($options['useId']) {
            $vars['id'] = $segments[(count($segments) - 1)];
          }
        }
//      echo '<pre>';
//      print_r($vars);
//      echo '</pre>';
        return $vars;
      }

      /**
       * Since we have no clear routing, we can only work on the standard interpretation. Only change is that we
       * can use the current menu's scheme if that is a com_one menu item.
       */

      $menu_options = array();
      if (($menu = JSite::getMenu()->getActive()) && $menu->component == 'com_one') {
        $menuscheme = $menu->query['scheme'];
        array_unshift($segments, $menuscheme);
      }

      $vars  = array();
      $count = count($segments);

      switch ($count) {
        case 4 :
          // format : SCHEME / TASK / VIEW / ID
          $vars['scheme'] = str_replace('-', ':', $segments[$count - 4]);
          $vars['task']   = $segments[$count - 3];
          $vars['view']   = $segments[$count - 2];
          $parts          = explode(',', $segments[$count - 1]);
          $vars['id']     = $parts[(count($parts) - 1)];
          break;

        case 3 :
          // format : SCHEME / detail / ID
          // format : SCHEME / list / VIEW
          // format : SCHEME / calendar* / VIEW
          // format : SCHEME / view / VIEW
          $vars['scheme'] = str_replace('-', ':', $segments[$count - 3]);
          $vars['task']   = $segments[$count - 2];

          $parts = explode(',', $segments[$count - 1]);
          if (preg_match('/calendar(Day|Month|Week)?/', $vars['task']) > 0) {
            $vars['view'] = $segments[$count - 1];
          }
          else {
            if (count($parts) > 1) {
              if ($vars['task'] == 'detail' && preg_match('/^(month|week|day)$/', $params->get('view')) > 0) {
                $vars['view'] = 'detail';
              }

              $parts      = explode(',', $segments[$count - 1]);
              $vars['id'] = $parts[(count($parts) - 1)];
            }
            else {
              if ($vars['task'] == 'detail') {
                $vars['view'] = 'detail';
                $vars['id']   = $parts[(count($parts) - 1)];
              }
              else {
                if ($vars['task'] == 'edit') {
                  $vars['view'] = $parts[(count($parts) - 1)];
                  $vars['id']   = 0;
                }
                else {
                  $vars['view'] = $segments[$count - 1];
                }
              }
            }
          }
          break;

        case 2 :
          $vars['scheme'] = str_replace('-', ':', $segments[$count - 2]);
          $vars['task']   = $segments[$count - 1];
          break;

        case 1 :
          $vars['scheme'] = str_replace('-', ':', $segments[$count - 1]);
          $vars['task']   = 'list';
          break;

        default :
      }

      return $vars;
    }
  }
