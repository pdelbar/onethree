<?php
class One_Routing
{
	/**
	 * Hashtable to easily find routing-options by alias
	 * @var array
	 */
	protected static $_aliasHash = array();

	/**
	 * Hashtable to easily find an alias by routing-options
	 * @var array
	 */
	protected static $_optionsHash = array();

	/**
	 * List of aliasses and the schemes that use this alias
	 * @var array
	 */
	protected static $_aliasSchemes = array();

	/**
	 * List of errors
	 * @var array
	 */
	protected static $_errors = array();

	/**
	 * Flag whether the schemes have been fetched so all routings are loaded
	 * @var boolean
	 */
	protected static $_schemesFetched = false;

	/**
	 * Retrieve list of all errors
	 * @return array
	 */
	public static function getErrors()
	{
		return self::$_errors;
	}

	/**
	 * Retrieve the last error that occured
	 * @return string
	 */
	public static function getError()
	{
		$errors = self::getErrors();

		if(0 < count($errors)) {
			return '';
		}

		return $errors[(count($errors) - 1)];
	}

	/**
	 * See whether errors occurred
	 * @return boolean
	 */
	public static function hasErrors()
	{
		return 0 < count(self::getErrors());
	}

	/**
	 * Add an error
	 * @param string $errorMsg
	 */
	public static function addError($errorMsg)
	{
		self::$_errors[] = $errorMsg;
	}

	/**
	 * Add a specified alias with options to the hashtables
	 * @param One_Scheme $alias
	 * @param string $alias
	 * @param array $options
	 * @param boolean $useId whether or not the alias uses an id
	 */
	public static function addAlias(One_Scheme $scheme, $alias, array $options, $useId = false)
	{
		if(!isset(self::$_aliasHash[$scheme->getName()]))
		{
			self::$_aliasHash[$scheme->getName()] = array();
			self::$_optionsHash[$scheme->getName()] = array();
		}

		if(array_key_exists($alias, self::$_aliasHash[$scheme->getName()]))
		{
			self::addError('Alias "'.$alias.'" already exists');
			return false;
		}

		array_change_key_case($options, CASE_LOWER);
		ksort($options, SORT_STRING);

		$aliasField = NULL;
		if(isset($options['aliasfield'])) {
			$aliasField = $options['aliasfield'];
		}

		$elseField = NULL;
		if(isset($options['else'])) {
			$elseField = $options['else'];
		}

		if(!isset($options['task']) || !isset($options['view']))
		{
			self::addError('At least task and view must be set');
			return false;
		}

		if(!isset(self::$_aliasHash[$scheme->getName()])) {
			self::$_aliasSchemes[$alias] = array();
		}

		self::$_aliasSchemes[$alias][] = $scheme->getName();
		self::$_aliasSchemes[$alias] = array_unique(self::$_aliasSchemes[$alias], SORT_STRING);

		self::$_aliasHash[$scheme->getName()][$alias] = array(
																'schemeName' => $scheme->getName(),
																'options' => $options,
																'useId' => $useId,
																'else' => $elseField,
																'aliasField' => $aliasField
															);

		$optionHash = md5($scheme->getName().$options['task'].$options['view']);

		self::$_optionsHash[$scheme->getName()][$optionHash] = array(
																		'schemeName' => $scheme->getName(),
																		'alias' => $alias,
																		'useId' => $useId,
																		'aliasField' => $aliasField
																	);

	}

	/**
	 * Check whether an alias for a given scheme exists
	 * @param One_Scheme $scheme
	 * @param string $alias
	 * @return boolean
	 */
	public static function aliasExists(One_Scheme $scheme, $alias)
	{
		if(!array_key_exists($scheme->getName(), self::$_aliasHash)) {
			return false;
		}

		return(array_key_exists($alias, self::$_aliasHash[$scheme->getName()]));
	}

	/**
	 * Return options for a specified alias for a specified scheme
	 * @param string $alias
	 * @return array Return options array if found, NULL if no match found
	 */
	public static function getOptionsForAlias($alias)
	{
		self::fetchAllRoutings();

		$withoutLast = preg_replace('/\/([^\/]+)$/', '', $alias);
    $use = null;

		if(array_key_exists($alias, self::$_aliasSchemes)) {
			$use = $alias;
		}
		else if(array_key_exists($withoutLast, self::$_aliasSchemes)) {
			$use = $withoutLast;
		}

    if ($use) {
      foreach(self::$_aliasSchemes[$use] as $schemeName) {
        if(array_key_exists($use, self::$_aliasHash[$schemeName])) {
          return self::$_aliasHash[$schemeName][$use];
        }
      }
		}

		return NULL;
	}

	/**
	 * Return alias for specified options for a specified scheme
	 * @param One_Scheme $scheme
	 * @param array $options
	 * @return array Return array if found, NULL if no match found
	 */
	public static function getAliasForOptions(One_Scheme $scheme, $options)
	{
		if(!array_key_exists($scheme->getName(), self::$_optionsHash)) {
			return NULL;
		}
		if(!isset($options['task']) || !isset($options['view'])) {
			return NULL;
		}

		$supposedHash = md5($scheme->getName().$options['task'].$options['view']);

		if(array_key_exists($supposedHash, self::$_optionsHash[$scheme->getName()])) {
			return self::$_optionsHash[$scheme->getName()][$supposedHash];
		}
		else {
      // modified, now take a look at standard actions
      $actionClass= 'One_Action_' . ucfirst($options['task']);
      if (class_exists($actionClass)) {
        return $actionClass::getStandardRouting($options);
      }
      else {
  			return NULL;
      }
		}
	}

	public static function fetchAllRoutings()
	{
		if(!self::$_schemesFetched) {
			$schemeNames = One::meta('schemes');
			foreach($schemeNames as $schemeName) {
				One_Repository::getScheme($schemeName);
			}
			self::$_schemesFetched = true;
		}
	}
}