<?php
/**
 * Handles the multi-date widget
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Widget_Multi_Date extends One_Form_Widget_Abstract
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
		$this->_type = 'date';
	}

	/**
	 * Bind the model to the widget
	 *
	 * @param One_Model $model
	 */
	public function bindModel( $model )
	{
		$value = $this->requestValue();	// bad name
		if( is_null( $value ) ) $value = ''; // if == NULL, set to 0, because NULL won't be picked up by $model->__modified

		$attributeName = $this->_name;

		// When the attributeName starts with 'r__', we're not saving an attribute but a relation
		if(preg_match('/^r__(.*)_(((?!._).)*)$/iU', $attributeName, $tmp))
		{
			$relName = $tmp[1];
			$relRole = $tmp[2];
			$relValue = $value;

			$model->setRelated($relRole, $relValue);
		}
		else
			$model->$attributeName = $value;
	}

	/**
	 * Return the allowed options for this widget
	 *
	 * @return array
	 */
	protected static function allowedOptions()
	{
		$additional = array(
								'required' => 2,
								'start' => 2
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
		return array();
	}

	/**
	 * Render the output of the widget and add it to the DOM
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 */
	protected function _render( $model, One_Dom $d )
	{
		$start = date( 'Y-m' );
		if( !is_null( $this->getCfg( 'start' ) ) )
			$start = trim( $this->getCfg( 'start' ) );

		$data = array(
						'id' => $this->getID(),
						'name' => $this->getFormName(),
						'required' => (($this->isRequired()) ? ' *' : ''),
						'value' => ((is_null($this->getValue($model))) ? $this->getDefault() : $this->getValue($model)),
						'label' => $this->getLabel(),
						'lblLast' => $this->getCfg('lblLast'),
						'start' => $start
					);

		$dom = $this->parse( $model, $data );

		// add multidate.js
		$d->add('<script type="text/javascript" src="' . One_Config::getInstance()->getUrl() . 'lib/form/js/multidate.js"></script>'."\n", '_head');
		// define our multiDate JS object

		// onload create the calendar
		$script = 'multi' . ucfirst( $this->getID() ) . '.createCalendar();';
		$d->add( $script, '_onload' );

		$script = '<script type="text/javascript">var multi' . ucfirst( $this->getID() ) . ' = "";</script>';
		$d->add( $script );

		$d->addDom( $dom );

		$script = '<script type="text/javascript">var multi' . ucfirst( $this->getID() ) . ' = new MultiDate( 2010, 4, "OneCalendar' . ucfirst( $this->getID() ) . '", "' . $this->getID() . '" );</script>';
		$d->add( $script );
	}
}
