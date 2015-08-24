<?php
/**
 * Class that shows the "schemes" button in the backend
 *


  * @TODO review this file and clean up historical code/comments
 **/
class One_Button_Schemes extends One_Button
{
	/**
	 * This method renders the "schemes" button
	 *
	 * @return string
	 */
	public function render()
	{
		$output = '<td><a href="#" onclick="document.getElementById( \'task\' ).value = \'schemes\'; document.getElementById( \''.One_Button::getFormId().'\' ).submit(); "><img src="' . One_Config::getInstance()->getUrl() . '/vendor/images/toolset/' . self::getToolset() . '/backto.png" title="Back To Schemes">';

		if( self::showText() )
			$output .= '<br />Back To Schemes';

		$output .= '</a></td>';

		return $output;
	}
}