<?php
/**
 * Handles the hidden widget
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Widget_Scalar_Hidden extends One_Form_Widget_Scalar_Textfield
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
		$this->_type = 'hidden';
		$this->_totf = 'hidden';
	}

	/**
	 * Render the output of the widget and add it to the DOM
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 */
	protected function _render( $model, One_Dom $d )
	{
		$data = array(
						'id' => $this->getID(),
						'name' => $this->getFormName(),
						'totf' => $this->_totf,
						'events' => $this->getEventsAsString(),
						'params' => $this->getParametersAsString(),
						'value' => ( is_null( $this->getValue( $model ) ) ? $this->getDefault() : $this->getValue( $model ) )
					);

		$dom = $this->parse( $model, $data );
		$d->addDom( $dom );
	}
}
