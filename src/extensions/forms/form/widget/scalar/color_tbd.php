<?php
/**
 * Handles the colorpicker widget
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Widget_Scalar_Color extends One_Form_Widget_Scalar
{
	/**
	 * @var boolean Is the current value a color
	 */
	private $isColor = true;

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
		$this->_type = 'color';
	}

	/**
	 * Render the output of the widget and add it to the DOM
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 */
	protected function _render( $model, One_Dom $d )
	{
		$this->setCfg('class', 'OneFieldInput ' . $this->getCfg('class'));
		$data = array(
						'id' => $this->getID(),
						'name' => $this->getFormName(),
						'value' => ( is_null( $this->getValue( $model ) ) ? $this->getDefault() : $this->getValue( $model ) ),
						'info' => $this->getCfg('info'),
						'error' => $this->getCfg('error'),
						'class' => $this->getCfg('class'),
						'required' => (($this->isRequired()) ? ' *' : ''),
						'label' => $this->getLabel(),
						'lblLast' => $this->getCfg('lblLast'),
						'One::getInstance()->getUrl()' => One_Config::getInstance()->getUrl()
					);

		$dom = One_Repository::getDom();
		$dom->add( '<script type="text/javascript" src="'.One_Vendor::getInstance()->getSitePath().'/js/ColorPicker2.js"></script>', '_head' );
		$dom->add( '<script type="text/javascript">
	      var cp = new ColorPicker( "window" );
	    </script>', '_head' );

		$content = $this->parse( $model, $data );

		$d->addDom( $dom );
		$d->addDom( $content );
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
	 * Is the submitted value a valid color?
	 *
	 * @return boolean
	 */
	public function validate()
	{
		$pValidated = parent::validate();
		$this->isColor = ( preg_match( '/^[a-f0-9]{6}$/i', trim( $_REQUEST[ $this->getName() ] ) ) || trim( $_REQUEST[ $this->getName() ] ) == '' );

		return ( $pValidated && $this->isColor );
	}

	/**
	 * Get all possible errors
	 *
	 * @return array
	 */
	public function getErrors()
	{
		$errors = parent::getErrors();

		if( !$this->isColor )
			$errors = array_merge( $errors, array( 'color' => $this->getLabel() . ' is no color' ) );

		return $errors;
	}
}
