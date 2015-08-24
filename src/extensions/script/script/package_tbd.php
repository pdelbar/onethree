<?php
//-------------------------------------------------------------------------------------------------
// 	One_Script_Package
//
//	One_Script_Package makes it possible to extend the nanoScript functionality.
//-------------------------------------------------------------------------------------------------

class One_Script_Package
{
	//--------------------------------------------------------------------------------
	// register : add this package
	//--------------------------------------------------------------------------------

//	function register( $package, $packageClass = "" )
//	{
//		One_Script_Config::$packages[ $package ] = ($packageClass == "") ? $package : $packageClass;
//	}

	//--------------------------------------------------------------------------------
	// registerHandler : add a handler function
	//--------------------------------------------------------------------------------

//	function registerHandler( $term, $handler )
//	{
//		One_Script_Config::$nodeExternalHandlers[ $term ] = $handler;
//	}

	//--------------------------------------------------------------------------------
	// call : call the selected method
	//--------------------------------------------------------------------------------

	public static function call( $package, $method, $ignore = NULL )
	{
//		echo "<br>Calling $package:$method";

		$packageClass = 'One_Script_Package_' . ucfirst(strtolower($package));

		if (class_exists( $packageClass ))
		{
			$arg_list = func_get_args();
			$numargs = count($arg_list);
	   		return call_user_func_array( array( $packageClass, $method ), array_slice( $arg_list,2 ) );
		}
		else
			return "One_Script_Package error : no class defined for package '$package'";
	}

	public static function isPackage( $pkName )
	{
		return One_Script_Loader::isPackage($pkName);
	}


	//--------------------------------------------------------------------------------
}

//$nanoFiles = scandir( dirname( __FILE__ ) );
//
//foreach( $nanoFiles as $nanoFile )
//{
//	if( preg_match( '/package\.([^\.]+)\.php/i', $nanoFile ) )
//		require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . $nanoFile );
//}
