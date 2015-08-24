<?php
/**
 * Handles a blockquote container
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Form_Container_Comment extends One_Form_Container_Abstract
{
	/**
	 * Class constructor
	 *
	 * @param string $id
	 * @param array $config
	 */
	public function __construct($id, array $config = array())
	{
		$this->setID( $id );
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
								'cite' => 1
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

		$dom = One_Repository::getDom();

		$dom->add('<div id="' . $id . '"' . $params . $events . '>' . "\n");

		foreach($this->getContent() as $widget)
		{
			if($widget instanceof One_Form_Widget_Abstract)
				$dom->add($widget->render( $model, $dom ));
			else
				$dom->add($widget);
		}

		$dom->add('</div>' . "\n");

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
