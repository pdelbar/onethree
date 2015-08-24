<?php
/**
 * Handles the file widget
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/

Class One_Form_Widget_File extends One_Form_Widget_Abstract
{
	protected $_totf;

	public function __construct( $id = NULL, $name = '', $label = NULL, array $config = array() )
	{
		parent::__construct($id, $name, NULL, $label, $config );
		$this->_type = 'fileupload';
		$this->_totf = 'file';
	}

	protected static function allowedOptions()
	{
		$additional = array(
							'dir' => 1,
							'lang' => 1,
							'xml:lang' => 1,
							'disabled' => 1,
							'size' => 1,
							'accept' => 1
							);
		return array_merge(parent::allowedOptions(), $additional);
	}

	public function validate()
	{
		return true;
	}

	protected function _render( $model, One_Dom $d )
	{
		$id     = $this->getID();
		$name   = $this->getFormName();
		$label  = $this->getLabel();
		$info    = $this->getCfg('info');
		$error   = $this->getCfg('error');

		$dom = One_Repository::getDom();

		$this->setCfg('class', 'OneFieldInput' . $this->getCfg('class'));

		$events = $this->getEventsAsString();
		$params = $this->getParametersAsString();

		//$output = '';

		if( !is_null($label) )
		{
			//$output .= '<label class="OneFieldLabel" for="' . $id . '">' . $label . '</label>'."\n";
			$dom->add('<label class="OneFieldLabel" for="' . $id . '">' . $label . '</label>'."\n");
		}

		//$output .= '<input type="'.$this->_totf.'" id="' . $id . '" name="' . $name . '"' . $events . $params . ' />'."\n";
		$dom->add('<input type="'.$this->_totf.'" id="' . $id . '" name="' . $name . '"' . $events . $params . ' />'."\n");

		if(is_null($info))
			//$output .= '<span id="' . $id . 'Info" class="OneInfo">' . $info . '</span>';
			$dom->add('<span id="' . $id . 'Info" class="OneInfo">' . $info . '</span>');

		if(is_null($error))
			//$output .= '<span id="' . $id . 'Error" class="OneError">' . $error . '</span>';
			$dom->add('<span id="' . $id . 'Error" class="OneError">' . $error . '</span>');

		//return $output;
		$d->addDom($dom);

	}

	public function __toString()
	{
		return get_class() . ': ' . $this->getID();
	}

	/* Getters */
	public function getValue()
	{
		return NULL;
	}

	public function getDefault()
	{
		return NULL;
	}

	/* Setters */
}
