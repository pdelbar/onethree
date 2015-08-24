<?php
/**
 * One_Script_Content_Store represents the class which implements teh functionality to load a namespace from
 * some form of stoage (database, INI files, ...) taking into account all relevant characteristics the
 * application needs to determine the proper context : language, user, application module, ...)
 */
class One_Script_Content_Store
{

	public static function loadNamespace( $namespace )
	{
		die( 'One_Script_Content_Store: cold not find an implementation, please set One_Script_Config::$nsContentStoreClass' );
	}

}
