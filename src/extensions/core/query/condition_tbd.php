<?php
/**
 * Instances of this class represent a queryCondition containing the attribute in question,
 * the operator to use and the value to match against.
 * In MySQL terms, this would be a part of the WHERE or HAVING clause
 *

ONEDISCLAIMER
 **/
class One_Query_Condition
{
	/**
	 * @var string
	 */
	public $attribute;

	/**
	 * @var string
	 */
	public $op;

	/**
	 * @var mixed
	 */
	public $value;

	/**
	 * Class constructor
	 *
	 * @param string $attribute
	 * @param string $op
	 * @param mixed $value
	 */
	public function __construct( $attribute, $op, $value )
	{
		$this->attribute = $attribute;
		$this->op = $op;
		$this->value = $value;
	}

	public function dump()
	{
		echo '<br/><b>CONDITION </b> : ', $this->attribute, ' ', $this->op, ' ', $this->value;

	}
}
