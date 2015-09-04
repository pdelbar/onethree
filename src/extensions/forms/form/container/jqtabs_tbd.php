<?php
/**
 * Handles a jQtabs container
 *

ONEDISCLAIMER
 **/
Class One_Form_Container_Jqtabs extends One_Form_Container_Abstract
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
	protected function _render($model, One_Dom $d)
	{
		$id 	= md5($this->getID().microtime(true));
		$dom    = One_Repository::getDom();

		$dom->jqtabs   = array();
		$dom->jqtitles = array();

		// add tabs
		foreach($this->getContent() as $content) {
			$content->render($model, $dom);
		}

		$dom->add('<div id="'.$id.'">'."\n");
		$dom->add('<ul>' . "\n");
		foreach($dom->jqtitles as $jqtab)
		{
			$dom->add('<li><a href="#'.$jqtab['id'].'">'.$jqtab['title'].'</a></li>');
		}
		$dom->add('</ul>'."\n");
		foreach($dom->jqtabs as $jqtab) {
			$dom->addDom($jqtab);
		}
		$dom->add("</div>");

		$js .= '
<script type="text/javascript">
	jQuery(function() {
		jQuery("#'.$id.'").tabs({
			ajaxOptions: {
				error: function( xhr, status, index, anchor ) {
					$( anchor.hash ).html(
						"Error loading data" );
				}
			}
		});
	});
</script>
		';

		$dom->add($js);

		$dom->jqtabs = NULL;
		$dom->jqtitles = NULL;
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
