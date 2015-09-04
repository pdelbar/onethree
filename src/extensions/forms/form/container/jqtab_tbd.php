<?php
/**
 * Handles a jQtab container
 *

ONEDISCLAIMER
 **/
Class One_Form_Container_Jqtab extends One_Form_Container_Abstract
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
								'title' => 2
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
	protected function _render($model, One_Dom $d)
	{
		$id 	= md5($this->getID().microtime(true));
		$title	= '' != trim($this->getCfg('title')) ? trim($this->getCfg('title')) : $this->getID();

		$dom = One_Repository::createDom();
		$dom->add('<div id="'.$id.'"">'."\n");

		$d->jqtitles[] = array(
								'id' => $id,
								'title' => $title
							);

		foreach($this->getContent() as $content) {
			$content->render($model, $dom);
		}

		$dom->add('</div>');
		$d->jqtabs[] = $dom;
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
