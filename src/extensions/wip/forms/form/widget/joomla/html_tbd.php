<?php
/**
 * Handles the HTML widget for Joomla! ( WYSIWYG )
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Form_Widget_Joomla_HTML extends One_Form_Widget_Abstract
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
		$this->_type = 'html';
	}

	/**
	 * Return the allowed options for this widget
	 *
	 * @return array
	 */
	protected static function allowedOptions()
	{
		$additional = array(
							'disabled' => 1,
							'width' => 1,
							'height' => 1,
							'buttons' => 2
		);
		return array_merge( parent::allowedOptions(), $additional );
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
	 * Renders the Joomla-HTML widget.
	 * This widget is too specific to render with One_Script and should not be rendered otherwise,
	 * hence this does not use the One_Form_Container_Abstract::parse() function
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 * @access protected
	 */
	protected function _render( $model, One_Dom $d )
	{
		$editor = JFactory::getEditor();

		JHTML::_('behavior.tooltip');

		$width = $this->getParameter( 'width' ) ? $this->getParameter( 'width' ) : '100%';
		$height = $this->getParameter( 'height' ) ? $this->getParameter( 'height' ) : '550';

		//TODO: clean this up, should add a 'save editor' part for each control, multiple controls do not work now
		ob_start();
		?>

		<script language="javascript" type="text/javascript">
		<!--
		function submitbutton(pressbutton)
		{
			var form = document.adminForm;

			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			//save
				<?php
				echo $editor->save( $this->getFormName() );
				?>
				submitform( pressbutton );
		}
		//-->
		</script>

		<?php

		echo preg_replace( '/<div class="button2-left"><div class="translate">(.*?)(<\/div><\/div>)/is', '', $editor->display( $this->getFormName(),  $this->getValue( $model ) , $width, $height, '75', '20' ) );
		$edit = ob_get_clean();

		if( !is_null( $this->getCfg( 'buttons' ) ) && in_array( $this->getCfg( 'buttons' ), array( 'no', '0', 'false' ) ) )
			$edit = preg_replace( '/<div class="button2-left">(.*?)(<\/div><\/div>)/is', '', $edit );

		JHTML::_('behavior.modal');
		JHTML::_('behavior.modal', 'a.modal-button');

		$dom = One_Repository::getDom();

		$dom->add( '<span class="OneWidget clearfix">' );

		// should we show a label?
		if( !is_null($this->getLabel()) && !$this->getCfg( 'noLabel' ) )
			$label = '<label class="OneFieldLabel" for="' . $this->getID() . '">' . $this->getLabel() . (($this->isRequired()) ? ' *' : '') . '</label>'."\n";

		// start with label?
		if($label && !$this->getCfg('lblLast')) $dom->add($label);

		$app = JFactory::getApplication();
		$jDoc = JFactory::getDocument();

		$dom->add( '<div class="OneWidgetEditor">' );

		$dom->add( $edit );
		$dom->add( '</div>' );

		// end with label?
		if($label && $this->getCfg('lblLast')) $dom->add($label);

		$dom->add( '</span>' );


		$d->addDom( $dom );
	}

	/**
	 * Overrides PHP's native __toString function
	 *
	 * @return string
	 */
	public function __toString()
	{
		return get_class().': '.$this->getID();
	}
}
