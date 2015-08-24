<?php
/**
 * Handles the code(mirror) widget
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Widget_Scalar_Code2 extends One_Form_Widget_Scalar
{
	/**
	 * @var string Type of the field //TODO check if this can go
	 */
	protected $_totf;

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
		$this->_type = 'code2';
	}

	/**
	 * Return the allowed options for this widget
	 *
	 * @return array
	 */
	protected static function allowedOptions()
	{
		$additional = array(
							'width' => 1,
							'height' => 1,
							'dir' => 1,
							'lang' => 1,
							'xml:lang' => 1,
							'tabindex' => 1,
							'accesskey' => 1,
							'disabled' => 1,
							'readonly' => 1,
							'type' => 2
							);
		return array_merge(parent::allowedOptions(), $additional);
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
	 * Render the output of the widget and add it to the DOM
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 */
	protected function _render( $model, One_Dom $d )
	{
		$codemirrorPath = One_Vendor::getInstance()->getSitePath() . '/codemirror2/';

//		$d->add('<script type="text/javascript" src="'.$codemirrorPath.'lib/codemirror.js"></script>', '_head' );
//		$d->add('<link rel="stylesheet" type="text/css" href="'.$codemirrorPath.'lib/codemirror.css"/>', '_head' );
//		$d->add('<link rel="stylesheet" type="text/css" href="'.$codemirrorPath.'theme/default.css"/>', '_head' );

		One_Vendor::getInstance()->loadScript('codemirror2/lib/codemirror.js', 'head', 1);
		One_Vendor::getInstance()->loadStyle('codemirror2/lib/codemirror.css', 'head', 1);
		One_Vendor::getInstance()->loadStyle('codemirror2/theme/default.css', 'head', 2);

		$type = strtolower($this->getCfg('type'));
		if(file_exists(One_Vendor::getInstance()->getFilePath().'/codemirror2/mode/'.$type.'/'.$type.'.js'))
		{
			$mode = strtolower($this->getCfg('type'));
//			$d->add('<script type="text/javascript" src="'.$codemirrorPath.'mode/'.$type.'/'.$type.'.js"></script>', '_head' );
			One_Vendor::getInstance()->loadScript('codemirror2/mode/'.$type.'/'.$type.'.js', 'head', 2);
			if(file_exists(One_Vendor::getInstance()->getFilePath().'/codemirror2/mode/'.$type.'/'.$type.'.css')) {
//				$d->add('<link rel="stylesheet" type="text/css" href="'.$codemirrorPath.'mode/'.$type.'/'.$type.'.css"/>', '_head' );
				One_Vendor::getInstance()->loadStyle('codemirror2/mode/'.$type.'/'.$type.'.css', 'head', 3);
			}
		}
		else {
			$mode = 'scheme';
//			$d->add('<script type="text/javascript" src="'.$codemirrorPath.'mode/scheme/scheme.js"></script>', '_head' );
			One_Vendor::getInstance()->loadScript('codemirror2/mode/scheme/scheme.js', 'head', 2);
		}

		switch($mode)
		{
			case 'htmlmixed':
//				$d->add('<script type="text/javascript" src="'.$codemirrorPath.'mode/css/css.js"></script>', '_head' );
//				$d->add('<script type="text/javascript" src="'.$codemirrorPath.'mode/javascript/javascript.js"></script>', '_head' );
//				$d->add('<script type="text/javascript" src="'.$codemirrorPath.'mode/xml/xml.js"></script>', '_head' );
				One_Vendor::getInstance()->loadScript('codemirror2/mode/css/css.js', 'head', 3);
				One_Vendor::getInstance()->loadScript('codemirror2/mode/javascript/javascript.js', 'head', 4);
				One_Vendor::getInstance()->loadScript('codemirror2/mode/xml/xml.js', 'head', 5);
				break;
			case 'php':
//				$d->add('<script type="text/javascript" src="'.$codemirrorPath.'mode/clike/clike.js"></script>', '_head' );
//				$d->add('<script type="text/javascript" src="'.$codemirrorPath.'mode/htmlmixed/htmlmixed.js"></script>', '_head' );
//				$d->add('<script type="text/javascript" src="'.$codemirrorPath.'mode/css/css.js"></script>', '_head' );
//				$d->add('<script type="text/javascript" src="'.$codemirrorPath.'mode/javascript/javascript.js"></script>', '_head' );
//				$d->add('<script type="text/javascript" src="'.$codemirrorPath.'mode/xml/xml.js"></script>', '_head' );
				One_Vendor::getInstance()->loadScript('codemirror2/mode/clike/clike.js', 'head', 3);
				One_Vendor::getInstance()->loadScript('codemirror2/mode/htmlmixed/htmlmixed.js', 'head', 4);
				One_Vendor::getInstance()->loadScript('codemirror2/mode/css/css.js', 'head', 5);
				One_Vendor::getInstance()->loadScript('codemirror2/mode/javascript/javascript.js', 'head', 6);
				One_Vendor::getInstance()->loadScript('codemirror2/mode/xml/xml.js', 'head', 7);
				break;

		}

		$data = array(
						'id' => $this->getID(),
						'name' => $this->getFormName(),
						'events' => $this->getEventsAsString(),
						'params' => $this->getParametersAsString(),
						'value' => ( is_null( $this->getValue( $model ) ) ? $this->getDefault() : $this->getValue( $model ) ),
						'info' => $this->getCfg('info'),
						'error' => $this->getCfg('error'),
						'class' => $this->getCfg('class'),
						'required' => (($this->isRequired()) ? ' *' : ''),
						'label' => $this->getLabel(),
						'lblLast' => $this->getCfg('lblLast')
					);

		$dom = $this->parse( $model, $data );
		$d->addDom( $dom );

		$script = 'var editor'.ucfirst($this->getID()).' = CodeMirror.fromTextArea(document.getElementById("'.$this->getID().'"), {
	      	mode: "'.$mode.'",
	        lineNumbers: true,
	        tabMode: "indent",
	        matchBrackets: true,
	        theme: "default"
	      });';

		if(null !== $this->getCfg('width')) {
			$script .= '
	      jQuery(editor'.ucfirst($this->getID()).'.getScrollerElement()).width("'.intval($this->getCfg('width')).'");';
		}

		if(null !== $this->getCfg('height')) {
			$script .= '
	      jQuery(editor'.ucfirst($this->getID()).'.getScrollerElement()).height("'.intval($this->getCfg('height')).'");';
		}

		$script .= '
	      editor'.ucfirst($this->getID()).'.refresh();';

		One_Vendor::getInstance()->loadScriptDeclaration($script, 'body', 1);

//		$d->add( $script );
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
