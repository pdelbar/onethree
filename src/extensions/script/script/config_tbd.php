<?php
//-------------------------------------------------------------------------------------------------
// One_Script_Config
//
// Definitions for nanoScript classes.
//-------------------------------------------------------------------------------------------------

class One_Script_Config {

	const NSNODE_OPEN = '{';
	const NSNODE_CLOSE = '}';

	public static $nodeExternalHandlers = array();

	public static $packages = array();

	public static $nsLanguagePackage;                               // use this package to make every search automatically language sensitive

	public static $ncEnabled = 0;

	public static $ncServer;

	public static $ncDatabase;

	public static $ncUser;

	public static $ncPass;

	public static $nsContentStoreClass = 'One_Script_Content_Store'; 	//PD27OCT09: added to store One_Script_Content_Store subclass

	public static $expressionAllowsFunctions = true;

	public static $defaultNamespace = 'lang';

	public static $expressionRestrictFunctions = array();

	public static $expressionForbidFunctions = array(
		"die",
		"eval",
		"echo",
		"print",
		);

	public static $ignoreTags = array(
		"mosimage",
		"mosbreak",
		"loadposition",
		);

	public static $nodeBlockTokens = array(
		"section"	=>	"endsection",
		"loop"		=>	"endloop",
		"while"		=>	"endwhile",
		"script"		=>	"endscript",
		);

	/**
	 * tagHandlers are responsible for the detection of new types of tag types during the
	 * token parsing process. They help extend the language. This is the array containing all handlers,
	 * containing instances of a subclass of One_Script_Tag_Handler
	 *
	 * @var array
	 */
	public static $tagHandlers = array(
		);

	public static function addBlockToken( $token, $endToken )
	{
		self::$nodeBlockTokens[ $token ] = $endToken;
	}

	public static function addIgnoreTag( $tag )
	{
		self::$ignoreTags[] = $tag;
	}

	public static function addHandler( $handler )
	{
		array_unshift( self::$tagHandlers, $handler );
	}

	public static function setNanoContentBase( $server, $database, $user, $pass )
	{
					self::$ncServer = $server;
					self::$ncDatabase = $database;
					self::$ncUser = $user;
					self::$ncPass = $pass;

					self::$ncEnabled = 1;
	}
}

One_Script_Config::addHandler( new One_Script_Tag_Handler_Catchall() );
One_Script_Config::addHandler( new One_Script_Tag_Handler_Ignore() );

One_Script_Config::$ncEnabled = 0;

// load custom config if there is any
$additionalConfigPaths = One_Locator::locate('script/config_add.php');
if (is_array($additionalConfigPaths)) foreach ($additionalConfigPaths as $additionalConfig )
	include_once( $additionalConfig );

