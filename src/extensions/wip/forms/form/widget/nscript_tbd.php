<?php
/**
 * Handles the nscript widget
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Widget_Nscript extends One_Form_Widget_Abstract
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
		$this->_type = 'nscript';
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
							'src' => 4
							);
		return array_merge( parent::allowedOptions(), $additional );
	}

	/**
	 * Render the output of the widget and add it to the DOM
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 */
	protected function _render( $model, One_Dom $dom )
	{
		$src = $this->getCfg( 'src' );
		if( trim( $src ) == '' )
			throw new One_Exception("A field of type 'nscript' should have a 'src'-attribute defining the nanoScript file to parse.");

		One_Script_Factory::saveSearchPath();
		One_Script_Factory::clearSearchPath();

		$useLang = $this->getCfg('language');
		if('' == trim($useLang)) {
			$useLang = strtolower(One_Config::get('app.language'));
		}

        die ('deprecated stuff found in ' . __FILE__ .':'.__LINE);
    $cps = One_Config::getInstance()->getCustomPaths();
    foreach ($cps as $cp)
		  One_Script_Factory::addSearchPath( $cp.'/views/'.One_Config::get('app.name').'/'.$model->getScheme()->getName().'/language/'.$useLang.'/');
    foreach ($cps as $cp)
		  One_Script_Factory::addSearchPath($cp.'/views/'.One_Config::get('app.name').'/'.$model->getScheme()->getName().'/');

		$ns = new One_Script();
		$ns->load( $src );

		if (!$ns->isError())
		{
			if ($this->getID()) $ns->set('id', $this->getID());
			if ($this->getName()) $ns->set('name', $this->getName());
			if ($this->getLabel()) $ns->set('label', $this->getLabel());
			if ($this->getValue($model)) $ns->set('value', $this->getValue($model));
			$ns->set('model', $model);

			$dom->add($ns->execute());
		} else {
			throw new One_Exception( $ns->error );
		}

		$dom->add($this->value);

		One_Script_Factory::restoreSearchPath();
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
