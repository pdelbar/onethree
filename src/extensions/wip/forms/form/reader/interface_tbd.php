<?php
/**
 * One_Form_Reader Interface
 *


  * @TODO review this file and clean up historical code/comments
 * @subpackage Form
 **/
interface One_Form_Reader_Interface
{
	/**
	 * Loads a form definition
	 *
	 * @param $schemeName
	 * @param $formFile
	 * @return One_Form_Container_Form
	 */
	public static function load($schemeName, $formFile = 'form');
}