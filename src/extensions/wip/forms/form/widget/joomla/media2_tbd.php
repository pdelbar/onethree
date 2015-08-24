<?php
/**
 * Handles the media widget for Joomla!
 * WARNING: this widget is to be used in Joomla! only and
 * also needs the specific template override for com_media/views/images/tmpl/default.php
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/

Class One_Form_Widget_Joomla_Media2 extends One_Form_Widget_Abstract
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
							'directory' => 2,
							'authorfield' => 2
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
		$formName = $this->getFormName();
		$name     = $this->getName();
		$id       = $this->getID();

		$dom = One_Repository::getDom();

		JHtml::_('behavior.modal');

		// Build the script.
		$script = array();
		$script[] = '	function jInsertFieldValue(value, id) {';
		$script[] = '		var old_id = document.id(id).value;';
		$script[] = '		if (old_id != id) {';
		$script[] = '			var elem = document.id(id);';
		$script[] = '			elem.value = value;';
		$script[] = '			elem.fireEvent("change");';
		$script[] = '		}';
		$script[] = '	}';

		// Add the script to the document head.
		One_Vendor::getInstance()->loadScriptDeclaration(implode("\n", $script), "head", 10);

		// should we show a label?
		if( !is_null($this->getLabel()) )
			$label = '<label class="OneFieldLabel" for="' . $id . '">' . $this->getLabel() . (($this->isRequired()) ? ' *' : '') . '</label>'."\n";

		$dom->add("<span class='OneWidget'>");

		// start with label?
		if($label && !$this->getCfg('lblLast')) $dom->add($label);

		// Initialize variables.
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->getCfg('class') ? ' class="' . (string) $this->getCfg('class') . '"' : '';

		// The text field.
		$html[] = '<div class="fltlft">';
		$html[] = '	<input type="text" name="' . $this->getFormName() . '" id="' . $this->getID() . '"' . ' value="'
		. htmlspecialchars($this->getValue($model), ENT_COMPAT, 'UTF-8') . '"' . ' readonly="readonly"' . $attr . ' />';
		$html[] = '</div>';

		$directory = (string) $this->getCfg('directory');
		if ($this->getValue($model) && file_exists(JPATH_ROOT . '/' . $this->getValue($model)))
		{
			$folder = explode('/', $this->getValue($model));
			array_shift($folder);
			array_pop($folder);
			$folder = implode('/', $folder);
		}
		elseif (file_exists(JPATH_ROOT . '/' . JComponentHelper::getParams('com_media')->get('image_path', 'images') . '/' . $directory))
		{
			$folder = $directory;
		}
		else
		{
			$folder = '';
		}

		$authorfield = $this->getCfg('authorfield');
		$author = 0;

		if(!is_null($authorfield)&& $authorfield != ''){
			$author = intval($model->$authorfield);
		}


		// The button.
		$html[] = '<div class="button2-left">';
		$html[] = '	<div class="blank">';

		$html[] = '		<a class="modal" title="' . JText::_('JLIB_FORM_BUTTON_SELECT') . '"' . ' href="'
		. 'index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset=15&amp;author=' . $author . '&amp;fieldid=' . $this->getID() . '&amp;folder=' . $folder . '"'
						. ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
		$html[] = JText::_('JLIB_FORM_BUTTON_SELECT') . '</a>';
		$html[] = '	</div>';
		$html[] = '</div>';

		$html[] = '<div class="button2-left">';
		$html[] = '	<div class="blank">';
		$html[] = '		<a title="' . JText::_('JLIB_FORM_BUTTON_CLEAR') . '"' . ' href="#" onclick="';
		$html[] = 'document.id(\'' . $this->getID() . '\').value=\'\';';
		$html[] = 'document.id(\'' . $this->getID() . '\').fireEvent(\'change\');';
		$html[] = 'return false;';
		$html[] = '">';
		$html[] = JText::_('JLIB_FORM_BUTTON_CLEAR') . '</a>';
		$html[] = '	</div>';
		$html[] = '</div>';

		$dom->add(implode("\n", $html));

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
