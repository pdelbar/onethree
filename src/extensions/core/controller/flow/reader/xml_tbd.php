<?php
class One_Controller_Flow_Reader_XML implements One_Controller_Flow_Reader_Interface
{
	/**
	 * Parse the flow definition and return an array with all redirects
	 * @param One_Scheme $scheme
	 * @return array
	 */
	public static function load(One_Scheme $scheme)
	{
		$defaultFile = self::getFlowFile('default');
		$file = self::getFlowFile($scheme->getName());

		$redirects = self::parseFile($defaultFile, true);

		if($file !== null) {
			$redirects = array_merge($redirects, self::parseFile($file));
		}

		foreach($redirects as $key => $parts)
		{
			if(isset($parts['scheme']) && strtoupper(trim($parts['scheme'])) == '::SCHEME::') {
				$redirects[$key]['scheme'] = $scheme->getName();
			}
		}

		return $redirects;
	}

	protected static function parseFile($file, $default = false)
	{
		$redirects = array();

		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->load($file);

		$xpath = new DOMXPath($dom);

		if($default)
		{
			$default = $xpath->query('/redirects/redirect[@task="default"]');
			if($default->length != 1)
			{
				throw new One_Exception('You must set 1 default flow in "'.$file.'"');
			}
		}

		$rawRedirects = $xpath->query('/redirects/redirect');
		if($rawRedirects->length > 0)
		{
			for($i = 0; $i < $rawRedirects->length; $i++)
			{
				$redirect = $rawRedirects->item($i);

				$task = $redirect->getAttribute('task');
				$type = ($redirect->hasAttribute('type')) ? $redirect->getAttribute('type') : 'string';
				$content = trim($redirect->textContent);

				switch(strtolower($type))
				{
					case 'script':
						$parts = array();
						break;
					case 'string':
					default:
						parse_str($content, $parts);
						break;
				}

				if($redirect->hasAttribute('alias'))
				{
					$aliasses = $redirect->getAttribute('alias');
					$aliasses = preg_split('/\s*,\s*/', trim($aliasses));

					foreach($aliasses as $alias) {
						$redirects[$alias] = $parts;
					}
				}

				if($redirect->hasAttribute('default') && in_array(strtolower($redirect->getAttribute('default')), array('1', 'yes', 'default', 'true')))
				{
					$redirects['default'] = $parts;
				}

				$redirects[$task] = $parts;
			}
		}

		return $redirects;
	}

	public function getFlowFile($fileName)
	{
    // search for appropriate flow definition xml file

    $pattern = "%ROOT%/meta/flows/";
    $filepath = One_Locator::locateUsing($fileName.'.xml',$pattern);
    return $filepath;

//
//
//    $paths = array();
//		$paths[] = One::getInstance()->getCustomPath().'/meta/flows/'.One::getInstance()->getApplication().'/';
//		$paths[] = One::getInstance()->getCustomPath().'/meta/flows/';
//		$paths[] = One::getInstance()->getPath().'/meta/flows/';
//
//		$validFile = false;
//		foreach($paths as $path)
//		{
//			$filePath = $path.$fileName.'.xml';
//			if(file_exists($filePath)) {
//				$validFile = true;
//				break;
//			}
//		}
//
//		return ((!$validFile) ? false : $filePath);
	}
}