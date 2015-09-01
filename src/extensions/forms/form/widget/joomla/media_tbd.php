<?php
/**
 * Handles the media widget for Joomla!
 * WARNING: this widget is to be used in Joomla! only and
 * also needs the specific template override for com_media/views/images/tmpl/default.php
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/

Class One_Form_Widget_Joomla_Media extends One_Form_Widget_Abstract
{
	/**
	 * @var array Extra options
	 */
	private $_options = NULL;

	/**
	 * Class constructor
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $label
	 * @param array $config
	 */
	public function __construct( $id = NULL, $name = '', $label = NULL, array $config = array() )
	{
		parent::__construct($id, $name, $label, $config );
		$this->_type = 'media';
	}

	/**
	 * Return the allowed options for this widget
	 *
	 * @return array
	 */
	protected static function allowedOptions()
	{
		$extra = array(
							'thWidth' => 2
						);

		return array_merge( parent::allowedOptions(), $extra );
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
	 * Return the allowed extensions for this widget
	 *
	 * @return array
	 */
	protected static function allowedExtensions()
	{
		$mediaParams = JComponentHelper::getParams( 'com_media' );
		$imageExtensions = $mediaParams->get( 'image_extensions' );
		$imageExtensions = explode( ',', $imageExtensions );

		return $imageExtensions;
	}

	/**
	 * Renders the Joomla-Media widget.
	 * This widget is too specific to render with One_Script and should not be rendered otherwise,
	 * hence this does not use the One_Form_Container_Abstract::parse() function
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 * @access protected
	 */
	protected function _render( $model, One_Dom $d )
	{
		JHTML::_('behavior.modal');
		JHTML::_('behavior.modal', 'a.modal-button');

		$formName = $this->getFormName();
		$name     = $this->getName();
		$id       = $this->getID();
		$link     = 'index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;oneWidget=true&amp;e_name='.$name;

		$button = new JObject();
		$button->set('modal', true);
		$button->set('link', $link);
		$button->set('text', JText::_('Image'));
		$button->set('name', 'image');
		$button->set('options', "{handler: 'iframe', size: {x: 570, y: 400}}");

		$modal		= ($button->get('modal')) ? 'class="modal-button"' : null;
		$href		= ($button->get('link')) ? 'href="'.JURI::base().$button->get('link').'"' : null;
		$onclick	= ($button->get('onclick')) ? 'onclick="'.$button->get('onclick').'"' : null;

		$dom = One_Repository::getDom();

		// should we show a label?
		if( !is_null($this->getLabel()) )
			$label = '<label class="OneFieldLabel" for="' . $id . '">' . $this->getLabel() . (($this->isRequired()) ? ' *' : '') . '</label>'."\n";

		$dom->add("<span class='OneWidget'>");

		// start with label?
		if($label && !$this->getCfg('lblLast')) $dom->add($label);

		$app = JFactory::getApplication();
		$jDoc = JFactory::getDocument();

		$path = '';
		if($app->getName() != 'site') {
			$path = '../';
		}

		$mediaParams = JComponentHelper::getParams( 'com_media' );
		$imageExtensions = $mediaParams->get( 'image_extensions' );
		$imageExtensions = explode( ',', $imageExtensions );

		preg_match( '/\.([a-z0-3]{1,4})$/i', $model->$name, $match );
		$currExt = $match[1];
		$isImage = in_array( $currExt, $imageExtensions );
		$currImgSrc= $path . ( ( $isImage ) ? $model->$name : 'images/spacer.gif' );

		$thWidth = ( !is_null( $this->getCfg( 'thWidth' ) ) ) ? $this->getCfg( 'thWidth' ) : 50;

		$setMedia = 'function changeMedia( item, path )
		{
			document.getElementById( item ).value = path;

			var setImg = "' . $path . 'images/spacer.gif";
			if( path.match( /\.(' . implode( '|', $imageExtensions ) . ')$/ ) )
				setImg = "' . $path . '" + ' . 'path;

			document.getElementById( "thImg" + item ).src = setImg;
			document.getElementById( "thImg" + item ).style.width = "' . $thWidth . 'px";
			document.getElementById( "thImgLink" + item ).href = setImg;
			document.getElementById("OneThumb"+item).style.display = "block";

		}

		function clearOneJMediaImg( item )
		{
			document.getElementById( item ).value = "";

			var setImg = "' . $path . 'images/spacer.gif";
			document.getElementById( "thImg" + item ).src = setImg;
			document.getElementById( "thImgLink" + item ).href = setImg;

			document.getElementById("OneThumb"+item).style.display = "none";
		}';

		// slimbox must be loaded AFTER mootools (current slimbox is meant for mootools 1.11)
		$jDoc->addScript( One_Config::getInstance()->getUrl() . '/vendor/slimbox/js/slimbox.js', 'text/javascript' );
		$jDoc->addStyleSheet( One_Config::getInstance()->getUrl() . '/vendor/slimbox/css/slimbox.css', 'text/css', 'screen' );

		$jDoc->addScriptDeclaration( $setMedia );

		// show the input
		$dom->add( '<div style="float: left;">' );
		$dom->add( '<input class="OneFieldInput" type="text" name="' . $formName . '" id="' . $id . '" size="50" readonly="readonly" value="' . $model->$name . '" />' . "\n" );
		$dom->add( '</div>' );

		$dom->add( '<div class="button2-left">' );
		$dom->add( '<div class="' . $button->get( 'name' ) . '">' );
		$dom->add( '<a ' . $modal . ' title="' . $button->get('text') . '" ' . $href . ' ' . $onclick . ' rel="' . $button->get( 'options' ) . '">' . $button->get( 'text' ) . '</a>' );
		$dom->add( '</div></div>');


		$dom->add( '<div class="button2-left">' );
		$dom->add( '<div class="' . $button->get( 'name' ) . '">' );
		$dom->add( '<a href="#" onclick="clearOneJMediaImg( \'' . $id . '\' ); return false;">Clear image</a>' );			//TODO: clear thumb image
		$dom->add( '</div>' );
		$dom->add( '</div>' );

		$dom->add( '<div class="OneThumb" id="OneThumb'.$id.'"'.(('' == trim($currImgSrc) || $path.'images/spacer.gif' == trim($currImgSrc)) ? ' style="display: none;"' : '').'>' );
		$dom->add( '<a href="' . $currImgSrc . '" id="thImgLink' . $id . '" rel="lightbox"><img id="thImg' . $id . '" src="' . $currImgSrc . '" width="' . $thWidth . '" /></a>' );
		$dom->add( '</div>' );

		// end with label?
		if($label && $this->getCfg('lblLast')) $dom->add($label);

		$dom->add("</span>");

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
