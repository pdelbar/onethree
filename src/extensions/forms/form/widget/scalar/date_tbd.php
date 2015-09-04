<?php
/**
 * Handles the date widget
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Widget_Scalar_Date extends One_Form_Widget_Scalar
{
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
		$this->_type = 'date';
	}

	/**
	 * Return the allowed options for this widget
	 *
	 * @return array
	 */
	protected static function allowedOptions()
	{
		$additional = array(
							'js' => 2,
							'jquery' => 2,
							'jsFlat' => 2,
							'yearFrom' => 2,
							'yearTill' => 2,
							'time' => 2,
							'readonly' => 2
							);
		return array_merge(parent::allowedOptions(), $additional);
	}

	/**
	 * Is the current date a valid date?
	 *
	 * @return boolean
	 */
	public function validate()
	{
		if( $this->isRequired() && ( is_null( $this->requestValue() ) || trim( $this->requestValue() ) == '' ) )
		{
			return false;
		}
		else
		{
			$validDate = One_Form_Validator::isValidDate( $this->requestValue(), $this->isRequired());
			$constraints = $this->_constraint->checkConstraints();

			return $validDate && $constraints;
		}
	}

	/**
	 * Renders the date widget.
	 * This widget is too specific to render with One_Script and should not be rendered otherwise,
	 * hence this does not use the One_Form_Container_Abstract::parse() function
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 * @access protected
	 */
	protected function _render( $model, One_Dom $d )
	{
		$id    = $this->getID();
		$name  = $this->getFormName();

		$info    = $this->getCfg('info');
		$error   = $this->getCfg('error');

		$dom = One_Repository::getDom();

		$this->setCfg('class', 'OneFieldDate ' . $this->getCfg('class'));

		$events = $this->getEventsAsString();
		$params = $this->getParametersAsString();

		$dom->add("<span class='OneWidget'>");

		if( !is_null($this->getLabel()) )
			$label = '<label class="OneFieldLabel" for="' . $id . '">' . $this->getLabel() . (($this->isRequired()) ? ' *' : '') . '</label>'."\n";

		// start with label?
		if($label && !$this->getCfg('lblLast')) $dom->add($label);

		// render the calendar
		if($this->getCfg('js'))
			$this->renderJSCalendar($model, $dom);
		else
			$this->renderDropdownCalendar($model, $dom);

		// end with label?
		if($label && $this->getCfg('lblLast')) $dom->add($label);

		$dom->add("</span>");

		if(is_null($info))
			$dom->add('<span id="' . $id . 'Info" class="OneInfo">' . $info . '</span>');

		if(is_null($error))
			$dom->add('<span id="' . $id . 'Error" class="OneError">' . $error . '</span>');

		$d->addDom($dom);
	}

	/**
	 * Render the widget as a combination of dropdowns
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 */
	private function renderDropdownCalendar( $model, One_Dom $d )
	{
		$id    = $this->getID();
		$name  = $this->getFormName();
		$value  = $this->getValue( $model );
		$dom = One_Repository::getDom();

		// @todo: should we rather use this?  does the same as preg_match, no?
//		$date = getdate(strtotime($value));

		preg_match('/^(\d{4})-(\d{2})-(\d{2})( (\d{2}):(\d{2}):(\d{2}))?$/' ,$value, $matches);

		$days     = array();
		$months   = array();
		$years    = array();
		$yearFrom = ((!is_null($this->getCfg('yearFrom'))) ? $this->getCfg('yearFrom') : (date('Y') - 100));
		$yearTill = ((!is_null($this->getCfg('yearTill'))) ? $this->getCfg('yearTill') : (date('Y')));

		for($i = 1; $i < 32; $i++)
		{
			$days[$i] = $i;
			if($i < 13) $months[$i] = $i;
		}

		$yearTill = ((!is_null($this->getCfg('yearTill'))) ? $this->getCfg('yearTill') : (date('Y')));

		for($i = $yearFrom; $i <= $yearTill; $i++)
		{
			$years[$i] = $i;
		}

		// @todo: should we add a JS function to the dropdowns to update the hidden field?
		$day    = new One_Form_Widget_Select_Dropdown($id.'Day', $id.'Day', NULL, array(), $days);
		$month  = new One_Form_Widget_Select_Dropdown($id.'Month', $id.'Month', NULL, array(), $months);
		$year   = new One_Form_Widget_Select_Dropdown($id.'Year', $id.'Year', NULL, array(), $years);
		$hidden = new One_Form_Widget_Scalar_Hidden($id, $name);

		// render the dropdowns
		// @todo: add config setting to change this order
		// @todo: add config setting to show/hide the time
		$day->render(array($id."day" => $matches[3]), $dom);
		$month->render(array($id."month" => $matches[2]), $dom);
		$year->render(array($id."year" => $matches[1]), $dom);
		$hidden->render($model, $dom);

		//return $node;
		$d->addDom($dom);
	}

	/**
	 * Render the widget as a JS datepicker
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 */
	private function renderJSCalendar( $model, One_Dom $d )
	{
		if(!is_null($this->getCfg('jquery'))) {
			return $this->renderJQueryDatepicker($model, $d);
		}

		$id    = $this->getID();
		$name  = $this->getName();
		$value  = $this->getValue( $model );

		$dom = One_Repository::getDom();

		preg_match('/^(\d{4})-(\d{2})-(\d{2})( (\d{2}):(\d{2}):(\d{2}))?$/' ,$value, $matches);

		One_Vendor::getInstance()->loadStyle('js/jscalendar/calendar-win2k-1.css', 'head');
		One_Vendor::getInstance()->loadScript('js/jscalendar/calendar.js', 'head', 10);
//		One_Vendor::getInstance()->loadScript('js/jscalendar/lang/calendar-'.strtolower(substr(One::getInstance()->getLanguage(), 0, 2)).'.js', 'head', 11); // problems with language packs so stick to EN
		One_Vendor::getInstance()->loadScript('js/jscalendar/lang/calendar-en.js', 'head', 11);
		One_Vendor::getInstance()->loadScript('js/jscalendar/calendar-setup.js', 'head', 12);

		if($this->getCfg('jsFlat'))
		{
			$container = new One_Form_Container_Div($id . 'container');

			$extraParams = array('default' => $this->getDefault());
			if(in_array($this->getCfg('one'), array('one', 'yes', 'true', '1'))) {
				$extraParams['one'] = 'one';
			}
			$hidden    = new One_Form_Widget_Scalar_Hidden($id, $name, null, $extraParams);

			$container->render($model, $dom);
			$hidden->render($model, $dom);

			$defaultDate = date('Y-m-d');
			if('' != $value && '0000-00-00' != $value && '0000-00-00 00:00:00' != $value) {
				$defaultDate = $value;
			}

			$onloadscript = '
			Calendar.setup(
			{
				flat         : "' . $id . 'container",
				flatCallback : dateChanged,
				ifFormat     : "%Y-%m-%d' . ( ( trim( $this->getCfg( 'time' ) ) != '' ) ? ' %H:%M' : '' ) . '"' . ( ( trim( $this->getCfg( 'time' ) ) != '' ) ? ',
				showsTime    : true' : '' ) . ',
				date         : "'.$value.'"
			}
			);
			';

			$headscript = 'function dateChanged(calendar)
			{
				if(calendar.dateClicked)
				{
					var hidden = document.getElementById("' . $id . '");
					var year = calendar.date.getFullYear();
					var month = ((calendar.date.getMonth() + 1) < 10) ? "0" + (calendar.date.getMonth() + 1) : (calendar.date.getMonth() + 1);
					var day = (calendar.date.getDate() < 10) ? "0" + calendar.date.getDate() : calendar.date.getDate();

					hidden.value = year + "-" + month + "-" + day;
				}
			}';

			One_Vendor::getInstance()->loadScriptDeclaration($headscript, 'head', 10);
			One_Vendor::getInstance()->loadScriptDeclaration($onloadscript, 'onload');
		}
		else
		{
			$extraParams = array('readonly' => 'readonly', 'default' => $this->getDefault());
			if(in_array($this->getCfg('one'), array('one', 'yes', 'true', '1'))) {
				$extraParams['one'] = 'one';
			}

			$tf  = new One_Form_Widget_Scalar_Textfield($id, $name, NULL, $extraParams);
			$trigger = new One_Form_Widget_Image($id . 'trigger', $name . 'trigger', NULL, array('src' => One_Config::getInstance()->getUrl() . '/vendor/images/calendar.png', 'alt' => 'Show calendar', 'title' => 'Show calendar'));

			$tf->render($model, $dom);
			$trigger->render($model, $dom);

			$script = 'Calendar.setup(
			{
				inputField : "' . $name . '",
				ifFormat   : "%Y-%m-%d' . ( ( trim( $this->getCfg( 'time' ) ) != '' ) ? ' %H:%M' : '' ) . '",
				button     : "' . $name . 'trigger"' . ( ( trim( $this->getCfg( 'time' ) ) != '' ) ? ',
				showsTime    : true' : '' ) . '
			}
			);';

			One_Vendor::getInstance()->loadScriptDeclaration($script, 'onload');
		}

		//return $output;
		$d->addDom($dom);
	}

	protected function renderJQueryDatepicker($model, One_Dom $d)
	{
		// include most common jquery files from vendor
		One_Vendor::requireVendor('jquery/one_loader');

		$id    = $this->getID();
		$name  = $this->getName();
		$value  = $this->getValue( $model );

		$dom = One_Repository::getDom();

		$extraParams = array('default' => $this->getDefault());
		if(in_array($this->getCfg('one'), array('one', 'yes', 'true', '1'))) {
			$extraParams['one'] = 'one';
		}

		if('readonly' == $this->getCfg('readonly')) {
			$extraParams['readonly'] = 'readonly';
		}

		$tf  = new One_Form_Widget_Scalar_Textfield($id, $name, NULL, $extraParams);
		$tf->render($model, $dom);

		$pickerType = 'date';
		$timeFormat = '';
		if(trim($this->getCfg('time')) != '')  // add time addon if the datepicker should contain time as well
		{
			One_Vendor::getInstance()->loadScript('jquery/js/jquery-ui-timepicker-addon.js', 'head', 10);
			One_Vendor::getInstance()->loadStyle('jquery/css/ui.timepicker.addon.css', 'head', 10);
			$pickerType = 'datetime';
			$timeFormat = '
										timeFormat: "hh:mm:ss",';
		}
		$script = '
		jQuery("#'.$id.'").'.$pickerType.'picker({
										dateFormat: "yy-mm-dd",'.$timeFormat.'
										showButtonPanel: true'.(('' != trim($value)) ? ',
										defaultDate: "'.$value.'"' : '').'
									});';

		One_Vendor::getInstance()->loadScriptDeclaration($script, 'onload');

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

	/**
	 * Get the value for the widget
	 *
	 * @return mixed
	 */
	public function getValue( $model )
	{
		if ( !$model ) return null;

		if($this->getCfg('js'))
		{
			return parent::getValue( $model );
		}
		else
		{
			$oc    = new One_Context();
			$day   = ( $oc->get( $this->getName() . 'Day' ) );
			$month = ( $oc->get( $this->getName() . 'Month' ) );
			$year  = ( $oc->get( $this->getName() . 'Year' ) );

			if( $day == 0 || $month == 0 || $year == 0 )
				return '0000-00-00';
			else
				return $year . '-' . $month . '-' . $day;
		}
	}
}
