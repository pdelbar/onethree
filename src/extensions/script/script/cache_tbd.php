<?php
//-------------------------------------------------------------------------------------------------
// 	One_Script_Cache
//
//	Caches code.
//-------------------------------------------------------------------------------------------------


class One_Script_Cache
{
	private static $cache = array();

	public static function inCache( $path )
	{
		return isset(self::$cache[ $path ]) ? $path : false;
	}

	public static function get( $path )
	{
		return self::inCache($path) ? self::$cache[ $path ] : false;
	}

	public static function cache( $path, &$rootNode )
	{
		self::$cache[ $path ] = $rootNode;
	}

}
