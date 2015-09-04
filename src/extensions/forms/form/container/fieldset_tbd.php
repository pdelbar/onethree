<?php
/**
 * Handles a fieldset container
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Container_Fieldset extends One_Form_Container_Abstract
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
								'legend' => 2
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
		return array(
						'accesskey',
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
	 * Render the output of the container and add it to the DOM
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 */
	protected function _render( $model, One_Dom $d )
	{
		$id     = $this->getID();
		$params = $this->getParametersAsString();
		$events = $this->getEventsAsString();

		$dom = One_Repository::createDom();

		$dom->add('<fieldset id="' . $id . '"' . $params . $events . '>' . "\n");

		if( trim( $this->getCfg( 'legend' ) ) != '' )
			$dom->add( '<legend>' . $this->getCfg( 'legend' ) . '</legend>' );

		foreach($this->getContent() as $widget)
		{
			$dom->add( $widget->render( $model, $dom) );
		}

		$dom->add('</fieldset>' . "\n");

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
