<?php

  /**
   * This class controls the actions being performed at the frontend of one|content
   *
   * ONEDISCLAIMER
   **/
  class One_Controller implements One_Controller_Interface
  {
    /**
     * @var array Options passed along to the controller
     */
    public $options;

    /**
     * @var One_View The view used for the current action
     */
    public $view;

    /**
     * @var string Name of the current task
     */
    public $task;

    /**
     * @var One_Scheme Scheme used for the current task
     */
    public $scheme;

    /**
     * @var mixed One_Model or array of One_Models
     */
    public $model;

    /**
     * @var array Array of redirect parameters
     */
    protected $_redirect = NULL;

    /**
     * Class constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
      $this->options = $options;
    }

    /**
     * Gets the variable passed to the method out of the context.
     * If the variable is not set, return the default value.
     *
     * @param string $key The required variable
     * @param mixed $default The default value that should be returned in case the required variable is not set
     * @return mixed
     */
    protected function getVariable($key, $default = NULL)
    {
      $cx    = new One_Context($this->options);
      $value = $cx->get($key);

      if (is_null($value)) {
        // TR20090604 Added the following checks in case that $this->options is an object
        if (is_array($this->options)) {
          $value = $this->options[$key];
        }
        else {
          if (is_object($this->options)) {
            $value = $this->options->options[$key];
          }
        }
      }
      return (is_null($value)) ? $default : $value;
    }

    /**
     * Set the One_View for the current action
     *
     * @param One_View $view
     */
    public function setView(One_View $view)
    {
      $this->view = $view;
    }

    /**
     * This method searches for the task that needs to be performed and executes it when found
     *
     * @param string $task
     * @param array $options
     * @return string result of the action
     */
    public function execute($task, $options = array())
    {
      if (method_exists(get_class($this), 'execute_' . ucfirst($task))) {
        $method = 'execute_' . ucfirst($task);
        return $this->$method($options);
      }
      else {
        if (class_exists('One_Action_Scheme_' . ucfirst($options['scheme']) . '_' . ucfirst($task))) {
          $className = 'One_Action_Scheme_' . ucfirst($options['scheme']) . '_' . ucfirst($task);
          $action    = new $className($this, $options);
          return $action->execute();
        }
        else {
          if (class_exists('One_Action_' . ucfirst($task))) {
            $className = 'One_Action_' . ucfirst($task);
            $action    = new $className($this, $options);
            return $action->execute();
          }
          else {
            throw new One_Exception('TASK_DOESNT_EXIST');
          }
        }
      }
    }

    /**
     * Returns whether the user is allowed to perform this task
     *
     * @return boolean
     */
    public function authorize()
    {
      return true;
    }

    /**
     * Replace redirect variables if needed. The pattern is ::param:: where param is one of the keys in the options
     * array.
     *
     * @param array $redirect
     */
    protected function replaceRedirectVariables(array $redirect = array())
    {
      foreach ($redirect as $key => $value) {
        if (preg_match('/^\:\:([^\:]+)\:\:$/', trim($value), $matches) > 0) {
          if (array_key_exists($matches[1], $this->options)) {
            $redirect[$key] = $this->options[$matches[1]];
          }
        }
      }

      return $redirect;
    }

    /**
     * Set the redirect options
     *
     * @param array $options
     */
    public function setRedirect($options = array())
    {
      if (!is_array($options)) {
        $this->_redirect = array();
      }
      else {
        $this->_redirect = $options;
      }
    }

    /**
     * Get the redirect options
     *
     * @return array
     */
    public function getRedirect()
    {
      return $this->_redirect;
    }
  }
