<?php
Class One_Form_Widget_Image extends One_Form_Widget_Scalar_Textfield
{
	public function __construct( $id = NULL, $name = '', $value = NULL, $label = NULL, array $config = array() )
	{
		parent::__construct($id, $name, $value, $label, $config );
		$this->_type = 'imagefield';
		$this->_totf = 'image';
	}

	protected static function allowedOptions()
	{
		$additional = array(
							'dir' => 1,
							'lang' => 1,
							'xml:lang' => 1,
							'disabled' => 1,
							'align' => 1,
							'alt' => 1,
							'src' => 1,
							'size' => 1
							);
		return array_merge(parent::allowedOptions(), $additional);
	}

	public function __toString()
	{
		return get_class() . ': ' . $this->getID();
	}

	/* Getters */

	/* Setters */
}
