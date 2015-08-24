<?php
/**
 * Abstract parent class of all OneFormContainers
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 * @abstract
 **/
abstract Class One_Form_Container_Abstract
{
	/**
	 * @var string ID of the container
	 */
	protected $_id;

	/**
	 * @var string Type of the container
	 */
	protected $_type;

	/**
	 * @var array List of containers and widgets contained in the container
	 */
	protected $content = array();

	/**
	 * @var array List of configuration settings of the container
	 */
	protected $config = array();

	/**
	 * @var array List of events linked to the container
	 */
	protected $events = array();

	/**
	 * Render the output of the container
	 *
	 * @param One_Model $model
	 * @param One_Dom $dom
	 * @final
	 */
	final public function render( $model, One_Dom $dom )
	{
		$id = $this->getID();

		$condition = $this->getCfg( 'condition' );
		if( !is_null( $condition ) )
		{
			$args = array(
							'model' => $model
						);
			if( !One_Form_Factory::authorize( $condition, $args ) )
				return;
		}

		$this->_render( $model, $dom );
	}

	/**
	 * Bind the model to the container
	 *
	 * @param One_Model $model
	 */
	public function bindModel( $model )
	{
		if( $this->getContent() )
		{
			foreach( $this->getContent() as $c )
			{
				$c->bindModel( $model );
			}
		}
	}

	/**
	 * Class constructor
	 *
	 * @param string $id
	 * @param array $config
	 */
	public function __construct( $id, array $config = array())
	{
		$this->setID( $id );

		foreach($config as $name => $val)
		{
			$this->setCfg($name, $val);
		}
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
						'class' => 1,
						'style' => 1,
						'language' => 2
					);
	}

	/**
	 * Add a OneFormWidget to the container
	 *
	 * @param One_Form_Widget_Abstract $widget
	 */
	public function addWidget( One_Form_Widget_Abstract &$widget )
	{
		if(!array_key_exists($widget->getID(), $this->getContent()))
			$this->content[$widget->getID()] = $widget;
		else
			throw new Exception( "A widget is already present with this ID:" . $widget->getID() );
	}

	/**
	 * Does the container have a widget with the specified ID
	 *
	 * @param string $id
	 * @return boolean
	 */
	public function hasWidget( $id )
	{
		$hasIt = $this->lookForWidget( $id, $this->getContent() );
		return $hasIt;
	}

	/**
	 * Look if a widget is present in a container
	 *
	 * @param string $id
	 * @param One_Form_Container_Abstract $context
	 * @return boolean
	 */
	private function lookForWidget( $id, $context )
	{
		foreach( $context as $key => $value)
		{
			if( $id === $key && $value instanceof One_Form_Widget_Abstract )
				return true;
			if( $value instanceof One_Form_Container_Abstract )
			{
				if( $this->lookForWidget( $id, $value->getContent() ) )
					return true;
			}
		}

		return false;
	}

	/**
	 * Add a container to the container
	 *
	 * @param $container
	 * @return unknown_type
	 */
	public function addContainer( One_Form_Container_Abstract &$container )
	{
		$this->content[] = $container;
	}

	/**
	 * Add HTML to the container
	 *
	 * @param string $html
	 */
	public function addHTML( $html )
	{
		// TODO create this function
	}

	/**
	 * Get all the content in the container
	 *
	 * @return array
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Get all the events in the container
	 *
	 * @return array
	 */
	public function getEvents()
	{
		return $this->events;
	}

	/**
	 * Get all the events as a string
	 *
	 * @return string
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
	 * Get all the configurations
	 *
	 * @return array
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * Get all parameters as a string
	 *
	 * @return array
	 */
	public function getParametersAsString()
	{
		$params  = '';
		$allowed = $this->allowedOptions();

		foreach($this->getConfig() as $param => $value)
		{
			if($allowed[$param] & 1)
			{
				$params .= ' ' . $param . '="' . $value . '"';
			}
		}

		return $params;
	}

	/**
	 * Get the container's ID
	 *
	 * @return string
	 */
	public function getID()
	{
		return $this->_id;
	}

	/**
	 * Get the container's type
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->_type;
	}

	/**
	 * Get a specific configuration
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getCfg( $name )
	{
		if( !array_key_exists( $name, $this->allowedOptions()) && $name != 'condition' ) { // TR 20100224 "condition" configuration should be allowed no matter what, even after overrides
			throw new One_Exception( 'The option \'' . $name . '\' does not exist for ' . $this->id );
		}
		else if(isset($this->config[$name])) {
			return $this->config[$name];
		}
		else {
			return NULL;
		}
	}

	/**
	 * Set the container's ID
	 *
	 * @param string $id
	 */
	public function setID( $id )
	{
		$this->_id = $id;
	}

	/**
	 * Set a configuration option
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function setCfg( $name, $value )
	{
		if( !array_key_exists( $name , $this->allowedOptions()) && $name != 'condition' ) // TR 20100224 "condition" configuration should be allowed no matter what, even after overrides
		{
			//throw new Exception( 'The option \'' . $name . '\' does not exist for ' . $this );
		}
		else
			$this->config[$name] = $value;
	}
}
