<?php
/**
 * Permission class that joins multiple rules and checks whether all authorisations are deemed true
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Permission_Rule_Joiner extends One_Permission_Rule
{
	/**
	 * @var array List of rules contained in the joiner
	 */
	private $rules;

	/**
	 * Class constructor
	 *
	 * @param array $options
	 */
	public function __construct( $options = array() )
	{
		parent::__construct( $options );
		$this->rules = array();
	}

	/**
	 * Add a rule to the joiner
	 *
	 * @param One_Permission_Rule $rule
	 */
	public function addRule(One_Permission_Rule $rule )
	{
		$this->rules[] = $rule;
	}

	/**
	 * Checks whether the user is authorised with all rules within the joiner
	 * according to the type passed along, "and" or "or"
	 *
	 * @param array $args Arguments passed to the authorize function
	 * @return boolean
	 */
	public function authorize(array $args )
	{
		$type = $this->options['type'];
		$not  = ( $this->options['not'] == 'not' ) ? true : false; // @TODO fix so and and or can have "not" parameter

		switch ($type) {

			case 'and' :
				if ($this->rules) foreach ($this->rules as $rule) if (!$rule->authorize( $args )) return false;
				return true;
				break;

			case 'or' :
				if ($this->rules) foreach ($this->rules as $rule) if ($rule->authorize( $args)) return true;
				return false;
				break;

			default:
				return true;
		}
	}
}
