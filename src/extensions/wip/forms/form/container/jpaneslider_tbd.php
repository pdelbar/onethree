<?php
/**
 * Handles a pane-slider container for Joomla! 2.x
 *

 * @copyright 2012 delius bvba
  * @TODO review this file and clean up historical code/comments
 **/
Class One_Form_Container_Jpaneslider extends One_Form_Container_Abstract
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
		return array();
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

		$dom = One_Repository::getDom();

		$dom->add('<div id="content-sliders-" class="pane-sliders">
			<div style="display:none;">
				<div></div>
			</div>');

		foreach($this->getContent() as $content)
			$content->render( $model, $dom );

		$dom->add('</div>');

		One_Vendor::getInstance()->loadScript(preg_replace('!administrator(/?)$!', '', JURI::base()).'/media/system/js/mootools-core.js', 'head', 30);
		One_Vendor::getInstance()->loadScript(preg_replace('!administrator(/?)$!', '', JURI::base()).'/media/system/js/core.js', 'head', 31);
		One_Vendor::getInstance()->loadScript(preg_replace('!administrator(/?)$!', '', JURI::base()).'/media/system/js/mootools-more.js', 'head', 32);

		One_Vendor::getInstance()->loadScriptDeclaration('new Fx.Accordion($$("div#content-sliders-.pane-sliders > .panel > h3.pane-toggler"), $$("div#content-sliders-.pane-sliders > .panel > div.pane-slider"), {
				onActive: function(toggler, i) {
					toggler.addClass("pane-toggler-down");toggler.removeClass("pane-toggler");i.addClass("pane-down");i.removeClass("pane-hide");Cookie.write("jpanesliders_content-sliders-",$$("div#content-sliders-.pane-sliders > .panel > h3").indexOf(toggler));
				},onBackground: function(toggler, i) {
					toggler.addClass("pane-toggler");toggler.removeClass("pane-toggler-down");i.addClass("pane-hide");i.removeClass("pane-down");if($$("div#content-sliders-.pane-sliders > .panel > h3").length==$$("div#content-sliders-.pane-sliders > .panel > h3.pane-toggler").length) Cookie.write("jpanesliders_content-sliders-",-1);
				},duration: 300,opacity: false,alwaysHide: true});', 'onload', 10);

		One_Vendor::getInstance()->loadScriptDeclaration('new Fx.Accordion($$("div#content-sliders.pane-sliders .panel h3.pane-toggler"),$$("div#content-sliders.pane-sliders .panel div.pane-slider"), {
				onActive: function(toggler, i) {
					toggler.addClass("pane-toggler-down");toggler.removeClass("pane-toggler");i.addClass("pane-down");i.removeClass("pane-hide");Cookie.write("jpanesliders_content-sliderscom_content",$$("div#content-sliders.pane-sliders .panel h3").indexOf(toggler));
				},onBackground: function(toggler, i) {
					toggler.addClass("pane-toggler");toggler.removeClass("pane-toggler-down");i.addClass("pane-hide");i.removeClass("pane-down");
				}, duration: 300, display: 0, show: 0, alwaysHide:true, opacity: false});', 'onload', 11);

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
