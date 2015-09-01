<?php
/**
 * Abstract parent of the search widgets
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 * @abstract
 **/
abstract Class One_Form_Widget_Search extends One_Form_Widget_Abstract
{
	/**
	 * Get the search value from the context
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		$session = One_Repository::getSession();
		if($session->varExists('usedSearchOptions', 'one_search')) {
			$cx   = new One_Context($session->get('usedSearchOptions', 'one_search'));
		}
		else {
			$cx   = new One_Context();
		}
		$name  = $this->getName();
		$value = $cx->get( $name );

		if( !is_null( $value ) )
		{
			return trim( $value );
		}
		else
			return NULL;
	}

	/**
	 * Get the op to use for the specified type
	 *
	 * @param string $type
	 * @return One_Form_Widget_Search_Op_Abstract
	 */
	public function getOp( $type )
	{
		$opClass = 'One_Form_Widget_Search_Op_' . ucfirst( strtolower( $type ) );
		if( class_exists( $opClass ) )
		{
			$op = new $opClass( $this->getName() );
			return $op;
		}
		else
		{
			return NULL;
		}
	}

	/**
	 * Returns all Options a Widget can have.
	 * Uses bitwise comparison to determine how to use it:
	 * 1: use in render
	 * 2: use to define the way to render
	 * 4: used to set a value in the class
	 *
	 * Add the Numbers to eachother to make combinations
	 *
	 * @return array
	 * @static
	 */
	protected static function allowedOptions()
	{
		return array(
						'type' => 4,
						'constraint' => 4,
						'class' => 1,
						'title' => 1,
						'style' => 1,
						'lblLast' => 2,
						'default' => 2,
						'role' => 4,
						'targetAttribute' => 4,
						'novalue' => 2,
						'noLabel' => 2,
						'from' => 2,
						'optype' => 2,
						'language' => 2
					);
	}

	/**
	 * Get the widget's current name, value and operator
	 * @return array Array containing the name, value and operator of the widget
	 */
	public function getWidgetData()
	{
		$name   = (!is_null($this->getCfg('role'))) ? $this->getCfg('role') : $this->getName();
		$value  = (0 == count($this->getValue())) ? null : $this->getValue();
		$op     = $this->getOp($this->getCfg('optype'));

		return array(
						'name'     => $name,
						'value'    => $value,
						'operator' => $op
					);
	}
}
