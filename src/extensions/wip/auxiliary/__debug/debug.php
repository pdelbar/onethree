<?php
class One_Debug
{
	/**
	 * Instance of FirePHP
	 * @var FirePHP
	 */
	protected static $debugger = NULL;

	/**
	 * Location of the file holding the configuration settings
	 * @var string
	 */
	protected static $config = NULL;

	/**
	 * Return the location of the config file
	 * @return string
	 */
	protected static function getConfigFile()
	{
		return self::$config;
	}

	/**
	 * Set the location of the config file
	 * @param string $configFile
	 */
	public static function setConfigFile($configFile)
	{
		self::$config = $configFile;
	}

	protected static function getDebugger()
	{
		if(is_null(self::$debugger))
		{
			header('x-insight: inspect');
			$_SERVER['x-insight'] = 'inspect';

			set_include_path(ONE_LIB_PATH.'/../vendor/firephp/'
                 . PATH_SEPARATOR
                 . get_include_path());

			define('INSIGHT_CONFIG_PATH', self::getConfigFile());

			require_once('FirePHP/Init.php');
			self::$debugger = FirePHP::getInstance(true);
			self::$debugger->setEnabled(true);
		}

		return self::$debugger;
	}

	public function log($object = NULL, $label = NULL, $options = array())
	{
		$debugger = self::getDebugger();
		$debugger->log($object, $label, $options);
	}

	public function error($object = NULL, $label = NULL, $options = array())
	{
		$debugger = self::getDebugger();
		$debugger->error($object, $label, $options);
	}
}