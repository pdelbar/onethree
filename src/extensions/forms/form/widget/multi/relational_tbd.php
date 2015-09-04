<?php
/**
 * Handles the multi-relational widget
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Form_Widget_Multi_Relational extends One_Form_Widget_Abstract
{
	/**
	 * @var array Extra options
	 */
	private $_options = array();

	/**
	 * Class constructor
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $label
	 * @param array $config
	 * @param array $options
	 */
	public function __construct( $id = NULL, $name = '', $label = NULL, array $config = array(), array $options = array()  )
	{
		parent::__construct($id, $name, $label, $config );
		$this->_type = 'relational';

		$this->setOptions($options);
	}

	/**
	 * Bind the model to the widget
	 *
	 * @param One_Model $model
	 */
	public function bindModel( $model )
	{
		$value = $this->requestValue();	// bad name
		if( is_null( $value ) ) // if == NULL, set to 0, because NULL won't be picked up by $model->__modified
			$value = array();
		else
			$value = explode( '^,^', $value );

		$attributeName = $this->_name;

		// When the attributeName starts with 'r__', we're not saving an attribute but a relation
		if(preg_match('/^r__(.*)_(((?!._).)*)$/iU', $attributeName, $tmp))
		{
			$relName = $tmp[1];
			$relRole = $tmp[2];
			$relValue = $value;

			$model->setRelated($relRole, $relValue);
		}
		else
			$model->$attributeName = $value;
	}

	/**
	 * Return the allowed options for this widget
	 *
	 * @return array
	 */
	protected static function allowedOptions()
	{
		$additional = array(
							'dir' => 1,
							'lang' => 1,
							'xml:lang' => 1,
							'accesskey' => 1,
							'tabindex' => 1,
							'disabled' => 1,
							'multiple' => 1,
							'targetAttribute' => 2,
							'onEmpty' => 2,
							'triggerOn' => 2,
							'data-placeholder' => 1 // added for support of chosen jQuery plugin
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
		return array(
						'onfocus',
						'onblur',
						'onchange'
					);
	}

	/**
	 * Render the output of the widget and add it to the DOM
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 */
	protected function _render( $model, One_Dom $d )
	{
		$this->setCfg('class', 'OneFieldDropdown ' . $this->getCfg('class'));

		// fetch all data to do with the relationship
		$parts      = explode( ':', $this->getCfg( 'role' ) );
		$related    = $model->getRelated( $parts[1] );
		$targetAttr = $this->getCfg( 'targetAttribute' );
		$triggerOn = ( ( intval( $this->getCfg( 'triggerOn' ) ) > 0 ) ? intval( $this->getCfg( 'triggerOn' ) ) : 2 );
		$scheme = $model->getScheme();
		$idAttr = $scheme->getIdentityAttribute()->getName();
		$link = $scheme->getLink( $parts[1] );

		$relatedIDs = '';
		$options    = array();
		if( is_array( $related ) && count( $related ) > 0 )
		{
			$relatedIDArray = array();
			$idAttr = One_Repository::getScheme($link->getTarget())->getIdentityAttribute()->getName();
			$tparts = explode( ':', $targetAttr );
			foreach( $related as $relate )
			{
				if( is_null( $idAttr ) )
				{
					$scheme = $model->getScheme();
					$idAttr = $scheme->getIdentityAttribute()->getName();
				}

				$value = $relate->$idAttr;
				$shown = '';

				foreach( $tparts as $tpart )
				{
					$shown .= $relate->$tpart . ' ';
				}

				$options[ $value ] = $shown;

				$relatedIDArray[] = $relate->$idAttr;
			}

			$relatedIDs = implode( '^,^', $relatedIDArray );
		}

		$data = array(
						'id' => $this->getID(),
						'name' => $this->getFormName(),
						'events' => $this->getEventsAsString(),
						'params' => $this->getParametersAsString(),
						'required' => (($this->isRequired()) ? ' *' : ''),
						'value' => ((is_null($this->getValue($model))) ? $this->getDefault() : $this->getValue($model)),
						'options' => $options,
						'onEmpty' => strtolower( $this->getCfg( 'onEmpty' ) ),
						'triggerOn' => $triggerOn,
						'scheme' => $scheme,
						'link' => $link,
						'targetAttr' => $targetAttr,
						'modelID' => $model->$idAttr,
						'relatedIDs' => $relatedIDs,
						'info' => $this->getCfg('info'),
						'error' => $this->getCfg('error'),
						'class' => $this->getCfg('class'),
						'label' => $this->getLabel(),
						'lblLast' => $this->getCfg('lblLast')
					);

//		$dom = new One_Dom(); // dom for head section
//
//		$head = '<script type="text/javascript" src="' . One::getInstance()->getUrl() . 'lib/libraries/js/featherajax.js"></script>';
//		$dom->add( $head, '_head' );


		$head = '
			function setRelatedOptions( selfscheme, scheme, selfId, id, targetAttribute, phrase )
			{
				var self = "";
				if( selfscheme == scheme )
					self = "&selfId=" + selfId;
				var aj = new AjaxObject101();
				aj.sndReq( "post", "' . One_Config::getInstance()->getUrl() . '/lib/form/ajax/relational.php", "searchscheme=" + scheme + "&dd=f" + id + "&target=" + targetAttribute + "&phrase=" + phrase + self );
			}

			function addChosenOptions( id )
			{
				var dropdown = document.getElementById( "f" + id );
				var to       = document.getElementById( "t" + id );
				for( var i = 0; i < dropdown.length; i++ )
				{
					if( dropdown.options[i].selected == true )
					{
						var option = document.createElement("option");
						option.value = dropdown.options[i].value;
						option.innerHTML = dropdown.options[i].text;

						var found = false
						for( var j = 0; j < to.length; j++ )
						{
							if( option.value == to.options[j].value )
							{
								found = true;
								break;
							}
						}

						if( !found )
						{
							var hidden = document.getElementById( id );
							to.appendChild( option );

							if( hidden.value != "" )
								hidden.value = hidden.value + "^,^" + option.value;
							else
								hidden.value = option.value;
						}
					}
				}

			}

			function removeChosenOptions( id )
			{
				var to     = document.getElementById( "t" + id );
				var hidden = document.getElementById( id );

				for( var i = ( to.length - 1 ); i >= 0; i-- )
				{
					if( to.options[i].selected == true )
					{
						var pattern  = \'((\\^,\\^)?\' + to.options[i].value + \'(\\^,\\^)?)\';
						var test     = new RegExp( pattern, "gi" );
						/* @TODO There is probably an easier way to do this  */
						hidden.value = hidden.value.replace( test, "" );
						hidden.value = hidden.value.replace( /\^,\^\^,\^/gi, "^,^" );
						hidden.value = hidden.value.replace( /^\^,\^/gi, "" );
						hidden.value = hidden.value.replace( /\^,\^$/gi, "" );
						to.remove( i );
					}
				}
			}';

//		$dom->add( $head, '_head' );

		One_Vendor::getInstance()->loadScript('js/featherajax.js', 200);
		One_Vendor::getInstance()->loadScriptDeclaration($head, 'head', 200);

		$content = $this->parse( $model, $data );

//		$d->addDom($dom);
		$d->addDom($content);
	}

	/**
	 * Add an option
	 *
	 * @param array $option
	 */
	public function addOption( array $option )
	{
		// $option should be an array in the form {"key", "value"}
		$this->_options[$option[0]] = $option[1];
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

	/**
	 * Get all the options set for this widget
	 *
	 * @return array
	 */
	public function getOptions()
	{
		return $this->_options;
	}

	/**
	 * Set the options for this widget
	 *
	 * @param array $options
	 */
	public function setOptions( array $options )
	{
		$this->_options = $options;
	}

	/**
	 * Should the options be set when the widget is instantiated
	 *
	 * @return boolean
	 */
	public function setOptionsOnDetermination()
	{
		return true;
	}
}
