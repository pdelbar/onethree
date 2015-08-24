<?php
/**
 * Authorisation-rule to allow a task in the Joomla! administrator application
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Permission_Rule_Jbackend extends One_Permission_Rule // @TODO move to custom
{
	public function __construct( $options = array() )
	{
		parent::__construct( $options );
		$this->rules = array();
	}

	public function authorize( array $args )
	{
		$app = JFactory::getApplication();
		return ( preg_match( '/admin(istrator)?/i', $app->getName() ) > 0 );
	}
}
