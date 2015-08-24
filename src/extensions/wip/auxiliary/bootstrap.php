<?php
/**
 * @deprecated
 */
class One_Bootstrap
{
	public static function initiate( $siteRoot, $path, $customPath )
	{
		throw new One_Exception_Deprecated('do your own bootstrapping, dude');
		// Register the autoloader
		One_Loader::register();

		One_Config::getInstance()
			->setUrl($siteRoot)
			->setCustomPath($customPath )
			->setUserStore('mysql')
			->setTemplater(new One_Template_Adapter_NanoPretend);
//		define( 'ONETEMPLATER', 'nano');

//		$tmp = $path . DIRECTORY_SEPARATOR . 'nano' . DIRECTORY_SEPARATOR;
//		define( 'ONE_SCRIPT_PATH', $tmp );
//		define( 'ONE_SCRIPT_CUSTOM_PATH', $customPath . DIRECTORY_SEPARATOR . 'nano' );
//
//		require_once( $tmp . 'tools' . DIRECTORY_SEPARATOR . 'autoload.php' );
		require_once( ONE_LIB_PATH . '/tools.php' );


	}
}
