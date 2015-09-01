<?php
/**
 * Handles the button widget
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Widget_Button extends One_Form_Widget_Scalar_Textfield
{
	/**
	 * @var string The value of the button
	 */
	protected $_value = 'Button';

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
		$this->_type = 'button';
		$this->_totf = 'button';
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
							'disabled' => 1,
							'size' => 1
							);
		return array_merge(parent::allowedOptions(), $additional);
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
	 * Render the output of the widget and add it to the DOM
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 */
	protected function _render( $model, One_Dom $dom )
	{
		// collect required data
		$id    = $this->getID();
		$name  = $this->getFormName();
		$value = $this->getValue( $model );

		if ($value == null) $value = $this->getDefault();

		$info    = $this->getCfg('info');
		$error   = $this->getCfg('error');

		$this->setCfg('class', 'OneFieldInput ' . $this->getCfg('class'));

		$events = $this->getEventsAsString();
		$params = $this->getParametersAsString();

		// show the input
		$dom->add('<input type="'.$this->_totf.'" id="' . $id . '" name="' . $name . '" value="' . $this->getLabel() . '"' . $events . $params . ' />'."\n");

		// extra info
		if(!is_null($info))
			$dom->add('<span id="' . $id . 'Info" class="OneInfo">' . $info . '</span>');

		// error message
		if(!is_null($error))
			$dom->add('<span id="' . $id . 'Error" class="OneError">' . $error . '</span>');
	}
}
