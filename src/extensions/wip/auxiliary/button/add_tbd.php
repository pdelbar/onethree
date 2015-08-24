<?php
/**
 * Class that shows the "add" button in the backend
 *


  * @TODO review this file and clean up historical code/comments
 **/
class One_Button_Add extends One_Button
{
	/**
	 * This method renders the "add" button
	 *
	 * @return string
	 */
	public function render()
	{
		$output = '<td><a href="#" onclick="document.getElementById( \'task\' ).value = \'add\'; document.getElementById( \'view\' ).value = \'edit\'; document.getElementById( \''.One_Button::getFormId().'\' ).submit(); "><img src="' . One_Config::getInstance()->getUrl() . '/vendor/images/toolset/' . self::getToolset() . '/add.png" title="Add">';

		if( self::showText() )
			$output .= '<br />Add';

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
						'noflow' => true,
						'task' => 'edit'
					);
	}
}
