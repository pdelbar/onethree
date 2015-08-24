<?php
/**
 * Handles a Mootabs container
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Container_Mootabs extends One_Form_Container_Abstract
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
								'dir' => 1,
								'lang' => 1,
								'xml:lang' => 1,
								'title' => 2,
								'transition' => 2,
								'width' => 2,
								'height' => 2,
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
		$id     = $this->getID();
		$width  = ( is_null( $this->getCfg( 'width' ) ) ) ? 400 : intval( $this->getCfg( 'width' ) );
		$height = ( is_null( $this->getCfg( 'height' ) ) ) ? 300 : intval( $this->getCfg( 'height' ) );
		$css    = ( is_null( $this->getCfg( 'css' ) ) ) ? One_Vendor::getInstance()->getSitePath() . '/mootabs/mootabs1.2.css' : $this->getCfg( 'css' );
		$dom    = One_Repository::getDom();

		$dom->add( '<script type="text/javascript" src="' . One_Vendor::getInstance()->getSitePath() . '/mootabs/mootabs1.2.js"></script>', '_head' );
		$dom->add( '<script type="text/javascript">
	      function initMooTabs' . $id . '() {
	      	myTabs' . $id . ' = new mootabs( "' . $id  . '", {
																	changeTransition: "none",
																	mouseOverClass: "over",
																	width: ' . $width .',
																	height: ' . $height .'
																} );
	      }
	      window.addEvent("domready", initMooTabs' . $id . ' );
	    </script>', '_head' );
		$dom->add( '<link href="' . $css . '" rel="stylesheet" type="text/css" />', '_head' );

		$dom->mootabs   = array();
		$dom->mootitles = array();

		// add tabs
		foreach($this->getContent() as $content)
			$content->render( $model, $dom );

		$dom->add('<div id="' . $id . '">' . "\n");
		$dom->add('<ul class="mootabs_title">' . "\n");
		$active = ' class="active"';
		foreach( $dom->mootitles as $mootab )
		{
			$dom->add('<li title="' . $mootab[ 'id' ] . '"' . $active . '>' . $mootab[ 'title' ] . '</li>');
			$active = '';
		}
		$dom->add('</ul>' . "\n");
		foreach( $dom->mootabs as $mootab )
		{
			$dom->addDom( $mootab );
		}
		$dom->add("</div>");

		$dom->mootabs = NULL;
		$dom->mootitles = NULL;
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
