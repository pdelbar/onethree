<?php
/**
 * Handles the document widget for Joomla! that looks for documents
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Widget_Joomla_Document extends One_Form_Widget_Abstract
{
	/**
	 * @var array Extra options
	 */
	private $_options = array(); //TODO does this need to be here?

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
		$this->_type = 'document';
	}

	/**
	 * Return the allowed options for this widget
	 *
	 * @return array
	 */
	protected static function allowedOptions()
	{
		$additional = array(
							'size' => 1,
							'path' => 2
							);
		return array_merge(parent::allowedOptions(), $additional);
	}

	/**
	 * Render the output of the widget and add it to the DOM
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 */
	protected function _render( $model, One_Dom $d )
	{
		JHTML::_('behavior.modal');
		JHTML::_('behavior.modal', 'a.modal-button');

		$this->setCfg('class', 'OneFieldTextfield ' . $this->getCfg('class'));
		$path = preg_replace( '{/|\\\}', DIRECTORY_SEPARATOR, trim( $this->getCfg('path') ) );
		if( substr( $path, -1, 1 ) != DIRECTORY_SEPARATOR )
			$path .= DIRECTORY_SEPARATOR;

		$data = array(
						'id' => $this->getID(),
						'name' => $this->getFormName(),
						'events' => $this->getEventsAsString(),
						'params' => $this->getParametersAsString(),
						'required' => (($this->isRequired()) ? ' *' : ''),
						'value' => ((is_null($this->getValue($model))) ? $this->getDefault() : $this->getValue($model)),
						'path' => $path,
						'info' => $this->getCfg('info'),
						'error' => $this->getCfg('error'),
						'class' => $this->getCfg('class'),
						'label' => $this->getLabel(),
						'lblLast' => $this->getCfg('lblLast'),
						'oneUrl' => One_Config::getInstance()->getUrl()
					);

		$dom = $this->parse($model, $data);
		$d->addDom($dom);
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
		//TODO does this need to be here?
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
}
