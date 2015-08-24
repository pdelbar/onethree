<?php
/**
 * Makes a scheme searchable
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Behavior_Searchable extends One_Behavior
{
	/**
	 * Return the name of the behaviour
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'searchable';
	}

	/**
	 * Adds the fields that are searchable to the scheme on loading of the scheme
	 *
	 * @param One_Scheme $scheme
	 */
	public function onLoadScheme( $scheme )
	{
		$options = $scheme->get('behaviorOptions.searchable' );

		if( is_null( $options['search'] ) || trim( $options['search'] ) == '' )
			throw new One_Exception( 'When defining a searchable behavior, you must define an attribute.' );

		$scheme->oneSearchableFields = explode( ':', $options['search'] );
	}
}
