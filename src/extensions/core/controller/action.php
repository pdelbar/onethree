<?php
/**
 * One_Controller_Action is the abstract base class for all actions. The One_Controller first tries to handle a task locally,
 * but if no handler is defined inside the controller, it defers to first a scheme-specific and then a generic
 * One_Controller_Action subcontroller. This effectively makes it easier to define generic actions in a flexible way.
 *
 * ONEDISCLAIMER
 **/
abstract class One_Controller_Action
{
	/**
	 * @var One_Controller The controller using the current action
	 */
	protected $controller;

	/**
	 * @var array Any additional options passed to the current action
	 */
	protected $options = array();

	/**
	 * @var One_View The One_View instance used for the current action
	 */
	protected $view;

	/**
	 * @var One_Scheme The One_Scheme used for the current action
	 */
	protected $scheme;

	/**
	 * Class constructor
	 *
	 * @param One_Controller $controller
	 * @param array $options
	 */
	public function __construct(One_Controller $controller, array $options = array())
	{
		$this->controller = $controller;
		$this->options    = $options;

		if(!($this->scheme instanceof One_Scheme)) {
			$this->scheme = One_Repository::getScheme($this->options['scheme']);
		}
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
		if(array_key_exists($key, $this->options) && !is_null($this->options[$key])) {
			return $this->options[$key];
		}
		else {
			return $default;
		}
	}

	/**
	 * Set the One_View for the current action
	 *
	 * @param One_View $view
	 */
	public function setView($view)
	{
		$this->view = $view;
	}

	/**
	 * Returns the controller for the current action
	 *
	 * @return One_Controller
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * Execute the actions needed for the current action
	 * This method should be overridden in the subclasses.
	 */
	public function execute(){}

	/**
	 * Processes any possibly given filters and alters the One_Query object accordingly.
   *
   * The filter option provided by eg. the com_one menu item is a list of filters, comma-separated, to apply.
   * The extraParameters are passed to the filters
	 *
	 * @param One_Query $query
	 * @param array $filters
	 */
  protected function processQueryConditions(One_Query $query)
  {
    $filterString = $this->getVariable('filters', '');
    if ($filterString) {
      $filters = explode(',',$filterString);
      $filterParameters = parse_ini_string($this->getVariable('filterParameters', ','));

      foreach ($filters as $filterName) {
        $filter = One_Repository::getFilter($filterName, $query->getScheme()->getName(), $filterParameters);
        $filter->affect($query);
      }
    }
  }

    /**
     * Replace redirect variables if needed
     * @param array $redirect
     * @return array
     */
    protected function replaceOtherVariables(array $redirect = array())
    {
        foreach($redirect as $key => $value)
        {
            if(preg_match('/^\:\:([^\:]+)\:\:$/', trim($value), $matches) > 0)
            {
                if(array_key_exists($matches[1], $this->options)) {
                    $redirect[$key] = $this->options[$matches[1]];
                }
                elseif(array_key_exists('oneForm', $this->options) && array_key_exists($matches[1], $this->options['oneForm'])) {
                	$redirect[$key] = $this->options['oneForm'][$matches[1]];
                }
                else {
                	$redirect[$key] = '';
                }
            }
        }

        return $redirect;
    }

}
