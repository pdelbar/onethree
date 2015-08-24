<?php
/**
 * Class that shows the "savefile" button in the backend
 *


  * @TODO review this file and clean up historical code/comments
 **/
class One_Button_Savefile extends One_Button
{
	/**
	 * This method renders the "savefile" button
	 *
	 * @return string
	 */
	public function render()
	{
		$output = '<td><a href="#" onclick="document.getElementById( \'code\' ).value = editorCode.getCode(); document.getElementById( \'task\' ).value = \'savefile\'; document.getElementById( \''.One_Button::getFormId().'\' ).submit(); "><img src="' . One_Config::getInstance()->getUrl() . '/vendor/images/toolset/' . self::getToolset() . '/backto.png" title="Save file">';

		if( self::showText() )
			$output .= '<br />Save File';

		$output .= '</a></td>';

		return $output;
	}

	/**
	 * This method returns the task that should be used for this tool
	 *
	 * @return string
	 */
	public function getTask()
	{
		return 'savefile';
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
}