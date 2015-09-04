<?php
/**
 * Handles the select-relational widget
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Widget_Select_Relational extends One_Form_Widget_Abstract
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
		$this->_type = 'relational';
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
		if( $this->getCfg('showEmpty') )
		{
			$keyvals = array();
			$keyvals[ '' ] = '---';
			foreach( $this->_options as $key => $val )
			{
				$keyvals[ $key ] = $val;
			}

			$this->_options = $keyvals;
		}

		$value   = ( ( count( $this->getValue( $model, $this->getCfg( 'role' ) ) ) == 0 ) ? $this->getDefault() : $this->getValue( $model, $this->getCfg( 'role' ) ) );

		if( !is_array( $value ) && !is_null( $value ) )
			$value = array( $value );
		else if( is_null( $value ) )
			$value = array();

		$this->setCfg('class', 'OneFieldDropdown ' . $this->getCfg('class'));
		$data = array(
						'id' => $this->getID(),
						'name' => $this->getFormName(),
						'events' => $this->getEventsAsString(),
						'params' => $this->getParametersAsString(),
						'required' => (($this->isRequired()) ? ' *' : ''),
						'value' => $value,
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
	 * Get the value for the widget
	 *
	 * @return mixed
	 */
	public function getValue( $model, $role )
	{
		$value = array();
		if( $model instanceof One_Model )
		{
			$parts    = explode( ':', $role );
			$relateds = $model->getRelated( $parts[1] ); // @FIXME zorgen voor 500

			if( !is_array( $relateds ) && !is_null( $relateds ) )
			{
				$idAttr = $relateds->getIdentityName();
				$value[] = $relateds->$idAttr;
			}
			elseif( is_array( $relateds ) )
			{
				$idAttr = NULL;
				foreach( $relateds as $related )
				{
					if( is_null( $idAttr ) )
						$idAttr = $relateds->getIdentityName();

					$value[] = $related->$idAttr;
				}
			}
		}

		return $value;
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
