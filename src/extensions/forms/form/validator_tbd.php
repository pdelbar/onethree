<?php
/**
 * Checks whether a form has been submitted correctly
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Validator
{
	/**
	 * Has the value been filled in?
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	public static function isFilledIn( $value )
	{
		if( is_array( $value ) )
			return ( count( $value ) > 0 );
		else
			return ( is_null( $value ) || trim( $value ) != '');
	}

	/**
	 * Is the date given in the form of "YYYY-mm-dd" OR "YYYY-mm-dd hh:mm(:ss)?" and is it a required field
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	public static function isValidDate($date, $isRequired = false)
	{
		if( $isRequired && ( is_null( $date ) || trim( $date ) == '') )
			return false;

		if( preg_match( '/^(\d{4})-(\d{2})-(\d{2})( (\d{2}):(\d{2})(:(\d{2}))?)?$/', $date ) > 0 || ( !$isRequired && ( is_null( $date ) || trim( $date ) == '' ) ) )
			return true;

		return false;
	}
}
