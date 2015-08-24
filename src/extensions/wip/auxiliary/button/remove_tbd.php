<?php
/**
 * Class that shows the "remove" button in the backend
 *


  * @TODO review this file and clean up historical code/comments
 **/
class One_Button_Remove extends One_Button
{
	/**
	 * This method renders the "remove" button
	 *
	 * @return string
	 */
	public function render()
	{
		$output = '<td><a href="#" onclick="document.getElementById( \'task\' ).value = \'remove\'; document.getElementById( \''.One_Button::getFormId().'\' ).submit(); "><img src="' . One_Config::getInstance()->getUrl() . '/vendor/images/toolset/' . self::getToolset() . '/remove.png" title="Remove">';

		if( self::showText() )
			$output .= '<br />Remove';

		$output .= '</a></td>';

		return $output;
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
					);
	}
}