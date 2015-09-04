<?php
/**
 * Handles the code(mirror) widget
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Widget_Scalar_Code extends One_Form_Widget_Scalar
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
		$this->_type = 'code';
	}

	/**
	 * Return the allowed options for this widget
	 *
	 * @return array
	 */
	protected static function allowedOptions()
	{
		$additional = array(
							'cols' => 1,
							'rows' => 1,
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
		$codemirrorPath = One_Vendor::getInstance()->getSitePath() . '/codemirror/';

		$script = '<script type="text/javascript" src="' . $codemirrorPath . 'js/codemirror.js"></script>';
		$d->add( $script, '_head' );

		switch( strtolower( $this->getCfg('type') ) )
		{
			case 'javascript':
				$parser     = '["tokenizejavascript.js", "parsejavascript.js"]';
				$stylesheet = '"' . $codemirrorPath . 'css/jscolors.css"';
				break;
			case 'css':
				$parser     = '"parsecss.js"';
				$stylesheet = '"' . $codemirrorPath . 'css/csscolors.css"';
				break;
			case 'htmlmix':
				$parser     = '["parsexml.js", "parsecss.js", "tokenizejavascript.js", "parsejavascript.js", "parsehtmlmixed.js"]';
				$stylesheet = '["' . $codemirrorPath . 'css/xmlcolors.css", "' . $codemirrorPath . 'css/jscolors.css", "' . $codemirrorPath . 'css/csscolors.css"]';
				$availableParsers = array( 'XMLParser' => '(X)(HT)ML', 'CSSParser' => 'CSS', 'JSParser' => 'JavaScript', 'HTMLMixedParser' => 'HTML Mixed' );
				break;
			case 'htmlphp':
				$parser     = '["parsexml.js", "parsecss.js", "tokenizejavascript.js", "parsejavascript.js", "../contrib/php/js/tokenizephp.js", "../contrib/php/js/parsephp.js", "../contrib/php/js/parsephphtmlmixed.js"]';
				$stylesheet = '["' . $codemirrorPath . 'css/xmlcolors.css", "' . $codemirrorPath . 'css/jscolors.css", "' . $codemirrorPath . 'css/csscolors.css", "' . $codemirrorPath . 'contrib/php/css/phpcolors.css"]';
				$availableParsers = array( 'XMLParser' => '(X)(HT)ML', 'CSSParser' => 'CSS', 'JSParser' => 'JavaScript', 'PHPParser' => 'PHP', 'PHPHTMLMixedParser' => 'PHP-HTML Mixed' );
				break;
			case 'sql':
				$parser     = '"../contrib/sql/js/parsesql.js"';
				$stylesheet = '"' . $codemirrorPath . 'contrib/sql/css/sqlcolors.css"';
				break;
			case 'nano':
				$parser     = '"parsenano.js"';
				$stylesheet = '"' . $codemirrorPath . 'css/nanocolors.css"';
				break;
			case 'nanomix':
				$parser     = '["parsenano.js","parsexml.js"]';
				$stylesheet = '["' . $codemirrorPath . 'css/nanocolors.css", "' . $codemirrorPath . 'css/xmlcolors.css"]';
				$availableParsers = array( 'XMLParser' => '(X)(HT)ML', 'NanoParser' => 'nano' );
				break;
			default:
				$parser     = '"parsexml.js"';
				$stylesheet = '"' . $codemirrorPath . 'css/xmlcolors.css"';
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
						'lblLast' => $this->getCfg('lblLast'),
						'parsers' => $availableParsers
					);

		$dom = $this->parse( $model, $data );
		$d->addDom( $dom );

		// onChange doesn't work well when changing parser
//		$script = '<script type="text/javascript">
//	      var editor' . ucfirst( $this->getID() ) . ' = CodeMirror.fromTextArea( "' . $this->getID() . '", {
//	      	width: "95%",
//	      	height: "350px",
//	        parserfile: ' . $parser . ',
//	        stylesheet: ' . $stylesheet . ',
//	        path: "' . $codemirrorPath . 'js/",
//	        continuousScanning: 50,
//	        lineNumbers: true,
//	        tabMode: "indent",
//	        autoMatchParens: true,
//	        iframeClass: "OneCodeWidget",
//	        onChange: function() { document.getElementById( "' . $this->getID() . '" ).value = editor' . ucfirst( $this->getID() ) . '.getCode(); }
//	      });
//	    </script>';

		$script = '<script type="text/javascript">
	      var editor' . ucfirst( $this->getID() ) . ' = CodeMirror.fromTextArea( "' . $this->getID() . '", {
	      	width: "95%",
	      	height: "350px",
	        parserfile: ' . $parser . ',
	        stylesheet: ' . $stylesheet . ',
	        path: "' . $codemirrorPath . 'js/",
	        continuousScanning: 50,
	        lineNumbers: true,
	        tabMode: "indent",
	        autoMatchParens: true,
	        iframeClass: "OneCodeWidget"
	      });
	    </script>';

		$d->add( $script );
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
