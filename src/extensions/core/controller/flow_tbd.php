<?php
class One_Controller_Flow
{
	/**
	 * cache for flowFiles
	 * @var array
	 */
	protected static $_flowCache = array();

	/**
	 * list of all redirects to follow
	 * @var array
	 */
	protected $_redirects = array();

	protected function __construct(One_Scheme $scheme, $redirects = array())
	{
		if(!array_key_exists(One_Config::get('app.name'), self::$_flowCache))
		{
			self::$_flowCache[One_Config::get('app.name')] = array();
		}
		$this->_redirects = array_merge(self::getFlow($scheme), $redirects);
	}

	/**
	 * Get an instance of One_Controller_Flow for the proper scheme
	 *
	 * @param One_Scheme $scheme
	 * @param array $redirects
	 * @return One_Controller_Flow
	 */
	public static function getInstance(One_Scheme $scheme, array $redirects = array()) {
		if(!array_key_exists(One_Config::get('app.name'), self::$_flowCache)
			|| !array_key_exists($scheme->getName(), self::$_flowCache[One_Config::get('app.name')]))
		{
			self::$_flowCache[One_Config::get('app.name')][$scheme->getName()] = new One_Controller_Flow($scheme, $redirects);
		}

		return self::$_flowCache[One_Config::get('app.name')][$scheme->getName()];
	}

	public function setRedirects(array $redirects = array())
	{
		$this->_redirects = $redirects;
		return $this;
	}

	public function getRedirects()
	{
		return $this->_redirects;
	}

	public function setRedirect($task, array $parts = array())
	{
		$this->_redirects[$task] = $parts;
		return $this;
	}

	public function getRedirect($task)
	{
		if(array_key_exists($task, $this->_redirects)) {
			return $this->_redirects[$task];
		}
		else {
			return NULL;
		}
	}

	public static function getFlow(One_Scheme $scheme)
	{
		return One_Controller_Flow_Reader_XML::load($scheme);
	}
}