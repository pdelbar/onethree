<?php
/**
 * Handles a tabbertab container
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/

Class One_Form_Container_Tabbertab extends One_Form_Container_Abstract
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
								'title'   => 2,
								'default' => 2
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
		$title = $this->getCfg('title');

		$dom = One_Repository::createDom();
		$dom->add('<div id="' . $id . '" class="tabbertab' . ( ( trim( $this->getCfg( 'default' ) ) == 'default' ) ? ' tabbertabdefault' : '' ) . '"' . ( ( trim( $title ) != '' ) ? ' title="' . $title . '"' : '' ) . '>' . "\n");

		foreach($this->getContent() as $content)
			$content->render( $model, $dom );

		$dom->add('</div>');

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
