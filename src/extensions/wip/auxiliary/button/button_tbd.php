<?php
/**
 *


  * @TODO review this file and clean up historical code/comments
 **/
abstract class One_Button implements One_Button_Interface
{
	private static $_toolset  = 'one';
	private static $_showText = true;
	private static $_formId  = 'oneForm';

	/**
	 * This method renders the "cancel" button
	 *
	 * @abstract
	 */
	public abstract function render();

	/**
	 * This method returns the task that should be used for this tool
	 *
	 * @return string
	 */
	public function getTask()
	{
		return NULL;
	}

	/**
	 * This method returns the options that should be used for this tool
	 *
	 * @return array
	 */
	public function getOptions()
	{
		return array();
	}

	public static function getToolset()
	{
		return ((trim(self::$_toolset) == '') ? 'one' : self::$_toolset);
	}

	public static function setToolset($toolset)
	{
		if (trim($toolset) == '')
			self::$_toolset = 'one';
		else
			self::$_toolset = $toolset;
	}

	public static function showText()
	{
		return self::$_showText;
	}

	public static function setShowText($showText = true)
	{
		self::$_showText = ($showText === true || intval($showText) == 1);
	}

	public static function getFormId()
	{
		return ((trim(self::$_formId) == '') ? 'oneForm' : self::$_formId);
	}

	public static function setFormId($formId = 'oneForm')
	{
		if (trim($formId) == '')
			self::$_formId = 'oneForm';
		else
			self::$_formId = $formId;
	}
}