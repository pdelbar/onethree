<?php

  /**
   * This class handles the list view of the chosen item
   *
   * ONEDISCLAIMER
   **/
  class One_Controller_Action_List extends One_Controller_Action
  {
    /**
     * Class constructor
     *
     * @param One_Controller $controller
     * @param array $options
     */
    public function __construct(One_Controller $controller, array $options = array())
    {
      parent::__construct($controller, $options);
      $this->scheme = $this->getVariable('scheme');
    }

    /**
     * This method returns the list view of the currently chosen item
     *
     * @return string The list view of the currently chosen item
     */
    public function execute()
    {
      $this->authorize();

      $type = $this->getVariable('type', 'html');

      switch (strtolower($type)) {
//  this should be handled by a REST call
//        case 'json':
//          $action = new One_Controller_Action_Json($this->controller, $this->options);
//          $action->execute();
//          break;


//  this was used for the old jqgrid widget
//        case 'jqgrid':
//          $action = new One_Controller_Action_Show($this->controller, $this->options);
//          $return = $action->execute();
//          break;

//  Most likely the standard jgrid used in the admin earlier
//        case 'jgrid':
//          $action = new One_Controller_Action_Jgrid($this->controller, $this->options);
//          $return = $action->execute();
//          break;

        case 'html':
        default:
          $results    = $this->getData();
          $view       = $this->getVariable('view', 'list');
          $this->view = new One_View($this->scheme, $view);
          $this->view->setAll($this->options);
          $this->view->setModel($results);
          $return = $this->view->show();
          break;
      }

      return $return;
    }

    /**
     * Get the list of instances for this action.
     *
     * @return mixed
     */
    protected function getData()
    {
      $schemeName = $this->scheme;
      if ($this->scheme instanceof One_Scheme) {
        $schemeName = $this->scheme->getName();
      }

      // First, create the query

      $factory = One_Repository::getFactory($schemeName);
      $query   = $factory->selectQuery($this->scheme);

      // Set the order if needed

      $orderField = $this->getVariable('order', '');
      if (!in_array(substr($orderField, -1, 1), array('-', '+'))) {
        if (in_array($this->getVariable('orderdirection', '+'), array('-', 'desc'))) {
          $orderField .= '-';
        }
        else {
          $orderField .= '+';
        }
      }
      $query->setOrder($orderField);

      // Set paging variables

      $start = $this->getVariable('start', 0);
      $cnt   = $this->getVariable('count', 0);
      $query->setLimit($cnt, $start);

      // Apply the filters

      $this->processQueryConditions($query);

      // Execute the query

      $results = $query->execute();

      return $results;
    }

    /**
     * Returns whether the user is allowed to perform this task
     *
     * @return boolean
     */
    public function authorize()
    {
      return One_Permission::authorize('list', $this->scheme, 0);
    }

    /**
     * Return the standard routing for this action.
     */
    public static function getStandardRouting($options)
    {
      return array('alias' => 'list/' . $options['view'], 'useId' => true);
    }
  }
