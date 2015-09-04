<?php
/**
 * Handles the inline widget
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Widget_Inline extends One_Form_Widget_Abstract
{
	/**
	 * Class constructor
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $label
	 * @param array $config
	 */
	public function __construct( $id = NULL, $name = '', $label = NULL, $config = array() )
	{
		parent::__construct($id, $name, $label, $config );
		$this->_type = 'inline';
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
							'code' => 4
							);
		return array_merge( parent::allowedOptions(), $additional );
	}

	/**
	 * Render the output of the widget and add it to the DOM
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 */
	protected function _render( $model, One_Dom $dom )
	{
		$src = $this->getCfg( 'code' );
		$ns = new One_Script();

		if ($this->getID()) $ns->set('id', $this->getID());
		if ($this->getName()) $ns->set('name', $this->getName());
		if ($this->getLabel()) $ns->set('label', $this->getLabel());
		if ($this->getValue($model)) $ns->set('value', $this->getValue($model));
		$ns->set('model', $model);

		$s = $ns->executeString( $src );
		$dom->add( $s );

		$dom->add($this->value);

		One_Script_Factory::restoreSearchPath();
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
