<?php
//------------------------------------------------------------------
// package Datetime : functions to handle DateTimes
//------------------------------------------------------------------

	class One_Script_Package_Datetime extends One_Script_Package
	{
		public static function create( $dateTime = NULL )
		{
			if( is_null( $dateTime ) )
				return new DateTime();
			else
				return new DateTime( $dateTime );
		}

		public static function daysDifferenceFromNow( DateTime $dateTime )
		{
			$now  = time();
			$then = $dateTime->format( 'U' );
			$diff = round( ( $then - $now ) / 86400 );

			return $diff;
		}
	}
