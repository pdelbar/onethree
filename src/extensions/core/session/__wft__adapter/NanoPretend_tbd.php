<?php
/**
 * The NanoPretend __wft__adapter is exactly the same as the Nano __wft__adapter, but it pretends
 * there are no issues with nano by hiding all errors. Experimental
 *


  * @TODO review this file and clean up historical code/comments
 * @subpackage Template
ONEDISCLAIMER

 **/
class One_Template_Adapter_NanoPretend extends One_Templater_Nano
{
	static $_old_error_level;

	/**
	 * Class constructor
	 * @param array $searchpaths
	 */
	public function __construct( array $searchpaths = array(), $setSearchpaths = true )
	{
		self::turnErrorsOff();
		parent::__construct( $searchpaths, $setSearchpaths );
		self::turnErrorsOn();
	}

	public function setFile( $filename )
	{
		self::turnErrorsOff();
		$result = parent::setFile( $filename );
		self::turnErrorsOn();
		return $result;
	}

	/**
	 * Parse the template or if $section is set and the section exists, parse the specified section
	 * @param string $section
	 */
	public function parse( $section = NULL )
	{
		self::turnErrorsOff();
		$result = parent::parse($section);
		self::turnErrorsOn();
		return $result;

	}

	public static function turnErrorsOff()
	{
		set_error_handler('One_Template_Adapter_NanoPretend::_ignoreErrorCallback');
//		self::$_old_error_level = error_reporting(0);
	}

	public static function turnErrorsOn()
	{
		restore_error_handler();
//		error_reporting(self::$_old_error_level);
	}

	public static function _ignoreErrorCallback($errno, $errstr)
	{
//		throw new Exception('Nano error');
	}
}