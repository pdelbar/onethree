<?php
/**
 * Handles the textarea widget
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Widget_Scalar_Textarea extends One_Form_Widget_Scalar
{
	/**
	 * Class constructor
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $label
	 * @param array $config
	 */
	public function __construct( $id = NULL, $name = '', $label = NULL, $config = array())
	{
		parent::__construct( $id, $name, $label, $config );
		$this->_type = 'textarea';
	}

	/**
	 * Return the allowed options for this widget
	 *
	 * @return array
	 */
	protected static function allowedOptions()
	{
		$additional = array(
							'cols' => 1,
							'rows' => 1,
							'dir' => 1,
							'lang' => 1,
							'xml:lang' => 1,
							'tabindex' => 1,
							'accesskey' => 1,
							'disabled' => 1,
							'readonly' => 1
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
						'onselect',
						'onchange',
						'onclick',
						'ondblclick',
						'onmousedown',
						'onmouseup',
						'onmouseover',
						'onmousemove',
						'onmouseout',
						'onkeypress',
						'onkeydown',
						'onkeyup'
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
		$this->setCfg('class', 'OneFieldTextarea ' . $this->getCfg('class'));
		$data = array(
						'id' => $this->getID(),
						'name' => $this->getFormName(),
						'events' => $this->getEventsAsString(),
						'params' => $this->getParametersAsString(),
						'value' => ( is_null( $this->getValue( $model ) ) ? $this->getDefault() : $this->getValue( $model ) ),
						'info' => $this->getCfg('info'),
						'error' => $this->getCfg('error'),
						'class' => $this->getCfg('class'),
						'required' => (($this->isRequired()) ? ' *' : ''),
						'label' => $this->getLabel(),
						'lblLast' => $this->getCfg('lblLast')
					);

		$dom = $this->parse( $model, $data );
		$d->addDom( $dom );

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
