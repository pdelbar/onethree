<?php
/**
 * The widgetFactory creates instances of widgets
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Widget_Factory
{
	/**
	 * Creates an instance of the specified widget
	 *
	 * @param string $type
	 * @return One_Form_Widget_Abstract
	 */
	public static function getInstance( $type )
	{
		$class = 'One_Form_Widget_' . ucfirst( strtolower( $type ) );

		if(class_exists($class))
		{
			$new = new $class();
			return $new;
		}
		else
			throw new One_Exception('There is no widget with type "' . $type . '"');
	}
}
