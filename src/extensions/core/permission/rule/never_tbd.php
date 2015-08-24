<?php
/**
 * Authorisation-rule to never allow a task
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Permission_Rule_Never extends One_Permission_Rule
{
	public function __construct( $options = array() )
	{
		parent::__construct( $options );
		$this->rules = array();
	}

	public function authorize( array $args )
	{
		return false;
	}
}
