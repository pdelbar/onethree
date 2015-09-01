<?php
/**
 * Handles a tabber container
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Container_Tabber extends One_Form_Container_Abstract
{
	/**
	 * Class constructor
	 *
	 * @param string $id
	 * @param array $config
	 */
	public function __construct($id, array $config = array())
	{
		parent::__construct( $id, $config );
	}

	/**
	 * Return the allowed options for this container
	 *
	 * @return array
	 */
	protected static function allowedOptions()
	{
		$additional =  array(
								'css' => 2
							);
		return array_merge( One_Form_Container_Abstract::allowedOptions(), $additional );
	}

	/**
	 * Return the allowed events for this container
	 *
	 * @return array
	 */
	protected static function allowedEvents()
	{
		return array();
	}

	/**
	 * Render the output of the container and add it to the DOM
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 */
	protected function _render( $model, One_Dom $d )
	{
		$id  = $this->getID();
		$dom = One_Repository::getDom();

		$css = ( ( trim( $this->getCfg( 'css' ) ) != '' ) ? trim( $this->getCfg( 'css' ) ) : One_Config::getInstance()->getUrl() . 'lib/libraries/tabber/tabber.css' );

		$dom->add( '<script type="text/javascript" src="' . One_Config::getInstance()->getUrl() . 'lib/libraries/tabber/tabber.js"></script>', '_head' );
		$dom->add( '<link href="' . $css . '" rel="stylesheet" type="text/css" />', '_head' );

		$dom->add( '<div class="tabber" id="' . $id . '">' );
		// add tabs
		foreach($this->getContent() as $content)
			$content->render( $model, $dom );

		$dom->add("</div>");

		$d->addDom($dom);
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
