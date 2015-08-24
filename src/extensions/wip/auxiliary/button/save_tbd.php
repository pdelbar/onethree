<?php
/**
 * Class that shows the "save" button in the backend
 *


  * @TODO review this file and clean up historical code/comments
 **/
class One_Button_Save extends One_Button
{
	/**
	 * This method renders the "save" button
	 *
	 * @return string
	 */
	public function render()
	{
		$output = '<td><a href="#" onclick="document.getElementById( \'task\' ).value = \'save\'; document.getElementById( \''.One_Button::getFormId().'\' ).submit(); "><img src="' . One_Config::getInstance()->getUrl() . '/vendor/images/toolset/' . self::getToolset() . '/save.png" title="Save">';

		if( self::showText() )
			$output .= '<br />Save';

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
		return 'edit';
	}

	/**
	 * This method returns the options that should be used for this tool
	 *
	 * @return array
	 */
	public function getOptions()
	{
		return array(
						'action' => 'save'
					);
	}
}