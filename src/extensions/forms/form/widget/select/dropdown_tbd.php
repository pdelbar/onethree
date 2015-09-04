<?php
/**
 * Handles the select-dropdown widget
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Widget_Select_Dropdown extends One_Form_Widget_Abstract
{
	/**
	 * @var array Extra options
	 */
	private $_options = array();

	/**
	 * Class constructor
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $label
	 * @param array $config
	 * @param array $options
	 */
	public function __construct( $id = NULL, $name = '', $label = NULL, array $config = array(), array $options = array() )
	{
		parent::__construct($id, $name, $label, $config );
		$this->_type = 'dropdown';
		$this->setOptions($options);
	}

	/**
	 * Return the allowed options for this widget
	 *
	 * @return array
	 */
	protected static function allowedOptions()
	{
		$additional = array(
							'dir' => 1,
							'lang' => 1,
							'xml:lang' => 1,
							'accesskey' => 1,
							'tabindex' => 1,
							'disabled' => 1,
							'size' => 1,
							'multiple' => 1,
							'options' => 2,
							'showEmpty' => 2,
							'data-placeholder' => 1 // added for support of chosen jQuery plugin
							);
		return array_merge(parent::allowedOptions(), $additional);
	}

	/**
	 * Return the allowed events for this widget
	 *
	 * @return array
	 */
	protected static function allowedEvents()
	{
		return array(
						'onfocus',
						'onblur',
						'onchange'
					);
	}

	/**
	 * Render the output of the widget and add it to the DOM
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 */
	protected function _render( $model, One_Dom $d )
	{
		$rKeyvals   = $this->getCfg('options');

		if( trim( $rKeyvals ) != '' )
		{
			$rKeyvals = explode( ';', $rKeyvals );
			$keyvals = array();
			foreach( $rKeyvals as $val )
			{
				$tmp = explode( '=', $val );
				$keyvals[ $tmp[0] ] = $tmp[1];
			}

			$this->_options = $keyvals;
		}


		if( $this->getCfg('showEmpty') )
		{
			$tmp = array( '' => '---' );
			foreach( $this->_options as $key => $val )
			{
				$tmp[ $key ] = $val;
			}
			$this->_options = $tmp;
		}

		$this->setCfg('class', 'OneFieldDropdown ' . $this->getCfg('class'));
		$data = array(
						'id' => $this->getID(),
						'name' => $this->getFormName(),
						'events' => $this->getEventsAsString(),
						'params' => $this->getParametersAsString(),
						'required' => (($this->isRequired()) ? ' *' : ''),
						'value' => ((is_null($this->getValue($model))) ? $this->getDefault() : $this->getValue($model)),
						'options' => $this->_options,
						'info' => $this->getCfg('info'),
						'error' => $this->getCfg('error'),
						'class' => $this->getCfg('class'),
						'label' => $this->getLabel(),
						'lblLast' => $this->getCfg('lblLast')
					);

		$dom = $this->parse( $model, $data );
		$d->addDom( $dom );
	}

	/**
	 * Add an option
	 *
	 * @param array $option
	 */
	public function addOption( array $option )
	{
		// $option should be an array in the form {"key", "value"}
		$this->_options[$option[0]] = $option[1];
	}

	/**
	 * Overrides PHP's native __toString function
	 *
	 * @return string
	 */
	public function __toString()
	{
		return get_class() . ': ' . $this->getID();
	}

	/**
	 * Get all the options set for this widget
	 *
	 * @return array
	 */
	public function getOptions()
	{
		return $this->_options;
	}

	/**
	 * Set the options for this widget
	 *
	 * @param array $options
	 */
	public function setOptions( array $options )
	{
		$this->_options = $options;
	}

	/**
	 * Should the options be set when the widget is instantiated
	 *
	 * @return boolean
	 */
	public function setOptionsOnDetermination()
	{
		return true;
	}
}
