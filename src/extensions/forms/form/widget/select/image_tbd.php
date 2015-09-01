<?php
/**
 * Handles the select-image widget
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Widget_Select_Image extends One_Form_Widget_Abstract
{
	/**
	 * @var array Extra options
	 */
	private $_options = NULL;

	/**
	 * Class constructor
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $label
	 * @param array $config
	 */
	public function __construct( $id = NULL, $name = '', $label = NULL, array $config = array() )
	{
		parent::__construct($id, $name, $label, $config );
		$this->_type = 'image';
	}

	/**
	 * Return the allowed options for this widget
	 *
	 * @return array
	 */
	protected static function allowedOptions()
	{
		$additional = array(
							'imgDir' => 4,
							'showEmpty' => 2
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
	 * Return the allowed extensions for this widget
	 *
	 * @return array
	 */
	protected static function allowedExtensions()
	{
		return array(
						'jpg',
						'jpeg',
						'gif',
						'png',
						'bmp'
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
			$this->_options = array_merge( array( '' => '---' ), $this->_options );

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
		if( is_null( $this->_options )  )
		{
			$this->_options = array();
			$dir = $this->getCfg('imgDir');

			if( !is_dir( $dir ) )
				$dir = '../' . $dir; // in case a relative path is given and this is requested in an administrator

			if( !is_dir( $dir ) )
			{
				throw new One_Exception( 'The directory "' . $this->getCfg('imgDir') . '" does not exist' );
				return false;
			}

			$files = scandir( $dir, 1 );

			$pattern = '/\.(' . implode( '|', OneFormWidgetSelectImage::allowedExtensions() ) . ')$/';

			foreach( $files as $key => $file )
			{
				if( preg_match( $pattern, $file) > 0 )
					$this->_options[ $file ] = $file;
			}
		}

		if( is_array( $this->_options ) )
			ksort( $this->_options, SORT_STRING );

		return $this->_options;
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
