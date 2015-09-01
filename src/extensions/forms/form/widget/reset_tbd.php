<?php
/**
 * Handles the reset widget
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/

Class One_Form_Widget_Reset extends One_Form_Widget_Scalar_Textfield
{
	/**
	 * @var string The value of the reset button
	 */
	protected $_value = 'Reset';

	/**
	 * Class constructor
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $label
	 * @param array $config
	 */
	public function __construct( $id = NULL, $name = '', $value = NULL, $label = NULL, array $config = array() )
	{
		parent::__construct($id, $name, $value, $label, $config );
		$this->_type = 'reset';
		$this->_totf = 'reset';
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
	 * Is the submitted value valid?
	 *
	 * @return boolean
	 */
	public function validate()
	{
		return true;
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
