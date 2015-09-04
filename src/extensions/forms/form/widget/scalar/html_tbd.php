<?php
/**
 * Handles the HTML widget
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 * @TODO check why the value isn't saved
 **/
Class One_Form_Widget_Scalar_Html extends One_Form_Widget_Scalar_Textarea
{
	/**
	 * Class constructor
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $label
	 * @param array $config
	 */
	public function __construct( $id = NULL, $name = '', $label = NULL, $config = array() )
	{
		parent::__construct($id, $name, $label, $config );
		$this->_type = 'html';
		if ($config['editor'])
			$this->_editor = $config['editor'];
		else
			$this->_editor = 'default';
	}

	/**
	 * Return the allowed options for this widget
	 *
	 * @return array
	 */
	protected static function allowedOptions()
	{
		$additional = array(
							'theme' => 2,
							'width' => 2,
							'height' => 2,
							);

		return array_merge(One_Form_Widget_Scalar_Textarea::allowedOptions(), $additional);
	}

	/**
	 * Renders the HTML widget.
	 * This widget is too specific to render with One_Script and should not be rendered otherwise,
	 * hence this does not use the One_Form_Container_Abstract::parse() function
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 */
	protected function _render( $model, One_Dom $d )
	{
		$id    = $this->getID();
		$name  = $this->getFormName();
		$value = $this->getValue( $model );

		if ( is_null( $value ) ) $value = $this->getDefault();

		$info    = $this->getCfg('info');
		$error   = $this->getCfg('error');
		$width   = ( intval( $this->getCfg('width') ) ) ? 600 : intval( $this->getCfg('width') );
		$height  = ( intval( $this->getCfg('height') ) ) ? 300 : intval( $this->getCfg('height') );

		$dom = One_Repository::getDom();

		$this->setCfg('class', 'OneFieldHtml ' . $this->getCfg('class'));

		$events = $this->getEventsAsString();
		$params = $this->getParametersAsString();

		$theme = ($this->getCfg('theme')) ? $this->getCfg('theme') : "simple";

		if( !is_null($this->getLabel()))
			$label = '<label class="OneFieldLabel" for="' . $id . '" style="float: none;">' . $this->getLabel() . (($this->isRequired()) ? ' *' : '') . '</label>'."\n";

		if (strtolower($this->_editor) == 'joomla')
		{
			ob_start();

			echo "<span class='OneWidget'>";

			if($label && !$this->getCfg('lblLast')) echo $label;

			$editor = JFactory::getEditor();
			echo $editor->display( $name,  $value, $width, $height, '20', '60', array('image', 'pagebreak', 'readmore') ) ;

			if($label && $this->getCfg('lblLast')) echo $label;

			echo "</span>";

			$result = ob_get_contents();
			ob_end_clean();

			$dom->add($result);
			$d->addDom($dom);
		}
		else
		{
			$head .= '<script type="text/javascript" src="'.JURI::root().'plugins/editors/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>';
			$dom->add($head, '_head');

			// @todo: tinyMCE.init in onload (��n keer)
			$main = '<script type="text/javascript">
						tinyMCE.init({
							theme : "' . $theme . '",
							language : "en",
							mode : "textareas",
							gecko_spellcheck : "true",
							editor_selector : "mce' . ucfirst( $theme ) . '",
							document_base_url : "'.JURI::root().'",
							entities : "60,lt,62,gt",
							relative_urls : 1,
							remove_script_host : false,
							save_callback : "TinyMCE_Save",
							invalid_elements : "applet",
							extended_valid_elements : "a[class|name|href|target|title|onclick|rel],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],,hr[id|title|alt|class|width|size|noshade]",
							theme_advanced_toolbar_location : "top",
							theme_advanced_source_editor_height : "550",
							theme_advanced_source_editor_width : "750",
							directionality: "ltr",
							force_br_newlines : "false",
							force_p_newlines : "true",
							content_css : "'.JURI::root().'templates/ja_xenia/css/editor.css",
							debug : false,
							cleanup : true,
							cleanup_on_startup : false,
							safari_warning : false,
							plugins : "advlink, advimage, searchreplace,insertdatetime,emotions,media,advhr,table,fullscreen,directionality,layer,style",
							theme_advanced_buttons1_add : "fontselect",
							theme_advanced_buttons2_add : "search,replace,insertdate,inserttime,emotions,media,ltr,rtl,insertlayer,moveforward,movebackward,absolute,forecolor",
							theme_advanced_buttons3_add : "advhr,tablecontrols,fullscreen,styleprops",
							theme_advanced_disable : "help",
							plugin_insertdate_dateFormat : "%Y-%m-%d",
							plugin_insertdate_timeFormat : "%H:%M:%S",


							fullscreen_settings : {
								theme_advanced_path_location : "top"
							}
						});
						function TinyMCE_Save(editor_id, content, node)
						{
							base_url = tinyMCE.settings[\'document_base_url\'];
							var vHTML = content;
							if (true == true){
								vHTML = tinyMCE.regexpReplace(vHTML, \'href\s*=\s*"?\'+base_url+\'\', \'href="\', \'gi\');
								vHTML = tinyMCE.regexpReplace(vHTML, \'src\s*=\s*"?\'+base_url+\'\', \'src="\', \'gi\');
								vHTML = tinyMCE.regexpReplace(vHTML, \'mce_real_src\s*=\s*"?\', \'\', \'gi\');
								vHTML = tinyMCE.regexpReplace(vHTML, \'mce_real_href\s*=\s*"?\', \'\', \'gi\');
							}
							return vHTML;
						}
					</script>';

			$dom->add($main, '_head');

			$this->setCfg('class', 'mce' . ucfirst($theme) . ' ' . $this->getCfg('class'));
			$conf = $this->getParameters();
			// @todo: why this line?
//			unset($conf['theme']);

			$dom->add("<span class='OneWidget'>");

			// start with label?
			if($label && !$this->getCfg('lblLast')) $dom->add($label);

			// render the required textarea
			$ta = new OneFormWidgetTextarea( $id, $this->getOriginalName(), NULL, array_merge($conf, array( 'style' => "width:' . $width . 'px; height:' . $height . 'px;" )));
			$ta->render( $model, $dom );

			// end with label?
			if($label && $this->getCfg('lblLast')) $dom->add($label);

			$dom->add("</span>");

			$script = 'tinyMCE.execCommand("mceAddControl", false, "' . $id . '");';
			$dom->add($script, '_onload');

			if(is_null($info))
				$dom->add('<span id="' . $id . 'Info" class="OneInfo">' . $info . '</span>');

			if(is_null($error))
				$dom->add('<span id="' . $id . 'Error" class="OneError">' . $error . '</span>');

			$onload = '<script type="text/javascript">tinyMCE.init();</script>';

			$dom->add( $onload );

			$d->addDom($dom);
		}
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
