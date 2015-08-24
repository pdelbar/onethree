<?php
/**
 * This widget lets you create children for a relation whilst in the original scheme
 * UNFINISHED, DO NOT USE
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Widget_Scalar_Child extends One_Form_Widget_Scalar
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
		throw new One_Exception_NotImplemented('Child widget is not yet implemented');
		parent::__construct($id, $name, $label, $config );
		$this->_type = 'child';
	}

	/**
	 * Return the allowed options for this widget
	 *
	 * @return array
	 */
	protected static function allowedOptions()
	{
		return array(
						'scheme' => 4,
						'language' => 2
					);
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
	protected function _render( $model, One_Dom $dom )
	{
		// collect required data
		$id    = $this->getID();
		$name  = $this->getName();
		$childScheme = $this->getCfg( 'scheme' );

		$dom->add( '<script src="' . One_Config::getInstance()->getUrl() . 'lib/libraries/js/jquery.js" type="text/javascript"></script>', '_head' );
		$dom->add( '<script type="text/javascript">jQuery.noConflict();</script>', 'head' );
		$dom->add( '<script src="' . One_Config::getInstance()->getUrl() . 'lib/libraries/js/jquery/jquery.color.js" type="text/javascript"></script>', '_head' );
		$dom->add( '<script src="' . One_Config::getInstance()->getUrl() . 'lib/libraries/js/jquery.simplemodal-1.3.3.js" type="text/javascript"></script>', '_head' );
		$dom->add( '<script src="' . One_Config::getInstance()->getUrl() . 'lib/libraries/js/managerToolbar.js" type="text/javascript"></script>', '_head' );
		$dom->add( '<link rel="stylesheet" href="' . One_Config::getInstance()->getUrl() . 'lib/libraries/js/toolbar.css" type="text/css" />', '_head' );
		$dom->add( '<link rel="stylesheet" href="' . One_Config::getInstance()->getUrl() . 'lib/libraries/js/toolbar.ie6.css" type="text/css" />', '_head' );

		$content = '<div class="childPopupPanel">
			<a href="#" onclick="jModal( \'' . $id . 'NsBox\', \'' . $childScheme . '\' ); return false;">Lookup</a>
			<div id="' . $id . 'NsBox"></div>
		</div>';

		$dom->add( $content );
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
	 * Get the value for the widget
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		return NULL;
	}
}
