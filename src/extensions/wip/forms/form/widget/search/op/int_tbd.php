<?php
/**
 * Handles search option for ints
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Form_Widget_Search_Op_Int extends One_Form_Widget_Search_Op_Abstract
{
	/**
	 * Class constructor
	 *
	 * @param One_Form_Widget_Abstract $forWidget
	 */
	public function __construct( $forWidget )
	{
		$this->forWidget = $forWidget;
		$this->type      = 'int';
	}

	/**
	 * Render the op
	 */
	public function render()
	{
		$data = array(
						'for'   => $this->forWidget,
						'value' => $this->getValue()
					);

		$content = $this->parse( $data );
		return $content;
	}

	/**
	 * Affect the One_Query appropriately
	 *
	 * @param One_Query $q
	 * @param string $attrName
	 * @param mixed $attrVal
	 * @final
	 */
	final public function affectQuery( One_Query $q, $attrName, $attrVal )
	{
		$allowed = self::getAllowed();
		$opVal   = $this->getValue();

		if( !is_null( $opVal ) )
		{
			if( in_array( $opVal, $allowed ) )
				$op = $opVal;
			else
				$op = $allowed[ 'default' ];
		}

		$q->where( $attrName, $op, $attrVal );
	}

	/**
	 * Get the allowed operators
	 *
	 * @return array
	 * @final
	 */
	final public function getAllowed()
	{
		return array(
						'default' => 'eq',
						'eq',
						'neq',
						'lt',
						'lte',
						'gt',
						'gte'
					);
	}
}