<?php
/**
 * Handles a Panel container
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Container_Panel extends One_Form_Container_Abstract
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
		$title = $this->getCfg('title');

		$dom = One_Repository::getDom();

		$dom->add('<div id="' . $id . '"' . $params . $events . '>' . "\n");
		$dom->add('<h3 id="' . $id . '-title" class="jpane-toggler title"' . $params . $events . '><span>' . $title . '</span></h3>' . "\n");
		$dom->add("<div class='jpane-slider content'>");

		foreach($this->getContent() as $content)
			$content->render( $model, $dom );

		$dom->add('</div></div>');

		// add js declaration for the panel
		$options = '{';
		$opt['onActive']	 = 'function(toggler, i) { toggler.addClass(\'jpane-toggler-down\'); toggler.removeClass(\'jpane-toggler\'); }';
		$opt['onBackground'] = 'function(toggler, i) { toggler.addClass(\'jpane-toggler\'); toggler.removeClass(\'jpane-toggler-down\'); }';
		$opt['duration']	 = 200;
		foreach ($opt as $k => $v)
		{
			if ($v) {
				$options .= $k.': '.$v.',';
			}
		}
		if (substr($options, -1) == ',') {
			$options = substr($options, 0, -1);
		}
		$options .= '}';

		// $dom->add('window.addEvent(\'domready\', function(){ new Accordion($$(\'.panel h3.jpane-toggler\'), $$(\'.panel div.jpane-slider\'), '.$options.'); });', '_onload');

		One_Vendor::getInstance()->loadScriptDeclaration('window.addEvent(\'domready\', function(){ new Accordion($$(\'.panel h3.jpane-toggler\'), $$(\'.panel div.jpane-slider\'), '.$options.'); });', 'onload', 10);

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
