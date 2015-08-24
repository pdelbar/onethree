<?php
class One_Vendor
{
	/**
	 * Instance of One_Vendor
	 * @var One_Vendor
	 */
	protected static $_instance = NULL;

	/**
	 * List of scripts to load
	 * @var array
	 */
	protected $_loadScripts = array(
										'head' => array(
															'css'    => array(),
															'cssdec' => array(),
															'js'     => array(),
															'jsdec'  => array()
														),
										'_onload' => array(
															'css'    => array(),
															'cssdec' => array(),
															'js'     => array(),
															'jsdec'  => array()
														),
										'body' => array(
															'css'    => array(),
															'cssdec' => array(),
															'js'     => array(),
															'jsdec'  => array()
														),
									);

	/**
	 * Absolute filepath to the root folder of the vendor files
	 * @return string
	 */
	protected $_filePath = NULL;

	/**
	 * Sitepath to the root folder of the vendor files
	 * @return string
	 */
	protected $_sitePath = NULL;

	/**
 	 * Get a One_Vendor instance
	 * @return One_Vendor
	 */
	public static function getInstance()
	{
		if(empty(self::$_instance)) {
			self::$_instance = new One_Vendor();
		}

		return self::$_instance;
	}

	/**
	 * Remove the instance
	 * @return void
	 */
	public static function unsetInstance()
	{
		self::$_instance = NULL;
	}

	/**
	 * Get the filepath of the vendor files
	 * @return string
	 */
	public function getFilePath()
	{
		return $this->_filePath;
	}

	/**
	 * Set the filepath of the vendor files
	 * @param string $path Path
	 * @return One_Config
	 */
	public function setFilePath($path)
	{
		$this->_filePath = $path;
		return $this;
	}

	/**
	 * Get the filepath of the vendor files
	 * @return string
	 */
	public function getSitePath()
	{
		return $this->_sitePath;
	}

	/**
	 * Set the filepath of the vendor files
	 * @param string $path Path
	 * @return One_Config
	 */
	public function setSitePath($path)
	{
		$this->_sitePath = $path;
		return $this;
	}

	/**
	 * Easier way of requiring php-files from the One_Vendor files
	 * Deliberately not using dot-notation because we do not control the vendor's naming convention (E.G.: path/to/class.classname.php as path.to.clas.classname.php would fail)
	 * @param string $path Path to the php-files without the actual .php extension
	 */
	public static function requireVendor($dotpath)
	{
		$relativePath = self::securePath($dotpath);

		// Get the proper desired filename and see if it exists
		$filename = self::getInstance()->getFilePath().'/'.$relativePath.'.php';
		if(!file_exists($filename)) {
			throw new One_Exception('"'.$relativePath.'" could not be found');
		}

		// If all is well, actually require the file
		require_once $filename;

		return $this;
	}

	/**
	 * Prepare loading a javascript-file into the system and avoid double loads
	 * This function should only be used for One_Vendor files
	 * @param string $path relative path to the javascript file
	 * @param string section where the script should be loaded. "head" to put it in the <head>-tags, "body" to render it just before </body>
	 * @param int $weight Ability to set an order in files to load
	 */
	public function loadScript($path, $section = "head", $weight = 0)
	{
		$relativePath = self::securePath($path);
		$this->loadScriptStyle('js', $relativePath, $section, $weight);

		return $this;
	}

	/**
	 * Prepare loading javascript-code into the system and avoid double loads
	 * @param string $code javascript-code to load without the script-tags
	 * @param string section where the script should be loaded. "head" to put it in the <head>-tags, "body" to render it just before </body>
	 * @param int $weight Ability to set an order in files to load
	 */
	public function loadScriptDeclaration($code, $section = "head", $weight = 0)
	{
		$this->loadScriptStyle('jsdec', $code, $section, $weight);

		return $this;
	}

	/**
	 * Prepare loading a css-file into the system and avoid double loads
	 * This function should only be used for One_Vendor files
	 * @param string $path relative path to the css file
	 * @param string section where the script should be loaded. "head" to put it in the <head>-tags, "body" to render it just before </body>
	 * @param int $weight Ability to set an order in files to load
	 */
	public function loadStyle($path, $section = "head", $weight = 0)
	{
		$relativePath = self::securePath($path);
		$this->loadScriptStyle('css', $relativePath, $section, $weight);

		return $this;
	}

	/**
	 * Prepare loading css-styles into the system and avoid double loads
	 * @param string $style css-styles to be used without the style-tags
	 * @param string section where the script should be loaded. "head" to put it in the <head>-tags, "body" to render it just before </body>
	 * @param int $weight Ability to set an order in files to load
	 */
	public function loadStyleDeclaration($style, $section = "head", $weight = 0)
	{
		$this->loadScriptStyle('cssdec', $style, $section, $weight);

		return $this;
	}

	/**
	 * Prepare loading a javascript into the system and avoid double loads
	 * This function should only be used for One_Vendor files
	 * @param string $type Should only be "js", "jsdec", "css" or "cssdec"
	 * @param string $pathCode relative path to a file or code/styles
	 * @param string section where the script should be loaded. "head" to put it in the <head>-tags, "body" to render it just before </body> or "onload" to put it in the <head>-tags as onload
	 * @param int $weight Ability to set an order in files to load
	 */
	protected function loadScriptStyle($type, $pathCode, $section = "head", $weight = 0)
	{
		$key = md5(trim($pathCode));

		switch(strtolower(trim($section)))
		{
			case 'body':
				$cleanSection = 'body';
				break;
			case 'onload': // only for javascript declarations
				$cleanSection = '_onload';
				if('jsdec' != $type) {
					$cleanSection = 'head';
				}
				break;
			case 'head':
			default:
				$cleanSection = 'head';
				break;
		}

		if(!array_key_exists($key, $this->_loadScripts[$cleanSection][$type])) {
			$this->_loadScripts[$cleanSection][$type][$key] = array('pathcode' => trim($pathCode), 'weight' => intval($weight), 'rendered' => false);
			uasort($this->_loadScripts[$cleanSection][$type], array($this, 'sortLoadByWeight'));
		}
	}

	/**
	 * Render all the needed scripts and styles on the given content
	 * @param string $content Entire HTML page with <head>- and <body>-tags
	 * @return string given $content with scripts and styles added
	 */
	public function renderLoadsOnContent($content)
	{
		$scriptedContent = $content;
		$heads           = array();
		$onloads         = array();
		$bodys         = array();

		foreach($this->_loadScripts as $section => $types)
		{
			foreach($types as $type => $codestyles)
			{
				if(0 < count($codestyles))
				{
					foreach($codestyles as $key => $codestyle)
					{
						$code = '';
						switch($type)
						{
							case 'js':
								if(preg_match('!http(s?)://!', $codestyle['pathcode']) > 0) {
									$url = $codestyle['pathcode'];
								}
								else {
									$url = $this->getSitePath().'/'.$codestyle['pathcode'];
								}
								$code = '<script type="text/javascript" src="'.$url.'"></script>';
								break;
							case 'jsdec':
								if('_onload' == $section) {
									$code = $codestyle['pathcode'];
								}
								else {
									$code = '<script type="text/javascript">'.$codestyle['pathcode'].'</script>';
								}
								break;
							case 'css':
								if(preg_match('!http(s?)://!', $codestyle['pathcode']) > 0) {
									$url = $codestyle['pathcode'];
								}
								else {
									$url = $this->getSitePath().'/'.$codestyle['pathcode'];
								}
								$code = '<link rel="stylesheet" href="'.$url.'" type="text/css" />';
								break;
							case 'cssdec':
								$code = '<style type="text/css">'.$codestyle['pathcode'].'</style>';
								break;
						}

						if('' != $code && !$codestyle['rendered'])
						{
							switch ($section) {
								case 'head':
										$heads[] = $code;
										break;
								case 'body':
										$bodys[] = $code;
										break;
								case '_onload':
										$onloads[] = $code;
										break;
							}
							$this->_loadScripts[$section][$type][$key]['rendered'] = true;
						}
					}
				}
			}
		}

		if(0 < preg_match_all('!\<\s*/\s*head\s*\>!i', $scriptedContent, $headMatches)) {
			$headparts = preg_split('!\<\s*/\s*head\s*\>!i', $scriptedContent);

			$allHeads = array();
			if(0 < count($heads)) {
				$allHeads = $heads;
			}

			if(0 < count($onloads))
			{
				$onload = '
		<script type="text/javascript">
			function doOneContentOnloadActions() {
					';

				$onload .= implode("\n\n\t\t\t", $onloads);

				$onload .= '
			}

			if(typeof(jQuery) !== "undefined") {
				jQuery(document).ready(function(){
					doOneContentOnloadActions()
				});
			}
			else{
				window.addEvent("domready", function(){
					doOneContentOnloadActions()
				});
			}
	</script>';

				$allHeads[] = $onload;

			}

			if(0 < count($allHeads)) {
				$scriptedContent = substr_replace($scriptedContent, implode("\n", $allHeads), strlen($headparts[0]), 0);
			}
		}

		if(0 < count($bodys) && 0 < preg_match_all('!\<\s*/\s*body\s*\>!i', $scriptedContent, $bodyMatches)) {
			$bodyparts = preg_split('!\<\s*/\s*body\s*\>!i', $scriptedContent);

			$lastBody = strlen($bodyMatches[0][(count($bodyMatches[0]) - 1)]);
			$lastPart = strlen($bodyparts[(count($bodyparts) - 1)]);
			$lastPos = ($lastBody + $lastPart) * -1;

			$scriptedContent = substr_replace($scriptedContent, implode("\n", $bodys), $lastPos, 0);
		}

		return $scriptedContent;
	}

	/**
	 * Function to sort the loadscriptstyles by weight
	 * @param array $a
	 * @param array $b
	 */
	protected function sortLoadByWeight(array $a, array $b)
	{
		if($a['weight'] > $b['weight']) {
			return 1;
		}
		else if($a['weight'] < $b['weight']) {
			return -1;
		}
		else {
			return 0;
		}
	}

	/**
	 * Perform necessary actions to secure a path (remove ../ and so on to prevent abuse)
	 * @param string $path
	 * @return string
	 */
	protected static function securePath($path)
	{
		if(preg_match('!http(s?)://!', $path) > 0) {
			return $path;
		}

		// Replace all . and \ by /
		// This to avoid requiring paths that shouldn't be required
		$path = trim(str_replace(array('..', '\\'), '/', $path));

		// Cleanup first slashes
		while('/' == substr($path, 0, 1)) {
			$path = substr($path, 1);
		}

		$path = preg_replace('!/{2,}!', '', $path);

		return $path;
	}
}