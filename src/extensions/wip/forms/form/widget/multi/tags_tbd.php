<?php
/**
 * Handles the multi-tags widget
 *

ONEDISCLAIMER
 **/
Class One_Form_Widget_Multi_Tags extends One_Form_Widget_Abstract
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
	public function __construct( $id = NULL, $name = '', $label = NULL, array $config = array(), array $options = array() )
	{
		parent::__construct($id, $name, $label, $config );
		$this->_type = 'tags';

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

		preg_match_all('/([^:]+):([^,]+),?/', $value, $matches);

		$parts      = explode(':', $this->getCfg('role'), 2);
		$targetAttr = $this->getCfg('targetAttribute');
		$tScheme    = One_Repository::getScheme($model->getScheme()->getLink($parts[1])->getTarget());
		$tIdAttr    = $tScheme->getIdentityAttribute()->getName();

		$value = array();
		if(0 < count($matches[0]))
		{
			foreach($matches[0] as $key => $tmp)
			{
				$usedKey   = $matches[1][$key];
				$usedValue = $matches[2][$key];

				if(null === One_Repository::selectOne($tScheme->getName(), $usedKey))
				{
					$tModel = One::make($tScheme->getName());
					$tModel->$targetAttr = $usedValue;
					$tModel->insert();
					$usedKey = $tModel->$tIdAttr;
				}

				$value[$usedKey] = $usedKey;
			}
		}

		if( is_null( $value ) ) $value = array(); // if == NULL, set to 0, because NULL won't be picked up by $model->__modified

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
		return parent::allowedOptions();
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
	protected function _render($model, One_Dom $d)
	{
		One_Vendor::requireVendor('jquery/one_loader');
		One_Vendor::getInstance()
			->loadScript('jquery/js/jquery.tagify.js', 'head', 10);

		if(is_null($this->getCfg('role')) || is_null($this->getCfg('targetAttribute'))) {
			throw new One_Exception('The Multi-tag widget is only allowed for Many-to-many relations');
		}

		$parts      = explode(':', $this->getCfg('role'), 2);
		$targetAttr = $this->getCfg('targetAttribute');
		$link       = $model->getScheme()->getLink($parts[1]);

		if(is_null($link) || !$link->getAdapterType() instanceof One_Relation_Adapter_Manytomany) {
			throw new One_Exception('The Multi-tag widget is only allowed for Many-to-many relations');
		}

		$tScheme    = One_Repository::getScheme($model->getScheme()->getLink($parts[1])->getTarget());
		$tIdAttr    = $tScheme->getIdentityAttribute()->getName();

		// set initial values
		$value  = array();
		$values = ((is_null($this->getValue($model))) ? $this->getDefault() : $this->getValue($model));
		foreach($values as $tId)
		{
			$tModel = One_Repository::selectOne($tScheme->getName(), $tId);
			if(null !== $tModel) {
				$value[$tModel->$tIdAttr] = $tModel->$targetAttr;
			}
		}

		$data = array(
						'id' => $this->getID(),
						'name' => $this->getFormName(),
						'events' => $this->getEventsAsString(),
						'params' => $this->getParametersAsString(),
						'required' => (($this->isRequired()) ? ' *' : ''),
						'options' => $this->getOptions(),
						'info' => $this->getCfg('info'),
						'error' => $this->getCfg('error'),
						'class' => $this->getCfg('class'),
						'label' => $this->getLabel(),
						'lblLast' => $this->getCfg('lblLast')
					);


		$dom = $this->parse( $model, $data );

//		$dom->add('<script type="text/javascript" href="'.One_Vendor::getInstance()->getSitePath().'/jquery/js/jquery.tagify.js" />'."\n", '_head');
//		$dom->add('<link rel="stylesheet" type="text/css" href="'.One_Vendor::getInstance()->getSitePath().'/jquery/css/tagify-style.css" />'."\n", '_head');

		//One_Vendor::getInstance()->loadScript('jquery/js/jquery.tagify.js', 'head', 20);
		One_Vendor::getInstance()->loadStyle('jquery/css/tagify-style.css', 'head', 20);

		// Prepare autocomplete tagfield
		$script = '
		var oneTags'.$this->getID().' = jQuery("#'.$this->getID().'").tagify();
		oneTags'.$this->getID().'.tagify("inputField").autocomplete({
	        source: function(req, add) {
				req.field = "'.$targetAttr.'";
				req.query = "formtags";

				//pass request to server
                jQuery.getJSON("'.One_Config::getInstance()->getSiterootUrl().One_Config::getInstance()->getAddressOne().'&scheme='.$tScheme->getName().'&task=json", req, function(data) {

              	 	//create array for response objects
                    var suggestions = [];

              		//process response
                    jQuery.each(data, function(i, val) {
						var keyval = { "label": val.'.$targetAttr.', "value": val.'.$tIdAttr.' };
		                suggestions.push(keyval);
	    			});

	            	//pass array to callback
                    add(suggestions);

                });
			},
	        position: { of: oneTags'.$this->getID().'.tagify("containerDiv") },
			select: function(e, ui) {
				oneTags'.$this->getID().'.tagify("inputField").val(ui.item.label);
        		oneTags'.$this->getID().'.tagify("add", ui.item.value, ui.item.label);
				oneTags'.$this->getID().'.tagify("inputField").val("");
        		return false;
			},
			focus: function(e, ui) {
				oneTags'.$this->getID().'.tagify("inputField").val(ui.item.label);
				return false;
			},
			change: function(e, ui) {
				oneTags'.$this->getID().'.tagify("inputField").val("");
				return false;
			}
    	});
		';

		foreach($value as $tId => $tVal) {
			$script .= '
		oneTags'.$this->getID().'.tagify("add", "'.$tId.'", "'.$tVal.'");';
		}

//		$dom->add($script, '_onload');
		One_Vendor::getInstance()->loadScriptDeclaration($script, 'onload', 20);

		$d->addDom( $dom );
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
		return false;
	}
}
