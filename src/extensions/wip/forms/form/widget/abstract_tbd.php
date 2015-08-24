<?php
/**
 * Widgets are codeblocks that lets you send values in a form
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 * @abstract
 **/
abstract class One_Form_Widget_Abstract
{
	/**
	 * @var string ID of the widget
	 */
	protected $_id;

	/**
	 * @var string Name of the widget
	 */
	protected $_name;

	/**
	 * @var string Original name of the widget
	 */
	protected $_originalName;

	/**
	 * @var string label to show with the widget
	 */
	protected $_label;

	/**
	 * @var string Type of the widget
	 */
	protected $_type;

	/**
	 * @var mixed Default value of the widget
	 */
	protected $_default;

	/**
	 * @var array List of constraints for the widget
	 */
	protected $_constraint;

	/**
	 * @var array List of configuration settings
	 */
	protected $config = array();

	/**
	 * @var array List of events for the widget
	 */
	protected $events = array();

	/**
	 * Render the output of the widget and add it to the DOM
	 *
	 * @param One_Model $model
	 * @param One_Dom $dom
	 * @final
	 */
	final public function render($model, One_Dom $dom)
	{
		$id = $this->getID();

		$condition = $this->getCfg('condition');
		if(!is_null($condition))
		{
			$args = array(
							'model' => $model
						);

			if(!One_Form_Factory::authorize($condition, $args))
				return;
		}

		$this->_render($model, $dom);
	}

	/**
	 * Get the value for the widget out of the request
	 *
	 * @return mixed
	 */
	public function requestValue()
	{
		$x = NULL;

		$from = $_REQUEST;
		if(!in_array($this->getCfg('one'), array('one', 'yes', 'true', '1'))) {
			$from = $_REQUEST['oneForm'];
		}

		if(isset($from[$this->_name])) {
			$x = $from[$this->_name];
		}

		$return = $x;
		if(ini_get('magic_quotes_gpc'))
		{
			$return = $this->recursiveStripSlashes($x);
//			if(!is_array($x)) {
//				$return = stripslashes($x);
//			}
//			else
//			{
//				foreach($x as $key => $val)
//				{
//					$x[$key] = stripslashes($val);
//				}
//
//				$return = $x;
//			}
		}
		return $return;
	}

	protected function recursiveStripSlashes($var)
	{
		if(!is_array($var)) {
			$return = stripslashes($var);
		}
		else
		{
			foreach($var as $key => $val)
			{
				if(is_array($val)) {
					$var[$key] = $this->recursiveStripSlashes($val);
				}
				else {
					$var[$key] = stripslashes($val);
				}
			}

			$return = $var;
		}

		return $return;
	}

	/**
	 * Bind the model to the widget
	 *
	 * @param One_Model $model
	 */
	public function bindModel($model)
	{
		$value = $this->requestValue();	// bad name
		$attributeName = $this->_name;

		// When the attributeName starts with 'r__', we're not saving an attribute but a relation
		if(preg_match('/^r__(.*)_(((?!._).)*)$/iU', $attributeName, $tmp))
		{
			$relName = $tmp[1];
			$relRole = $tmp[2];
			$relValue = $value;

			$model->setRelated($relRole, $relValue);
		}
		else
			$model->$attributeName = $value;
	}

	/**
	 * Class constructor
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $label
	 * @param array $config
	 */
	public function __construct($id, $name = '', $label = NULL, $config = array())
	{
		$this->setID($id);
		$this->setName($name);
		$this->setLabel($label);

		$this->_constraint = new One_Form_Constraint($this);

		if (is_array($config)) foreach($config as $name => $val)
		{
			if(in_array($name, self::allowedEvents()))
				$this->addEvent($name, $val);
			else
				$this->setCfg($name, $val);
		}
	}

	//
	/**
	 * Does this widget render HTML?
	 * Used to override in hidden widgets to avoid conainering them
	 *
	 * @return boolean
	 */
	public function doesRenderHTML()
	{
		return true;
	}

	/**
	 * Is the submitted value valid?
	 *
	 * @return boolean
	 */
	public function validate()
	{
		$checkedOut = true;
		if($this->isRequired() && (is_null($this->requestValue()) || trim($this->requestValue()) == ''))
		{
			if(!is_null($this->config['requiredlabel']))
				$requiredlabel = $this->config['requiredlabel'];
			else
				$requiredlabel = '{script}{@ formerrors:REQUIRED}{endscript}';

			$checkedOut = false;
			$this->_constraint->addError($requiredlabel);
		}
		else
		{
			$checkedOut = $this->_constraint->checkConstraints();
		}

		if(!$checkedOut) {
			$session = One_Repository::getSession();
			$session->set($this->getID(), $this->getErrors(), 'OneWidgetFormErrors');
		}
		return $checkedOut;
	}

	/**
	 * Get the errors that occured
	 *
	 * @return array
	 */
	public function getErrors()
	{
		return $this->_constraint->getErrors();
	}

	/**
	 * Returns all Options a Widget can have.
	 * Uses bitwise comparison to determine how to use it:
	 * 1: use in render
	 * 2: use to define the way to render
	 * 4: used to set a value in the class
	 *
	 * Add the Numbers to eachother to make combinations
	 *
	 * @return array
	 */
	protected static function allowedOptions()
	{
		return array(
						'type' => 4,
						'constraint' => 4,
						'class' => 1,
						'title' => 1,
						'style' => 1,
						'info' => 2,
						'error' => 2,
						'lblLast' => 2,
						'inContainer' => 2,
						'excludeInfo' => 2,
						'excludeError' => 2,
						'useContainer' => 2,
						'default' => 2,
						'required' => 6,
						'requiredlabel' => 6,
						'role' => 4,
						'targetAttribute' => 4,
						'novalue' => 2,
						'noLabel' => 2,
						'one' => 4,
						//PD16FEB10
						'from' => 2,
						'optionsFrom' => 4,
						'cacheOptions' => 4,
						'language' => 2
					);
	}

	/**
	 * Return the allowed events for this widget
	 *
	 * @return array
	 */
	protected static function allowedEvents()
	{
		return array('tabindex',
						'accesskey',
						'onfocus',
						'onblur',
						'onselect',
						'onchange',
						'onclick',
						'ondblclick',
						'onmousedown',
						'onmouseup',
						'onmouseover',
						'onmousemove',
						'onmouseout',
						'onkeypress',
						'onkeydown',
						'onkeyup'
					);
	}

	/**
	 * Add an event to the widget
	 *
	 * @param string $event
	 * @param string $todo
	 */
	public function addEvent($event, $todo)
	{
		if(!in_array($event, $this->allowedEvents()))
			throw new One_Exception('The event "' . $event . '" does not exist for ' . $this->getID());
		else
			$this->events[$event] = $todo;
	}

	/**
	 * Is this a required field?
	 *
	 * @return boolean
	 */
	public function isRequired()
	{
		if($this->getCfg('required'))
			return true;
		else
			return false;
	}

	/**
	 * Get the type of this widget
	 *
	 * @return string
	 */
	public function getType()
	{
		switch($this->getCfg('type'))
		{
			case 'int':
				return 'int';
			case 'decimal':
				return 'decimal';
			case 'char':
				return 'char';
			case 'date':
				return 'date';
			case 'time':
				return 'time';
			case 'datetime':
				return 'datetime';
			case 'boolean':
				return 'boolean';
			case 'string':
			default:
				return 'string';
		}
	}

	/**
	 * Get the widget type
	 *
	 * @return string
	 */
	public function getWidgetType()
	{
		return $this->_type;
	}

	/**
	 * Get the ID of this widget
	 *
	 * @return string
	 */
	public function getID()
	{
		return $this->_id;
	}

	/**
	 * Get the name of this widget
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Get the original name of this widget
	 *
	 * @return string
	 */
	public function getOriginalName()
	{
		return $this->_originalName;
	}

	/**
	 * Get the name used for this widget in the form itself.
	 * This is to create the possibility of adding the element to the formArray
	 */
	public function getFormName()
	{
		$name = $this->getName();
		if(!in_array($this->getCfg('one'), array('one', 'yes', 'true', '1'))) {
			$name = 'oneForm['.$this->getName().']';
		}
		return $name;
	}

	/**
	 * Get the label for this widget
	 *
	 * @return string
	 */
	public function getLabel()
	{
		return ($this->_label ? $this->_label : null);
	}

	/**
	 * Get the default value of this widget
	 *
	 * @return string
	 */
	public function getDefault()
	{
		$default = $this->_default;
		if(is_null($default))
			$default = $this->getCfg('default');

		return $default;
	}

	/**
	 * Get the value
	 *
	 * @param One_Model $model
	 * @return Mixed
	 */
	public function getValue($model)
	{
		if (!$model) return null;

		// pick up vars entered entered from an invalidly filled in form
		$session = One_Repository::getSession();
		$posted = $session->get('posted', 'OneFormErrors');
		if(!in_array($this->getCfg('one'), array('one', 'yes', 'true', '1')) && isset($posted['oneForm'][$this->getName()])) {
			return $posted['oneForm'][$this->getName()];
		}
		else if(isset($posted[$this->getName()])) {
			return $posted[$this->getName()];
		}
		else
		{
			$role = $this->getCfg('role');

			if(!is_null($role) && $model instanceof One_Model)
			{
				$parts = explode(':', $role);
				$related = $model->getRelated($parts[1]);
				$relation = One_Repository::getRelation($parts[0]);
				$role = $relation->getRole($parts[1]);
				$relScheme = One_Repository::getScheme($role->schemeName);

				if ($related instanceof One_Model)
					return $related[$relScheme->getIdentityAttribute()->getName()];
				elseif (is_array($related))
				{
					// $related is an array of One_Models
					$returnValues = array();

					foreach ($related as $r)
						$returnValues[] = $r[$relScheme->getIdentityAttribute()->getName()];

					return $returnValues;
				}
			}
			else
			{
				//PD16FEB10 : add option to pick this up from request

				if ($this->getCfg('from') != "") {
					list($source, $key) = preg_split("/:/", $this->getCfg('from'));
					switch ($source) {
						case 'request' :
							$from = $_REQUEST;
							if(!in_array($this->getCfg('one'), array('one', 'yes', 'true', '1'))) {
								$from = $_REQUEST['oneForm'];
							}
							if (isset($from[$key])) return $from[$key];
							break;
					}
				}

				if($this->getCfg('novalue') != "novalue")
					return $model[$this->getOriginalName()];
			}
		}
	}

	/**
	 * Parse the output of the widget with nanoscript
	 *
	 * @param One_Model $model
	 * @param array $data
	 */
	protected function parse($model, $data = array())
	{
		// general Dataset
		$data['excludeError'] = (in_array($this->getCfg('excludeError'), array('true', 'yes', '1', 'exclude', 'excludeError'))) ? 1 : 0;

    // determine if we need to look for the template-file in a subfolder of widget
		$current = str_replace('One_Form_Widget_', '', get_class($this));
		$parts   = preg_split('/_/', strtolower($current));
		array_pop($parts);
		$wtype = implode(DIRECTORY_SEPARATOR, $parts);

    $formChrome = One_Config::get('form.chrome','');

    $pattern = "%ROOT%/views/"
            . "{%APP%,default}/"
            . "oneform/"
            . ($formChrome ? "{".$formChrome . '/,}' : '')
            . "widget/"
            . ( $wtype ? "{" . $wtype . "/,}" : '')
            . "{%LANG%/,}";
    One_Script_Factory::pushSearchPath( $pattern );

    $script = new One_Script();
    $script->load($this->_type . '.html');
    if ($script->error) {
      One_Script_Factory::popSearchPath();
      throw new One_Exception('Error loading template for widget ' . $this->_type . ' : ' . $script->error);
    }

		$dom = One_Repository::getDom();
    $dom->add($script->execute($data));



		return $dom;
	}

	/**
	 * Get the specified configuration setting
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getCfg($name)
	{
		if(!array_key_exists($name, $this->allowedOptions()) && $name != 'condition') {
//			throw new One_Exception('The option \'' . $name . '\' does not exist for ' . $this->getID());
			return NULL; // safer to return NULL
		}
		else if(isset($this->config[$name])) {
			return $this->config[$name];
		}
		else {
			return NULL;
		}
	}

	/**
	 * Get all the events of the widget
	 *
	 * @return array
	 */
	public function getEvents()
	{
		return $this->events;
	}

	/**
	 * Get all events of the widget as a string
	 * @return unknown_type
	 */
	public function getEventsAsString()
	{
		$events = '';
		foreach($this->getEvents() as $event => $todo)
		{
			$events .= ' ' . $event . '="' . $todo . '"';
		}

		return trim($events);
	}

	/**
	 * Get all configuration parameters of the widget
	 *
	 * @return array
	 */
	public function getParameters()
	{
		return $this->config;
	}

	/**
	 * Get the specified parameter of the widget
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getParameter($key)
	{
		return $this->config[$key];
	}

	/**
	 * Get all parameters as a string
	 *
	 * @return string
	 */
	public function getParametersAsString()
	{
		$params  = '';
		$allowed = $this->allowedOptions();

		foreach($this->getParameters() as $param => $value)
		{
			if($allowed[$param] & 1)
			{
				switch($param)
				{
					case 'readonly':
							$params .= ' readonly="readonly"';
							break;
					case 'disabled':
							$params .= ' disabled="disabled"';
							break;
					case 'selected':
							$params .= ' selected="selected"';
							break;
					default:
							$params .= ' ' . $param . '="' . $value . '"';
							break;
				}
			}
		}

		return $params;
	}

	/**
	 * Add a constraint to the widget
	 *
	 * @param string $type
	 * @param mixed $value
	 * @param string $error
	 */
	public function addConstraint($type, $value, $error)
	{
		$this->_constraint->addConstraint($type, $value, $error);
	}

	/**
	 * Set the ID of this widget
	 *
	 * @return string
	 */
	public function setID($id)
	{
		$this->_id = One_Form_Helper::mangle(trim($id));
	}

	/**
	 * Set the name of this widget
	 *
	 * @return string
	 */
	public function setName($name)
	{
		$this->_originalName = $name;
		$this->_name = One_Form_Helper::mangle(trim($name));
	}

	/**
	 * Set the label for this widget
	 *
	 * @return string
	 */
	public function setLabel($label)
	{
		$this->_label = $label;
	}

	/**
	 * Set the default value for this widget
	 *
	 * @return string
	 */
	public function setDefault($default)
	{
		$this->_default = trim($default);
	}


	public function setRequiredLabel(){

	}

	/**
	 * Set a configuration parameter
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function setCfg($name, $value)
	{
		if(!array_key_exists($name, $this->allowedOptions()) && $name != 'condition')
			return false;
		else
			$this->config[$name] = $value;
	}

}
